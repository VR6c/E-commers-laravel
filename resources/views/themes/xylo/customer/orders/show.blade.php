@extends('themes.xylo.layouts.master')

@section('content')
<section class="xsf-section">
    <div class="container">
        <div class="xsf-account-layout">
            <div class="xsf-account-layout__aside">
                @include('themes.xylo.customer._nav', ['active' => 'orders'])
            </div>

            <div class="xsf-account-layout__main">
        {{-- Top Back & Action Bar --}}
        <div class="xsf-order-detail-header">
            <div>
                <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary btn-sm xsf-back-btn mb-2">
                    <i class="fa-solid fa-arrow-left me-1"></i> {{ 'Back to Orders' }}
                </a>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <h1>{{ 'Order #' . $order->id }}</h1>
                    @php
                        $statusClass = match($order->status) {
                            'pending'    => 'xsf-status--pending',
                            'processing' => 'xsf-status--processing',
                            'completed'  => 'xsf-status--completed',
                            'canceled'   => 'xsf-status--canceled',
                            default      => 'xsf-status--pending',
                        };
                        $statusIcon = match($order->status) {
                            'pending'    => 'fa-regular fa-clock',
                            'processing' => 'fa-solid fa-rotate',
                            'completed'  => 'fa-solid fa-circle-check',
                            'canceled'   => 'fa-solid fa-ban',
                            default      => 'fa-regular fa-circle',
                        };
                    @endphp
                    <span class="xsf-status {{ $statusClass }}">
                        <i class="{{ $statusIcon }}" aria-hidden="true"></i>
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <p class="text-muted small mb-0 mt-1">
                    {{ 'Placed on' }} {{ $order->created_at ? $order->created_at->format('F d, Y \a\t h:i A') : 'N/A' }}
                </p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('orders.download-receipt', $order->id) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-file-pdf me-1"></i> {{ 'Download Receipt' }}
                </a>
                <button type="button" class="btn btn-outline-dark btn-sm btn-print" onclick="window.print();">
                    <i class="fa-solid fa-print me-1"></i> {{ 'Print Receipt' }}
                </button>
            </div>
        </div>

        <div class="xsf-order-detail-grid">
            {{-- Left column: Items list --}}
            <div>
                <div class="xsf-account-card">
                    <div class="xsf-account-card__header">
                        <h2 class="xsf-account-card__title">
                            <i class="fa-solid fa-box-open" aria-hidden="true"></i>
                            {{ 'Items in this Order' }} ({{ $order->details->count() }})
                        </h2>
                    </div>
                    <div class="xsf-account-card__body">
                        @foreach ($order->details as $detail)
                            @php
                                $product = $detail->product;
                                $imageUrl = $product?->thumbnail?->image_url
                                    ? product_image_url($product->thumbnail->image_url)
                                    : ($product?->image_url ? product_image_url($product->image_url) : null);
                                $itemTotal = $detail->price * $detail->quantity;
                            @endphp
                            <div class="xsf-detail-item">
                                @if ($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="{{ $product?->name ?? 'Product' }}" class="xsf-detail-item__thumb">
                                @else
                                    <div class="xsf-detail-item__thumb">
                                        <i class="fa-solid fa-box"></i>
                                    </div>
                                @endif

                                <div class="xsf-detail-item__info">
                                    @if ($product && $product->slug)
                                        <a href="{{ route('product.show', $product->slug) }}" class="xsf-detail-item__title">
                                            {{ $product->name }}
                                        </a>
                                    @else
                                        <span class="xsf-detail-item__title text-dark">
                                            {{ $product?->name ?? 'Product Not Available' }}
                                        </span>
                                    @endif
                                    <div class="xsf-detail-item__meta">
                                        {{ $currency->symbol }}{{ number_format($detail->price, 2) }} &times; {{ $detail->quantity }}
                                    </div>
                                </div>

                                <div class="xsf-detail-item__total">
                                    {{ $currency->symbol }}{{ number_format($itemTotal, 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @php
                    $orderRecipes = $order->unlockedRecipes();
                @endphp

                @if($orderRecipes->isNotEmpty())
                    <div class="xsf-account-card">
                        <div class="xsf-account-card__header">
                            <h2 class="xsf-account-card__title">
                                <i class="fa-solid fa-utensils" aria-hidden="true"></i>
                                {{ 'Bonus Recipes Unlocked with this Order' }} ({{ $orderRecipes->count() }})
                            </h2>
                        </div>
                        <div class="xsf-account-card__body">
                            @foreach($orderRecipes as $recipe)
                                <div class="xsf-recipe-row">
                                    <img src="{{ $recipe->image_src }}" alt="{{ $recipe->title }}" class="xsf-recipe-row__thumb" onerror="this.src='https://placehold.co/50x50/eee/999?text=Recipe';">
                                    <div class="xsf-recipe-row__body">
                                        <a href="{{ route('recipes.show', $recipe->slug) }}" class="xsf-recipe-row__title">{{ $recipe->title }}</a>
                                        <div class="xsf-recipe-row__meta">
                                            {{ $recipe->total_time ? $recipe->total_time . ' min' : '30 min' }} &bull; {{ ucfirst($recipe->difficulty) }}
                                        </div>
                                    </div>
                                    <div class="xsf-recipe-row__actions">
                                        <a href="{{ route('recipes.show', $recipe->slug) }}" class="btn btn-outline-dark btn-sm">
                                            <i class="fa-solid fa-eye me-1" aria-hidden="true"></i> View
                                        </a>
                                        <a href="{{ route('recipes.download-pdf', $recipe->slug) }}" class="btn btn-primary btn-sm">
                                            <i class="fa-solid fa-file-pdf me-1" aria-hidden="true"></i> PDF
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Payment & Transaction Card --}}
                <div class="xsf-account-card">
                    <div class="xsf-account-card__header">
                        <h2 class="xsf-account-card__title">
                            <i class="fa-solid fa-credit-card" aria-hidden="true"></i>
                            {{ 'Payment Information' }}
                        </h2>
                    </div>
                    <div class="xsf-account-card__body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="xsf-info-label">{{ 'Payment Method' }}</div>
                                <div class="xsf-info-value">
                                    <i class="fa-solid fa-wallet me-1 text-muted"></i>
                                    @php
                                        $friendlyMethods = [
                                            'abapayway' => 'ABA PayWay',
                                            'aba_payway' => 'ABA PayWay',
                                            'paypal' => 'PayPal',
                                            'stripe' => 'Stripe',
                                            'cod' => 'Cash on Delivery (COD)',
                                        ];
                                        $methodKey = strtolower($order->payment_method ?? 'cod');
                                    @endphp
                                    {{ $friendlyMethods[$methodKey] ?? strtoupper($order->payment_method ?? 'COD') }}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="xsf-info-label">{{ 'Payment Status' }}</div>
                                <div class="xsf-info-value">
                                    @if (in_array($order->status, ['completed', 'processing']))
                                        <span class="text-success"><i class="fa-solid fa-circle-check me-1" aria-hidden="true"></i>{{ 'Paid' }}</span>
                                    @elseif ($order->status === 'canceled')
                                        <span class="text-danger"><i class="fa-solid fa-circle-xmark me-1" aria-hidden="true"></i>{{ 'Canceled / Refunded' }}</span>
                                    @else
                                        <span class="text-warning-emphasis"><i class="fa-regular fa-clock me-1" aria-hidden="true"></i>{{ 'Pending' }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right column: Summary & Shipping Address --}}
            <div>
                {{-- Order Summary --}}
                <div class="xsf-account-card">
                    <div class="xsf-account-card__header">
                        <h2 class="xsf-account-card__title">
                            <i class="fa-solid fa-receipt" aria-hidden="true"></i>
                            {{ 'Order Summary' }}
                        </h2>
                    </div>
                    <div class="xsf-account-card__body">
                        @php
                            $subtotal = $order->details->sum(fn($d) => $d->price * $d->quantity);
                            $discount = round($subtotal - $order->total_amount, 2);
                        @endphp
                        <div class="xsf-summary-row">
                            <span>{{ 'Items Subtotal' }}</span>
                            <span class="fw-semibold text-dark">{{ $currency->symbol }}{{ number_format($subtotal, 2) }}</span>
                        </div>

                        @if ($discount > 0 || $order->coupon_code)
                            <div class="xsf-summary-row text-success">
                                <span>
                                    {{ 'Coupon Discount' }}
                                    @if ($order->coupon_code)
                                        <span class="badge bg-success-subtle text-success ms-1">{{ $order->coupon_code }}</span>
                                    @endif
                                </span>
                                <span class="fw-semibold">&minus;{{ $currency->symbol }}{{ number_format(max(0, $discount), 2) }}</span>
                            </div>
                        @endif

                        <div class="xsf-summary-row">
                            <span>{{ 'Shipping' }}</span>
                            <span class="text-success fw-semibold">{{ 'Free Shipping' }}</span>
                        </div>

                        <div class="xsf-summary-total">
                            <span>{{ 'Total Amount' }}</span>
                            <span>{{ $currency->symbol }}{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Delivery / Shipping Address --}}
                <div class="xsf-account-card">
                    <div class="xsf-account-card__header">
                        <h2 class="xsf-account-card__title">
                            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                            {{ 'Shipping Address' }}
                        </h2>
                    </div>
                    <div class="xsf-account-card__body">
                        @if ($order->shippingAddress)
                            @php $shipping = $order->shippingAddress; @endphp
                            <div class="fw-bold text-dark mb-1">{{ $shipping->name }}</div>
                            @if ($shipping->phone)
                                <div class="text-muted small mb-2">
                                    <i class="fa-solid fa-phone me-1"></i>{{ $shipping->phone }}
                                </div>
                            @endif
                            <div class="text-secondary small line-height-base">
                                {{ $shipping->address }}
                                @if ($shipping->suite)
                                    <br>{{ $shipping->suite }}
                                @endif
                                @if ($shipping->city || $shipping->state || $shipping->postal_code)
                                    <br>{{ implode(', ', array_filter([$shipping->city, $shipping->state, $shipping->postal_code])) }}
                                @endif
                                @if ($shipping->country)
                                    <br>{{ $shipping->country }}
                                @endif
                            </div>
                        @else
                            <p class="text-muted small mb-0">{{ 'No shipping address specified for this order.' }}</p>
                        @endif
                    </div>
                </div>

                {{-- Need Help / Support --}}
                <div class="p-3 bg-light rounded-3 text-center border mb-4">
                    <p class="small text-muted mb-2">{{ 'Have a question about your order?' }}</p>
                    <a href="{{ route('xylo.home') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fa-regular fa-envelope me-1"></i> {{ 'Contact Support' }}
                    </a>
                </div>
            </div>
        </div>
            </div>{{-- /account main --}}
        </div>{{-- /account layout --}}
    </div>
</section>
@endsection
