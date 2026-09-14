@extends('admin.layouts.admin')

@section('title', 'Refund Details — Admin')

@section('content')

<x-admin.page-header
    :title="'Refund #' . $refund->id"
    :breadcrumbs="['Refunds' => route('admin.refunds.index'), 'Details #' . $refund->id => '']">
    <x-slot:actions>
        <a href="{{ route('admin.refunds.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to Refunds
        </a>
    </x-slot:actions>
</x-admin.page-header>

<div class="row g-4">
    <div class="col-lg-8">
        <x-admin.form-card :title="'Refund Information'" :icon="'bi bi-arrow-return-left'">
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-bold text-uppercase mb-1">{{ 'Refund Amount' }}</label>
                    <div class="fs-3 fw-bold text-danger">${{ number_format($refund->amount, 2) }}</div>
                </div>
                <div class="col-md-6 text-md-end">
                    <label class="form-label text-muted small fw-bold text-uppercase mb-1 d-block">{{ 'Refund Status' }}</label>
                    @if($refund->status === 'completed')
                        <span class="vp-status-badge active fs-6"><i class="bi bi-check-circle-fill me-1"></i> Completed</span>
                    @elseif($refund->status === 'pending')
                        <span class="vp-status-badge pending fs-6"><i class="bi bi-clock-history me-1"></i> Pending</span>
                    @else
                        <span class="vp-status-badge cancelled fs-6"><i class="bi bi-x-circle-fill me-1"></i> {{ ucfirst($refund->status) }}</span>
                    @endif
                </div>
            </div>

            <div class="mb-0">
                <label class="form-label text-muted small fw-bold text-uppercase mb-2">{{ 'Refund Reason' }}</label>
                <div class="p-3 bg-light rounded" style="border: 1px solid var(--border-subtle); line-height: 1.6;">
                    {{ $refund->reason ?: 'No explicit refund reason specified.' }}
                </div>
            </div>
        </x-admin.form-card>
    </div>

    <div class="col-lg-4">
        <x-admin.form-card :title="'Payment & Timestamps'" :icon="'bi bi-clock-history'">
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold text-uppercase mb-1">{{ 'Associated Payment' }}</label>
                <div class="fw-semibold text-dark">
                    @if($refund->payment)
                        <div class="text-primary fw-bold">Payment #{{ $refund->payment->id }}</div>
                        <div class="text-muted small mt-1">
                            Amount: ${{ number_format($refund->payment->amount, 2) }}<br>
                            Status: <span class="text-capitalize">{{ $refund->payment->status }}</span>
                        </div>
                    @else
                        <span class="text-muted">{{ 'N/A' }}</span>
                    @endif
                </div>
            </div>

            <hr class="my-3" style="border-color: var(--border-subtle);">

            <div class="mb-3">
                <label class="form-label text-muted small fw-bold text-uppercase mb-1">{{ 'Created At' }}</label>
                <div class="text-dark fw-medium">{{ $refund->created_at->format('M d, Y · H:i') }}</div>
            </div>

            <div class="mb-0">
                <label class="form-label text-muted small fw-bold text-uppercase mb-1">{{ 'Updated At' }}</label>
                <div class="text-muted small">{{ $refund->updated_at->format('M d, Y · H:i') }}</div>
            </div>
        </x-admin.form-card>
    </div>
</div>
@endsection