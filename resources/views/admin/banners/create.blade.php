@extends('admin.layouts.admin')

@section('title', 'Create Banner — Admin')

@section('content')

<x-admin.page-header
    :title="'Create Banner'"
    :breadcrumbs="['Banners' => route('admin.banners.index'), 'Create' => '']">
    <x-slot:actions>
        <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to Banners
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form id="bannerForm" action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <x-admin.form-card :title="'Banner Content'" :icon="'bi bi-card-image'" class="mb-4">
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">{{ 'Banner Title' }} <span class="text-danger">*</span></label>
                    <input type="text"
                           name="languages[en][title]"
                           class="form-control @error('languages.en.title') is-invalid @enderror"
                           value="{{ old('languages.en.title') }}"
                           placeholder="e.g. Summer Mega Sale Up To 50% Off"
                           required
                           autofocus>
                    @error('languages.en.title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold text-dark">{{ 'Description / Subtitle' }}</label>
                    <textarea id="description_en"
                              name="languages[en][description]"
                              class="form-control ck-editor @error('languages.en.description') is-invalid @enderror"
                              rows="4"
                              placeholder="Brief promotional copy or CTA subtitle...">{{ old('languages.en.description') }}</textarea>
                    @error('languages.en.description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </x-admin.form-card>

            <x-admin.form-card :title="'Banner Image'" :icon="'bi bi-image'">
                <x-admin.image-uploader
                    name="languages[en][image]"
                    :hint="'Recommended 1920x600px for hero banners. PNG, JPG or WebP (Max 8MB)'"
                    aspect-ratio="wide"
                    required />
            </x-admin.form-card>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <x-admin.form-card :title="'Display Settings'" :icon="'bi bi-sliders'">
                <x-admin.combobox
                    name="type"
                    id="type"
                    wrapper-class="mb-4"
                    :label="'Banner Placement / Type'"
                    :options="[
                        'promotion' => 'Promotion Banner',
                        'sale' => 'Flash Sale Banner',
                        'seasonal' => 'Seasonal Campaign',
                        'featured' => 'Featured Highlight',
                        'announcement' => 'Top Announcement',
                    ]"
                    required />

                <hr class="my-4" style="border-color: var(--border-subtle);">

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                        <i class="bi bi-check-lg me-1 fs-6"></i> {{ 'Save Banner' }}
                    </button>
                    <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary py-2">
                        {{ 'Cancel' }}
                    </a>
                </div>
            </x-admin.form-card>
        </div>
    </div>
</form>

@endsection

@section('js')
<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
<script>
let ckEditor;
ClassicEditor.create(document.getElementById('description_en'))
    .then(editor => { ckEditor = editor; })
    .catch(error => { console.error('CKEditor error', error); });

document.getElementById('bannerForm').addEventListener('submit', function () {
    if (ckEditor) {
        document.getElementById('description_en').value = ckEditor.getData();
    }
});
</script>
@endsection
