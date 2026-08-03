@props(['label' => 'Data Table'])

{{-- Card wrapper for admin index tables / content --}}
<div {{ $attributes->merge(['class' => 'card card-primary card-outline admin-data-card']) }} role="region" aria-label="{{ $label }}">
    <div class="card-body p-0">
        {{ $slot }}
    </div>
</div>

