@extends('admin.layouts.admin')

@section('title', 'Create Page — Admin')

@section('content')

<x-admin.page-header
    :title="'Create Page'"
    :breadcrumbs="['Pages' => route('admin.pages.index'), 'Create' => '']">
    <x-slot:actions>
        <a href="{{ route('admin.pages.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to Pages
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form id="pageForm" action="{{ route('admin.pages.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <x-admin.form-card :title="'Page Content'" :icon="'bi bi-file-earmark-richtext'">
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">{{ 'Page Title' }} <span class="text-danger">*</span></label>
                    <input type="text"
                           name="title"
                           value="{{ old('title') }}"
                           class="form-control @error('title') is-invalid @enderror"
                           placeholder="e.g. About Us, Terms of Service, Privacy Policy"
                           required
                           autofocus>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold text-dark mb-2">{{ 'Body Content' }}</label>
                    <textarea id="content_en"
                              name="content"
                              class="form-control ck-editor @error('content') is-invalid @enderror">{{ old('content') }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </x-admin.form-card>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Featured Image -->
            <x-admin.form-card :title="'Featured Image'" :icon="'bi bi-image'">
                <div class="mb-3">
                    <div class="image-upload-wrapper">
                        <div class="image-preview mb-3 text-center bg-light rounded py-4 border-2 border-dashed"
                             id="image_preview_en"
                             style="{{ old('image_base64') ? '' : 'display:none;' }}">
                            <img id="image_preview_img_en"
                                src="{{ old('image_base64') ?: '#' }}"
                                class="img-fluid rounded shadow-sm" style="max-height: 160px; object-fit: cover;">
                        </div>

                        @if(!old('image_base64'))
                        <div class="placeholder-preview mb-3 text-center bg-light rounded py-4 border-2 border-dashed"
                             onclick="document.getElementById('image_file_en').click()"
                             id="placeholder_en"
                             style="cursor: pointer;">
                            <i class="bi bi-cloud-arrow-up text-primary" style="font-size: 2.25rem;"></i>
                            <p class="text-dark fw-medium small mt-2 mb-1">Click to upload header image</p>
                            <span class="text-muted" style="font-size: 0.75rem;">PNG, JPG, WEBP up to 2MB</span>
                        </div>
                        @endif

                        <div class="d-grid">
                            <label class="btn btn-outline-primary btn-sm" for="image_file_en">
                                <i class="bi bi-upload me-1"></i> {{ 'Choose File' }}
                            </label>
                            <input type="file" id="image_file_en"
                                name="image" accept="image/*"
                                class="form-control d-none @error('image') is-invalid @enderror"
                                onchange="previewImage(this)">
                        </div>

                        <input type="hidden" id="image_base64_en"
                            name="image_base64"
                            value="{{ old('image_base64') }}">
                    </div>
                    @error('image')
                        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </x-admin.form-card>

            <div class="mt-4">
                <!-- Publishing Actions -->
                <x-admin.form-card :title="'Publishing'" :icon="'bi bi-send'">
                    <p class="text-muted small mb-4">
                        Pages are automatically published upon saving and will appear in header or footer menus once linked.
                    </p>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ 'Publish Page' }}
                        </button>
                        <a href="{{ route('admin.pages.index') }}" class="btn btn-secondary py-2">
                            {{ 'Cancel' }}
                        </a>
                    </div>
                </x-admin.form-card>
            </div>
        </div>
    </div>
</form>
@endsection

@section('js')
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
<script>
let ckEditor;
ClassicEditor.create(document.getElementById('content_en'))
    .then(editor => { ckEditor = editor; })
    .catch(error => { console.error('CKEditor init error', error); });

function previewImage(input) {
    var file = input.files[0];
    var previewElement = document.getElementById('image_preview_en');
    var previewImg = document.getElementById('image_preview_img_en');
    var hiddenInput = document.getElementById('image_base64_en');
    var placeholder = document.getElementById('placeholder_en');
    if (file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            previewElement.style.display = 'block';
            previewImg.src = e.target.result;
            hiddenInput.value = e.target.result;
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    } else {
        previewElement.style.display = 'none';
        previewImg.src = '';
        hiddenInput.value = '';
        if (placeholder) placeholder.style.display = 'block';
    }
}

function base64ToFile(dataurl, baseName) {
    if (!dataurl || dataurl.indexOf(',') === -1) return null;
    var arr = dataurl.split(',');
    var mimeMatch = arr[0].match(/data:(.*);base64/);
    if (!mimeMatch) return null;
    var mime = mimeMatch[1];
    var ext = mime.split('/')[1].split('+')[0];
    if (ext === 'jpeg') ext = 'jpg';
    var bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
    for (var i = 0; i < n; i++) { u8arr[i] = bstr.charCodeAt(i); }
    return new File([u8arr], baseName + '.' + ext, { type: mime });
}

document.getElementById('pageForm').addEventListener('submit', function () {
    if (ckEditor) {
        document.getElementById('content_en').value = ckEditor.getData();
    }
    var fileInput = document.getElementById('image_file_en');
    var base64Input = document.getElementById('image_base64_en');
    if (fileInput && fileInput.files.length === 0 && base64Input && base64Input.value) {
        try {
            var f = base64ToFile(base64Input.value, 'image_en');
            if (f) {
                var dt = new DataTransfer();
                dt.items.add(f);
                fileInput.files = dt.files;
            }
        } catch (err) {
            console.error('base64 -> File failed', err);
        }
    }
});
</script>
@endsection
