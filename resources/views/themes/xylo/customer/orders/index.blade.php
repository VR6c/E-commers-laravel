@extends('themes.xylo.layouts.master')

@section('css')
<style>
/* ─────────────────────────────────────────────
   Customer Order History Styles
   ───────────────────────────────────────────── */
.xsf-orders-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 24px;
}

.xsf-order-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    margin-bottom: 20px;
    overflow: hidden;
    transition: all 0.25s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.xsf-order-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.07);
    transform: translateY(-2px);
}

.xsf-order-card__header {
    padding: 16px 22px;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.xsf-order-card__meta-group {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.xsf-order-card__meta-item {
    display: flex;
    flex-direction: column;
}

.xsf-order-card__meta-label {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    margin-bottom: 2px;
}

.xsf-order-card__meta-val {
    font-size: 0.9rem;
    font-weight: 700;
    color: #0f172a;
}

.xsf-order-card__body {
    padding: 20px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
}

.xsf-order-items-preview {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    flex: 1;
    min-width: 250px;
}

.xsf-order-item-thumb {
    width: 58px;
    height: 58px;
    border-radius: 10px;
    object-fit: cover;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    flex-shrink: 0;
}

.xsf-order-item-thumb-empty {
    width: 58px;
    height: 58px;
    border-radius: 10px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.xsf-order-items-info {
    font-size: 0.875rem;
    color: #475569;
}

.xsf-order-card__actions {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    margin-left: auto;
}

.xsf-order-total-block {
    text-align: right;
}

.xsf-order-total-label {
    font-size: 0.75rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    display: block;
}

.xsf-order-total-val {
    font-size: 1.2rem;
    font-weight: 800;
    color: #0f172a;
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

/* Empty State */
.xsf-orders-empty {
    text-align: center;
    padding: 60px 20px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
}

.xsf-orders-empty__icon {
    width: 80px;
    height: 80px;
    background: #f1f5f9;
    color: #64748b;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    margin: 0 auto 20px;
}

.xsf-orders-empty__title {
    font-size: 1.35rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
}

.xsf-orders-empty__sub {
    font-size: 0.95rem;
    color: #64748b;
    max-width: 420px;
    margin: 0 auto 24px;
    line-height: 1.5;
}
</style>
@endsection

@section('content')
<section class="xsf-section">
    <div class="container">
        <div class="xsf-listing-head">
            <h1 class="xsf-listing-head__title">{{ 'My Orders' }}</h1>
        </div>

        <div class="row g-4">
            {{-- Account Navigation Sidebar --}}
            <aside class="col-lg-3">
                <nav class="xsf-account-nav">
                    <a href="{{ route('customer.profile.edit') }}" class="xsf-account-nav__link">
                        <i class="fa-solid fa-user-gear" aria-hidden="true"></i> {{ 'My Profile' }}
                    </a>
                    <a href="{{ route('customer.orders.index') }}" class="xsf-account-nav__link is-active">
                        <i class="fa-solid fa-box-archive" aria-hidden="true"></i> {{ 'My Orders' }}
                    </a>
                    <a href="{{ route('customer.wishlist.index') }}" class="xsf-account-nav__link">
                        <i class="fa-regular fa-heart" aria-hidden="true"></i> {{ 'Wishlist' }}
                    </a>
                    <a href="{{ route('xylo.home') }}" class="xsf-account-nav__link">
                        <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i> {{ 'Continue Shopping' }}
                    </a>
                    <a href="{{ route('customer.logout') }}" class="xsf-account-nav__link text-danger"
                        onclick="event.preventDefault(); document.getElementById('account-logout-form').submit();">
                        <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> {{ 'Logout' }}
                    </a>
                    <form id="account-logout-form" action="{{ route('customer.logout') }}" method="POST" class="d-none">@csrf</form>
                </nav>
            </aside>

            {{-- Main Orders List --}}
            <div class="col-lg-9">
                <div class="xsf-orders-header">
                    <div>
                        <h2 class="h5 fw-bold mb-1 text-dark">{{ 'Order History' }}</h2>
                        <p class="text-muted small mb-0">{{ 'Track progress, manage orders, and view past purchases.' }}</p>
                    </div>
                    <div>
                        <span class="badge bg-secondary-subtle text-dark px-3 py-2 rounded-pill fw-medium">
                            <i class="fa-solid fa-boxes-stacked me-1"></i> {{ $orders->total() }} {{ \Illuminate\Support\Str::plural('Order', $orders->total()) }}
                        </span>
                    </div>
                </div>

                @if ($orders->isEmpty())
                    <div class="xsf-orders-empty">
                        <div class="xsf-orders-empty__icon">
                            <i class="fa-solid fa-box-open"></i>
                        </div>
                        <h3 class="xsf-orders-empty__title">{{ 'No Orders Placed Yet' }}</h3>
                        <p class="xsf-orders-empty__sub">{{ 'Looks like you haven\'t made any purchases with us yet. Start exploring our premium collections today!' }}</p>
                        <a href="{{ route('xylo.home') }}" class="btn btn-primary btn-pill px-4 py-2">
                            <i class="fa-solid fa-bag-shopping me-2"></i>{{ 'Start Shopping' }}
                        </a>
                    </div>
                @else
                    @foreach ($orders as $order)
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
                            $itemCount = $order->details->sum('quantity');
                        @endphp
                        <div class="xsf-order-card">
                            <div class="xsf-order-card__header">
                                <div class="xsf-order-card__meta-group">
                                    <div class="xsf-order-card__meta-item">
                                        <span class="xsf-order-card__meta-label">{{ 'Order Number' }}</span>
                                        <span class="xsf-order-card__meta-val">#{{ $order->id }}</span>
                                    </div>
                                    <div class="xsf-order-card__meta-item">
                                        <span class="xsf-order-card__meta-label">{{ 'Date Placed' }}</span>
                                        <span class="xsf-order-card__meta-val">{{ $order->created_at ? $order->created_at->format('M d, Y') : 'N/A' }}</span>
                                    </div>
                                    <div class="xsf-order-card__meta-item">
                                        <span class="xsf-order-card__meta-label">{{ 'Payment Method' }}</span>
                                        <span class="xsf-order-card__meta-val small">
                                            <i class="fa-solid fa-credit-card me-1 text-muted"></i>
                                            @php
                                                $friendlyMethods = [
                                                    'abapayway' => 'ABA PayWay',
                                                    'aba_payway' => 'ABA PayWay',
                                                    'paypal' => 'PayPal',
                                                    'stripe' => 'Stripe',
                                                    'cod' => 'COD',
                                                ];
                                                $methodKey = strtolower($order->payment_method ?? 'cod');
                                            @endphp
                                            {{ $friendlyMethods[$methodKey] ?? strtoupper($order->payment_method ?? 'COD') }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <span class="xsf-badge {{ $statusClass }}">
                                        <i class="{{ $statusIcon }}"></i>
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>

                            <div class="xsf-order-card__body">
                                <div class="xsf-order-items-preview">
                                    @foreach ($order->details->take(3) as $detail)
                                        @php
                                            $prod = $detail->product;
                                            $imageUrl = $prod?->thumbnail?->image_url
                                                ? product_image_url($prod->thumbnail->image_url)
                                                : ($prod?->image_url ? product_image_url($prod->image_url) : null);
                                        @endphp
                                        @if ($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $prod?->name ?? 'Product' }}" class="xsf-order-item-thumb" title="{{ $prod?->name ?? 'Product' }}">
                                        @else
                                            <div class="xsf-order-item-thumb-empty" title="{{ $prod?->name ?? 'Product' }}">
                                                <i class="fa-solid fa-box"></i>
                                            </div>
                                        @endif
                                    @endforeach

                                    @if ($order->details->count() > 3)
                                        <div class="xsf-order-item-thumb-empty fw-bold text-dark small" title="{{ 'More items' }}">
                                            +{{ $order->details->count() - 3 }}
                                        </div>
                                    @endif

                                    <div class="xsf-order-items-info ms-2">
                                        <div class="fw-semibold text-dark">
                                            {{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ $order->details->first()?->product?->name ?? 'Items in order' }}
                                            @if ($order->details->count() > 1)
                                                {{ 'and ' . ($order->details->count() - 1) . ' other' . ($order->details->count() > 2 ? 's' : '') }}
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="xsf-order-card__actions">
                                    <div class="xsf-order-total-block">
                                        <span class="xsf-order-total-label">{{ 'Total' }}</span>
                                        <span class="xsf-order-total-val">{{ $currency->symbol }}{{ number_format($order->total_amount, 2) }}</span>
                                    </div>
                                    <a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-outline-dark btn-pill btn-sm px-3">
                                        <i class="fa-regular fa-eye me-1"></i>{{ 'View Details' }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Pagination --}}
                    @if ($orders->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $orders->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
