@extends('themes.xylo.layouts.master')

@section('content')
<section class="xsf-section">
    <div class="container">
        <div class="xsf-account-layout">
            <div class="xsf-account-layout__aside">
                @include('themes.xylo.customer._nav', ['active' => 'orders'])
            </div>

            <div class="xsf-account-layout__main">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 xsf-account-head">
                    <div>
                        <h1>{{ 'My Orders' }}</h1>
                        <p>{{ 'Track progress, manage orders, and view past purchases.' }}</p>
                    </div>
                    <span class="xsf-orders-count">{{ $orders->total() }} {{ \Illuminate\Support\Str::plural('order', $orders->total()) }}</span>
                </div>

                @if ($orders->isEmpty())
                    <div class="xsf-empty">
                        <i class="fa-solid fa-box-open" aria-hidden="true"></i>
                        <h2 class="h5">{{ 'No orders placed yet' }}</h2>
                        <p class="mb-3">{{ 'Looks like you haven\'t made any purchases with us yet. Start exploring our collections today!' }}</p>
                        <a href="{{ route('xylo.home') }}" class="btn btn-primary">
                            <i class="fa-solid fa-bag-shopping me-2" aria-hidden="true"></i>{{ 'Start Shopping' }}
                        </a>
                    </div>
                @else
                    @php
                        $friendlyMethods = [
                            'abapayway' => 'ABA PayWay',
                            'aba_payway' => 'ABA PayWay',
                            'paypal' => 'PayPal',
                            'stripe' => 'Stripe',
                            'cod' => 'COD',
                        ];
                        $statusIcons = [
                            'pending'    => 'fa-regular fa-clock',
                            'processing' => 'fa-solid fa-rotate',
                            'completed'  => 'fa-solid fa-circle-check',
                            'canceled'   => 'fa-solid fa-ban',
                        ];
                    @endphp

                    {{-- Phones: cards --}}
                    <div class="xsf-order-cards">
                        @foreach ($orders as $order)
                            @php
                                $statusKey = array_key_exists($order->status, $statusIcons) ? $order->status : 'pending';
                                $statusIcon = $statusIcons[$order->status] ?? 'fa-regular fa-circle';
                                $itemCount = $order->details->sum('quantity');
                                $methodKey = strtolower($order->payment_method ?? 'cod');
                            @endphp
                            <article class="xsf-order-card">
                                <div class="xsf-order-card__row">
                                    <div>
                                        <div class="xsf-order-card__id">#{{ $order->id }}</div>
                                        <div class="xsf-order-card__meta">{{ $order->created_at ? $order->created_at->format('M d, Y') : 'N/A' }}</div>
                                    </div>
                                    <span class="xsf-status xsf-status--{{ $statusKey }}">
                                        <i class="{{ $statusIcon }}" aria-hidden="true"></i>
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>

                                <div class="xsf-order-thumbs">
                                    @foreach ($order->details->take(3) as $detail)
                                        @php
                                            $prod = $detail->product;
                                            $imageUrl = $prod?->thumbnail?->image_url
                                                ? product_image_url($prod->thumbnail->image_url)
                                                : ($prod?->image_url ? product_image_url($prod->image_url) : null);
                                        @endphp
                                        @if ($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $prod?->name ?? 'Product' }}" class="xsf-order-thumb" title="{{ $prod?->name ?? 'Product' }}" loading="lazy">
                                        @else
                                            <div class="xsf-order-thumb" title="{{ $prod?->name ?? 'Product' }}"><i class="fa-solid fa-box" aria-hidden="true"></i></div>
                                        @endif
                                    @endforeach
                                    @if ($order->details->count() > 3)
                                        <div class="xsf-order-thumb" title="{{ 'More items' }}">+{{ $order->details->count() - 3 }}</div>
                                    @endif
                                    <span class="xsf-order-card__meta ms-1">{{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }}</span>
                                </div>

                                <div class="xsf-order-card__row">
                                    <div>
                                        <div class="xsf-order-card__total">{{ $currency->symbol }}{{ number_format($order->total_amount, 2) }}</div>
                                        <div class="xsf-order-card__meta">{{ $friendlyMethods[$methodKey] ?? strtoupper($order->payment_method ?? 'COD') }}</div>
                                    </div>
                                    <a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-outline-dark btn-sm">
                                        {{ 'View Details' }}
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    {{-- md+: table --}}
                    <div class="xsf-order-table-wrap">
                        <table class="xsf-order-table">
                            <caption class="visually-hidden">{{ 'Order History' }}</caption>
                            <thead>
                                <tr>
                                    <th scope="col">{{ 'Order Number' }}</th>
                                    <th scope="col">{{ 'Date Placed' }}</th>
                                    <th scope="col">Items</th>
                                    <th scope="col">{{ 'Payment Method' }}</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="is-num">{{ 'Total' }}</th>
                                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    @php
                                        $statusKey = array_key_exists($order->status, $statusIcons) ? $order->status : 'pending';
                                        $statusIcon = $statusIcons[$order->status] ?? 'fa-regular fa-circle';
                                        $itemCount = $order->details->sum('quantity');
                                        $methodKey = strtolower($order->payment_method ?? 'cod');
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">#{{ $order->id }}</td>
                                        <td>{{ $order->created_at ? $order->created_at->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            {{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }}
                                            <div class="text-muted small text-truncate" style="max-width: 14rem;">
                                                {{ $order->details->first()?->product?->name ?? 'Items in order' }}@if ($order->details->count() > 1) {{ '+ ' . ($order->details->count() - 1) . ' more' }}@endif
                                            </div>
                                        </td>
                                        <td>{{ $friendlyMethods[$methodKey] ?? strtoupper($order->payment_method ?? 'COD') }}</td>
                                        <td>
                                            <span class="xsf-status xsf-status--{{ $statusKey }}">
                                                <i class="{{ $statusIcon }}" aria-hidden="true"></i>
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="is-num fw-semibold">{{ $currency->symbol }}{{ number_format($order->total_amount, 2) }}</td>
                                        <td class="is-num">
                                            <a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-outline-dark btn-sm">
                                                {{ 'View Details' }}<span class="visually-hidden"> #{{ $order->id }}</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

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
