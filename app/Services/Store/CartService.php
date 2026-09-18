<?php

declare(strict_types=1);

namespace App\Services\Store;

use App\Models\AttributeValue;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Apply a coupon by code and store in session.
     */
    public function applyCoupon(string $code): array
    {
        $coupon = Coupon::where('code', trim($code))->first();

        if (! $coupon) {
            return ['success' => false, 'message' => 'Invalid coupon code.'];
        }

        if ($coupon->isExpired()) {
            return ['success' => false, 'message' => 'This coupon has expired.'];
        }

        Session::put('cart_coupon', [
            'id'       => $coupon->id,
            'code'     => $coupon->code,
            'discount' => (float) $coupon->discount,
            'type'     => $coupon->type,
        ]);

        return [
            'success'  => true,
            'message'  => 'Coupon applied successfully!',
            'discount' => $coupon->discount,
            'type'     => $coupon->type,
        ];
    }

    /**
     * Calculate discount amount given subtotal and optional coupon.
     */
    public function calculateDiscount(float $subtotal, Coupon|array|null $coupon = null): float
    {
        if ($coupon === null) {
            $coupon = Session::get('cart_coupon');
        }

        if (! $coupon) {
            return 0.0;
        }

        $type = is_array($coupon) ? ($coupon['type'] ?? 'fixed') : $coupon->type;
        $discountValue = is_array($coupon) ? (float) ($coupon['discount'] ?? 0) : (float) $coupon->discount;

        if ($type === 'percentage') {
            $discount = $subtotal * ($discountValue / 100);
        } else {
            $discount = $discountValue;
        }

        return max(0.0, min($subtotal, round($discount, 2)));
    }

    /**
     * Calculate subtotal, discount, shipping, and grand total.
     */
    public function calculateTotals(array $cart, Coupon|array|null $coupon = null, float $shipping = 0.0): array
    {
        $subtotal = 0.0;
        foreach ($cart as $item) {
            $price = (float) ($item['price'] ?? 0);
            $qty = (int) ($item['quantity'] ?? 1);
            $subtotal += $price * $qty;
        }

        $discount = $this->calculateDiscount($subtotal, $coupon);
        $total = max(0.0, round($subtotal - $discount + $shipping, 2));

        return [
            'subtotal'        => round($subtotal, 2),
            'discount_amount' => $discount,
            'shipping'        => round($shipping, 2),
            'total'           => $total,
        ];
    }

    /**
     * Backwards-compatible discount method.
     */
    public function getCartTotalWithDiscount(float $total): float
    {
        $discount = $this->calculateDiscount($total);
        return max(0.0, round($total - $discount, 2));
    }

    /**
     * Remove applied coupon from session.
     */
    public function removeCoupon(): void
    {
        Session::forget('cart_coupon');
    }

    /**
     * Match product variant given attribute value IDs.
     */
    public function matchVariant(int $productId, array $attributeValueIds): ?ProductVariant
    {
        if (empty($attributeValueIds)) {
            return ProductVariant::where('product_id', $productId)->where('is_primary', true)->first();
        }

        $variants = ProductVariant::with('attributeValues')
            ->where('product_id', $productId)
            ->get();

        $sortedTarget = collect($attributeValueIds)->map(fn ($id) => (int) $id)->sort()->values()->toArray();

        foreach ($variants as $variant) {
            $variantAttrIds = $variant->attributeValues->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->toArray();
            if ($variantAttrIds === $sortedTarget) {
                return $variant;
            }
        }

        return null;
    }

    /**
     * Hydrate raw session cart items with full database models and attributes.
     */
    public function hydrateCart(array $cart): array
    {
        if (empty($cart)) {
            return [];
        }

        $productIds = collect($cart)->pluck('product_id')->filter()->unique()->values();
        $variantIds = collect($cart)->pluck('variant_id')->filter()->unique()->values();
        $allAttrValueIds = collect($cart)->pluck('attributes')->flatten()->filter()->unique()->values();

        $products = Product::with('thumbnail')->whereIn('id', $productIds)->get()->keyBy('id');
        $variants = ProductVariant::with('images')->whereIn('id', $variantIds)->get()->keyBy('id');
        $attributeValues = AttributeValue::with('attribute')->whereIn('id', $allAttrValueIds)->get()->keyBy('id');

        $hydrated = [];
        foreach ($cart as $key => $item) {
            $product = $products->get($item['product_id']);
            $variant = isset($item['variant_id']) ? $variants->get($item['variant_id']) : null;
            if (! $variant && $product) {
                $variant = $product->variants()->where('is_primary', true)->first();
            }

            $sizes = [];
            $colors = [];
            if (! empty($item['attributes'])) {
                foreach ($item['attributes'] as $attrId) {
                    $av = $attributeValues->get($attrId);
                    if ($av && $av->attribute) {
                        $attrName = strtolower($av->attribute->name);
                        if (str_contains($attrName, 'size')) {
                            $sizes[] = $av->value;
                        } elseif (str_contains($attrName, 'color')) {
                            $colors[] = $av->value;
                        }
                    }
                }
            }

            $unitPrice = $variant
                ? ($variant->converted_discount_price ?? $variant->converted_price)
                : ($item['price'] ?? 0);

            $quantity = (int) ($item['quantity'] ?? 1);
            $subtotal = $unitPrice * $quantity;

            $imageUrl = $item['image'] ?? null;
            if (! $imageUrl) {
                $rawImg = optional($variant?->images->first() ?? $product?->thumbnail)->image_url;
                $imageUrl = $rawImg ? product_image_url($rawImg) : asset('images/no-product.png');
            } elseif (! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
                $imageUrl = product_image_url($imageUrl);
            }

            $hydrated[$key] = [
                'product_id'   => $item['product_id'],
                'variant_id'   => $variant?->id ?? ($item['variant_id'] ?? null),
                'display_name' => $variant?->name ?? $product?->name ?? 'Product',
                'price'        => (float) $unitPrice,
                'quantity'     => $quantity,
                'subtotal'     => round($subtotal, 2),
                'image_url'    => $imageUrl,
                'sizes'        => $sizes,
                'colors'       => $colors,
            ];
        }

        return $hydrated;
    }
}
