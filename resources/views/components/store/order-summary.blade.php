@props([
    'subtotal' => 0,
    'discountAmount' => 0,
    'shipping' => null,
    'total' => 0,
    'coupon' => null,
    'currency' => null,
    'showCouponForm' => false,
])

@php
    $activeCurrency = $currency ?? activeCurrency();
    $symbol = $activeCurrency->symbol ?? '$';
@endphp

<div {{ $attributes->merge(['class' => 'card border-0 shadow-sm rounded-4 p-4 xsf-summary-card']) }}>
    <h3 class="fs-5 fw-bold text-dark mb-4 pb-2 border-bottom">{{ __('Order Summary') }}</h3>

    <div class="d-flex justify-content-between mb-3 text-secondary">
        <span>{{ __('Subtotal') }}</span>
        <span class="fw-semibold text-dark">{{ $symbol }}{{ number_format((float) $subtotal, 2) }}</span>
    </div>

    @if ((float) $discountAmount > 0)
        <div class="d-flex justify-content-between mb-3 text-success">
            <span>
                {{ __('Coupon Discount') }}
                @if (!empty($coupon['code']))
                    <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">{{ $coupon['code'] }}</span>
                @endif
            </span>
            <span class="fw-semibold">-{{ $symbol }}{{ number_format((float) $discountAmount, 2) }}</span>
        </div>
    @endif

    <div class="d-flex justify-content-between mb-3 text-secondary">
        <span>{{ __('Shipping') }}</span>
        <span>
            @if ($shipping !== null && (float) $shipping > 0)
                <span class="fw-semibold text-dark">{{ $symbol }}{{ number_format((float) $shipping, 2) }}</span>
            @else
                <span class="text-success fw-medium">{{ __('Free') }}</span>
            @endif
        </span>
    </div>

    @if ($showCouponForm)
        <div class="my-3 pt-2 border-top">
            <form action="{{ route('coupon.apply') }}" method="POST" class="d-flex gap-2" id="coupon-form">
                @csrf
                <input type="text" name="code" class="form-control rounded-pill text-uppercase" placeholder="{{ __('Enter coupon code') }}" value="{{ $coupon['code'] ?? '' }}">
                <button type="submit" class="btn btn-dark rounded-pill px-4 flex-shrink-0">{{ __('Apply') }}</button>
            </form>
        </div>
    @endif

    <div class="d-flex justify-content-between pt-3 mt-2 border-top align-items-baseline">
        <span class="fs-5 fw-bold text-dark">{{ __('Total') }}</span>
        <span class="fs-4 fw-bolder text-primary">
            {{ $symbol }}{{ number_format((float) $total, 2) }}
        </span>
    </div>

    @if (isset($slot) && $slot->isNotEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>
