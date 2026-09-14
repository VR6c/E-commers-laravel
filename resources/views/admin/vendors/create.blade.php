@extends('admin.layouts.admin')

@section('title', 'Register New Vendor — Admin')

@section('content')

<x-admin.page-header
    :title="'Register New Vendor'"
    :breadcrumbs="['Vendors' => route('admin.vendors.index'), 'Register' => '']">
    <x-slot:actions>
        <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('admin.vendors.store') }}" method="POST">
    @csrf

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <x-admin.form-card :title="'Vendor Account Details'" :icon="'bi bi-person-badge'">
                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label fw-semibold text-dark">{{ 'Vendor Full Name' }} <span class="text-danger">*</span></label>
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="e.g. John Doe"
                               maxlength="255"
                               required
                               autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label fw-semibold text-dark">{{ 'Email Address' }} <span class="text-danger">*</span></label>
                        <input type="email"
                               name="email"
                               id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               placeholder="vendor@company.com"
                               maxlength="255"
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label fw-semibold text-dark">{{ 'Phone Number' }}</label>
                    <input type="text"
                           name="phone"
                           id="phone"
                           class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone') }}"
                           placeholder="+855 12 345 678"
                           maxlength="20">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-section-divider">
                    <span class="form-section-divider__label">Security Credentials</span>
                    <div class="form-section-divider__line"></div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label fw-semibold text-dark">{{ 'Initial Password' }} <span class="text-danger">*</span></label>
                        <input type="password"
                               name="password"
                               id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Minimum 8 characters"
                               required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label fw-semibold text-dark">{{ 'Confirm Password' }} <span class="text-danger">*</span></label>
                        <input type="password"
                               name="password_confirmation"
                               id="password_confirmation"
                               class="form-control"
                               placeholder="Repeat password"
                               required>
                    </div>
                </div>
            </x-admin.form-card>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <x-admin.form-card :title="'Account Status'" :icon="'bi bi-shield-check'">
                <div class="mb-4">
                    <label for="status" class="form-label fw-semibold text-dark">{{ 'Initial Status' }}</label>
                    <select name="status" id="status" class="form-select">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active (Approved)</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive (Pending Review)</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4" style="border-color: var(--border-subtle);">

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                        <i class="bi bi-person-plus-fill me-1 fs-6"></i> {{ 'Register Vendor' }}
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