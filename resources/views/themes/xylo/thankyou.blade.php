@extends('themes.xylo.layouts.master')

@section('content')
    @php $currency = activeCurrency(); @endphp

    <section class="xsf-ty-section">
        <div class="container">

            <nav aria-label="breadcrumb" class="xsf-breadcrumb">
                <a href="{{ route('xylo.home') }}">{{ 'Home' }}</a>
                <i class="fa fa-angle-right" aria-hidden="true"></i>
                <span aria-current="page">{{ 'Order Completed' }}</span>
            </nav>

            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-8 col-xl-6">
                    <div class="xsf-ty-card">

                        {{-- Success status --}}
                        <div class="xsf-ty-status" role="status">
                            <div class="xsf-ty-status__icon" aria-hidden="true"><i class="fa fa-check"></i></div>
                            <h1 class="xsf-ty-status__title">{{ 'Thank You for Your Order!' }}</h1>
                            <p class="xsf-ty-status__lead">{{ 'Your order has been received and is being processed.' }}</p>
                        </div>

                        {{-- Order meta --}}
                        <dl class="xsf-ty-meta">
                            <div>
                                <dt>{{ 'Order ID' }}</dt>
                                <dd>#{{ $order->id }}</dd>
                            </div>
                            <div>
                                <dt>{{ 'Date' }}</dt>
                                <dd>{{ $order->created_at->format('M d, Y · h:i A') }}</dd>
                            </div>
                        </dl>

                        {{-- Items --}}
                        <div class="xsf-ty-items">
                            <h2 class="xsf-ty-items__heading">{{ 'Items Ordered' }}</h2>
                            <ul class="xsf-ty-items__list">
                                @foreach ($order->details as $detail)
                                    @php
                                        $product   = $detail->product;
                                        $thumbUrl  = $product?->thumbnail?->image_url
                                                     ? product_image_url($product->thumbnail->image_url)
                                                     : ($product?->image_url ? product_image_url($product->image_url) : null);
                                        $name = $product?->name ?? 'Product Name Not Available';
                                    @endphp
                                    <li class="xsf-ty-item">
                                        @if($thumbUrl)
                                            <img src="{{ $thumbUrl }}" alt="" class="xsf-ty-item__thumb" loading="lazy" width="48" height="48">
                                        @else
                                            <div class="xsf-ty-item__thumb-placeholder" aria-hidden="true">
                                                <i class="fa fa-box"></i>
                                            </div>
                                        @endif
                                        <div class="xsf-ty-item__info">
                                            <p class="xsf-ty-item__name">{{ $name }}</p>
                                            <span class="xsf-ty-item__qty">{{ 'Qty' }}: {{ $detail->quantity }}</span>
                                        </div>
                                        <span class="xsf-ty-item__price">
                                            {{ $currency->symbol }}{{ number_format($detail->price * $detail->quantity, 2) }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Totals --}}
                        @php
                            $itemsSubtotal = $order->details->sum(fn($d) => $d->price * $d->quantity);
                            $discountOnOrder = round($itemsSubtotal - $order->total_amount, 2);
                        @endphp
                        <div class="xsf-ty-totals">
                            @if ($discountOnOrder > 0)
                                <div class="xsf-ty-totals__row">
                                    <span>{{ 'Subtotal' }}</span>
                                    <span>{{ $currency->symbol }}{{ number_format($itemsSubtotal, 2) }}</span>
                                </div>
                                <div class="xsf-ty-totals__row xsf-ty-totals__row--discount">
                                    <span>{{ 'Discount' }}</span>
                                    <span>&minus;{{ $currency->symbol }}{{ number_format($discountOnOrder, 2) }}</span>
                                </div>
                            @endif
                            <div class="xsf-ty-totals__row xsf-ty-totals__row--total">
                                <span>{{ 'Total Paid' }}</span>
                                <span>{{ $currency->symbol }}{{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>

                        {{-- Unlocked Recipes Section --}}
                        @if(isset($unlockedRecipes) && $unlockedRecipes->isNotEmpty())
                            <section class="xsf-ty-recipes" aria-labelledby="ty-recipes-title">
                                <h2 class="xsf-ty-recipes__title" id="ty-recipes-title">Bonus Chef Recipes Unlocked</h2>
                                <p class="xsf-ty-recipes__lead">Your purchased items unlocked the following recipe guides:</p>
                                @foreach($unlockedRecipes as $recipe)
                                    @php
                                        $token = app(\App\Services\Store\RecipeAccessService::class)->generateOrderToken($order);
                                    @endphp
                                    <div class="xsf-ty-recipes__item">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="{{ $recipe->image_src }}" alt="" class="xsf-ty-recipes__thumb" width="44" height="44" onerror="this.src='https://placehold.co/44x44/eee/999?text=Recipe';">
                                            <div>
                                                <a href="{{ route('recipes.show', ['slug' => $recipe->slug, 'order_id' => $order->id, 'token' => $token]) }}" class="xsf-ty-recipes__link">
                                                    {{ $recipe->title }}
                                                </a>
                                                <span class="xsf-ty-recipes__meta">
                                                    {{ $recipe->total_time ? $recipe->total_time . ' min' : '30 min' }} &bull; {{ ucfirst($recipe->difficulty) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="xsf-ty-recipes__btns">
                                            <a href="{{ route('recipes.show', ['slug' => $recipe->slug, 'order_id' => $order->id, 'token' => $token]) }}" class="btn btn-outline-dark btn-sm" style="min-height:44px;display:inline-flex;align-items:center;">
                                                <i class="fa-solid fa-eye me-1" aria-hidden="true"></i> View
                                            </a>
                                            <a href="{{ route('recipes.download-pdf', ['slug' => $recipe->slug, 'order_id' => $order->id, 'token' => $token]) }}" class="btn btn-outline-dark btn-sm" style="min-height:44px;display:inline-flex;align-items:center;">
                                                <i class="fa-solid fa-file-pdf me-1" aria-hidden="true"></i>PDF
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </section>
                        @endif

                        {{-- Actions --}}
                        <div class="xsf-ty-actions">
                            @php
                                $receiptToken = app(\App\Services\Store\RecipeAccessService::class)->generateOrderToken($order);
                            @endphp
                            @auth('customer')
                                <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary">
                                    <i class="fa fa-list-check" aria-hidden="true"></i>{{ 'View Orders' }}
                                </a>
                            @endauth
                            <a href="{{ route('orders.download-receipt', ['id' => $order->id, 'token' => $receiptToken]) }}" class="btn btn-outline-dark">
                                <i class="fa-solid fa-file-invoice" aria-hidden="true"></i>{{ 'Download Receipt (PDF)' }}
                            </a>
                            <a href="{{ route('xylo.home') }}" class="btn btn-primary">
                                <i class="fa fa-bag-shopping" aria-hidden="true"></i>{{ 'Continue Shopping' }}
                            </a>
                        </div>

                    </div>{{-- /.xsf-ty-card --}}
                </div>
            </div>

        </div>
    </section>
@endsection
