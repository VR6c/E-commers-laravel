@props([
    'icon'        => 'bi bi-inbox',
    'title'       => 'No items found',
    'description' => 'There are no records to display at the moment.',
    'actionRoute' => null,
    'actionLabel' => null,
])

<div class="text-center py-5 px-3">
    <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 64px; height: 64px; background: var(--neutral-100); color: var(--neutral-400);">
        <i class="{{ $icon }}" style="font-size: 1.75rem;"></i>
    </div>
    <h5 class="fw-bold text-dark mb-1" style="font-size: 1.05rem;">{{ $title }}</h5>
    <p class="text-muted small mb-3 mx-auto" style="max-width: 360px;">{{ $description }}</p>
    @if ($actionRoute && $actionLabel)
        <a href="{{ $actionRoute }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> {{ $actionLabel }}
        </a>
    @endif
</div>
