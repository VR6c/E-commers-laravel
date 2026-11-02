@props([
    'category',
])

@php
    $imageUrl = optimized_image_url($category->image_url ?? 'default.jpg');
    $name = $category->name ?? 'Category';
    $slug = $category->slug ?? '#';
@endphp

<a href="{{ route('category.show', $slug) }}" {{ $attributes->merge(['class' => 'xsf-category-card']) }}>
    <img src="{{ $imageUrl }}"
         onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&auto=format&fit=crop&q=80';"
         loading="lazy"
         decoding="async"
         width="320"
         height="400"
         alt=""
         class="xsf-category-card__img">
    <span class="xsf-category-card__shade" aria-hidden="true"></span>
    <span class="xsf-category-card__label">
        <span class="xsf-category-card__name">{{ $name }}</span>
        <span class="xsf-category-card__cta">
            {{ 'Shop now' }}
            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </span>
    </span>
</a>
