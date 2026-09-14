@props([
    'type' => 'sale',
    'text' => null,
])

@php
    $text = $text ?? match($type) {
        'sale' => 'Sale',
        'new' => 'New',
        'hot' => 'Hot',
        default => ucfirst($type),
    };

    $badgeClass = match($type) {
        'sale' => 'xsf-badge--sale',
        'new' => 'xsf-badge--new',
        'hot' => 'xsf-badge--hot',
        default => 'xsf-badge--default',
    };
@endphp

<span {{ $attributes->merge(['class' => 'xsf-badge ' . $badgeClass]) }}>
    {{ $text }}
</span>
