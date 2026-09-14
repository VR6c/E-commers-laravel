@extends('admin.layouts.admin')

@section('title', 'Review Details — Admin')

@section('content')

<x-admin.page-header
    :title="'Review Details'"
    :breadcrumbs="['Reviews' => route('admin.reviews.index'), 'Details #' . $review->id => '']">
    <x-slot:actions>
        <button id="toggleApproveBtn"
            class="btn shadow-xs {{ $review->is_approved ? 'btn-outline-warning' : 'btn-success' }}"
            data-id="{{ $review->id }}"
            data-approved="{{ $review->is_approved ? '1' : '0' }}">
            <i class="bi {{ $review->is_approved ? 'bi-x-circle me-1' : 'bi-check-circle me-1' }}"></i>
            <span id="toggleApproveLabel">{{ $review->is_approved ? 'Reject' : 'Approve' }}</span>
        </button>
        <a href="{{ route('admin.reviews.edit', $review->id) }}" class="btn btn-outline-primary shadow-xs">
            <i class="bi bi-pencil me-1"></i> {{ 'Edit' }}
        </a>
        <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to Reviews
        </a>
    </x-slot:actions>
</x-admin.page-header>

<div class="row g-4">
    <div class="col-lg-8">
        <x-admin.form-card :title="'Review Content'" :icon="'bi bi-chat-quote'">
            <div class="mb-4">
                <label class="form-label text-muted small fw-bold text-uppercase mb-2">
                    {{ 'Customer Rating' }}
                </label>
                <div class="d-flex align-items-center gap-2">
                    <div class="fs-4">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star{{ $i <= $review->rating ? '-fill text-warning' : ' text-muted' }}"></i>
                        @endfor
                    </div>
                    <span class="badge bg-light text-dark border px-2-5 py-1-5 fw-bold">{{ $review->rating }} / 5.0</span>
                </div>
            </div>

            <div class="mb-0">
                <label class="form-label text-muted small fw-bold text-uppercase mb-2">
                    {{ 'Written Review' }}
                </label>
                <div class="p-4 bg-light rounded" style="border: 1px solid var(--border-subtle); line-height: 1.6;">
                    {{ $review->review ?? 'No commentary provided.' }}
                </div>
            </div>
        </x-admin.form-card>
    </div>

    <div class="col-lg-4">
        <x-admin.form-card :title="'Customer & Product'" :icon="'bi bi-person-badge'">
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold text-uppercase mb-1">
                    {{ 'Customer' }}
                </label>
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                        style="width: 40px; height: 40px; font-weight: 600;">
                        {{ strtoupper(substr(optional($review->customer)->name ?? 'G', 0, 1)) }}
                    </div>
                    <div>
                        <span class="fw-semibold text-dark d-block">{{ optional($review->customer)->name ?? 'Guest User' }}</span>
                        <small class="text-muted">{{ optional($review->customer)->email ?? 'No email' }}</small>
                    </div>
                </div>
            </div>

            <hr class="my-3" style="border-color: var(--border-subtle);">

            <div class="mb-3">
                <label class="form-label text-muted small fw-bold text-uppercase mb-1">
                    {{ 'Target Product' }}
                </label>
                <div class="fw-semibold text-dark">
                    {{ $review->product?->name ?? 'N/A' }}
                </div>
            </div>

            <hr class="my-3" style="border-color: var(--border-subtle);">

            <div>
                <label class="form-label text-muted small fw-bold text-uppercase mb-1">
                    {{ 'Moderation Status' }}
                </label>
                <div id="statusBadge" class="mt-1">
                    @if ($review->is_approved)
                        <span class="vp-status-badge active"><i class="bi bi-check-circle-fill me-1"></i> Approved</span>
                    @else
                        <span class="vp-status-badge pending"><i class="bi bi-clock-history me-1"></i> Pending Approval</span>
                    @endif
                </div>
            </div>
        </x-admin.form-card>
    </div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('toggleApproveBtn');
    if (!btn) return;

    btn.addEventListener('click', function () {
        const id = this.dataset.id;

        $.ajax({
            url: '/admin/reviews/' + id + '/toggle-approve',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'PATCH',
            },
            success: function (response) {
                if (!response.success) return;

                const isApproved = response.is_approved;

                // Update button appearance
                btn.dataset.approved = isApproved ? '1' : '0';
                btn.className = 'btn shadow-sm ' + (isApproved ? 'btn-warning' : 'btn-success');
                btn.querySelector('i').className = 'bi ' + (isApproved ? 'bi-x-circle me-1' : 'bi-check-circle me-1');
                document.getElementById('toggleApproveLabel').textContent = isApproved
                    ? '{{ 'Reject' }}'
                    : '{{ 'Approve' }}';

                // Update status badge
                document.getElementById('statusBadge').innerHTML = isApproved
                    ? '<span class="badge bg-success px-3 fw-bold">{{ 'Approved' }}</span>'
                    : '<span class="badge bg-warning text-dark px-3 fw-bold">{{ 'Pending' }}</span>';

                showToast('success', response.message);
            },
            error: function () {
                showToast('error', 'Something went wrong.');
            }
        });
    });
});
</script>
@endsection
