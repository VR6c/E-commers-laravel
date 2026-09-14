@extends('admin.layouts.admin')

@section('title', 'Create Menu Item — Admin')

@section('content')

<x-admin.page-header
    :title="'Create Menu Item'"
    :breadcrumbs="['Menus' => route('admin.menus.index'), 'Items' => route('admin.menus.item.index'), 'Create' => '']">
    <x-slot:actions>
        <a href="{{ route('admin.menus.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to Menus
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('admin.menus.items.store', $menu->id) }}" method="POST">
    @csrf

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <x-admin.form-card :title="'Item Details'" :icon="'bi bi-link-45deg'">
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">{{ 'Item Title' }} <span class="text-danger">*</span></label>
                    <input type="text"
                           name="title[en]"
                           class="form-control @error('title.en') is-invalid @enderror"
                           value="{{ old('title.en') }}"
                           placeholder="e.g. Home, Shop Now, Categories, Contact"
                           required
                           autofocus>
                    @error('title.en')
                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <x-admin.combobox
                            name="parent_id"
                            id="parent_id"
                            :label="'Parent Item'"
                            :placeholder="'None (Top-Level Item)'"
                            :options="$menu->menuItems"
                            option-label="title"
                            option-label-fallback="No Title" />
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="order_number" class="form-label fw-semibold text-dark">{{ 'Display Order' }}</label>
                        <input type="number"
                               name="order_number"
                               id="order_number"
                               class="form-control @error('order_number') is-invalid @enderror"
                               value="{{ old('order_number', 0) }}"
                               placeholder="0">
                        @error('order_number')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </x-admin.form-card>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <x-admin.form-card :title="'Assignment & Action'" :icon="'bi bi-check-circle'">
                <div class="mb-4">
                    <x-admin.combobox
                        name="menu_id"
                        id="menu_id"
                        :label="'Target Menu'"
                        :selected="$menu->id"
                        :placeholder="'Select Menu'"
                        :placeholder-disabled="true"
                        :options="$menus"
                        option-label="title"
                        required />
                </div>

                <hr class="my-4" style="border-color: var(--border-subtle);">

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                        <i class="bi bi-plus-circle me-1"></i> {{ 'Create Item' }}
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
