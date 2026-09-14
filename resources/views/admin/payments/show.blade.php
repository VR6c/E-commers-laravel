@extends('admin.layouts.admin')

@section('title', 'Payment Details — Admin')

@section('content')

<x-admin.page-header
    :title="'Payment #' . $payment->id"
    :breadcrumbs="['Payments' => route('admin.payments.index'), 'Details #' . $payment->id => '']">
    <x-slot:actions>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to Payments
        </a>
    </x-slot:actions>
</x-admin.page-header>

<div class="row g-4">
    <div class="col-lg-8">
        <x-admin.form-card :title="'Payment Information'" :icon="'bi bi-credit-card'">
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-bold text-uppercase mb-1">{{ 'Payment Amount' }}</label>
                    <div class="fs-3 fw-bold text-primary">${{ number_format($payment->amount, 2) }}</div>
                </div>
                <div class="col-md-6 text-md-end">
                    <label class="form-label text-muted small fw-bold text-uppercase mb-1 d-block">{{ 'Payment Status' }}</label>
                    @if($payment->status === 'completed' || $payment->status === 'paid')
                        <span class="vp-status-badge active fs-6"><i class="bi bi-check-circle-fill me-1"></i> Paid / Completed</span>
                    @elseif($payment->status === 'pending')
                        <span class="vp-status-badge pending fs-6"><i class="bi bi-clock-history me-1"></i> Pending</span>
                    @else
                        <span class="vp-status-badge cancelled fs-6"><i class="bi bi-x-circle-fill me-1"></i> {{ ucfirst($payment->status) }}</span>
                    @endif
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-bold text-uppercase mb-1">{{ 'Transaction Identifier' }}</label>
                    <div class="p-2-5 bg-light rounded font-monospace small" style="border: 1px solid var(--border-subtle);">
                        {{ $payment->transaction_id ?: 'N/A' }}
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-bold text-uppercase mb-1">{{ 'Payment Gateway' }}</label>
                    <div class="p-2-5 bg-light rounded text-dark fw-medium" style="border: 1px solid var(--border-subtle);">
                        <i class="bi bi-wallet2 me-1 text-primary"></i> {{ $payment->gateway->name ?? 'Standard Gateway' }}
                    </div>
                </div>
            </div>
        </x-admin.form-card>
    </div>

    <div class="col-lg-4">
        <x-admin.form-card :title="'Payer & Order Link'" :icon="'bi bi-receipt'">
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold text-uppercase mb-1">{{ 'Payer / User' }}</label>
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                        style="width: 40px; height: 40px; font-weight: 600;">
                        {{ strtoupper(substr($payment->user->name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <span class="fw-semibold text-dark d-block">{{ $payment->user->name ?? 'Guest Payer' }}</span>
                        <small class="text-muted">{{ $payment->user->email ?? 'No email' }}</small>
                    </div>
                </div>
            </div>

            <hr class="my-3" style="border-color: var(--border-subtle);">

            <div class="mb-3">
                <label class="form-label text-muted small fw-bold text-uppercase mb-1">{{ 'Associated Order' }}</label>
                <div class="fw-semibold">
                    @if($payment->order)
                        <span class="text-primary fw-bold">Order #{{ $payment->order->id }}</span>
                    @else
                        <span class="text-muted">No associated order</span>
                    @endif
                </div>
            </div>

            <hr class="my-3" style="border-color: var(--border-subtle);">

            <div class="mb-0">
                <label class="form-label text-muted small fw-bold text-uppercase mb-1">{{ 'Recorded Date' }}</label>
                <div class="text-dark fw-medium">{{ $payment->created_at->format('M d, Y · H:i A') }}</div>
            </div>
        </x-admin.form-card>
    </div>
</div>
@endsection