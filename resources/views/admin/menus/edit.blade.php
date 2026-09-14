@extends('admin.layouts.admin')

@section('title', 'Edit Menu — Admin')

@section('content')

<x-admin.page-header
    :title="'Edit Menu'"
    :breadcrumbs="['Menus' => route('admin.menus.index'), 'Edit #' . $menu->id => '']">
    <x-slot:actions>
        <a href="{{ route('admin.menus.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to Menus
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('admin.menus.update', $menu->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-lg-8">
            <x-admin.form-card :title="'Menu Details'" :icon="'bi bi-menu-button-wide'">
                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold text-dark">{{ 'Menu Title' }} <span class="text-danger">*</span></label>
                    <input type="text"
                           name="title"
                           id="title"
                           class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $menu->title) }}"
                           required
                           autofocus>
                    <small class="text-muted mt-1 d-block">Used to identify this menu collection in themes and templates.</small>
                    @error('title')
                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </x-admin.form-card>
        </div>

        <div class="col-lg-4">
            <x-admin.form-card :title="'Update & Action'" :icon="'bi bi-check-circle'">
                <div class="p-3 bg-light rounded mb-4" style="border: 1px solid var(--border-subtle);">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-info-circle text-primary fs-5 mt-n1"></i>
                        <p class="text-muted small mb-0">
                            Updating the title changes how it appears across all templates referencing this identifier.
                        </p>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                        <i class="bi bi-check-circle-fill me-1"></i> {{ 'Update Menu' }}
                    </button>
                    <a href="{{ route('admin.menus.index') }}" class="btn btn-secondary py-2">
                        {{ 'Cancel' }}
                    </a>
                </div>
            </x-admin.form-card>
        </div>
    </div>
</form>
@endsection
