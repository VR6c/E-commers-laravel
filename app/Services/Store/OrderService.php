<?php

declare(strict_types=1);

namespace App\Services\Store;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create an order with line items and shipping address in an ACID transaction.
     */
    public function createOrder(
        array $cartItems,
        array $shippingData,
        float $totalAmount,
        string $gatewayCode,
        ?int $customerId = null,
        ?string $guestEmail = null,
        ?string $couponCode = null,
        float $discountAmount = 0.0,
        string $status = 'pending'
    ): Order {
        return DB::transaction(function () use (
            $cartItems,
            $shippingData,
            $totalAmount,
            $gatewayCode,
            $customerId,
            $guestEmail,
            $couponCode,
            $discountAmount,
            $status
        ) {
            // Resolve vendor_id from first product
            $vendorId = null;
            if (! empty($cartItems)) {
                $firstItem = reset($cartItems);
                $firstProductId = $firstItem['product_id'] ?? array_key_first($cartItems);
                $vendorId = Product::where('id', $firstProductId)->value('vendor_id');
            }

            // 1. Create order
            $order = Order::create([
                'vendor_id'       => $vendorId,
                'customer_id'     => $customerId,
                'guest_email'     => $guestEmail,
                'total_amount'    => $totalAmount,
                'discount_amount' => $discountAmount,
                'coupon_code'     => $couponCode,
                'status'          => $status,
                'payment_method'  => $gatewayCode,
            ]);

            // 2. Save order line items
            foreach ($cartItems as $productId => $item) {
                $actualProductId = $item['product_id'] ?? $productId;
                OrderDetail::create([
                    'order_id'   => $order->id,
                    'product_id' => $actualProductId,
                    'quantity'   => (int) ($item['quantity'] ?? 1),
                    'price'      => (float) ($item['price'] ?? 0),
                ]);
            }

            // 3. Save shipping address
            $recipientName = trim(($shippingData['first_name'] ?? '') . ' ' . ($shippingData['last_name'] ?? ''));
            if (empty($recipientName)) {
                $recipientName = $shippingData['name'] ?? 'Customer';
            }

            ShippingAddress::create([
                'order_id'    => $order->id,
                'customer_id' => $customerId,
                'name'        => $recipientName,
                'phone'       => $shippingData['phone'] ?? '',
                'address'     => $shippingData['address'] ?? '',
                'suite'       => $shippingData['suite'] ?? null,
                'city'        => $shippingData['city'] ?? '',
                'state'       => $shippingData['state'] ?? null,
                'postal_code' => $shippingData['postal_code'] ?? null,
                'country'     => $shippingData['country'] ?? '',
            ]);

            return $order->load(['details.product', 'shippingAddress']);
        });
    }

    /**
     * Create a new order from PayPal payment and session cart.
     */
    public function createOrderFromPaypal(array $paypalResult): Order
    {
        $payer = $paypalResult['payer'] ?? [];
        $purchaseUnit = $paypalResult['purchase_units'][0] ?? [];
        $amount = (float) ($purchaseUnit['payments']['captures'][0]['amount']['value'] ?? 0);

        $cart = session('cart', []);
        $shippingData = session('checkout.shipping', []);
        $customerId = Auth::guard('customer')->id() ?? Auth::id();

        $order = $this->createOrder(
            cartItems: $cart,
            shippingData: [
                'name'        => $shippingData['name'] ?? trim(($payer['name']['given_name'] ?? '') . ' ' . ($payer['name']['surname'] ?? '')),
                'phone'       => $shippingData['phone'] ?? '',
                'address'     => $shippingData['address'] ?? ($purchaseUnit['shipping']['address']['address_line_1'] ?? ''),
                'city'        => $shippingData['city'] ?? ($purchaseUnit['shipping']['address']['admin_area_2'] ?? ''),
                'postal_code' => $shippingData['postal_code'] ?? ($purchaseUnit['shipping']['address']['postal_code'] ?? null),
                'country'     => $shippingData['country'] ?? ($purchaseUnit['shipping']['address']['country_code'] ?? ''),
            ],
            totalAmount: $amount,
            gatewayCode: 'paypal',
            customerId: $customerId,
            guestEmail: $payer['email_address'] ?? null,
            status: 'completed'
        );

        session()->forget(['cart', 'checkout.shipping']);

        return $order;
    }
}
