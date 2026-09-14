@extends('themes.xylo.layouts.master')

@section('content')
    @php
        $currency = activeCurrency();
        $wishlistIds = $products->pluck('id')->toArray();
    @endphp

    <section class="xsf-section">
        <div class="container">
            <div class="xsf-listing-head mb-4">
                <span class="xsf-section__eyebrow">{{ 'Saved Items' }}</span>
                <h1 class="xsf-listing-head__title">{{ 'My Wishlist' }}</h1>
            </div>

            @if ($products->isEmpty())
                <div class="xsf-empty">
                    <i class="fa-regular fa-heart xsf-empty__icon" aria-hidden="true"></i>
                    <h2 class="xsf-empty__title">{{ 'Your wishlist is empty' }}</h2>
                    <p class="xsf-empty__text">{{ 'Explore our catalog and save items you love for later.' }}</p>
                    <a href="{{ route('shop.index') }}" class="btn btn-primary btn-pill mt-3 px-4 py-2">{{ 'Browse Products' }}</a>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($products as $product)
                        <div class="col-6 col-md-4 col-lg-3 xsf-wishlist-item">
                            <x-store.product-card :product="$product" :currency="$currency" :wishlist-ids="$wishlistIds" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
