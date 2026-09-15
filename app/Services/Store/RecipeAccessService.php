<?php

namespace App\Services\Store;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Recipe;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Illuminate\Support\Collection;

class RecipeAccessService
{
    /**
     * Check if a given recipe is accessible/unlocked.
     */
    public function hasAccess(Recipe $recipe, ?Customer $customer = null, ?int $orderId = null, ?string $token = null): bool
    {
        // 1. Free recipe with no linked products
        if ((float) $recipe->price === 0.0 && $recipe->products()->count() === 0) {
            return true;
        }

        // 2. Check specific Order ID if provided
        if ($orderId) {
            $order = Order::with('details')->find($orderId);
            if ($order && in_array(strtolower($order->status), ['completed', 'processing'])) {
                // If token is provided, verify it
                if ($token && !$this->verifyOrderToken($order, $token)) {
                    return false;
                }

                $linkedProductIds = $recipe->products()->pluck('products.id')->toArray();
                $orderProductIds = $order->details->pluck('product_id')->toArray();

                if (array_intersect($linkedProductIds, $orderProductIds)) {
                    return true;
                }
            }
        }

        // 3. Check guest session last_order_id
        $sessionOrderId = session('last_order_id');
        if ($sessionOrderId) {
            $sessionOrder = Order::with('details')->find($sessionOrderId);
            if ($sessionOrder && in_array(strtolower($sessionOrder->status), ['completed', 'processing'])) {
                $linkedProductIds = $recipe->products()->pluck('products.id')->toArray();
                $orderProductIds = $sessionOrder->details->pluck('product_id')->toArray();
                if (array_intersect($linkedProductIds, $orderProductIds)) {
                    return true;
                }
            }
        }

        // 4. Check customer history
        if ($customer) {
            $linkedProductIds = $recipe->products()->pluck('products.id')->toArray();
            if (!empty($linkedProductIds)) {
                return OrderDetail::whereIn('product_id', $linkedProductIds)
                    ->whereHas('order', function ($q) use ($customer) {
                        $q->where('customer_id', $customer->id)
                          ->whereIn('status', ['completed', 'processing']);
                    })
                    ->exists();
            }
        }

        return false;
    }

    /**
     * Get all unlocked recipes for a given customer across all their completed orders.
     */
    public function getUnlockedRecipesForCustomer(Customer $customer): Collection
    {
        $purchasedProductIds = OrderDetail::whereHas('order', function ($q) use ($customer) {
            $q->where('customer_id', $customer->id)
              ->whereIn('status', ['completed', 'processing']);
        })->pluck('product_id')->unique()->toArray();

        if (empty($purchasedProductIds)) {
            return collect();
        }

        return Recipe::whereHas('products', function ($q) use ($purchasedProductIds) {
            $q->whereIn('products.id', $purchasedProductIds);
        })->where('is_active', true)->get();
    }

    /**
     * Generate an access token for an order.
     */
    public function generateOrderToken(Order $order): string
    {
        return hash_hmac('sha256', 'order-' . $order->id . '-' . $order->created_at, config('app.key'));
    }

    /**
     * Verify an access token for an order.
     */
    public function verifyOrderToken(Order $order, string $token): bool
    {
        return hash_equals($this->generateOrderToken($order), $token);
    }

    /**
     * Generate a signed download URL for a recipe.
     */
    public function generateDownloadUrl(Recipe $recipe, ?Order $order = null): string
    {
        // Build the URL without relying on a named route that may not exist in all environments
        $params = ['slug' => $recipe->slug];
        if ($order) {
            $params['order_id'] = $order->id;
            $params['token']    = $this->generateOrderToken($order);
        }

        try {
            return route('recipes.download-pdf', $params);
        } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            // Fallback: return a plain URL if the named route is not registered
            return url('/recipes/' . $recipe->slug . '/download?' . http_build_query(
                array_filter($params, fn ($v, $k) => $k !== 'slug'),
                '',
                '&',
                PHP_QUERY_RFC3986
            ));
        }
    }
}
