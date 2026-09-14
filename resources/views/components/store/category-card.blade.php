@props([
    'category',
])

@php
    $imageUrl = optimized_image_url($category->image_url ?? 'default.jpg');
    $name = $category->name ?? 'Category';
    $slug = $category->slug ?? '#';
@endphp

<a href="{{ route('category.show', $slug) }}" {{ $attributes->merge(['class' => 'xsf-category-card']) }}>
    <div class="xsf-category-card__media">
        <img src="{{ $imageUrl }}"
             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&auto=format&fit=crop&q=80';"
             loading="lazy"
             decoding="async"
             width="110"
             height="110"
             alt="{{ $name }}"
             class="xsf-category-card__img">
    </div>
    <span class="xsf-category-card__name">{{ $name }}</span>
</a>
