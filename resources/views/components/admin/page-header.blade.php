@props([
    'title',
    'icon'        => null,
    'subtitle'    => null,
    'badge'       => null,
    'createRoute' => null,
    'createLabel' => null,
    'breadcrumbs' => [],
])

<div class="admin-page-header" role="banner">
    <div class="admin-page-header__left">
        @if (!empty($breadcrumbs))
            @php
                $resolvedHome = $homeRoute ?? (auth()->guard('vendor')->check() ? route('vendor.dashboard') : route('admin.dashboard'));
            @endphp
            <nav class="admin-page-header__breadcrumb" aria-label="Breadcrumb">
                <a href="{{ $resolvedHome }}"><i class="bi bi-house-door me-1"></i>Home</a>
                @foreach ($breadcrumbs as $label => $link)
                    <span class="sep"><i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i></span>
                    @if ($loop->last)
                        <span class="current">{{ $label }}</span>
                    @else
                        <a href="{{ $link }}">{{ $label }}</a>
                    @endif
                @endforeach
            </nav>
        @endif

        <div class="d-flex align-items-center gap-3 mt-1">
            @if ($icon)
                <div class="admin-page-header__icon-badge">
                    <i class="{{ $icon }}"></i>
                </div>
            @endif
            <div>
                <h1 class="admin-page-header__title">
                    {{ $title }}
                    @if ($badge)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill ms-2" style="font-size: 0.72rem; font-weight: 600; vertical-align: middle;">
                            {{ $badge }}
                        </span>
                    @endif
                </h1>
                @if ($subtitle)
                    <p class="admin-page-header__subtitle">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="admin-page-header__actions">
        {{ $actions ?? '' }}
        @if ($createRoute)
            <a href="{{ $createRoute }}" class="btn btn-primary shadow-sm" id="page-header-create-btn">
                <i class="bi bi-plus-lg me-1"></i>
                {{ $createLabel ?? __('Add New') }}
            </a>
        @endif
    </div>
</div>
