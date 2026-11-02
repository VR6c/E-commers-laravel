@props([
    'subtotal' => 0,
    'discountAmount' => 0,
    'shipping' => null,
    'total' => 0,
    'coupon' => null,
    'currency' => null,
    'showCouponForm' => false,
    'heading' => true,
    'headingTag' => 'h2',
])

@php
    $activeCurrency = $currency ?? activeCurrency();
    $symbol = $activeCurrency->symbol ?? '$';
@endphp

<div {{ $attributes->merge(['class' => 'xsf-summary']) }}>
    @if ($heading)
        <{{ $headingTag }} class="xsf-summary__title">{{ __('Order Summary') }}</{{ $headingTag }}>
    @endif

    <div class="xsf-summary__row">
        <span>{{ __('Subtotal') }}</span>
        <span>{{ $symbol }}{{ number_format((float) $subtotal, 2) }}</span>
    </div>

    @if ((float) $discountAmount > 0)
        <div class="xsf-summary__row xsf-summary__row--discount">
            <span>
                {{ __('Coupon Discount') }}
                @if (!empty($coupon['code']))
                    ({{ $coupon['code'] }})
                @endif
            </span>
            <span>&minus;{{ $symbol }}{{ number_format((float) $discountAmount, 2) }}</span>
        </div>
    @endif

    <div class="xsf-summary__row">
        <span>{{ __('Shipping') }}</span>
        @if ($shipping === null)
            <span class="text-muted">{{ __('Calculated at next step') }}</span>
        @elseif ((float) $shipping > 0)
            <span>{{ $symbol }}{{ number_format((float) $shipping, 2) }}</span>
        @else
            <span>{{ __('Free') }}</span>
        @endif
    </div>

    @if ($showCouponForm)
        <details class="xsf-coupon">
            <summary>{{ __('Have a Coupon?') }}</summary>
            <form action="{{ route('coupon.apply') }}" method="POST" class="xsf-coupon__form" id="coupon-form">
                @csrf
                <label for="coupon-form-code" class="visually-hidden">{{ __('Enter coupon code') }}</label>
                <input type="text" name="code" id="coupon-form-code" class="form-control text-uppercase" placeholder="{{ __('Enter coupon code') }}" value="{{ $coupon['code'] ?? '' }}" autocomplete="off">
                <button type="submit" class="btn btn-dark">{{ __('Apply') }}</button>
            </form>
        </details>
    @endif

    <div class="xsf-summary__row xsf-summary__row--total">
        <span>{{ __('Total') }}</span>
        <span>{{ $symbol }}{{ number_format((float) $total, 2) }}</span>
    </div>

    @if (isset($slot) && $slot->isNotEmpty())
        <div class="mt-3">
            {{ $slot }}
        </div>
    @endif
</div>
