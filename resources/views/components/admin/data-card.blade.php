@props(['label' => 'Data Table'])

<div {{ $attributes->merge(['class' => 'admin-data-card']) }} role="region" aria-label="{{ $label }}">
    <div class="p-0">
        {{ $slot }}
    </div>
</div>
