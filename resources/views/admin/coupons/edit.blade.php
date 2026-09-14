@extends('admin.layouts.admin')

@section('title', 'Edit Coupon — Admin')

@section('content')

<x-admin.page-header
    :title="'Edit Coupon'"
    :breadcrumbs="['Coupons' => route('admin.coupons.index'), 'Edit #' . $coupon->id => '']">
    <x-slot:actions>
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to Coupons
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <x-admin.form-card :title="'Coupon Details'" :icon="'bi bi-ticket-perforated'">
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">{{ 'Coupon Code' }} <span class="text-danger">*</span></label>
                    <input type="text"
                           name="code"
                           class="form-control text-uppercase @error('code') is-invalid @enderror"
                           value="{{ old('code', $coupon->code) }}"
                           placeholder="e.g. SUMMER25, FLASH50, WELCOME10"
                           style="letter-spacing: 0.05em; font-weight: 600;"
                           required
                           autofocus>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <x-admin.combobox
                            name="type"
                            :label="'Discount Type'"
                            :selected="$coupon->type"
                            :options="[
                                'percentage' => 'Percentage Discount (%)',
                                'fixed' => 'Fixed Amount Discount ($)',
                            ]"
                            required />
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold text-dark">{{ 'Discount Value' }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-percent"></i></span>
                            <input type="number"
                                   step="0.01"
                                   name="discount"
                                   class="form-control @error('discount') is-invalid @enderror"
                                   value="{{ old('discount', $coupon->discount) }}"
                                   placeholder="e.g. 10.00"
                                   required>
                        </div>
                        @error('discount')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </x-admin.form-card>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <x-admin.form-card :title="'Validity & Expiry'" :icon="'bi bi-calendar-event'">
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">{{ 'Expiration Date' }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                        <input type="date"
                               name="expires_at"
                               class="form-control @error('expires_at') is-invalid @enderror"
                               value="{{ old('expires_at', $coupon->expires_at ? $coupon->expires_at->format('Y-m-d') : '') }}">
                    </div>
                    @error('expires_at')
                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                    @enderror
                    <small class="text-muted mt-1 d-block">Leave blank for no expiration date.</small>
                </div>

                <hr class="my-4" style="border-color: var(--border-subtle);">

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                        <i class="bi bi-check-circle-fill me-1"></i> {{ 'Update Coupon' }}
                    </button>
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary py-2">
                        {{ 'Cancel' }}
                    </a>
                </div>
            </x-admin.form-card>
        </div>
    </div>
</form>

@endsection
