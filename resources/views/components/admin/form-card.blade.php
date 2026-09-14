@props([
    'title'    => null,
    'icon'     => null,
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'admin-card']) }}
     @isset($title) role="region" aria-label="{{ $title }}" @endisset>

    @if ($title)
        <div class="admin-card__header">
            <div class="d-flex align-items-center gap-2">
                @if ($icon)
                    <div class="admin-card__header-icon" aria-hidden="true">
                        <i class="{{ $icon }}"></i>
                    </div>
                @endif
                <div>
                    <h5 class="admin-card__title">{{ $title }}</h5>
                    @if ($subtitle)
                        <small class="text-muted d-block" style="font-size: 0.78rem;">{{ $subtitle }}</small>
                    @endif
                </div>
            </div>
            @isset($headerActions)
                <div class="d-flex align-items-center gap-2">{{ $headerActions }}</div>
            @endisset
        </div>
    @endif

    <div class="admin-card__body">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="admin-card__footer">
            {{ $footer }}
        </div>
    @endisset
</div>
