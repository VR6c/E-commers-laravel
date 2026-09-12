<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ShippingAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CheckoutApiController extends Controller
{
    public function process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'suite' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'required|string|max:100',
            'email' => 'required|email|max:50',
            'phone' => 'required|string|max:20',
            'gateway' => 'required|string|in:abapayway,cod',
            'cart' => 'required|array|min:1',
            'cart.*.product_id' => 'required|integer|exists:products,id',
            'cart.*.quantity' => 'required|integer|min:1',
            'cart.*.variant_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Clear any lingering aborted transaction state on pooled connections (e.g. Supabase / Neon / PgBouncer)
        try {
            $pdo = DB::connection()->getPdo();
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        } catch (\Throwable $t) {
            try {
                DB::connection()->getPdo()->exec('ROLLBACK;');
            } catch (\Throwable $t2) {
            }
        }

        $gateway = $request->input('gateway');
        $cartItems = $request->input('cart');
        $customer = $request->user('sanctum') ?? $request->user();

        try {
            // 1. Calculate total and verify prices from DB (Read-only, outside transaction)
            $subtotal = 0;
            $itemsWithDetails = [];
            foreach ($cartItems as $item) {
                $product = Product::with(['variants'])->findOrFail($item['product_id']);

                // Find variant if variant_id passed, else primary variant, else first variant
                $variant = null;
                if (!empty($item['variant_id'])) {
                    $variant = $product->variants->firstWhere('id', (int) $item['variant_id']);
                }
                $variant ??= $product->primaryVariant
                    ?? $product->variants->firstWhere('is_primary', true)
                    ?? $product->variants->first();

                // Determine effective unit price (respecting discount_price)
                if ($variant) {
                    if ($variant->discount_price !== null && (float) $variant->discount_price > 0) {
                        $price = $variant->getConvertedDiscountPriceAttribute();
                    } elseif ($variant->price !== null && (float) $variant->price > 0) {
                        $price = $variant->getConvertedPriceAttribute();
                    } else {
                        $price = $product->getConvertedPriceAttribute();
                    }
                } else {
                    $discountPrice = $product->getConvertedDiscountPriceAttribute();
                    if ($discountPrice !== null && (float) $discountPrice > 0) {
                        $price = $discountPrice;
                    } else {
                        $price = $product->getConvertedPriceAttribute();
                    }
                }

                $price = (float) $price;
                $quantity = (int) $item['quantity'];

                $subtotal += $price * $quantity;
                $itemsWithDetails[] = [
                    'product'  => $product,
                    'variant'  => $variant,
                    'price'    => $price,
                    'quantity' => $quantity,
                ];
            }

            // 2. Apply coupon discount if provided
            $discountAmount = 0;
            $couponCode = $request->input('coupon_code');
            if ($couponCode) {
                $coupon = Coupon::where('code', trim($couponCode))->first();
                if ($coupon && !$coupon->isExpired()) {
                    if ($coupon->type === 'percentage') {
                        $discountAmount = $subtotal * ($coupon->discount / 100);
                    } else {
                        $discountAmount = $coupon->discount;
                    }
                }
            }
            $total = max(0, $subtotal - $discountAmount);

            // 3. Resolve vendor_id from first product
            $firstProduct = $itemsWithDetails[0]['product'];
            $vendorId = $firstProduct->vendor_id;

            // 4. Begin transaction ONLY for database persistence
            DB::beginTransaction();

            // 5. Create Order
            $order = Order::create([
                'vendor_id'       => $vendorId,
                'customer_id'     => $customer ? $customer->id : null,
                'guest_email'     => $customer ? $customer->email : $request->input('email'),
                'total_amount'    => $total,
                'coupon_code'     => $discountAmount > 0 ? $couponCode : null,
                'discount_amount' => $discountAmount,
                'status'          => $gateway === 'abapayway' ? 'processing' : 'pending',
                'payment_method'  => $gateway,
            ]);

            // 6. Create Order Details
            foreach ($itemsWithDetails as $detail) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $detail['product']->id,
                    'quantity' => $detail['quantity'],
                    'price' => $detail['price'],
                ]);
            }

            // 7. Create Shipping Address
            ShippingAddress::create([
                'order_id' => $order->id,
                'customer_id' => $customer ? $customer->id : null,
                'name' => $request->input('first_name') . ' ' . $request->input('last_name'),
                'phone' => $request->input('phone'),
                'address' => $request->input('address'),
                'suite' => $request->input('suite'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'postal_code' => null,
                'country' => $request->input('country'),
            ]);

            DB::commit();

            // 7. Generate Response
            if ($gateway === 'abapayway') {
                $paymentService = \App\Services\PaymentGateway\PaymentManager::make('abapayway', 'sandbox');

                $reqTime = now()->utc()->format('YmdHis');
                $tranId = 'ORD-' . $order->id . '-' . time();

                // Build items
                $itemsData = [];
                foreach ($itemsWithDetails as $detail) {
                    $name = $detail['product']->name ?? 'Product';
                    $variantName = $detail['variant']->name ?? null;
                    if ($variantName && !in_array(strtolower($variantName), ['default', 'standard'])) {
                        $name .= ' (' . $variantName . ')';
                    }
                    $itemsData[] = [
                        'name'     => $name,
                        'quantity' => $detail['quantity'],
                        'price'    => number_format($detail['price'], 2, '.', ''),
                    ];
                }
                $itemsBase64 = base64_encode(json_encode($itemsData));

                $paywayParams = [
                    'req_time' => $reqTime,
                    'merchant_id' => $paymentService->getMerchantId(),
                    'tran_id' => $tranId,
                    'amount' => number_format($total, 2, '.', ''),
                    'items' => $itemsBase64,
                    'shipping' => '0.00',
                    'firstname' => $request->input('first_name'),
                    'lastname' => $request->input('last_name'),
                    'email' => $request->input('email'),
                    'phone' => $request->input('phone'),
                    'type' => 'purchase',
                    'payment_option' => 'abapay_khqr', // Focus on KHQR and Deep links!
                    'return_url' => base64_encode(route('payway.callback')),
                    'cancel_url' => route('payway.cancel'),
                    'continue_success_url' => route('payway.success'),
                    'return_deeplink' => 'flutterecommerce://', // Custom deep link to return to our app!
                    'currency' => 'USD',
                    'custom_fields' => '',
                    'return_params' => (string)$order->id,
                    'payout' => '',
                    'lifetime' => 45,
                    'additional_params' => '',
                    'google_pay_token' => '',
                    'skip_success_page' => 1,
                ];

                $paywayParams['hash'] = $paymentService->generateHash($paywayParams);

                // Call ABA PayWay server-to-server POST
                $response = \Illuminate\Support\Facades\Http::asForm()->post($paymentService->getCheckoutUrl(), $paywayParams);
                $paywayBody = $response->body();
                $paywayJson = json_decode($paywayBody, true);

                if (json_last_error() === JSON_ERROR_NONE && isset($paywayJson['qrString'])) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Order created successfully',
                        'data' => [
                            'order_id' => $order->id,
                            'total_amount' => number_format($total, 2, '.', ''),
                            'gateway' => $gateway,
                            'abapay_deeplink' => $paywayJson['abapay_deeplink'] ?? null,
                            'qrString' => $paywayJson['qrString'] ?? null,
                            'qrImage' => $paywayJson['qrImage'] ?? null,
                            'tran_id' => $tranId,
                            'check_status_url' => route('payway.status', ['tran_id' => $tranId]),
                        ]
                    ]);
                } else {
                    Log::error('PayWay API call failed or did not return QR info: ' . $paywayBody);
                    return response()->json([
                        'status' => false,
                        'message' => 'Failed to generate ABA PayWay payment: ' . ($paywayJson['description'] ?? 'Invalid response')
                    ], 500);
                }
            } else {
                // Cash on Delivery
                $order->status = 'pending';
                $order->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Order created successfully',
                    'data' => [
                        'order_id' => $order->id,
                        'total_amount' => number_format($total, 2, '.', ''),
                        'gateway' => $gateway,
                        'abapay_deeplink' => null,
                        'qrString' => null,
                        'qrImage' => null,
                        'tran_id' => null,
                    ]
                ]);
            }

        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error('API Checkout creation failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to process checkout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Flutter polls this to check if ABA PayWay payment has been approved.
     * Uses order_id directly — no web session needed.
     */
    public function checkPaymentStatus(Request $request)
    {
        $tranId  = $request->input('tran_id');
        $orderId = $request->input('order_id');

        if (!$tranId || !$orderId) {
            return response()->json(['status' => false, 'message' => 'tran_id and order_id are required'], 422);
        }

        try {
            $payway = new \App\Services\PaymentGateway\ABAPayWayService('sandbox');
            $result = $payway->checkTransaction($tranId);

            $paymentStatus = $result['data']['payment_status'] ?? null;
            $approved = ($paymentStatus === 'APPROVED' || ($result['data']['payment_status_code'] ?? null) === 0);

            if ($approved) {
                $order = Order::find($orderId);
                if ($order && $order->status !== 'completed') {
                    $order->status = 'completed';
                    $order->save();
                }
            }

            return response()->json([
                'status'   => true,
                'approved' => $approved,
                'payment_status' => $paymentStatus,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
