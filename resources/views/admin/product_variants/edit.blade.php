@extends('admin.layouts.admin')

@section('title', 'Edit Product Variant — Admin')

@section('content')

<x-admin.page-header
    :title="'Edit Product Variant'"
    :breadcrumbs="['Product Variants' => route('admin.product_variants.index'), 'Edit #' . $productVariant->id => '']">
    <x-slot:actions>
        <a href="{{ route('admin.product_variants.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to Variants
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('admin.product_variants.update', $productVariant->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Main Details -->
        <div class="col-lg-8">
            <x-admin.form-card :title="'Variant Details'" :icon="'bi bi-layers'">
                <div class="mb-4">
                    <x-admin.combobox
                        name="product_id"
                        id="product_id"
                        :label="'Parent Product'"
                        :selected="$productVariant->product_id"
                        :placeholder="'Select Product'"
                        :options="$products"
                        option-label="name"
                        :option-label-fallback="'Unknown Product'"
                        required />
                </div>

                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label fw-semibold text-dark">{{ 'Variant Name / Option' }} <span class="text-danger">*</span></label>
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $productVariant->name) }}"
                               required
                               placeholder="e.g. Size, Color, Storage">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="value" class="form-label fw-semibold text-dark">{{ 'Variant Value' }} <span class="text-danger">*</span></label>
                        <input type="text"
                               name="value"
                               id="value"
                               class="form-control @error('value') is-invalid @enderror"
                               value="{{ old('value', $productVariant->value) }}"
                               required
                               placeholder="e.g. XL, Red, 256GB">
                        @error('value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label for="price" class="form-label fw-semibold text-dark">{{ 'Regular Price' }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number"
                                   step="0.01"
                                   name="price"
                                   id="price"
                                   class="form-control @error('price') is-invalid @enderror"
                                   value="{{ old('price', $productVariant->price) }}"
                                   placeholder="0.00"
                                   required>
                        </div>
                        @error('price')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="discount_price" class="form-label fw-semibold text-dark">{{ 'Discount Price' }}</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number"
                                   step="0.01"
                                   name="discount_price"
                                   id="discount_price"
                                   class="form-control @error('discount_price') is-invalid @enderror"
                                   value="{{ old('discount_price', $productVariant->discount_price) }}"
                                   placeholder="0.00">
                        </div>
                        @error('discount_price')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </x-admin.form-card>

            <div class="mt-4">
                <x-admin.form-card :title="'Inventory & Shipping'" :icon="'bi bi-boxes'">
                    <div class="row g-3">
                        <div class="col-md-4 mb-3">
                            <label for="stock" class="form-label fw-semibold text-dark">{{ 'Stock Quantity' }} <span class="text-danger">*</span></label>
                            <input type="number"
                                   name="stock"
                                   id="stock"
                                   class="form-control @error('stock') is-invalid @enderror"
                                   value="{{ old('stock', $productVariant->stock) }}"
                                   required>
                            @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="SKU" class="form-label fw-semibold text-dark">{{ 'SKU Code' }} <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="SKU"
                                   id="SKU"
                                   class="form-control text-uppercase @error('SKU') is-invalid @enderror"
                                   value="{{ old('SKU', $productVariant->SKU) }}"
                                   required
                                   placeholder="e.g. PRD-XL-01">
                            @error('SKU')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="weight" class="form-label fw-semibold text-dark">{{ 'Weight (kg)' }}</label>
                            <input type="text"
                                   name="weight"
                                   id="weight"
                                   class="form-control @error('weight') is-invalid @enderror"
                                   value="{{ old('weight', $productVariant->weight) }}"
                                   placeholder="e.g. 0.5">
                            @error('weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </x-admin.form-card>
            </div>
        </div>

        <!-- Sidebar / Actions -->
        <div class="col-lg-4">
            <x-admin.form-card :title="'Configuration'" :icon="'bi bi-gear'">
                <div class="mb-4">
                    <label for="variant_slug" class="form-label fw-semibold text-dark">{{ 'Variant Slug' }} <span class="text-danger">*</span></label>
                    <input type="text"
                           name="variant_slug"
                           id="variant_slug"
                           class="form-control @error('variant_slug') is-invalid @enderror"
                           value="{{ old('variant_slug', $productVariant->variant_slug) }}"
                           required
                           placeholder="e.g. size-xl-navy">
                    <small class="text-muted mt-1 d-block">Used for URL identification and variant mapping.</small>
                    @error('variant_slug')
                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4" style="border-color: var(--border-subtle);">

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                        <i class="bi bi-check-circle-fill me-1"></i> {{ 'Update Variant' }}
                    </button>
                    <a href="{{ route('admin.product_variants.index') }}" class="btn btn-secondary py-2">
                        {{ 'Cancel' }}
                    </a>
                </div>
            </x-admin.form-card>
        </div>
    </div>
</form>
@endsection
