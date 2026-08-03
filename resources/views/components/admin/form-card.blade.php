@props([
    'title' => null,
    'icon'  => null,
])

{{-- Admin form card using vp-* design system with optional icon + ARIA region --}}
<div {{ $attributes->merge(['class' => 'vp-card vp-anim-slide-up']) }}
     @isset($title) role="region" aria-label="{{ $title }}" @endisset>

    @if ($title)
        <div class="vp-card-header">
            <h5 class="vp-card-header__title">
                @if ($icon)
                    <span class="vp-card-header__icon" aria-hidden="true">
                        <i class="{{ $icon }}"></i>
                    </span>
                @endif
                {{ $title }}
            </h5>
            @isset($headerActions)
                <div class="d-flex align-items-center gap-2">{{ $headerActions }}</div>
            @endisset
        </div>
    @endif

    <div class="vp-card-body">
        {{ $slot }}
    </div>

    @isset($footer)
        <div style="padding: 14px 20px; border-top: 1px solid var(--vp-border-sub); background: var(--vp-surface-muted); display: flex; justify-content: flex-end; gap: 8px;">
            {{ $footer }}
        </div>
    @endisset
</div>
