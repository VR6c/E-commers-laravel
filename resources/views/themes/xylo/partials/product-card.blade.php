{{--
    Backwards-compatible wrapper delegating to the modern Blade component
--}}
<x-store.product-card
    :product="$product"
    :currency="$currency ?? null"
    :wishlist-ids="$wishlistIds ?? []"
/>
