@extends('vendor.layouts.master')

@section('title', 'Create Product')

@section('css')
<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.css">
@endsection

@section('content')

<x-admin.page-header
    :title="'Create Product'"
    icon="bi bi-plus-circle-fill"
    :subtitle="'Fill in the details below to add a new product to your store.'"
    :breadcrumbs="['Products' => route('vendor.products.index'), 'Create' => '#']">
    <x-slot:actions>
        <a href="{{ route('vendor.products.index') }}"
           class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 shadow-sm"
           style="border-radius:10px; font-size:.85rem;">
            <i class="bi bi-arrow-left"></i> Back to Products
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('vendor.products.store') }}" method="POST" enctype="multipart/form-data" id="create-product-form">
@csrf

<div class="row g-4">

    {{-- LEFT: main info --}}
    <div class="col-xl-8">

        {{-- Basic Info --}}
        <x-admin.form-card :title="'Product Information'" icon="bi bi-tag-fill" class="mb-4">
            <div class="mb-3">
                <label class="form-label fw-semibold" for="name">Product Name <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}"
                       placeholder="e.g. Premium Wireless Headphones" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-0">
                <label class="form-label fw-semibold" for="description">Description</label>
                <textarea id="description" name="description"
                          class="form-control ck-editor @error('description') is-invalid @enderror"
                          rows="5"
                          placeholder="Describe your product…">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </x-admin.form-card>

        {{-- Classification --}}
        <x-admin.form-card :title="'Classification'" icon="bi bi-diagram-3-fill" class="mb-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <x-admin.combobox name="category_id" wrapper-class=""
                        :label="'Category'" :options="$categories"
                        option-label="name" :selected="old('category_id')" />
                </div>
                <div class="col-md-6">
                    <x-admin.combobox name="brand_id" wrapper-class=""
                        :label="'Brand'" :placeholder="'No Brand'"
                        :options="$brands" option-label="name"
                        :selected="old('brand_id')" />
                </div>
            </div>
        </x-admin.form-card>

        {{-- Variants --}}
        <x-admin.form-card :title="'Product Variants'" icon="bi bi-layers-fill" class="mb-4">
            <x-slot:headerActions>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold"
                      id="variant-count-badge">0 variants</span>
            </x-slot:headerActions>

            <div id="variants-wrapper"></div>
            <div class="d-flex align-items-center gap-2 mt-3 pt-2 border-top">
                <button type="button" id="add-variant-btn" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2 rounded-pill px-3 shadow-sm">
                    <i class="bi bi-plus-lg"></i> Add Variant
                </button>
                <button type="button" id="remove-variant-btn" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-2 rounded-pill px-3" disabled>
                    <i class="bi bi-trash"></i> Remove Last
                </button>
                <span class="text-muted small ms-2">At least one primary variant is required.</span>
            </div>
        </x-admin.form-card>

    </div>

    {{-- RIGHT: images + submit --}}
    <div class="col-xl-4">

        {{-- Images --}}
        <x-admin.form-card :title="'Product Images'" icon="bi bi-images" class="mb-4">
            <div class="vp-upload-zone" id="upload-zone"
                 onclick="document.getElementById('productImages').click();">
                <div class="vp-upload-icon"><i class="bi bi-cloud-arrow-up-fill fs-3"></i></div>
                <p class="vp-upload-label">Click or drag images here</p>
                <p class="vp-upload-hint">PNG, JPG, WEBP — multiple allowed</p>
            </div>
            <input type="file" name="images[]" id="productImages" multiple accept="image/*"
                   class="d-none" onchange="previewMultipleImages(this)">
            <div id="productImagesPreview" class="vp-image-grid"></div>
        </x-admin.form-card>

        {{-- Submit --}}
        <x-admin.form-card class="mb-4">
            <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2 py-2 fw-semibold shadow-sm" id="saveProductBtn" style="border-radius: 10px;">
                <span class="spinner-border spinner-border-sm d-none" id="productLoader" role="status"></span>
                <i class="bi bi-check-circle-fill" id="saveIcon"></i>
                Save Product
            </button>
            <a href="{{ route('vendor.products.index') }}"
               class="btn btn-outline-secondary w-100 mt-2 d-flex align-items-center justify-content-center gap-2 py-2 fw-semibold"
               style="border-radius:10px;">
                <i class="bi bi-x-lg"></i> Cancel
            </a>
        </x-admin.form-card>

    </div>
</div>
</form>

{{-- Variant template --}}
<template id="variant-template">
    <div class="vp-variant-block card mb-3 border bg-light shadow-none" data-index="__INDEX__" style="border-radius: 12px;">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div class="fw-bold text-primary d-flex align-items-center gap-2">
                    <i class="bi bi-box"></i> Variant <span class="badge bg-primary-subtle text-primary">#__NUMBER__</span>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Variant Name</label>
                    <input type="text" name="variants[__INDEX__][name]" class="form-control form-control-sm" value="__NAME__" placeholder="e.g. Standard" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Price ($) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="variants[__INDEX__][price]" class="form-control form-control-sm" value="__PRICE__" placeholder="0.00" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Discount Price ($)</label>
                    <input type="number" step="0.01" name="variants[__INDEX__][discount_price]" class="form-control form-control-sm" value="__DISCOUNT__" placeholder="0.00">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Stock Quantity</label>
                    <input type="number" name="variants[__INDEX__][stock]" class="form-control form-control-sm" value="__STOCK__" placeholder="0" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">SKU</label>
                    <input type="text" name="variants[__INDEX__][SKU]" class="form-control form-control-sm" value="__SKU__" placeholder="SKU-001" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Barcode</label>
                    <input type="text" name="variants[__INDEX__][barcode]" class="form-control form-control-sm" value="__BARCODE__">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Weight</label>
                    <input type="text" name="variants[__INDEX__][weight]" class="form-control form-control-sm" value="__WEIGHT__" placeholder="e.g. 0.5kg">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Dimensions</label>
                    <input type="text" name="variants[__INDEX__][dimension]" class="form-control form-control-sm" value="__DIMENSION__" placeholder="e.g. 10x5x3cm">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Size</label>
                    <select name="variants[__INDEX__][size_id]" class="form-select form-select-sm">
                        <option value="">Select Size (Optional)</option>
                        @foreach($sizes as $size)
                            <option value="{{ $size->id }}">{{ $size->value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Color</label>
                    <select name="variants[__INDEX__][color_id]" class="form-select form-select-sm">
                        <option value="">Select Color (Optional)</option>
                        @foreach($colors as $color)
                            <option value="{{ $color->id }}">{{ $color->value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</template>

@endsection

@section('js')
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
<script>
document.querySelectorAll('.ck-editor').forEach(el => {
    ClassicEditor.create(el).catch(console.error);
});
</script>

<script>
let variantIndex = 0;

function updateVariantBadge() {
    const count = document.querySelectorAll('#variants-wrapper .vp-variant-block').length;
    document.getElementById('variant-count-badge').textContent = count + (count === 1 ? ' variant' : ' variants');
    document.getElementById('remove-variant-btn').disabled = count === 0;
}

function addVariant(variant = {}, index = variantIndex) {
    let tpl = document.getElementById('variant-template').innerHTML;
    tpl = tpl
        .replaceAll('__INDEX__',     index)
        .replaceAll('__NUMBER__',    index + 1)
        .replaceAll('__NAME__',      variant.name || '')
        .replaceAll('__PRICE__',     variant.price || '')
        .replaceAll('__DISCOUNT__',  variant.discount_price || '')
        .replaceAll('__STOCK__',     variant.stock || '')
        .replaceAll('__SKU__',       variant.SKU || '')
        .replaceAll('__BARCODE__',   variant.barcode || '')
        .replaceAll('__WEIGHT__',    variant.weight || '')
        .replaceAll('__DIMENSION__', variant.dimension || '');
    const wrap = document.createElement('div');
    wrap.innerHTML = tpl;
    document.getElementById('variants-wrapper').appendChild(wrap.firstElementChild);
    variantIndex++;
    updateVariantBadge();
}

document.addEventListener('DOMContentLoaded', function () {
    @if(old('variants'))
        let oldVariants = @json(old('variants'));
        oldVariants.forEach((v, i) => addVariant(v, i));
    @else
        addVariant();
    @endif

    document.getElementById('add-variant-btn').addEventListener('click', () => addVariant());

    document.getElementById('remove-variant-btn').addEventListener('click', () => {
        const items = document.querySelectorAll('#variants-wrapper .vp-variant-block');
        if (items.length > 0) { items[items.length - 1].remove(); variantIndex--; updateVariantBadge(); }
    });

    document.getElementById('create-product-form').addEventListener('submit', function () {
        const btn = document.getElementById('saveProductBtn');
        btn.disabled = true;
        document.getElementById('productLoader').classList.remove('d-none');
        document.getElementById('saveIcon').classList.add('d-none');
    });

    const zone = document.getElementById('upload-zone');
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
    zone.addEventListener('drop', e => {
        e.preventDefault(); zone.classList.remove('drag-over');
        const input = document.getElementById('productImages');
        const dt = new DataTransfer();
        [...e.dataTransfer.files].forEach(f => dt.items.add(f));
        input.files = dt.files;
        previewMultipleImages(input);
    });
});

let selectedFiles = [];

function previewMultipleImages(input) {
    const files = Array.from(input.files);
    files.forEach(file => {
        if (!selectedFiles.some(f => f.name === file.name && f.size === file.size)) selectedFiles.push(file);
    });
    const preview = document.getElementById('productImagesPreview');
    preview.innerHTML = '';
    selectedFiles.forEach((file, idx) => {
        const reader = new FileReader();
        reader.onload = e => {
            const thumb = document.createElement('div');
            thumb.className = 'vp-image-thumb';
            thumb.innerHTML = `<img src="${e.target.result}" alt="">
                <button type="button" class="vp-image-thumb__remove" onclick="removePreviewImage(${idx})">
                    <i class="bi bi-x-lg"></i>
                </button>`;
            preview.appendChild(thumb);
        };
        reader.readAsDataURL(file);
    });
    syncFiles(input);
}

function removePreviewImage(idx) {
    selectedFiles.splice(idx, 1);
    const input = document.getElementById('productImages');
    syncFiles(input);
    previewMultipleImages({ files: [] });
}

function syncFiles(input) {
    const dt = new DataTransfer();
    selectedFiles.forEach(f => dt.items.add(f));
    input.files = dt.files;
}
</script>
@endsection
