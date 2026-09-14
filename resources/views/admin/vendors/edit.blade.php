@extends('admin.layouts.admin')

@section('title', 'Edit Vendor — Admin')

@section('content')

<x-admin.page-header
    :title="'Edit Vendor'"
    :subtitle="'#' . $vendor->id . ' · ' . $vendor->name"
    :breadcrumbs="['Vendors' => route('admin.vendors.index'), 'Edit' => '']">
    <x-slot:actions>
        <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('admin.vendors.update', $vendor->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <x-admin.form-card :title="'Vendor Account Details'" :icon="'bi bi-person-badge'">
                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold text-dark">{{ 'Vendor Full Name' }} <span class="text-danger">*</span></label>
                        <input type="text"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $vendor->name) }}"
                               maxlength="255"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold text-dark">{{ 'Email Address' }} <span class="text-danger">*</span></label>
                        <input type="email"
                               name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $vendor->email) }}"
                               maxlength="255"
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">{{ 'Phone Number' }}</label>
                    <input type="text"
                           name="phone"
                           class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $vendor->phone) }}"
                           maxlength="20">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-section-divider">
                    <span class="form-section-divider__label">Change Password (Leave blank to keep current)</span>
                    <div class="form-section-divider__line"></div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold text-dark">{{ 'New Password' }}</label>
                        <input type="password"
                               name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Minimum 8 characters">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold text-dark">{{ 'Confirm New Password' }}</label>
                        <input type="password"
                               name="password_confirmation"
                               class="form-control"
                               placeholder="Repeat new password">
                    </div>
                </div>
            </x-admin.form-card>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <x-admin.form-card :title="'Account Management'" :icon="'bi bi-shield-check'">
                <div class="mb-4">
                    <label for="status" class="form-label fw-semibold text-dark">{{ 'Account Status' }}</label>
                    <select name="status" id="status" class="form-select">
                        <option value="active" {{ old('status', $vendor->status ?? 'active') === 'active' ? 'selected' : '' }}>Active (Approved)</option>
                        <option value="inactive" {{ old('status', $vendor->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive (Suspended)</option>
                    </select>
                </div>

                <div class="mb-3 text-muted small">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Registered:</span>
                        <span class="text-dark fw-medium">{{ $vendor->created_at ? $vendor->created_at->format('M d, Y') : '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Role:</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Merchant</span>
                    </div>
                </div>

                <hr class="my-4" style="border-color: var(--border-subtle);">

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                        <i class="bi bi-save me-1 fs-6"></i> {{ 'Update Vendor' }}
                    </button>
                    <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary py-2">
                        {{ 'Cancel' }}
                    </a>
                </div>
            </x-admin.form-card>
        </div>
    </div>
</form>

@endsection
