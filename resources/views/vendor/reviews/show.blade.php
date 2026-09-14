@extends('vendor.layouts.master')

@section('title', 'Review Details')

@section('content')

<x-admin.page-header
    :title="'Review #' . $review->id"
    icon="bi bi-star-fill"
    :subtitle="'Customer feedback for ' . ($review->product?->name ?? 'Product')"
    :breadcrumbs="['Reviews' => route('vendor.reviews.index'), 'Review #' . $review->id => '#']">
    <x-slot:actions>
        <a href="{{ route('vendor.reviews.index') }}"
           class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 shadow-sm"
           style="border-radius:10px; font-size:.85rem;">
            <i class="bi bi-arrow-left"></i> Back to Reviews
        </a>
    </x-slot:actions>
</x-admin.page-header>

<div class="row g-4">

    {{-- Review content --}}
    <div class="col-lg-8">
        <x-admin.form-card :title="'Customer Review'" icon="bi bi-chat-square-quote-fill" class="mb-4">
            <x-slot:headerActions>
                @if($review->is_approved)
                    <span class="badge bg-success-soft px-3 py-1">
                        <i class="bi bi-check-circle-fill me-1"></i>Approved
                    </span>
                @else
                    <span class="badge bg-warning-soft px-3 py-1">
                        <i class="bi bi-clock-fill me-1"></i>Pending
                    </span>
                @endif
            </x-slot:headerActions>

            @php $stars = intval($review->rating ?? 0); @endphp
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="d-flex align-items-center gap-1">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star-fill"
                           style="font-size:1.2rem;color:{{ $i <= $stars ? '#f59e0b' : '#e2e8f0' }};"></i>
                    @endfor
                </div>
                <div class="d-flex flex-column">
                    <span class="fs-4 fw-bold text-dark lh-1">
                        {{ number_format((float) ($review->rating ?? 0), 1) }}
                    </span>
                    <small class="text-muted">out of 5 stars</small>
                </div>
            </div>

            <div class="p-3 bg-light rounded-3 border">
                @if($review->review)
                    <p class="mb-0 text-dark" style="font-size: .95rem; line-height: 1.7;">
                        "{{ $review->review }}"
                    </p>
                @else
                    <p class="text-muted mb-0 fst-italic">
                        No written review provided.
                    </p>
                @endif
            </div>
        </x-admin.form-card>
    </div>

    {{-- Sidebar meta --}}
    <div class="col-lg-4">

        {{-- Customer --}}
        <x-admin.form-card :title="'Customer'" icon="bi bi-person-fill" class="mb-4">
            <div class="d-flex align-items-center gap-3">
                <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#818cf8);color:#fff;font-size:1rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    {{ strtoupper(substr(optional($review->customer)->name ?? 'G', 0, 1)) }}
                </div>
                <div>
                    <div class="fw-bold text-dark">
                        {{ optional($review->customer)->name ?? 'Guest Customer' }}
                    </div>
                    @if(optional($review->customer)->email)
                        <div class="text-muted small">
                            {{ $review->customer->email }}
                        </div>
                    @endif
                </div>
            </div>
        </x-admin.form-card>

        {{-- Product --}}
        <x-admin.form-card :title="'Product'" icon="bi bi-box-seam" class="mb-4">
            <div class="fw-semibold text-dark mb-2">
                {{ $review->product?->name ?? 'Deleted Product' }}
            </div>
            @if($review->product)
                <a href="{{ route('vendor.products.edit', $review->product->id) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2 rounded-pill">
                    <i class="bi bi-box-arrow-up-right"></i> View Product
                </a>
            @endif
        </x-admin.form-card>

        {{-- Meta details --}}
        <x-admin.form-card :title="'Details'" icon="bi bi-info-circle-fill">
            <dl class="mb-0 small">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <dt class="text-muted fw-normal">Review ID</dt>
                    <dd class="fw-bold mb-0">#{{ $review->id }}</dd>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <dt class="text-muted fw-normal">Rating</dt>
                    <dd class="fw-bold mb-0 text-warning">{{ $stars }}/5 ★</dd>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <dt class="text-muted fw-normal">Status</dt>
                    <dd class="mb-0">
                        <span class="badge {{ $review->is_approved ? 'bg-success-soft' : 'bg-warning-soft' }}">
                            {{ $review->is_approved ? 'Approved' : 'Pending' }}
                        </span>
                    </dd>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <dt class="text-muted fw-normal">Submitted</dt>
                    <dd class="fw-semibold mb-0">{{ $review->created_at?->format('M j, Y') ?? '—' }}</dd>
                </div>
            </dl>
        </x-admin.form-card>

    </div>
</div>

@endsection
