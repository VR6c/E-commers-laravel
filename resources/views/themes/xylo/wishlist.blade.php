@extends('themes.xylo.layouts.master')

@section('content')
    @php
        $currency = activeCurrency();
        $wishlistIds = $products->pluck('id')->toArray();
    @endphp

    <section class="xsf-section xsf-listing-page">
        <div class="container">
            <div class="xsf-listing-head">
                <div class="xsf-listing-head__left">
                    <h1 class="xsf-listing-head__title">{{ 'My Wishlist' }}</h1>
                    <span class="xsf-listing-head__count" role="status">{{ $products->count() }} {{ $products->count() === 1 ? 'item' : 'items' }}</span>
                </div>
            </div>

            @if ($products->isEmpty())
                <div class="xsf-empty">
                    <i class="fa-regular fa-heart xsf-empty__icon" aria-hidden="true"></i>
                    <h2 class="xsf-empty__title">{{ 'Your wishlist is empty' }}</h2>
                    <p class="xsf-empty__text">{{ 'Explore our catalog and save items you love for later.' }}</p>
                    <a href="{{ route('shop.index') }}" class="xsf-empty__cta">{{ 'Browse Products' }}</a>
                </div>
            @else
                <div class="xsf-product-grid">
                    @foreach ($products as $product)
                        <div class="xsf-product-grid__item xsf-wishlist-item">
                            <x-store.product-card :product="$product" :currency="$currency" :wishlist-ids="$wishlistIds" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
