<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ShippingAddress;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Services\Store\OrderService;
use App\Services\PaymentGateway\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.view')
                ->with('error', 'Your cart is empty.');
        }

        $paymentGateways = PaymentGateway::with('configs')
            ->where('is_active', 1)
            ->get();

        $paypal = $paymentGateways->firstWhere('code', 'paypal');
        $paypalClientId = $paypal
            ? $paypal->getConfigValue('client_id', 'sandbox')
            : null;

        $subtotal = 0;

        foreach ($cart as $key => $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $shipping = null;

        // Apply coupon discount if one is stored in the session
        $coupon = Session::get('cart_coupon');
        $discountAmount = 0;
        if ($coupon) {
            $discountAmount = $coupon['type'] === 'percentage'
                ? $subtotal * ($coupon['discount'] / 100)
                : $coupon['discount'];
        }
        $total = max(0, $subtotal - $discountAmount + ($shipping ?? 0));

        // Load countries for the shipping form
        $countriesJson = file_get_contents(resource_path('data/countries.json'));
        $countries = json_decode($countriesJson, true);

        return view('themes.xylo.checkout', compact('cart', 'subtotal', 'shipping', 'total', 'coupon', 'discountAmount', 'paymentGateways', 'paypalClientId', 'countries'));
    }

    /**
     * Return all countries as JSON (for AJAX or initial load)
     */
    public function countries()
    {
        $countriesJson = file_get_contents(resource_path('data/countries.json'));
        $countries = json_decode($countriesJson, true);

        return response()->json(
            collect($countries)->map(fn($c) => ['code' => $c['code'], 'name' => $c['name']])
        );
    }

    /**
     * Return states for a given country code as JSON (AJAX)
     */
    public function states(string $countryCode)
    {
        $countriesJson = file_get_contents(resource_path('data/countries.json'));
        $countries = collect(json_decode($countriesJson, true));

        $country = $countries->firstWhere('code', strtoupper($countryCode));

        if (!$country) {
            return response()->json([]);
        }

        return response()->json($country['states']);
    }

    public function process(Request $request)
    {
        $request->validate([
            'gateway' => 'required|string|max:50',
        ]);

        $gatewayCode = $request->input('gateway');

        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty!'
            ], 400);
        }

        // Calculate total
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $shipping = 0;

        // Apply coupon discount from session
        $coupon = Session::get('cart_coupon');
        $discountAmount = 0;
        if ($coupon) {
            $discountAmount = $coupon['type'] === 'percentage'
                ? $subtotal * ($coupon['discount'] / 100)
                : $coupon['discount'];
        }
        $total = max(0, $subtotal - $discountAmount + $shipping);

        try {
            $paymentService = PaymentManager::make($gatewayCode, 'sandbox');

            if ($gatewayCode === 'abapayway') {
                $request->validate([
                    'first_name' => 'required|string|max:100',
                    'last_name' => 'required|string|max:100',
                    'address' => 'required|string|max:255',
                    'suite' => 'nullable|string|max:100',
                    'city' => 'required|string|max:100',
                    'state' => 'nullable|string|max:100',
                    'country' => 'required|string|max:100',
                    'email' => 'required|email|max:50',
                    'phone' => 'required|string|max:20',
                ]);

                // 1. Create order
                $order = Order::create([
                    'customer_id' => Auth::guard('customer')->check() ? Auth::guard('customer')->id() : null,
                    'guest_email' => $request->input('email'),
                    'total_amount' => $total,
                    'status' => 'pending',
                ]);

                // 2. Save order details
                foreach ($cart as $productId => $item) {
                    OrderDetail::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }

                // 3. Save shipping address
                ShippingAddress::create([
                    'order_id' => $order->id,
                    'customer_id' => Auth::guard('customer')->check() ? Auth::guard('customer')->id() : null,
                    'name' => $request->input('first_name') . ' ' . $request->input('last_name'),
                    'phone' => $request->input('phone'),
                    'address' => $request->input('address'),
                    'suite' => $request->input('suite'),
                    'city' => $request->input('city'),
                    'state' => $request->input('state'),
                    'postal_code' => null,
                    'country' => $request->input('country'),
                ]);

                // 4. Build PayWay fields
                $reqTime = now()->utc()->format('YmdHis');
                $tranId = 'ORD-' . $order->id . '-' . time();

                $itemsData = [];
                foreach ($cart as $item) {
                    $itemsData[] = [
                        'name' => $item['name'] ?? 'Product',
                        'quantity' => $item['quantity'],
                        'price' => number_format($item['price'], 2, '.', ''),
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
                    'payment_option' => '', // show all options
                    'return_url' => base64_encode(route('payway.callback')),
                    'cancel_url' => route('payway.cancel'),
                    'continue_success_url' => route('payway.success'),
                    'return_deeplink' => '',
                    'currency' => 'USD',
                    'custom_fields' => '',
                    'return_params' => (string)$order->id,
                    'payout' => '',
                    'lifetime' => 45,
                    'additional_params' => '',
                    'google_pay_token' => '',
                    'skip_success_page' => 0,
                ];

                $paywayParams['hash'] = $paymentService->generateHash($paywayParams);

                // Save transition state in session
                Session::put('last_order_id', $order->id);
                Session::put('last_tran_id', $tranId);

                // Send the POST request to PayWay directly from the backend
                $response = \Illuminate\Support\Facades\Http::asForm()->post($paymentService->getCheckoutUrl(), $paywayParams);
                $body = $response->body();

                $decoded = json_decode($body, true);
                $isJson = (json_last_error() === JSON_ERROR_NONE);

                if ($isJson) {
                    return response()->json([
                        'success' => true,
                        'gateway' => 'abapayway',
                        'response_type' => 'json',
                        'amount' => number_format($total, 2, '.', ''),
                        'currency' => 'USD',
                        'tran_id' => $tranId,
                        'data' => $decoded,
                    ]);
                } else {
                    Session::put('abapayway_html', $body);
                    return response()->json([
                        'success' => true,
                        'gateway' => 'abapayway',
                        'response_type' => 'html',
                        'redirect_url' => route('payway.hosted'),
                    ]);
                }
            }

            $order = $paymentService->createOrder($total, 'USD');

            return response()->json([
                'success' => true,
                'gateway' => $gatewayCode,
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            Log::error('Payment process failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ABA PayWay server-to-server callback (pushback webhook)
     */
    public function paywayCallback(Request $request)
    {
        Log::info('ABA PayWay webhook callback received: ', $request->all());

        $receivedSignature = $request->header('X-Payway-Hmac-Sha512') ?? '';
        
        try {
            $payway = new \App\Services\PaymentGateway\ABAPayWayService('sandbox');
            $data = $request->all();

            if (!$payway->verifyCallbackSignature($data, $receivedSignature)) {
                Log::warning('ABA PayWay callback signature mismatch!');
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $orderId = $data['return_params'] ?? null;
            $status = $data['status'] ?? null;

            if ($orderId) {
                $order = Order::find($orderId);
                if ($order) {
                    if ($status === '0') {
                        $order->status = 'completed';
                        $order->save();

                        $gateway = PaymentGateway::where('code', 'abapayway')->first();

                        Payment::firstOrCreate(
                            ['transaction_id' => $data['tran_id'] ?? null],
                            [
                                'order_id' => $order->id,
                                'user_id' => \App\Models\User::first()->id ?? 1,
                                'gateway_id' => $gateway ? $gateway->id : 1,
                                'amount' => $order->total_amount,
                                'currency' => 'USD',
                                'status' => 'completed',
                                'response' => $data,
                                'meta' => ['apv' => $data['apv'] ?? ''],
                            ]
                        );
                        Log::info("Order #{$order->id} paid successfully via PayWay webhook.");
                    } else {
                        $order->status = 'canceled';
                        $order->save();
                        Log::info("Order #{$order->id} payment failed via PayWay webhook status: {$status}");
                    }
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('PayWay Webhook processing error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * ABA PayWay Success User Redirect Page
     */
    public function paywaySuccess(Request $request)
    {
        $orderId = Session::get('last_order_id');
        $tranId = Session::get('last_tran_id');

        if (!$orderId || !$tranId) {
            return redirect()->route('xylo.home');
        }

        try {
            $payway = new \App\Services\PaymentGateway\ABAPayWayService('sandbox');
            $result = $payway->checkTransaction($tranId);

            Log::info("PayWay check transaction result on success redirect: ", $result);

            $paymentStatus = $result['data']['payment_status'] ?? null;
            
            if ($paymentStatus === 'APPROVED' || ($result['data']['payment_status_code'] ?? null) === 0) {
                $order = Order::find($orderId);
                if ($order) {
                    if ($order->status !== 'completed') {
                        $order->status = 'completed';
                        $order->save();
                    }

                    $gateway = PaymentGateway::where('code', 'abapayway')->first();

                    Payment::firstOrCreate(
                        ['transaction_id' => $tranId],
                        [
                            'order_id' => $order->id,
                            'user_id' => \App\Models\User::first()->id ?? 1,
                            'gateway_id' => $gateway ? $gateway->id : 1,
                            'amount' => $order->total_amount,
                            'currency' => 'USD',
                            'status' => 'completed',
                            'response' => $result,
                            'meta' => ['apv' => $result['data']['apv'] ?? ''],
                        ]
                    );

                    // Clear cart
                    Session::forget('cart');
                    return redirect()->route('thankyou')->with('success', 'Payment successful!');
                }
            }

            return redirect()->route('checkout.index')->with('error', 'Payment verification pending or failed.');
        } catch (\Exception $e) {
            Log::error('PayWay user redirect success verification error: ' . $e->getMessage());
            return redirect()->route('checkout.index')->with('error', 'Error verifying payment: ' . $e->getMessage());
        }
    }

    /**
     * ABA PayWay Cancel User Redirect
     */
    public function paywayCancel()
    {
        $orderId = Session::get('last_order_id');
        if ($orderId) {
            $order = Order::find($orderId);
            if ($order && $order->status === 'pending') {
                $order->status = 'canceled';
                $order->save();
            }
        }

        return redirect()->route('checkout.index')->with('error', 'Payment was canceled by the user.');
    }

    /**
     * Storefront Thank You success page
     */
    public function thankYou()
    {
        $orderId = Session::get('last_order_id');
        if (!$orderId) {
            return redirect()->route('xylo.home');
        }

        $order = Order::with(['details.product.thumbnail'])->find($orderId);
        if (!$order) {
            return redirect()->route('xylo.home');
        }

        return view('themes.xylo.thankyou', compact('order'));
    }

    /**
     * Renders raw PayWay hosted HTML payment page from session
     */
    public function paywayHosted()
    {
        $html = Session::get('abapayway_html');
        if (!$html) {
            return redirect()->route('checkout.index');
        }
        return response($html);
    }

    /**
     * Checks payment status on PayWay server (used for AJAX polling)
     */
    public function checkPaywayStatus($tranId)
    {
        try {
            $payway = new \App\Services\PaymentGateway\ABAPayWayService('sandbox');
            $result = $payway->checkTransaction($tranId);
            
            $paymentStatus = $result['data']['payment_status'] ?? null;
            $approved = ($paymentStatus === 'APPROVED' || ($result['data']['payment_status_code'] ?? null) === 0);

            if ($approved) {
                $orderId = Session::get('last_order_id');
                if ($orderId) {
                    $order = Order::find($orderId);
                    if ($order && $order->status !== 'completed') {
                        $order->status = 'completed';
                        $order->save();

                        $gateway = PaymentGateway::where('code', 'abapayway')->first();
                        Payment::firstOrCreate(
                            ['transaction_id' => $tranId],
                            [
                                'order_id' => $order->id,
                                'user_id' => \App\Models\User::first()->id ?? 1,
                                'gateway_id' => $gateway ? $gateway->id : 1,
                                'amount' => $order->total_amount,
                                'currency' => 'USD',
                                'status' => 'completed',
                                'response' => $result,
                                'meta' => ['apv' => $result['data']['apv'] ?? ''],
                            ]
                        );
                        Session::forget('cart');
                    }
                }
            }

            return response()->json([
                'success' => true,
                'approved' => $approved,
                'status' => $paymentStatus
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * PayPal success callback
     */
    public function paypalSuccess(Request $request, OrderService $orderService)
    {
        $orderId = $request->query('token');

        try {
            $paypal = PaymentManager::make('paypal', 'sandbox');
            $result = $paypal->captureOrder($orderId);

            if (($result['status'] ?? null) === 'COMPLETED') {
                $order = $orderService->createOrderFromPaypal($result);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment completed & order stored successfully.',
                    'order_id' => $order->id,
                    'details' => $result,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment not completed.',
                'details' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('PayPal success error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PayPal cancel callback
     */
    public function paypalCancel()
    {
        return response()->json([
            'success' => false,
            'message' => 'Payment was cancelled by user.',
        ]);
    }

    public function store(Request $request)
    {
        // Delegate all payment logic to process(), which handles all gateways
        // and returns a JSON response expected by the checkout page JS.
        return $this->process($request);
    }

    /**
     * Hosted Payment Page for mobile WebView
     */
    public function paywayHostedMobile($orderId)
    {
        $order = Order::with('details.product')->findOrFail($orderId);
        
        if ($order->status !== 'pending') {
            return response('Order is already paid or canceled.', 400);
        }

        $paymentService = \App\Services\PaymentGateway\PaymentManager::make('abapayway', 'sandbox');

        // Resolve name and contact info from shipping address
        $shipping = \App\Models\ShippingAddress::where('order_id', $order->id)->first();
        
        $nameParts = explode(' ', $shipping ? $shipping->name : 'Customer Name', 2);
        $firstName = $nameParts[0] ?? 'Customer';
        $lastName = $nameParts[1] ?? 'Customer';
        $phone = $shipping ? $shipping->phone : '000000000';
        $email = $order->guest_email ?? ($order->customer ? $order->customer->email : 'customer@example.com');

        $reqTime = now()->utc()->format('YmdHis');
        $tranId = 'ORD-' . $order->id . '-' . time();

        // Build items data for ABA transaction
        $itemsData = [];
        foreach ($order->details as $detail) {
            $itemsData[] = [
                'name' => $detail->product->name ?? 'Product',
                'quantity' => $detail->quantity,
                'price' => number_format($detail->price, 2, '.', ''),
            ];
        }
        $itemsBase64 = base64_encode(json_encode($itemsData));

        $paywayParams = [
            'req_time' => $reqTime,
            'merchant_id' => $paymentService->getMerchantId(),
            'tran_id' => $tranId,
            'amount' => number_format($order->total_amount, 2, '.', ''),
            'items' => $itemsBase64,
            'shipping' => '0.00',
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'type' => 'purchase',
            'payment_option' => '', // show all options
            'return_url' => base64_encode(route('payway.callback')),
            'cancel_url' => route('payway.cancel'),
            'continue_success_url' => route('payway.success'),
            'return_deeplink' => '',
            'currency' => 'USD',
            'custom_fields' => '',
            'return_params' => (string)$order->id,
            'payout' => '',
            'lifetime' => 45,
            'additional_params' => '',
            'google_pay_token' => '',
            'skip_success_page' => 1, // Skip success page to redirect directly on successful payment
        ];

        $paywayParams['hash'] = $paymentService->generateHash($paywayParams);

        // Keep track of the transition state in the user's session if needed (though webhook/polling works stateless)
        Session::put('last_order_id', $order->id);
        Session::put('last_tran_id', $tranId);

        $checkoutUrl = $paymentService->getCheckoutUrl();

        // Output HTML that self-submits the form to PayWay HPP
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Redirecting to PayWay...</title>
        </head>
        <body>
            <form id="payway-form" action="' . $checkoutUrl . '" method="post">';
        foreach ($paywayParams as $key => $value) {
            $html .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
        }
        $html .= '
            </form>
            <div style="text-align: center; margin-top: 100px; font-family: sans-serif;">
                <h2>Redirecting to secure payment page...</h2>
                <noscript><button type="submit" form="payway-form">Click here if not redirected automatically</button></noscript>
            </div>
            <script>
                document.getElementById("payway-form").submit();
            </script>
        </body>
        </html>';

        return response($html);
    }
}
