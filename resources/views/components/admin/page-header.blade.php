@props([
    'title',
    'icon'        => null,
    'subtitle'    => null,
    'createRoute' => null,
    'createLabel' => null,
    'breadcrumbs' => [],
])

<div class="vp-page-header vp-anim-fade-in" role="banner">
    <div>
        <h1 class="vp-page-header__title">
            @if ($icon)
                <span class="vp-page-header__title-icon" aria-hidden="true">
                    <i class="{{ $icon }}"></i>
                </span>
            @endif
            {{ $title }}
        </h1>

        @if ($subtitle ?? false)
            <p class="vp-page-header__sub">{{ $subtitle }}</p>
        @elseif (!empty($breadcrumbs))
            <p class="vp-page-header__sub">
                <a href="{{ route('admin.dashboard') }}" style="color:inherit;text-decoration:none;">Home</a>
                @foreach ($breadcrumbs as $label => $link)
                    <span style="margin: 0 4px; opacity:.5;">/</span>
                    @if ($loop->last)
                        <span>{{ $label }}</span>
                    @else
                        <a href="{{ $link }}" style="color:inherit;text-decoration:none;">{{ $label }}</a>
                    @endif
                @endforeach
            </p>
        @endif
    </div>

    <div class="vp-page-header__actions">
        {{ $actions ?? '' }}
        @if ($createRoute)
            <a href="{{ $createRoute }}" class="vp-btn-primary" id="page-header-create-btn">
                <i class="fas fa-plus" aria-hidden="true"></i>
                {{ $createLabel ?? __('Add New') }}
            </a>
        @endif
    </div>
</div>
