@extends('admin.layouts.admin')

@section('title', 'Add Currency — Admin')

@section('content')

<x-admin.page-header
    :title="'Add Currency'"
    :breadcrumbs="['Currencies' => route('admin.currencies.index'), 'Create' => '']">
    <x-slot:actions>
        <a href="{{ route('admin.currencies.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to Currencies
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('admin.currencies.store') }}" method="POST">
    @csrf

    <div class="row g-4">
        <div class="col-lg-8">
            <x-admin.form-card :title="'Currency Details'" :icon="'bi bi-cash-stack'">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">{{ 'Currency Name' }} <span class="text-danger">*</span></label>
                        <input type="text"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               required
                               placeholder="e.g. US Dollar, Euro, Khmer Riel"
                               autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">{{ 'Currency Code' }} <span class="text-danger">*</span></label>
                        <input type="text"
                               name="code"
                               class="form-control text-uppercase @error('code') is-invalid @enderror"
                               value="{{ old('code') }}"
                               required
                               placeholder="e.g. USD, EUR, KHR">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">{{ 'Currency Symbol' }} <span class="text-danger">*</span></label>
                        <input type="text"
                               name="symbol"
                               class="form-control @error('symbol') is-invalid @enderror"
                               value="{{ old('symbol') }}"
                               required
                               placeholder="e.g. $, €, ៛">
                        @error('symbol')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">{{ 'Exchange Rate (against USD)' }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-currency-exchange"></i></span>
                            <input type="number"
                                   step="0.0001"
                                   name="exchange_rate"
                                   class="form-control @error('exchange_rate') is-invalid @enderror"
                                   value="{{ old('exchange_rate') }}"
                                   required
                                   placeholder="1.0000">
                        </div>
                        @error('exchange_rate')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </x-admin.form-card>
        </div>

        <div class="col-lg-4">
            <x-admin.form-card :title="'Publish & Action'" :icon="'bi bi-check-circle'">
                <div class="p-3 bg-light rounded mb-4" style="border: 1px solid var(--border-subtle);">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-info-circle text-primary fs-5 mt-n1"></i>
                        <p class="text-muted small mb-0">
                            Exchange rates are used for automatic conversion during storefront browsing and checkout calculation.
                        </p>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                        <i class="bi bi-check-circle-fill me-1"></i> {{ 'Save Currency' }}
                    </button>
                    <a href="{{ route('admin.currencies.index') }}" class="btn btn-secondary py-2">
                        {{ 'Cancel' }}
                    </a>
                </div>
            </x-admin.form-card>
        </div>
    </div>
</form>
@endsection
