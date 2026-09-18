@props([
    'price' => 0,
    'discountPrice' => null,
    'currency' => null,
    'size' => 'md', // sm, md, lg
])

@php
    $activeCurrency = $currency ?? activeCurrency();
    $symbol = $activeCurrency->symbol ?? '$';
    $hasDiscount = !empty($discountPrice) && (float) $discountPrice > 0 && (float) $discountPrice < (float) $price;
    $fontSizeClass = match($size) {
        'sm' => 'fs-6',
        'lg' => 'fs-4',
        default => 'fs-5',
    };
@endphp

<div {{ $attributes->merge(['class' => 'd-inline-flex align-items-baseline gap-2 flex-wrap xsf-price-tag']) }}>
    @if ($hasDiscount)
        <span class="fw-bold text-danger {{ $fontSizeClass }}">
            {{ $symbol }}{{ number_format((float) $discountPrice, 2) }}
        </span>
        <span class="text-muted text-decoration-line-through small">
            {{ $symbol }}{{ number_format((float) $price, 2) }}
        </span>
    @else
        <span class="fw-bold text-dark {{ $fontSizeClass }}">
            {{ $symbol }}{{ number_format((float) $price, 2) }}
        </span>
    @endif
</div>
