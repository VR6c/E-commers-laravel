@props([
    'icon' => 'fas fa-truck-fast',
    'title' => '',
    'description' => '',
])

<div {{ $attributes->merge(['class' => 'xsf-feature-card']) }}>
    <div class="xsf-feature-card__icon-wrap">
        <i class="{{ $icon }}" aria-hidden="true"></i>
    </div>
    <div class="xsf-feature-card__content">
        <h3 class="xsf-feature-card__title">{{ $title }}</h3>
        <p class="xsf-feature-card__desc">{{ $description }}</p>
    </div>
</div>
