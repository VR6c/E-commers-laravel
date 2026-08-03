@extends('admin.layouts.admin')

@section('content')

{{-- Page header --}}
<x-admin.page-header
    :title="'Edit Product'"
    icon="bi bi-box-seam"
    :subtitle="'#' . $product->id . ' · ' . $product->getTranslation('name', 'en')"
    :breadcrumbs="['Products' => route('admin.products.index'), 'Edit' => '#']">
    <x-slot:actions>
        <a href="{{ route('admin.products.index') }}" class="vp-btn-primary"
           style="background: var(--vp-surface); color: var(--vp-text) !important; border: 1.5px solid var(--vp-border); box-shadow: var(--vp-sh-xs);"
           id="back-to-products-btn">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Back
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('admin.products.update', $product->id) }}"
      method="POST"
      enctype="multipart/form-data"
      id="edit-product-form"
      novalidate>
    @csrf
    @method('PUT')

    <div class="row g-4">

        {{-- ── Left column: main content ───────────────────────────────────── --}}
        <div class="col-lg-8">

            {{-- Product Info Card --}}
            <x-admin.form-card title="Product Info" icon="bi bi-box-seam" class="mb-4 vp-anim-slide-up">
                <div class="vp-form-group">
                    <label class="vp-label" for="product-name">
                        Product Name <span class="required">*</span>
                    </label>
                    <input type="text"
                           id="product-name"
                           name="name"
                           class="vp-input @error('name') is-invalid @enderror"
                           value="{{ old('name', $product->name) }}"
                           placeholder="e.g. Wireless Headphones"
                           autocomplete="off">
                    @error('name')
                        <p class="vp-error"><i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>{{ $message }}</p>
                    @enderror
                </div>

                <div class="vp-form-group">
                    <label class="vp-label" for="product-description">Description</label>
                    <textarea id="product-description"
                              name="description"
                              class="vp-textarea ck-editor @error('description') is-invalid @enderror"
                              rows="8"
                              placeholder="Full product description…">{{ old('description', $product->description) }}</textarea>
                    @error('description')
                        <p class="vp-error"><i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>{{ $message }}</p>
                    @enderror
                </div>

                <div class="vp-form-group" style="margin-bottom:0;">
                    <label class="vp-label" for="product-short-desc">Short Description</label>
                    <textarea id="product-short-desc"
                              name="short_description"
                              class="vp-textarea"
                              rows="3"
                              placeholder="Brief product summary…">{{ old('short_description', $product->short_description) }}</textarea>
                </div>
            </x-admin.form-card>

            {{-- Variants Card --}}
            <div class="vp-card mb-4 vp-anim-slide-up" style="animation-delay:.08s;" role="region" aria-label="Product Variants">
                <div class="vp-card-header">
                    <h5 class="vp-card-header__title">
                        <span class="vp-card-header__icon" aria-hidden="true"><i class="bi bi-layers-fill"></i></span>
                        Variants
                        <span class="vp-variant-badge ms-1" id="variant-count">{{ count($product->variants) }}</span>
                    </h5>
                    <button type="button" id="add-variant-btn" class="vp-btn-primary" style="padding: 7px 14px; font-size: .8rem;" aria-label="Add a new variant">
                        <i class="bi bi-plus-lg" aria-hidden="true"></i> Add Variant
                    </button>
                </div>

                <div class="vp-card-body">
                    <div id="variants-wrapper" class="vp-stagger">

                        @foreach ($product->variants as $index => $variant)
                        <div class="vp-variant-block vp-anim-slide-up" data-index="{{ $index }}" style="animation-delay: {{ $index * 60 }}ms;">
                            <div class="vp-variant-header">
                                <div class="vp-variant-title">
                                    <i class="bi bi-tag-fill" aria-hidden="true"></i>
                                    Variant
                                    <span class="vp-variant-badge">{{ '#' . ($index + 1) }}</span>
                                </div>
                                <div class="vp-variant-controls">
                                    <button type="button"
                                            class="vp-variant-remove remove-variant-item"
                                            onclick="removeVariant({{ $variant->id ?? 'null' }}, this)"
                                            aria-label="Remove variant {{ $index + 1 }}">
                                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="vp-form-group" style="margin-bottom:0;">
                                        <label class="vp-label">Variant Name</label>
                                        <input type="text" name="variants[{{ $index }}][name]"
                                               class="vp-input"
                                               value="{{ old("variants.{$index}.name", $variant->name) }}"
                                               placeholder="e.g. XL / Red">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="vp-form-group" style="margin-bottom:0;">
                                        <label class="vp-label">Price</label>
                                        <div class="vp-input-group">
                                            <span class="vp-input-group-icon">$</span>
                                            <input type="number" step="0.01" name="variants[{{ $index }}][price]"
                                                   class="vp-input"
                                                   value="{{ old("variants.{$index}.price", $variant->price) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="vp-form-group" style="margin-bottom:0;">
                                        <label class="vp-label">Discount Price</label>
                                        <div class="vp-input-group">
                                            <span class="vp-input-group-icon">$</span>
                                            <input type="number" step="0.01" name="variants[{{ $index }}][discount_price]"
                                                   class="vp-input"
                                                   value="{{ old("variants.{$index}.discount_price", $variant->discount_price) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="vp-form-group" style="margin-bottom:0;">
                                        <label class="vp-label">Stock</label>
                                        <input type="number" name="variants[{{ $index }}][stock]"
                                               class="vp-input"
                                               value="{{ old("variants.{$index}.stock", $variant->stock) }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="vp-form-group" style="margin-bottom:0;">
                                        <label class="vp-label">SKU</label>
                                        <input type="text" name="variants[{{ $index }}][SKU]"
                                               class="vp-input"
                                               value="{{ old("variants.{$index}.SKU", $variant->SKU) }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="vp-form-group" style="margin-bottom:0;">
                                        <label class="vp-label">Barcode</label>
                                        <input type="text" name="variants[{{ $index }}][barcode]"
                                               class="vp-input"
                                               value="{{ old("variants.{$index}.barcode", $variant->barcode) }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="vp-form-group" style="margin-bottom:0;">
                                        <label class="vp-label">Weight</label>
                                        <input type="text" name="variants[{{ $index }}][weight]"
                                               class="vp-input"
                                               value="{{ old("variants.{$index}.weight", $variant->weight) }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="vp-form-group" style="margin-bottom:0;">
                                        <label class="vp-label">Dimensions</label>
                                        <input type="text" name="variants[{{ $index }}][dimensions]"
                                               class="vp-input"
                                               value="{{ old("variants.{$index}.dimensions", $variant->dimensions) }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="vp-form-group" style="margin-bottom:0;">
                                        <label class="vp-label">Size</label>
                                        <select name="variants[{{ $index }}][size_id]" class="vp-select">
                                            <option value="">No Size</option>
                                            @foreach($sizes as $size)
                                            <option value="{{ $size->id }}"
                                                {{ old("variants.{$index}.size_id", $variant->size_id) == $size->id ? 'selected' : '' }}>
                                                {{ $size->value }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="vp-form-group" style="margin-bottom:0;">
                                        <label class="vp-label">Color</label>
                                        <select name="variants[{{ $index }}][color_id]" class="vp-select">
                                            <option value="">No Color</option>
                                            @foreach($colors as $color)
                                            <option value="{{ $color->id }}"
                                                {{ old("variants.{$index}.color_id", $variant->color_id) == $color->id ? 'selected' : '' }}>
                                                {{ $color->value }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex align-items-center" style="padding-top: 22px;">
                                    <label class="d-flex align-items-center gap-2" style="cursor:pointer; font-size:.82rem; font-weight:600; color: var(--vp-text-2);">
                                        <span class="switch" style="margin:0;">
                                            <input type="radio"
                                                   name="primary_variant"
                                                   value="{{ $index }}"
                                                   id="primary_{{ $index }}"
                                                   {{ $variant->is_primary ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </span>
                                        Primary
                                    </label>
                                </div>
                            </div>
                        </div>
                        @endforeach

                    </div>{{-- /variants-wrapper --}}

                    {{-- Empty state --}}
                    <div id="no-variants-msg" class="vp-empty-state" style="{{ count($product->variants) > 0 ? 'display:none' : '' }}">
                        <div class="vp-empty-state__icon"><i class="bi bi-layers"></i></div>
                        <p class="vp-empty-state__text">Click <strong>Add Variant</strong> to add product options.</p>
                    </div>
                </div>
            </div>

        </div>{{-- /col-lg-8 --}}

        {{-- ── Right sidebar ──────────────────────────────────────────────── --}}
        <div class="col-lg-4">

            {{-- Save card --}}
            <x-admin.form-card class="mb-4 vp-anim-slide-up" style="animation-delay:.04s;" role="region" aria-label="Save actions">
                <button type="submit" id="save-product-btn" class="vp-btn-save w-100" aria-label="Save product changes">
                    <i class="bi bi-check-circle-fill" aria-hidden="true"></i> Save Product
                </button>
                <p style="font-size: .73rem; color: var(--vp-text-muted); text-align: center; margin: 10px 0 0;">
                    Changes will be saved immediately.
                </p>
            </x-admin.form-card>

            {{-- Organisation card --}}
            <x-admin.form-card title="Organization" icon="bi bi-diagram-3-fill" class="mb-4 vp-anim-slide-up" style="animation-delay:.1s;">
                <x-admin.combobox
                    name="category_id"
                    wrapper-class="vp-form-group"
                    :label="'Category'"
                    :selected="$product->category_id"
                    :options="$categories"
                    option-label="name" />

                <x-admin.combobox
                    name="brand_id"
                    wrapper-class="vp-form-group"
                    :label="'Brand'"
                    :selected="$product->brand_id"
                    :placeholder="'No Brand'"
                    :options="$brands"
                    option-label="name" />

                <x-admin.combobox
                    name="vendor_id"
                    wrapper-class="vp-form-group"
                    :label="'Vendor'"
                    :selected="$product->vendor_id"
                    :placeholder="'Select Vendor'"
                    :options="$vendors" />
            </x-admin.form-card>

            {{-- Images card --}}
            <x-admin.form-card title="Images" icon="bi bi-images" class="mb-4 vp-anim-slide-up" style="animation-delay:.16s;" role="region" aria-label="Product images">

                {{-- Existing images --}}
                @if ($product->images->count())
                <div class="vp-image-grid mb-3" id="existing-images-grid" aria-label="Existing product images">
                    @foreach ($product->images as $image)
                    <div class="vp-image-thumb" id="existing_image_{{ $image->id }}" role="img" aria-label="Product image">
                        <img src="{{ asset('storage/' . $image->image_url) }}" alt="Product image {{ $loop->iteration }}">
                        <button type="button"
                                class="vp-image-thumb__remove"
                                onclick="removeExistingImage({{ $image->id }})"
                                aria-label="Remove image {{ $loop->iteration }}">
                            <i class="bi bi-x" aria-hidden="true"></i>
                        </button>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- New image previews --}}
                <div class="vp-image-grid mb-3" id="image-previews" aria-label="New image previews"></div>

                {{-- Upload zone --}}
                <label class="vp-upload-zone" for="product-images" role="button" aria-label="Upload product images">
                    <div class="vp-upload-icon" aria-hidden="true">
                        <i class="bi bi-cloud-arrow-up"></i>
                    </div>
                    <p class="vp-upload-label">Upload Images</p>
                    <p class="vp-upload-hint">PNG, JPG, GIF up to 10MB</p>
                    <input type="file" name="images[]" multiple class="d-none" id="product-images" accept="image/*">
                </label>

                <div id="removedImagesInputs" aria-hidden="true"></div>
            </x-admin.form-card>

        </div>{{-- /col-lg-4 --}}
    </div>{{-- /row --}}
</form>

{{-- Variant template (injected by JS) --}}
<template id="variant-template">
    <div class="vp-variant-block vp-anim-pop-in" data-index="__INDEX__">
        <div class="vp-variant-header">
            <div class="vp-variant-title">
                <i class="bi bi-tag-fill" aria-hidden="true"></i>
                Variant
                <span class="vp-variant-badge">#__NUM__</span>
            </div>
            <div class="vp-variant-controls">
                <button type="button" class="vp-variant-remove remove-variant-item" aria-label="Remove this variant">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="vp-form-group" style="margin-bottom:0;">
                    <label class="vp-label">Variant Name</label>
                    <input type="text" name="variants[__INDEX__][name]" class="vp-input" placeholder="e.g. XL / Red">
                </div>
            </div>
            <div class="col-md-3">
                <div class="vp-form-group" style="margin-bottom:0;">
                    <label class="vp-label">Price</label>
                    <div class="vp-input-group">
                        <span class="vp-input-group-icon">$</span>
                        <input type="number" step="0.01" name="variants[__INDEX__][price]" class="vp-input" value="0.00">
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="vp-form-group" style="margin-bottom:0;">
                    <label class="vp-label">Discount Price</label>
                    <div class="vp-input-group">
                        <span class="vp-input-group-icon">$</span>
                        <input type="number" step="0.01" name="variants[__INDEX__][discount_price]" class="vp-input">
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="vp-form-group" style="margin-bottom:0;">
                    <label class="vp-label">Stock</label>
                    <input type="number" name="variants[__INDEX__][stock]" class="vp-input" value="0">
                </div>
            </div>
            <div class="col-md-3">
                <div class="vp-form-group" style="margin-bottom:0;">
                    <label class="vp-label">SKU</label>
                    <input type="text" name="variants[__INDEX__][SKU]" class="vp-input">
                </div>
            </div>
            <div class="col-md-3">
                <div class="vp-form-group" style="margin-bottom:0;">
                    <label class="vp-label">Barcode</label>
                    <input type="text" name="variants[__INDEX__][barcode]" class="vp-input">
                </div>
            </div>
            <div class="col-md-3">
                <div class="vp-form-group" style="margin-bottom:0;">
                    <label class="vp-label">Weight</label>
                    <input type="text" name="variants[__INDEX__][weight]" class="vp-input">
                </div>
            </div>
            <div class="col-md-3">
                <div class="vp-form-group" style="margin-bottom:0;">
                    <label class="vp-label">Dimensions</label>
                    <input type="text" name="variants[__INDEX__][dimensions]" class="vp-input">
                </div>
            </div>
            <div class="col-md-3">
                <div class="vp-form-group" style="margin-bottom:0;">
                    <label class="vp-label">Size</label>
                    <select name="variants[__INDEX__][size_id]" class="vp-select">
                        <option value="">No Size</option>
                        @foreach($sizes as $size)
                        <option value="{{ $size->id }}">{{ $size->value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="vp-form-group" style="margin-bottom:0;">
                    <label class="vp-label">Color</label>
                    <select name="variants[__INDEX__][color_id]" class="vp-select">
                        <option value="">No Color</option>
                        @foreach($colors as $color)
                        <option value="{{ $color->id }}">{{ $color->value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-center" style="padding-top:22px;">
                <label class="d-flex align-items-center gap-2" style="cursor:pointer; font-size:.82rem; font-weight:600; color: var(--vp-text-2);">
                    <span class="switch" style="margin:0;">
                        <input type="radio" name="primary_variant" value="__INDEX__" id="primary___INDEX__">
                        <span class="slider round"></span>
                    </span>
                    Primary
                </label>
            </div>
        </div>
    </div>
</template>

@endsection

@section('js')
<script>
$(document).ready(function () {
    const template = $('#variant-template').html();
    let variantIndex = {{ count($product->variants) }};

    /* ── Count badge ── */
    function updateCount() {
        const n = $('#variants-wrapper .vp-variant-block').length;
        $('#variant-count').text(n);
        n > 0 ? $('#no-variants-msg').hide() : $('#no-variants-msg').show();
    }

    /* ── Add variant ── */
    function addVariant(data = {}) {
        const index = variantIndex++;
        let html = template
            .replace(/__INDEX__/g, index)
            .replace(/__NUM__/g,   index + 1);
        const $v = $(html);
        if (data.size_id)    $v.find(`select[name="variants[${index}][size_id]"]`).val(data.size_id);
        if (data.color_id)   $v.find(`select[name="variants[${index}][color_id]"]`).val(data.color_id);
        if (data.is_primary) $v.find('input[name="primary_variant"]').prop('checked', true);
        $('#variants-wrapper').append($v);
        updateCount();
        // Focus first input in new variant for a11y
        $v.find('.vp-input').first().trigger('focus');
    }

    $('#add-variant-btn').on('click', () => addVariant());

    $(document).on('click', '.remove-variant-item', function () {
        $(this).closest('.vp-variant-block').remove();
        reindex();
        updateCount();
    });

    window.removeVariant = (id, btn) => {
        $(btn).closest('.vp-variant-block').remove();
        reindex();
        updateCount();
    };

    function reindex() {
        $('#variants-wrapper .vp-variant-block').each(function (i) {
            $(this).find('.vp-variant-badge').first().text('#' + (i + 1));
        });
    }

    /* ── Image preview ── */
    $('#product-images').on('change', function () {
        const container = $('#image-previews').empty();
        Array.from(this.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                const thumb = $('<div class="vp-image-thumb vp-anim-pop-in"></div>');
                thumb.append(`<img src="${e.target.result}" alt="New image preview">`);
                container.append(thumb);
            };
            reader.readAsDataURL(file);
        });
    });

    /* ── Remove existing image ── */
    window.removeExistingImage = id => {
        if (!confirm('Remove this image?')) return;
        $(`#existing_image_${id}`).addClass('vp-anim-fade-in').css('opacity', 0).remove();
        $('#removedImagesInputs').append(`<input type="hidden" name="remove_images[]" value="${id}">`);
    };

    /* ── Upload zone drag-over state ── */
    const $zone = $('.vp-upload-zone');
    $zone.on('dragover', e => { e.preventDefault(); $zone.addClass('drag-over'); });
    $zone.on('dragleave drop', () => $zone.removeClass('drag-over'));

    /* ── Save button loading state (frontend-patterns: loading state) ── */
    $('#edit-product-form').on('submit', function () {
        $('#save-product-btn').addClass('vp-btn-save--loading').prop('disabled', true);
    });

    updateCount();
});
</script>

<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
<script>
const ckEditors = [];
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.ck-editor').forEach(el => {
        ClassicEditor.create(el, {
            toolbar: ['heading','|','bold','italic','link','bulletedList','numberedList','blockQuote'],
        }).then(editor => ckEditors.push({ editor, el }))
          .catch(err => console.error(err));
    });

    document.getElementById('edit-product-form').addEventListener('submit', () => {
        ckEditors.forEach(({ editor, el }) => { el.value = editor.getData(); });
    });
});
</script>
@endsection
