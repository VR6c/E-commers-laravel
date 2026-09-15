@extends('themes.xylo.layouts.master')

@section('css')
<style>
/* ─────────────────────────────────────────────
   Customer Order Details Page Styles
   ───────────────────────────────────────────── */
.xsf-order-detail-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e2e8f0;
}

.xsf-detail-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    margin-bottom: 24px;
}

.xsf-detail-card__header {
    padding: 16px 24px;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.xsf-detail-card__title {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.xsf-detail-card__body {
    padding: 24px;
}

/* Order Item Row */
.xsf-detail-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 0;
    border-bottom: 1px solid #f1f5f9;
}

.xsf-detail-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.xsf-detail-item:first-child {
    padding-top: 0;
}

.xsf-detail-item__thumb {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    object-fit: cover;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    flex-shrink: 0;
}

.xsf-detail-item__thumb-empty {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    font-size: 1.4rem;
    flex-shrink: 0;
}

.xsf-detail-item__info {
    flex: 1;
    min-width: 0;
}

.xsf-detail-item__title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 4px;
    text-decoration: none;
    display: inline-block;
}

.xsf-detail-item__title:hover {
    color: #4f46e5;
}

.xsf-detail-item__meta {
    font-size: 0.8rem;
    color: #64748b;
}

.xsf-detail-item__total {
    text-align: right;
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
    flex-shrink: 0;
}

/* Status Badges */
.xsf-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 5px 12px;
    border-radius: 50px;
    text-transform: capitalize;
}

.xsf-badge-pending {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}

.xsf-badge-processing {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
}

.xsf-badge-completed {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #bbf7d0;
}

.xsf-badge-canceled {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

/* Summary List */
.xsf-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.875rem;
    color: #475569;
    margin-bottom: 12px;
}

.xsf-summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 14px;
    margin-top: 14px;
    border-top: 1px dashed #cbd5e1;
    font-size: 1.15rem;
    font-weight: 800;
    color: #0f172a;
}

/* Info Details */
.xsf-info-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #64748b;
    margin-bottom: 4px;
}

.xsf-info-value {
    font-size: 0.9rem;
    font-weight: 600;
    color: #0f172a;
    line-height: 1.4;
}

@media print {
    .xsf-header, .xsf-footer, .xsf-account-nav, .xsf-back-btn, .btn-print {
        display: none !important;
    }
}
</style>
@endsection

@section('content')
<section class="xsf-section">
    <div class="container">
        {{-- Top Back & Action Bar --}}
        <div class="xsf-order-detail-header">
            <div>
                <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary btn-pill btn-sm xsf-back-btn mb-2">
                    <i class="fa-solid fa-arrow-left me-1"></i> {{ 'Back to Orders' }}
                </a>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <h1 class="h4 fw-bold mb-0 text-dark">{{ 'Order #' . $order->id }}</h1>
                    @php
                        $statusClass = match($order->status) {
                            'pending'    => 'xsf-badge-pending',
                            'processing' => 'xsf-badge-processing',
                            'completed'  => 'xsf-badge-completed',
                            'canceled'   => 'xsf-badge-canceled',
                            default      => 'xsf-badge-pending',
                        };
                        $statusIcon = match($order->status) {
                            'pending'    => 'fa-regular fa-clock',
                            'processing' => 'fa-solid fa-rotate',
                            'completed'  => 'fa-solid fa-circle-check',
                            'canceled'   => 'fa-solid fa-ban',
                            default      => 'fa-regular fa-circle',
                        };
                    @endphp
                    <span class="xsf-badge {{ $statusClass }}">
                        <i class="{{ $statusIcon }}"></i>
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <p class="text-muted small mb-0 mt-1">
                    {{ 'Placed on' }} {{ $order->created_at ? $order->created_at->format('F d, Y \a\t h:i A') : 'N/A' }}
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('orders.download-receipt', $order->id) }}" class="btn btn-outline-primary btn-pill btn-sm px-3">
                    <i class="fa-solid fa-file-pdf me-1"></i> {{ 'Download Receipt' }}
                </a>
                <button type="button" class="btn btn-outline-dark btn-pill btn-sm btn-print px-3" onclick="window.print();">
                    <i class="fa-solid fa-print me-1"></i> {{ 'Print Receipt' }}
                </button>
            </div>
        </div>

        <div class="row g-4">
            {{-- Left column: Items list --}}
            <div class="col-lg-8">
                <div class="xsf-detail-card">
                    <div class="xsf-detail-card__header">
                        <h2 class="xsf-detail-card__title">
                            <i class="fa-solid fa-box-open text-primary"></i>
                            {{ 'Items in this Order' }} ({{ $order->details->count() }})
                        </h2>
                    </div>
                    <div class="xsf-detail-card__body">
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
                                    <div class="xsf-detail-item__thumb-empty">
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
                    <div class="xsf-detail-card" style="border: 1px solid #bbf7d0;">
                        <div class="xsf-detail-card__header" style="background: #f0fdf4;">
                            <h2 class="xsf-detail-card__title" style="color: #15803d;">
                                <i class="fa-solid fa-utensils text-success"></i>
                                {{ 'Bonus Recipes Unlocked with this Order' }} ({{ $orderRecipes->count() }})
                            </h2>
                        </div>
                        <div class="xsf-detail-card__body">
                            @foreach($orderRecipes as $recipe)
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 py-2 border-bottom">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $recipe->image_src }}" alt="{{ $recipe->title }}" style="width: 50px; height: 50px; border-radius: 10px; object-fit: cover;" onerror="this.src='https://placehold.co/50x50/eee/999?text=Recipe';">
                                        <div>
                                            <a href="{{ route('recipes.show', $recipe->slug) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $recipe->title }}
                                            </a>
                                            <div class="text-muted small">
                                                {{ $recipe->total_time ? $recipe->total_time . ' min' : '30 min' }} &bull; {{ ucfirst($recipe->difficulty) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="{{ route('recipes.show', $recipe->slug) }}" class="btn btn-outline-dark btn-pill btn-sm">
                                            <i class="fa-solid fa-eye me-1"></i> View
                                        </a>
                                        <a href="{{ route('recipes.download-pdf', $recipe->slug) }}" class="btn btn-success btn-pill btn-sm">
                                            <i class="fa-solid fa-file-pdf me-1"></i> Download PDF
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Payment & Transaction Card --}}
                <div class="xsf-detail-card">
                    <div class="xsf-detail-card__header">
                        <h2 class="xsf-detail-card__title">
                            <i class="fa-solid fa-credit-card text-success"></i>
                            {{ 'Payment Information' }}
                        </h2>
                    </div>
                    <div class="xsf-detail-card__body">
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
                                        <span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>{{ 'Paid' }}</span>
                                    @elseif ($order->status === 'canceled')
                                        <span class="text-danger"><i class="fa-solid fa-circle-xmark me-1"></i>{{ 'Canceled / Refunded' }}</span>
                                    @else
                                        <span class="text-warning"><i class="fa-regular fa-clock me-1"></i>{{ 'Pending' }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right column: Summary & Shipping Address --}}
            <div class="col-lg-4">
                {{-- Order Summary --}}
                <div class="xsf-detail-card">
                    <div class="xsf-detail-card__header">
                        <h2 class="xsf-detail-card__title">
                            <i class="fa-solid fa-receipt text-secondary"></i>
                            {{ 'Order Summary' }}
                        </h2>
                    </div>
                    <div class="xsf-detail-card__body">
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
                <div class="xsf-detail-card">
                    <div class="xsf-detail-card__header">
                        <h2 class="xsf-detail-card__title">
                            <i class="fa-solid fa-location-dot text-danger"></i>
                            {{ 'Shipping Address' }}
                        </h2>
                    </div>
                    <div class="xsf-detail-card__body">
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
                <div class="p-3 bg-light rounded-4 text-center border">
                    <p class="small text-muted mb-2">{{ 'Have a question about your order?' }}</p>
                    <a href="{{ route('xylo.home') }}" class="btn btn-outline-primary btn-pill btn-sm px-3">
                        <i class="fa-regular fa-envelope me-1"></i> {{ 'Contact Support' }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
