@extends('admin.layouts.admin')

@section('title', 'Edit Customer #' . $customer->id . ' — Admin')

@section('content')

<x-admin.page-header
    :title="'Edit Customer'"
    icon="bi bi-person-gear"
    :subtitle="'#' . $customer->id . ' · ' . $customer->name"
    :breadcrumbs="['Customers' => route('admin.customers.index'), 'Edit' => '']">
    <x-slot:actions>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('admin.customers.update', $customer->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <x-admin.form-card :title="'Customer Account Details'" :icon="'bi bi-person-badge'">
                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label fw-semibold text-dark">{{ 'Full Name' }} <span class="text-danger">*</span></label>
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $customer->name) }}"
                               placeholder="e.g. Jane Doe"
                               maxlength="255"
                               required>
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
                               value="{{ old('email', $customer->email) }}"
                               placeholder="customer@example.com"
                               maxlength="255"
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label fw-semibold text-dark">{{ 'New Password' }}</label>
                        <input type="password"
                               name="password"
                               id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Leave blank to keep unchanged"
                               minlength="6">
                        <div class="form-text text-muted">Only fill this field if you want to reset customer password.</div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label fw-semibold text-dark">{{ 'Phone Number' }}</label>
                        <input type="text"
                               name="phone"
                               id="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $customer->phone) }}"
                               placeholder="+1 234 567 8900"
                               maxlength="20">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label fw-semibold text-dark">{{ 'Shipping / Billing Address' }}</label>
                    <textarea name="address"
                              id="address"
                              rows="3"
                              class="form-control @error('address') is-invalid @enderror"
                              placeholder="Street address, city, state, postal code...">{{ old('address', $customer->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </x-admin.form-card>
        </div>

        <!-- Sidebar / Actions -->
        <div class="col-lg-4">
            <x-admin.form-card :title="'Account Status'" :icon="'bi bi-toggle2-on'">
                <div class="mb-3">
                    <label for="status" class="form-label fw-semibold text-dark">{{ 'Status' }} <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status', $customer->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $customer->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid gap-2 pt-2">
                    <button type="submit" class="btn btn-primary shadow-sm">
                        <i class="bi bi-check-circle-fill me-1"></i> {{ 'Update Customer' }}
                    </button>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                        {{ 'Cancel' }}
                    </a>
                </div>
            </x-admin.form-card>
        </div>
    </div>
</form>

@endsection
