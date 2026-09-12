@extends('admin.layouts.admin')

@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold">{{ 'Edit Banner' }}</h4>
            <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> {{ 'Back' }}
            </a>
        </div>
    </div>
</div>

<form id="bannerForm" action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">{{ 'Title' }}</label>
                        <input type="text" name="languages[en][title]"
                            class="form-control border-0 bg-light @error('languages.en.title') is-invalid @enderror"
                            value="{{ old('languages.en.title', $banner->title ?? '') }}"
                            required placeholder="Banner title">
                        @error('languages.en.title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold">{{ 'Description' }}</label>
                        <textarea id="description_en" name="languages[en][description]"
                            class="form-control border-0 bg-light ck-editor @error('languages.en.description') is-invalid @enderror"
                            rows="4">{{ old('languages.en.description', $banner->description ?? '') }}</textarea>
                        @error('languages.en.description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">{{ 'Settings' }}</h6>

                    <x-admin.combobox
                        name="type"
                        id="type"
                        wrapper-class="mb-4"
                        :label="'Banner Type'"
                        :selected="$banner->type"
                        :options="[
                            'promotion' => 'Promotion',
                            'sale' => 'Sale',
                            'seasonal' => 'Seasonal',
                            'featured' => 'Featured',
                            'announcement' => 'Announcement',
                        ]"
                        required />

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary shadow-sm py-2">
                            <i class="bi bi-save me-1"></i> {{ 'Save Banner' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Banner Image -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4 text-center">
                    <h6 class="fw-bold mb-3 text-start">{{ 'Banner Image' }}</h6>

                    <div class="image-preview mb-3 bg-light rounded py-4 border-2 border-dashed" id="image_preview_en">
                        @php
                            $imgSrc = asset('images/no-product.png');
                            if (!empty($banner->image_url)) {
                                $imgSrc = \Illuminate\Support\Str::startsWith($banner->image_url, ['http://', 'https://'])
                                    ? $banner->image_url
                                    : Storage::disk('public')->url($banner->image_url);
                            }
                        @endphp
                        <img id="image_preview_img_en" src="{{ $imgSrc }}"
                            class="img-fluid rounded shadow-sm" style="max-height: 150px; display: block; margin: 0 auto;"
                            onerror="this.onerror=null;this.src='{{ asset('images/no-product.png') }}';">
                    </div>
                    <p id="image_filename_en" class="text-muted small mb-2" style="display:none;"></p>

                    <div class="d-grid gap-2">
                        <label for="image_file_en" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-cloud-arrow-up me-1"></i> {{ 'Change Image' }}
                        </label>
                        <input type="file" id="image_file_en"
                            name="languages[en][image]"
                            class="d-none form-control @error('languages.en.image') is-invalid @enderror"
                            accept="image/*"
                            onchange="previewImage(this)">
                    </div>
                    @error('languages.en.image')
                    <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('js')
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
<script>
function previewImage(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const previewImg = document.getElementById('image_preview_img_en');
    const filenameEl = document.getElementById('image_filename_en');
    const previewWrapper = document.getElementById('image_preview_en');

    filenameEl.textContent = file.name;
    filenameEl.style.display = 'block';
    previewWrapper.style.borderColor = '#0d6efd';

    const reader = new FileReader();
    reader.onload = function (e) { previewImg.src = e.target.result; };
    reader.readAsDataURL(file);
}

let ckEditor;
document.addEventListener("DOMContentLoaded", function () {
    const el = document.getElementById('description_en');
    if (el) {
        ClassicEditor.create(el, {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote'],
        }).then(editor => {
            ckEditor = editor;
        }).catch(error => { console.error(error); });
    }

    document.getElementById('bannerForm').addEventListener('submit', function () {
        if (ckEditor) el.value = ckEditor.getData();
    });
});
</script>
@endsection
