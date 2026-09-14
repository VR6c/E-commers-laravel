@extends('admin.layouts.admin')

@section('title', 'Edit Payment Gateway — Admin')

@section('content')

<x-admin.page-header
    :title="'Edit Payment Gateway'"
    :breadcrumbs="['Payment Gateways' => route('admin.payment-gateways.index'), 'Edit #' . $paymentGateway->id => '']">
    <x-slot:actions>
        <a href="{{ route('admin.payment-gateways.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to Gateways
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('admin.payment-gateways.update', $paymentGateway->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Gateway Details --}}
            <x-admin.form-card :title="'Gateway Information'" :icon="'bi bi-wallet2'">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">{{ 'Gateway Name' }} <span class="text-danger">*</span></label>
                        <input type="text"
                               name="name"
                               value="{{ old('name', $paymentGateway->name) }}"
                               class="form-control"
                               required
                               autofocus>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">{{ 'Gateway Code' }} <span class="text-danger">*</span></label>
                        <input type="text"
                               name="code"
                               value="{{ old('code', $paymentGateway->code) }}"
                               class="form-control text-uppercase"
                               required>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold text-dark">{{ 'Description' }}</label>
                    <textarea name="description"
                              class="form-control"
                              rows="3">{{ old('description', $paymentGateway->description) }}</textarea>
                </div>
            </x-admin.form-card>

            {{-- Configurations --}}
            <div class="mt-4">
                <x-admin.form-card :title="'API Credentials & Environment'" :icon="'bi bi-key'">
                    @forelse ($paymentGateway->configs as $config)
                    <div class="p-4 mb-3 rounded" style="background: var(--surface-subtle); border: 1px solid var(--border-subtle);">
                        <input type="hidden" name="configs[{{ $config->id }}][id]" value="{{ $config->id }}">

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">{{ 'Key Name' }}</label>
                                <input type="text"
                                       name="configs[{{ $config->id }}][key_name]"
                                       value="{{ old('configs.' . $config->id . '.key_name', $config->key_name) }}"
                                       class="form-control font-monospace"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">{{ 'Key Value' }}</label>
                                <input type="text"
                                       name="configs[{{ $config->id }}][key_value]"
                                       value="{{ old('configs.' . $config->id . '.key_value', $config->key_value) }}"
                                       class="form-control font-monospace"
                                       required>
                            </div>
                        </div>

                        <div class="row align-items-center g-3">
                            <div class="col-md-6">
                                <x-admin.combobox
                                    :name="'configs[' . $config->id . '][environment]'"
                                    :label="'Environment'"
                                    :selected="$config->environment"
                                    :options="[
                                        'sandbox' => 'Sandbox (Test Mode)',
                                        'production' => 'Production (Live)',
                                    ]" />
                            </div>
                            <div class="col-md-6 pt-md-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="configs[{{ $config->id }}][is_encrypted]" value="0">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="configs[{{ $config->id }}][is_encrypted]"
                                           value="1"
                                           id="encrypt_{{ $config->id }}"
                                           {{ $config->is_encrypted ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark" for="encrypt_{{ $config->id }}">
                                        Encrypted Key Storage
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="bi bi-shield-lock text-muted display-5 mb-2 d-block"></i>
                        <p class="text-muted mb-0">{{ 'No configuration keys defined for this gateway.' }}</p>
                    </div>
                    @endforelse
                </x-admin.form-card>
            </div>
        </div>

        <div class="col-lg-4">
            <x-admin.form-card :title="'Status & Action'" :icon="'bi bi-toggle-on'">
                <div class="form-check form-switch mb-4">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox"
                           name="is_active"
                           id="is_active"
                           class="form-check-input"
                           value="1"
                           {{ $paymentGateway->is_active ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold text-dark" for="is_active">
                        Enable Gateway in Checkout
                    </label>
                </div>

                <hr class="my-4" style="border-color: var(--border-subtle);">

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                        <i class="bi bi-check-circle-fill me-1"></i> {{ 'Update Gateway' }}
                    </button>
                    <a href="{{ route('admin.payment-gateways.index') }}" class="btn btn-secondary py-2">
                        {{ 'Cancel' }}
                    </a>
                </div>
            </x-admin.form-card>
        </div>
    </div>
</form>
@endsection