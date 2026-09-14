@props([
    'product',
    'currency' => null,
    'wishlistIds' => [],
])

@php
    $currency = $currency ?? activeCurrency();
    $variant = $product->primaryVariant;
    $hasDiscount = !empty($variant?->converted_discount_price);

    $isNew = $product->created_at && $product->created_at->diffInDays(now()) <= 30;
    $isSale = $hasDiscount;

    $reviewCount = $product->reviews_count ?? 0;
    $avgRating = $product->average_rating ?? ($reviewCount > 0 ? 4.5 : 0);
    $inWishlist = in_array($product->id, $wishlistIds ?? []);
    $productUrl = route('product.show', $product->slug);
    $imageUrl = product_image_url(optional($product->thumbnail)->image_url);
@endphp

<article {{ $attributes->merge(['class' => 'xsf-product-card']) }}>
    <div class="xsf-product-card__media">
        {{-- Badges --}}
        <div class="xsf-product-card__badges">
            @if ($isSale)
                <x-store.badge type="sale" />
            @elseif ($isNew)
                <x-store.badge type="new" />
            @endif
        </div>

        {{-- Wishlist Button --}}
        <button type="button"
                class="xsf-product-card__wish js-wishlist-toggle {{ $inWishlist ? 'is-active' : '' }}"
                data-product-id="{{ $product->id }}"
                aria-label="{{ $inWishlist ? 'Remove from wishlist' : 'Add to wishlist' }}"
                title="{{ $inWishlist ? 'In wishlist' : 'Add to wishlist' }}">
            <i class="{{ $inWishlist ? 'fa-solid fa-heart' : 'fa-regular fa-heart' }}" aria-hidden="true"></i>
        </button>

        {{-- Product Image Link --}}
        <a href="{{ $productUrl }}" class="xsf-product-card__img-wrap" tabindex="-1" aria-hidden="true">
            <img src="{{ $imageUrl }}"
                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&auto=format&fit=crop&q=80';"
                 loading="lazy"
                 decoding="async"
                 width="280"
                 height="280"
                 alt="{{ $product->name ?? 'Product' }}"
                 class="xsf-product-card__img">
        </a>

        {{-- Quick View Hover Action --}}
        <div class="xsf-product-card__quickview-wrap">
            <a href="{{ $productUrl }}" class="xsf-product-card__quickview">
                <i class="fa-regular fa-eye me-1" aria-hidden="true"></i>
                <span>Quick View</span>
            </a>
        </div>
    </div>

    <div class="xsf-product-card__body">
        {{-- Star Rating --}}
        <x-store.rating :rating="$avgRating" :count="$reviewCount" class="mb-1" />

        {{-- Title --}}
        <h3 class="xsf-product-card__title">
            <a href="{{ $productUrl }}" title="{{ $product->name }}">{{ $product->name }}</a>
        </h3>

        {{-- Price & Cart Row --}}
        <div class="xsf-product-card__footer">
            <div class="xsf-product-card__price">
                @if ($hasDiscount)
                    <span class="xsf-product-card__price--sale">
                        {{ $currency->symbol ?? '$' }}{{ $variant->converted_discount_price }}
                    </span>
                    <span class="xsf-product-card__price--original">
                        {{ $currency->symbol ?? '$' }}{{ $variant->converted_price }}
                    </span>
                @else
                    <span class="xsf-product-card__price--regular">
                        {{ $currency->symbol ?? '$' }}{{ optional($variant)->converted_price ?? 'N/A' }}
                    </span>
                @endif
            </div>

            <button type="button"
                    class="xsf-product-card__cart-btn js-add-to-cart"
                    data-product-id="{{ $product->id }}"
                    aria-label="Add {{ $product->name }} to cart"
                    title="Add to Bag">
                <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
                <span class="btn-text">Add</span>
            </button>
        </div>
    </div>
</article>
