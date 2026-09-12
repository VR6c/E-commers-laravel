<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    /**
     * GET /api/orders
     * List all orders for the authenticated customer.
     */
    public function index(Request $request)
    {
        $customer = $request->user();

        $orders = Order::with(['details.product.thumbnail', 'shippingAddress'])
            ->where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->get();

        $data = $orders->map(function (Order $order) {
            $shipping = $order->shippingAddress;

            $nameParts = $shipping ? explode(' ', $shipping->name, 2) : [];
            $firstName = $nameParts[0] ?? '';
            $lastName  = $nameParts[1] ?? '';

            $items = $order->details->map(function ($detail) {
                $product = $detail->product;
                $thumbnailUrl = $product?->thumbnail?->image_url
                    ? product_image_url($product->thumbnail->image_url)
                    : ($product?->image_url ? product_image_url($product->image_url) : null);
                return [
                    'id'                => $detail->id,
                    'product_id'        => $detail->product_id,
                    'product_name'      => $product?->name ?? 'Unknown Product',
                    'product_thumbnail' => $thumbnailUrl,
                    'quantity'          => $detail->quantity,
                    'price'             => (float) $detail->price,
                ];
            });

            return [
                'id'              => $order->id,
                'status'          => $order->status,
                'total'           => (float) $order->total_amount,
                'coupon_code'     => $order->coupon_code,
                'discount_amount' => (float) $order->discount_amount,
                'first_name'      => $firstName,
                'last_name'       => $lastName,
                'phone'           => $shipping?->phone,
                'email'           => $order->guest_email,
                'address'         => $shipping?->address,
                'city'            => $shipping?->city,
                'country'         => $shipping?->country,
                'gateway'         => $order->payment_method ?? 'cod',
                'items'           => $items,
                'created_at'      => $order->created_at?->toISOString(),
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => $data,
        ]);
    }

    /**
     * GET /api/orders/{id}
     * Single order detail for the authenticated customer.
     */
    public function show(Request $request, int $id)
    {
        $customer = $request->user();

        $order = Order::with(['details.product.thumbnail', 'shippingAddress'])
            ->where('customer_id', $customer->id)
            ->find($id);

        if (! $order) {
            return response()->json(['status' => false, 'message' => 'Order not found.'], 404);
        }

        $shipping  = $order->shippingAddress;
        $nameParts = $shipping ? explode(' ', $shipping->name, 2) : [];

        $items = $order->details->map(function ($detail) {
            $product      = $detail->product;
            $thumbnailUrl = $product?->thumbnail?->image_url
                ? product_image_url($product->thumbnail->image_url)
                : null;

            return [
                'id'                => $detail->id,
                'product_id'        => $detail->product_id,
                'product_slug'      => $product?->slug,
                'product_name'      => $product?->name ?? 'Unknown Product',
                'product_thumbnail' => $thumbnailUrl,
                'quantity'          => $detail->quantity,
                'price'             => (float) $detail->price,
                'subtotal'          => (float) ($detail->price * $detail->quantity),
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => [
                'id'              => $order->id,
                'status'          => $order->status,
                'subtotal'        => (float) ($order->total_amount + $order->discount_amount),
                'discount_amount' => (float) $order->discount_amount,
                'coupon_code'     => $order->coupon_code,
                'total'           => (float) $order->total_amount,
                'gateway'         => $order->payment_method ?? 'cod',
                'email'           => $order->guest_email,
                'shipping'        => $shipping ? [
                    'first_name' => $nameParts[0] ?? '',
                    'last_name'  => $nameParts[1] ?? '',
                    'phone'      => $shipping->phone,
                    'address'    => $shipping->address,
                    'suite'      => $shipping->suite,
                    'city'       => $shipping->city,
                    'state'      => $shipping->state,
                    'country'    => $shipping->country,
                ] : null,
                'items'      => $items,
                'created_at' => $order->created_at?->toISOString(),
                'updated_at' => $order->updated_at?->toISOString(),
            ],
        ]);
    }
}
