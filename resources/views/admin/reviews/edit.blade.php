@extends('admin.layouts.admin')

@section('title', 'Edit Review — Admin')

@section('content')

<x-admin.page-header
    :title="'Edit Review'"
    :breadcrumbs="['Reviews' => route('admin.reviews.index'), 'Edit #' . $review->id => '']">
    <x-slot:actions>
        <a href="{{ route('admin.reviews.show', $review->id) }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to Review
        </a>
    </x-slot:actions>
</x-admin.page-header>

<div class="row g-4">
    <div class="col-lg-8">
        <x-admin.form-card :title="'Edit Review Content'" :icon="'bi bi-chat-quote'">
            <form action="{{ route('admin.reviews.update', $review->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Rating --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">{{ 'Rating' }} <span class="text-danger">*</span></label>
                    <div id="editStarWrapper" class="d-flex gap-2 fs-3 py-1" style="cursor:pointer;">
                        @for ($i = 1; $i <= 5; $i++)
                            <span class="edit-star" data-value="{{ $i }}"
                                style="color: {{ $i <= $review->rating ? '#f59e0b' : '#cbd5e1' }};">&#9733;</span>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" id="editRatingInput" value="{{ old('rating', $review->rating) }}">
                    @error('rating')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Review text --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">{{ 'Review Commentary' }}</label>
                    <textarea name="review"
                              class="form-control @error('review') is-invalid @enderror"
                              rows="5">{{ old('review', $review->review) }}</textarea>
                    @error('review')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Approval status --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">{{ 'Status' }}</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch"
                            name="is_approved" id="isApprovedSwitch" value="1"
                            {{ old('is_approved', $review->is_approved) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-dark" for="isApprovedSwitch">
                            {{ 'Approve & Display on Storefront' }}
                        </label>
                    </div>
                </div>

                <hr class="my-4" style="border-color: var(--border-subtle);">

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary shadow-sm py-2-5 px-4">
                        <i class="bi bi-check-circle-fill me-1"></i> {{ 'Save Changes' }}
                    </button>
                    <a href="{{ route('admin.reviews.show', $review->id) }}" class="btn btn-secondary py-2-5 px-4">
                        {{ 'Cancel' }}
                    </a>
                </div>
            </form>
        </x-admin.form-card>
    </div>

    {{-- Sidebar: review meta --}}
    <div class="col-lg-4">
        <x-admin.form-card :title="'Review Metadata'" :icon="'bi bi-info-circle'">
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold text-uppercase mb-1">
                    {{ 'Customer' }}
                </label>
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                        style="width: 38px; height: 38px; font-weight: 600;">
                        {{ strtoupper(substr(optional($review->customer)->name ?? 'G', 0, 1)) }}
                    </div>
                    <span class="fw-semibold text-dark">{{ optional($review->customer)->name ?? 'Guest User' }}</span>
                </div>
            </div>

            <hr class="my-3" style="border-color: var(--border-subtle);">

            <div class="mb-3">
                <label class="form-label text-muted small fw-bold text-uppercase mb-1">
                    {{ 'Product' }}
                </label>
                <div class="fw-semibold text-dark">{{ $review->product?->name ?? 'N/A' }}</div>
            </div>

            <hr class="my-3" style="border-color: var(--border-subtle);">

            <div>
                <label class="form-label text-muted small fw-bold text-uppercase mb-1">Submitted At</label>
                <div class="text-muted small">{{ $review->created_at?->format('M j, Y \a\t g:i A') ?? '—' }}</div>
            </div>
        </x-admin.form-card>
    </div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const stars = document.querySelectorAll('#editStarWrapper .edit-star');
    const ratingInput = document.getElementById('editRatingInput');

    stars.forEach(star => {
        star.addEventListener('mouseover', function () {
            const val = parseInt(this.dataset.value);
            stars.forEach(s => {
                s.style.color = parseInt(s.dataset.value) <= val ? 'gold' : '#ccc';
            });
        });

        star.addEventListener('mouseout', function () {
            const current = parseInt(ratingInput.value) || 0;
            stars.forEach(s => {
                s.style.color = parseInt(s.dataset.value) <= current ? 'gold' : '#ccc';
            });
        });

        star.addEventListener('click', function () {
            const val = parseInt(this.dataset.value);
            ratingInput.value = val;
            stars.forEach(s => {
                s.style.color = parseInt(s.dataset.value) <= val ? 'gold' : '#ccc';
            });
        });
    });
});
</script>
@endsection
