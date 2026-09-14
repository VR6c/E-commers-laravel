<?php

namespace App\Services\Store;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    /**
     * Create a new order from PayPal payment and session cart.
     *
     * @param  array  $paypalResult  The PayPal API capture result
     */
    public function createOrderFromPaypal(array $paypalResult): Order
    {
        // Extract PayPal payer & order data
        $payer = $paypalResult['payer'] ?? [];
        $purchaseUnit = $paypalResult['purchase_units'][0] ?? [];
        $amount = $purchaseUnit['payments']['captures'][0]['amount']['value'] ?? 0;

        // Create Order
        $cart = session('cart', []);

        // Resolve vendor_id from the first product in the cart
        $vendorId = null;
        if (!empty($cart)) {
            $firstProductId = array_key_first($cart);
            $vendorId = Product::where('id', $firstProductId)->value('vendor_id');
        }

        $order = Order::create([
            'vendor_id'      => $vendorId,
            'customer_id'    => Auth::check() ? Auth::id() : null,
            'guest_email'    => $payer['email_address'] ?? null,
            'total_amount'   => $amount,
            'status'         => 'completed',
            'payment_method' => 'paypal',
        ]);

        // Create Order Details from cart session
        foreach ($cart as $productId => $item) {
            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }

        // Create Shipping Address if available
        $shippingData = session('checkout.shipping');
        if ($shippingData) {
            ShippingAddress::create([
                'order_id' => $order->id,
                'customer_id' => Auth::check() ? Auth::id() : null,
                'name' => $shippingData['name'],
                'phone' => $shippingData['phone'],
                'address' => $shippingData['address'],
                'city' => $shippingData['city'],
                'postal_code' => $shippingData['postal_code'],
                'country' => $shippingData['country'],
            ]);
        }

        // Clear cart session
        session()->forget(['cart', 'checkout.shipping']);

        return $order;
    }
}
