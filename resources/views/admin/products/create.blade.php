@extends('admin.layouts.admin')

@section('title', 'Create Product — Admin')

@section('content')

<x-admin.page-header
    :title="'Create Product'"
    :breadcrumbs="['Products' => route('admin.products.index'), 'Create' => '']">
    <x-slot:actions>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to Products
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row g-4">
        {{-- Main Content Column --}}
        <div class="col-lg-8">
            <x-admin.form-card :title="'Product Information'" :icon="'bi bi-box-seam'" class="mb-4">
                {{-- Product Name --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">{{ 'Product Name' }} <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           placeholder="e.g. Wireless Noise Canceling Headphones"
                           required
                           autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="mb-0">
                    <label class="form-label fw-semibold text-dark">{{ 'Description' }}</label>
                    <textarea name="description"
                              class="form-control ck-editor @error('description') is-invalid @enderror"
                              rows="8"
                              placeholder="Full product overview, features, specifications...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </x-admin.form-card>

            {{-- Variants Card --}}
            <x-admin.form-card :title="'Product Variants'" :icon="'bi bi-layers'" class="mb-4">
                <x-slot:headerActions>
                    <button type="button" id="add-variant-btn" class="btn btn-primary btn-sm shadow-xs">
                        <i class="bi bi-plus-lg me-1"></i> {{ 'Add Variant' }}
                    </button>
                </x-slot:headerActions>

                <div id="variants-wrapper"></div>

                <div id="no-variants-msg" class="text-center py-4">
                    <div class="mb-2 text-muted opacity-50" style="font-size: 2.2rem;">
                        <i class="bi bi-layers"></i>
                    </div>
                    <p class="text-muted small mb-0">{{ 'No variants added yet. Click \'Add Variant\' above to configure price, SKU, size, or color.' }}</p>
                </div>
            </x-admin.form-card>
        </div>

        {{-- Sidebar Column --}}
        <div class="col-lg-4">
            {{-- Organization Card --}}
            <x-admin.form-card :title="'Organization'" :icon="'bi bi-tags'" class="mb-4">
                <x-admin.combobox
                    name="category_id"
                    wrapper-class="mb-3"
                    :label="'Category'"
                    :options="$categories"
                    option-label="name" />

                <x-admin.combobox
                    name="brand_id"
                    wrapper-class="mb-3"
                    :label="'Brand'"
                    :placeholder="'No Brand'"
                    :options="$brands"
                    option-label="name" />

                <x-admin.combobox
                    name="vendor_id"
                    wrapper-class="mb-0"
                    :label="'Vendor'"
                    :placeholder="'Select Vendor'"
                    :options="$vendors" />
            </x-admin.form-card>

            {{-- Media Card --}}
            <x-admin.form-card :title="'Product Gallery'" :icon="'bi bi-images'" class="mb-4">
                <div id="image-previews" class="row g-2 mb-3"></div>
                <label class="border rounded-3 p-3 text-center d-flex flex-column align-items-center justify-content-center w-100 transition-all"
                       style="background: var(--neutral-50); border: 2px dashed var(--neutral-300) !important; cursor: pointer;">
                    <i class="bi bi-cloud-arrow-up fs-2 text-primary mb-1"></i>
                    <span class="fw-semibold text-dark small mb-1">{{ 'Upload Product Images' }}</span>
                    <span class="text-muted" style="font-size: 0.72rem;">Multi-file selection supported</span>
                    <input type="file" name="images[]" multiple class="d-none" id="product-images" accept="image/*">
                </label>
            </x-admin.form-card>

            {{-- Publish Card --}}
            <x-admin.form-card :title="'Publishing'" :icon="'bi bi-send-check'">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                        <i class="bi bi-check-circle-fill me-1"></i> {{ 'Save Product' }}
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary py-2">
                        {{ 'Cancel' }}
                    </a>
                </div>
            </x-admin.form-card>
        </div>
    </div>
</form>

{{-- Variant Template --}}
<template id="variant-template">
    <div class="variant-item border rounded-3 p-3 mb-3 position-relative" style="background: #ffffff; border-color: var(--border-color) !important;" data-index="__INDEX__">
        <button type="button"
            class="btn btn-sm btn-light text-danger border-0 p-1 position-absolute top-0 end-0 mt-2 me-2 remove-variant-item"
            title="Remove variant">
            <i class="bi bi-trash3-fill"></i>
        </button>

        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                <i class="bi bi-tag-fill me-1"></i> Variant #<span class="variant-number">__INDEX__</span>
            </span>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-dark small mb-1">{{ 'Variant Name' }}</label>
                <input type="text" name="variants[__INDEX__][name]" class="form-control form-control-sm" value="__NAME__" placeholder="e.g. XL - Midnight Black" />
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold text-dark small mb-1">{{ 'Price' }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" name="variants[__INDEX__][price]" class="form-control" value="__PRICE__" />
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold text-dark small mb-1">{{ 'Discount Price' }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" name="variants[__INDEX__][discount_price]" class="form-control" value="__DISCOUNT__" />
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold text-dark small mb-1">{{ 'Stock Qty' }}</label>
                <input type="number" name="variants[__INDEX__][stock]" class="form-control form-control-sm" value="__STOCK__" />
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold text-dark small mb-1">{{ 'SKU' }}</label>
                <input type="text" name="variants[__INDEX__][SKU]" class="form-control form-control-sm" value="__SKU__" />
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold text-dark small mb-1">{{ 'Barcode' }}</label>
                <input type="text" name="variants[__INDEX__][barcode]" class="form-control form-control-sm" value="__BARCODE__" />
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold text-dark small mb-1">{{ 'Size' }}</label>
                <select name="variants[__INDEX__][size_id]" class="form-select form-select-sm">
                    <option value="">{{ 'No Size' }}</option>
                    @foreach($sizes as $size)
                    <option value="{{ $size->id }}">{{ $size->value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold text-dark small mb-1">{{ 'Color' }}</label>
                <select name="variants[__INDEX__][color_id]" class="form-select form-select-sm">
                    <option value="">{{ 'No Color' }}</option>
                    @foreach($colors as $color)
                    <option value="{{ $color->id }}">{{ $color->value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-center pt-3">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="radio" name="primary_variant" value="__INDEX__" id="primary__INDEX__">
                    <label class="form-check-label small fw-semibold text-dark" for="primary__INDEX__">{{ 'Primary Variant' }}</label>
                </div>
            </div>
        </div>
    </div>
</template>

@endsection

@section('js')
<script>
$(document).ready(function() {
    const template = $('#variant-template').html();

    function updateNoVariantsMsg() {
        if ($('#variants-wrapper .variant-item').length > 0) {
            $('#no-variants-msg').hide();
        } else {
            $('#no-variants-msg').show();
        }
    }

    function addVariant(variant = {}) {
        const index = $('#variants-wrapper .variant-item').length;
        let html = template
            .replace(/__INDEX__/g, index)
            .replace(/__NAME__/g, variant.name || '')
            .replace(/__PRICE__/g, variant.price || '0.00')
            .replace(/__DISCOUNT__/g, variant.discount_price || '')
            .replace(/__STOCK__/g, variant.stock || '0')
            .replace(/__SKU__/g, variant.SKU || '')
            .replace(/__BARCODE__/g, variant.barcode || '');

        const $variant = $(html);
        if (variant.size_id)  $variant.find(`select[name="variants[${index}][size_id]"]`).val(variant.size_id);
        if (variant.color_id) $variant.find(`select[name="variants[${index}][color_id]"]`).val(variant.color_id);
        if (variant.is_primary) $variant.find(`input[name="primary_variant"]`).prop('checked', true);

        $('#variants-wrapper').append($variant);
        updateNoVariantsMsg();
    }

    $('#add-variant-btn').click(function() { addVariant(); });

    $(document).on('click', '.remove-variant-item', function() {
        $(this).closest('.variant-item').remove();
        $('#variants-wrapper .variant-item').each(function(idx) {
            $(this).find('.variant-number').text(idx);
        });
        updateNoVariantsMsg();
    });

    $('#product-images').change(function() {
        const container = $('#image-previews').empty();
        const files = this.files;
        for (let i = 0; i < files.length; i++) {
            const reader = new FileReader();
            reader.onload = function(e) {
                container.append(`<div class="col-4"><div class="position-relative"><img src="${e.target.result}" class="img-fluid rounded-3 border shadow-xs" style="height: 80px; width: 100%; object-fit: cover;"></div></div>`);
            };
            reader.readAsDataURL(files[i]);
        }
    });

    @if(old('variants'))
        const oldVariants = @json(old('variants'));
        const primaryIndex = {{ old('primary_variant', 0) }};
        Object.keys(oldVariants).forEach(key => {
            let v = oldVariants[key];
            v.is_primary = (key == primaryIndex);
            addVariant(v);
        });
    @endif

    updateNoVariantsMsg();
});
</script>
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
<script>
let ckEditor;
document.querySelectorAll('.ck-editor').forEach(el => {
    ClassicEditor.create(el)
        .then(editor => { ckEditor = editor; })
        .catch(error => { console.error('CKEditor init error', error); });
});
document.querySelector('form').addEventListener('submit', function() {
    if (ckEditor) {
        document.querySelector('.ck-editor').value = ckEditor.getData();
    }
});
</script>
@endsection
