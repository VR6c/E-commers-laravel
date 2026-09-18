<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckoutProcessRequest;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Services\PaymentGateway\ABAPayWayService;
use App\Services\PaymentGateway\PaymentManager;
use App\Services\Store\CartService;
use App\Services\Store\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckoutApiController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService
    ) {}

    public function process(CheckoutProcessRequest $request): JsonResponse
    {
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
            $subtotal = 0.0;
            $itemsWithDetails = [];
            foreach ($cartItems as $item) {
                $product = Product::with(['variants'])->findOrFail($item['product_id']);

                // Find variant if variant_id passed, else primary variant, else first variant
                $variant = null;
                if (! empty($item['variant_id'])) {
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
                    'product'    => $product,
                    'variant'    => $variant,
                    'product_id' => $product->id,
                    'price'      => $price,
                    'quantity'   => $quantity,
                ];
            }

            // 2. Apply coupon discount if provided
            $discountAmount = 0.0;
            $couponCode = $request->input('coupon_code');
            if ($couponCode) {
                $coupon = Coupon::where('code', trim($couponCode))->first();
                if ($coupon && ! $coupon->isExpired()) {
                    $discountAmount = $this->cartService->calculateDiscount((float) $subtotal, $coupon);
                }
            }
            $total = max(0.0, round($subtotal - $discountAmount, 2));

            // 3. Create Order, OrderDetails, and Shipping Address via OrderService
            $orderStatus = $gateway === 'abapayway' ? 'processing' : 'pending';
            $order = $this->orderService->createOrder(
                cartItems: $itemsWithDetails,
                shippingData: $request->only([
                    'first_name', 'last_name', 'address', 'suite', 'city', 'state', 'country', 'phone',
                ]),
                totalAmount: $total,
                gatewayCode: $gateway,
                customerId: $customer ? $customer->id : null,
                guestEmail: $customer ? $customer->email : $request->input('email'),
                couponCode: $discountAmount > 0 ? $couponCode : null,
                discountAmount: $discountAmount,
                status: $orderStatus
            );

            // 4. Generate Gateway Response
            if ($gateway === 'abapayway') {
                $paymentService = PaymentManager::make('abapayway', 'sandbox');

                $reqTime = now()->utc()->format('YmdHis');
                $tranId = 'ORD-' . $order->id . '-' . time();

                // Build items payload
                $itemsData = [];
                foreach ($itemsWithDetails as $detail) {
                    $name = $detail['product']->name ?? 'Product';
                    $variantName = $detail['variant']->name ?? null;
                    if ($variantName && ! in_array(strtolower($variantName), ['default', 'standard'])) {
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
                    'req_time'             => $reqTime,
                    'merchant_id'          => $paymentService->getMerchantId(),
                    'tran_id'              => $tranId,
                    'amount'               => number_format($total, 2, '.', ''),
                    'items'                => $itemsBase64,
                    'shipping'             => '0.00',
                    'firstname'            => $request->input('first_name'),
                    'lastname'             => $request->input('last_name'),
                    'email'                => $request->input('email'),
                    'phone'                => $request->input('phone'),
                    'type'                 => 'purchase',
                    'payment_option'       => 'abapay_khqr',
                    'return_url'           => base64_encode(route('payway.callback')),
                    'cancel_url'           => route('payway.cancel'),
                    'continue_success_url' => route('payway.success'),
                    'return_deeplink'      => 'flutterecommerce://',
                    'currency'             => 'USD',
                    'custom_fields'        => '',
                    'return_params'        => (string) $order->id,
                    'payout'               => '',
                    'lifetime'             => 45,
                    'additional_params'    => '',
                    'google_pay_token'     => '',
                    'skip_success_page'    => 1,
                ];

                $paywayParams['hash'] = $paymentService->generateHash($paywayParams);

                // Call ABA PayWay server-to-server POST
                $response = Http::asForm()->post($paymentService->getCheckoutUrl(), $paywayParams);
                $paywayBody = $response->body();
                $paywayJson = json_decode($paywayBody, true);

                if (json_last_error() === JSON_ERROR_NONE && isset($paywayJson['qrString'])) {
                    return response()->json([
                        'status'  => true,
                        'message' => 'Order created successfully',
                        'data'    => [
                            'order_id'         => $order->id,
                            'total_amount'     => number_format($total, 2, '.', ''),
                            'gateway'          => $gateway,
                            'abapay_deeplink'  => $paywayJson['abapay_deeplink'] ?? null,
                            'qrString'         => $paywayJson['qrString'] ?? null,
                            'qrImage'          => $paywayJson['qrImage'] ?? null,
                            'tran_id'          => $tranId,
                            'check_status_url' => route('payway.status', ['tran_id' => $tranId]),
                        ],
                    ]);
                } else {
                    Log::error('PayWay API call failed or did not return QR info: ' . $paywayBody);

                    return response()->json([
                        'status'  => false,
                        'message' => 'Failed to generate ABA PayWay payment: ' . ($paywayJson['description'] ?? 'Invalid response'),
                    ], 500);
                }
            } else {
                // Cash on Delivery
                $order->status = 'pending';
                $order->save();

                return response()->json([
                    'status'  => true,
                    'message' => 'Order created successfully',
                    'data'    => [
                        'order_id'        => $order->id,
                        'total_amount'    => number_format($total, 2, '.', ''),
                        'gateway'         => $gateway,
                        'abapay_deeplink' => null,
                        'qrString'        => null,
                        'qrImage'         => null,
                        'tran_id'         => null,
                    ],
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
                'status'  => false,
                'message' => 'Failed to process checkout: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Flutter polls this to check if ABA PayWay payment has been approved.
     * Uses order_id directly — no web session needed.
     */
    public function checkPaymentStatus(Request $request): JsonResponse
    {
        $tranId  = $request->input('tran_id');
        $orderId = $request->input('order_id');

        if (! $tranId || ! $orderId) {
            return response()->json(['status' => false, 'message' => 'tran_id and order_id are required'], 422);
        }

        try {
            $payway = new ABAPayWayService('sandbox');
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
                'status'         => true,
                'approved'       => $approved,
                'payment_status' => $paymentStatus,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
