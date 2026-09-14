@props([
    'name'         => 'image',
    'label'        => null,
    'currentImage' => null,
    'hint'         => 'PNG, JPG, WebP or GIF (Max 10MB)',
    'aspectRatio'  => 'square', // square or wide
    'required'     => false,
])

@php
    $id = 'uploader_' . str_replace(['[', ']', '.'], '_', $name) . '_' . uniqid();
    $hasImage = !empty($currentImage);
    $resolvedUrl = $hasImage ? (\Illuminate\Support\Str::startsWith($currentImage, ['http://', 'https://']) ? $currentImage : asset('storage/' . $currentImage)) : '';
@endphp

<div class="admin-image-uploader mb-3" id="{{ $id }}">
    @if ($label)
        <label class="form-label fw-semibold text-dark mb-2">
            {{ $label }}
            @if ($required) <span class="text-danger">*</span> @endif
        </label>
    @endif

    <div class="uploader-dropzone border rounded-3 p-3 text-center position-relative transition-all"
         style="background: var(--neutral-50, #f8fafc); border: 2px dashed var(--neutral-300, #cbd5e1) !important; cursor: pointer;"
         onclick="document.getElementById('{{ $id }}_file').click();"
         ondragover="event.preventDefault(); this.style.borderColor='var(--color-primary)'; this.style.background='var(--primary-50)';"
         ondragleave="event.preventDefault(); this.style.borderColor='var(--neutral-300)'; this.style.background='var(--neutral-50)';"
         ondrop="handleImageDrop(event, '{{ $id }}_file', '{{ $id }}_preview_img', '{{ $id }}_preview_wrap', '{{ $id }}_placeholder');">

        {{-- Preview Wrap --}}
        <div id="{{ $id }}_preview_wrap" class="uploader-preview-wrap mb-2" style="{{ $hasImage ? '' : 'display: none;' }}">
            <img id="{{ $id }}_preview_img"
                 src="{{ $resolvedUrl }}"
                 alt="Image preview"
                 class="img-thumbnail rounded-3 shadow-xs"
                 style="max-height: {{ $aspectRatio === 'wide' ? '180px' : '140px' }}; width: auto; object-fit: contain;">
            
            <div class="mt-2">
                <span class="badge bg-light text-dark border px-2 py-1">
                    <i class="bi bi-arrow-repeat me-1"></i> Click or drop to replace
                </span>
            </div>
        </div>

        {{-- Placeholder State --}}
        <div id="{{ $id }}_placeholder" class="uploader-placeholder py-3" style="{{ $hasImage ? 'display: none;' : '' }}">
            <div class="uploader-icon mb-2" style="font-size: 2rem; color: var(--neutral-400);">
                <i class="bi bi-cloud-arrow-up"></i>
            </div>
            <div class="fw-semibold text-dark mb-1" style="font-size: 0.88rem;">
                Click to browse or drag & drop image
            </div>
            <div class="text-muted small">{{ $hint }}</div>
        </div>

        <input type="file"
               id="{{ $id }}_file"
               name="{{ $name }}"
               accept="image/*"
               class="d-none @error($name) is-invalid @enderror"
               onchange="handleImageChange(this, '{{ $id }}_preview_img', '{{ $id }}_preview_wrap', '{{ $id }}_placeholder');">
    </div>

    @error($name)
        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
    @enderror
</div>

@once
@push('js')
<script>
function handleImageChange(input, previewImgId, previewWrapId, placeholderId) {
    const file = input.files && input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const previewImg = document.getElementById(previewImgId);
            const previewWrap = document.getElementById(previewWrapId);
            const placeholder = document.getElementById(placeholderId);
            if (previewImg) previewImg.src = e.target.result;
            if (previewWrap) previewWrap.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
}

function handleImageDrop(e, fileInputId, previewImgId, previewWrapId, placeholderId) {
    e.preventDefault();
    const dropzone = e.currentTarget;
    dropzone.style.borderColor = 'var(--neutral-300)';
    dropzone.style.background = 'var(--neutral-50)';

    if (e.dataTransfer && e.dataTransfer.files.length > 0) {
        const fileInput = document.getElementById(fileInputId);
        fileInput.files = e.dataTransfer.files;
        handleImageChange(fileInput, previewImgId, previewWrapId, placeholderId);
    }
}
</script>
@endpush
@endonce
