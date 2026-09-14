@extends('themes.xylo.layouts.master')

@section('css')
<style>
/* ─────────────────────────────────────────────
   Thank You / Order Confirmation Page
   ───────────────────────────────────────────── */

.xsf-ty-section {
    padding: 60px 0 80px;
    background: #f8f9fb;
    min-height: 60vh;
}

/* ── Success icon ── */
.xsf-ty-icon-wrap {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
    box-shadow: 0 8px 24px rgba(34, 197, 94, 0.28);
    animation: ty-pop 0.45s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

.xsf-ty-icon-wrap i {
    font-size: 32px;
    color: #fff;
}

@keyframes ty-pop {
    from { transform: scale(0.5); opacity: 0; }
    to   { transform: scale(1);   opacity: 1; }
}

/* ── Card ── */
.xsf-ty-card {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 4px 32px rgba(0, 0, 0, 0.07);
    overflow: hidden;
    animation: ty-slide 0.4s ease both;
    animation-delay: 0.08s;
}

@keyframes ty-slide {
    from { transform: translateY(18px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}

/* Title & lead */
.xsf-ty-title {
    font-size: 1.65rem;
    font-weight: 800;
    color: #111827;
    margin: 0 0 8px;
    letter-spacing: -0.02em;
}

.xsf-ty-lead {
    font-size: 0.9rem;
    color: #6b7280;
    margin: 0 0 32px;
    line-height: 1.6;
}

/* ── Card inner sections ── */
.xsf-ty-body {
    padding: 36px 40px 28px;
    text-align: center;
}

/* ── Order info block ── */
.xsf-ty-info {
    background: #f8f9fb;
    border-radius: 14px;
    padding: 20px 24px;
    margin-bottom: 28px;
    text-align: left;
}

.xsf-ty-info__heading {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #9ca3af;
    margin: 0 0 14px;
}

.xsf-ty-info__row {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.875rem;
    color: #374151;
    margin-bottom: 6px;
}

.xsf-ty-info__row:last-child { margin-bottom: 0; }

.xsf-ty-info__label {
    color: #6b7280;
    min-width: 70px;
}

.xsf-ty-info__value {
    font-weight: 600;
    color: #111827;
}

/* ── Items table ── */
.xsf-ty-items {
    width: 100%;
    text-align: left;
    margin-bottom: 0;
}

.xsf-ty-items__heading {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #9ca3af;
    margin: 0 0 14px;
}

.xsf-ty-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 0;
    border-bottom: 1px solid #f3f4f6;
}

.xsf-ty-item:last-child { border-bottom: none; }

.xsf-ty-item__thumb {
    width: 52px;
    height: 52px;
    border-radius: 10px;
    object-fit: cover;
    background: #f3f4f6;
    flex-shrink: 0;
    border: 1px solid #e5e7eb;
}

.xsf-ty-item__thumb-placeholder {
    width: 52px;
    height: 52px;
    border-radius: 10px;
    background: #f3f4f6;
    flex-shrink: 0;
    border: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d1d5db;
    font-size: 18px;
}

.xsf-ty-item__info { flex: 1; min-width: 0; }

.xsf-ty-item__name {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin: 0 0 2px;
}

.xsf-ty-item__qty {
    font-size: 0.78rem;
    color: #9ca3af;
}

.xsf-ty-item__price {
    font-size: 0.9rem;
    font-weight: 700;
    color: #111827;
    white-space: nowrap;
    flex-shrink: 0;
}

/* ── Total row ── */
.xsf-ty-total-bar {
    background: #111827;
    border-radius: 0 0 20px 20px;
    padding: 18px 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.xsf-ty-total-bar__label {
    font-size: 0.875rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.7);
}

.xsf-ty-total-bar__amount {
    font-size: 1.3rem;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.02em;
}

/* ── Action buttons ── */
.xsf-ty-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
    padding: 28px 40px 36px;
    border-top: 1px solid #f3f4f6;
}

.xsf-ty-actions .btn { min-width: 160px; }

/* ── Breadcrumb (reuse existing but keep it light here) ── */
.xsf-ty-section .xsf-breadcrumb { margin-bottom: 32px; }
</style>
@endsection

@section('content')
    @php $currency = activeCurrency(); @endphp

    <section class="xsf-ty-section">
        <div class="container">

            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="xsf-breadcrumb">
                <a href="{{ route('xylo.home') }}">{{ 'Home' }}</a>
                <i class="fa fa-angle-right" aria-hidden="true"></i>
                <span>{{ 'Order Completed' }}</span>
            </nav>

            <div class="row justify-content-center">
                <div class="col-md-9 col-lg-7 col-xl-6">

                    {{-- Success icon --}}
                    <div class="xsf-ty-icon-wrap">
                        <i class="fa fa-check" aria-hidden="true"></i>
                    </div>

                    <div class="xsf-ty-card">
                        <div class="xsf-ty-body">

                            {{-- Heading --}}
                            <h1 class="xsf-ty-title">{{ 'Thank You for Your Order!' }}</h1>
                            <p class="xsf-ty-lead">{{ 'Your order has been received and is being processed.' }}</p>

                            {{-- Order meta --}}
                            <div class="xsf-ty-info">
                                <p class="xsf-ty-info__heading">{{ 'Order Information' }}</p>
                                <div class="xsf-ty-info__row">
                                    <span class="xsf-ty-info__label">{{ 'Order ID' }}</span>
                                    <span class="xsf-ty-info__value">#{{ $order->id }}</span>
                                </div>
                                <div class="xsf-ty-info__row">
                                    <span class="xsf-ty-info__label">{{ 'Date' }}</span>
                                    <span class="xsf-ty-info__value">{{ $order->created_at->format('M d, Y · h:i A') }}</span>
                                </div>
                            </div>

                            {{-- Items --}}
                            <p class="xsf-ty-items__heading text-start">{{ 'Items Ordered' }}</p>
                            <div class="xsf-ty-items">
                                @foreach ($order->details as $detail)
                                    @php
                                        $product   = $detail->product;
                                        $thumbUrl  = $product?->thumbnail?->image_url
                                                     ? product_image_url($product->thumbnail->image_url)
                                                     : ($product?->image_url ? product_image_url($product->image_url) : null);
                                        $name = $product?->name ?? 'Product Name Not Available';
                                    @endphp
                                    <div class="xsf-ty-item">
                                        @if($thumbUrl)
                                            <img src="{{ $thumbUrl }}" alt="{{ $name }}" class="xsf-ty-item__thumb">
                                        @else
                                            <div class="xsf-ty-item__thumb-placeholder">
                                                <i class="fa fa-box"></i>
                                            </div>
                                        @endif
                                        <div class="xsf-ty-item__info">
                                            <p class="xsf-ty-item__name">{{ $name }}</p>
                                            <span class="xsf-ty-item__qty">&times; {{ $detail->quantity }}</span>
                                        </div>
                                        <span class="xsf-ty-item__price">
                                            {{ $currency->symbol }}{{ number_format($detail->price * $detail->quantity, 2) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                        </div>

                        {{-- Discount row (shown only when a coupon was applied) --}}
                        @php
                            $itemsSubtotal = $order->details->sum(fn($d) => $d->price * $d->quantity);
                            $discountOnOrder = round($itemsSubtotal - $order->total_amount, 2);
                        @endphp
                        @if ($discountOnOrder > 0)
                            <div style="padding: 0 40px 4px; display:flex; justify-content:space-between; font-size:0.875rem;">
                                <span style="color:#6b7280;">{{ 'Subtotal' }}</span>
                                <span style="color:#111827; font-weight:600;">{{ $currency->symbol }}{{ number_format($itemsSubtotal, 2) }}</span>
                            </div>
                            <div style="padding: 0 40px 16px; display:flex; justify-content:space-between; font-size:0.875rem;">
                                <span style="color:#6b7280;">{{ 'Discount' }}</span>
                                <span style="color:#dc2626; font-weight:600;">&minus;{{ $currency->symbol }}{{ number_format($discountOnOrder, 2) }}</span>
                            </div>
                        @endif

                        {{-- Total dark bar --}}
                        <div class="xsf-ty-total-bar">
                            <span class="xsf-ty-total-bar__label">{{ 'Total Paid' }}</span>
                            <span class="xsf-ty-total-bar__amount">
                                {{ $currency->symbol }}{{ number_format($order->total_amount, 2) }}
                            </span>
                        </div>

                        {{-- Actions --}}
                        <div class="xsf-ty-actions">
                            <a href="{{ route('xylo.home') }}" class="btn btn-primary btn-pill">
                                <i class="fa fa-bag-shopping me-2"></i>{{ 'Continue Shopping' }}
                            </a>
                            @auth('customer')
                                <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary btn-pill">
                                    <i class="fa fa-list-check me-2"></i>{{ 'View Orders' }}
                                </a>
                            @endauth
                        </div>

                    </div>{{-- /.xsf-ty-card --}}
                </div>
            </div>

        </div>
    </section>
@endsection
