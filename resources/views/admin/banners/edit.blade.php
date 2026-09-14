@extends('admin.layouts.admin')

@section('title', 'Edit Banner — Admin')

@section('content')

<x-admin.page-header
    :title="'Edit Banner'"
    :subtitle="$banner->title"
    :breadcrumbs="['Banners' => route('admin.banners.index'), 'Edit' => '']">
    <x-slot:actions>
        <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to Banners
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form id="bannerForm" action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <x-admin.form-card :title="'Banner Content'" :icon="'bi bi-card-image'" class="mb-4">
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">{{ 'Banner Title' }} <span class="text-danger">*</span></label>
                    <input type="text"
                           name="languages[en][title]"
                           class="form-control @error('languages.en.title') is-invalid @enderror"
                           value="{{ old('languages.en.title', $banner->title ?? '') }}"
                           required>
                    @error('languages.en.title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold text-dark">{{ 'Description / Subtitle' }}</label>
                    <textarea id="description_en"
                              name="languages[en][description]"
                              class="form-control ck-editor @error('languages.en.description') is-invalid @enderror"
                              rows="4">{{ old('languages.en.description', $banner->description ?? '') }}</textarea>
                    @error('languages.en.description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </x-admin.form-card>

            <x-admin.form-card :title="'Banner Image'" :icon="'bi bi-image'">
                @php
                    $currentBannerImg = !empty($banner->image_url)
                        ? (\Illuminate\Support\Str::startsWith($banner->image_url, ['http://', 'https://'])
                            ? $banner->image_url
                            : Storage::disk('public')->url($banner->image_url))
                        : null;
                @endphp
                <x-admin.image-uploader
                    name="languages[en][image]"
                    :current-image="$currentBannerImg"
                    :hint="'Recommended 1920x600px. PNG, JPG or WebP (Max 8MB)'"
                    aspect-ratio="wide" />
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
                    :selected="$banner->type"
                    :options="[
                        'promotion' => 'Promotion Banner',
                        'sale' => 'Flash Sale Banner',
                        'seasonal' => 'Seasonal Campaign',
                        'featured' => 'Featured Highlight',
                        'announcement' => 'Top Announcement',
                    ]"
                    required />

                <div class="mb-3 text-muted small">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Created:</span>
                        <span class="text-dark fw-medium">{{ $banner->created_at ? $banner->created_at->format('M d, Y') : '—' }}</span>
                    </div>
                </div>

                <hr class="my-4" style="border-color: var(--border-subtle);">

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                        <i class="bi bi-save me-1 fs-6"></i> {{ 'Update Banner' }}
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
