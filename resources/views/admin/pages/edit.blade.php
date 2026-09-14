@extends('admin.layouts.admin')

@section('title', 'Edit Page — Admin')

@section('content')

<x-admin.page-header
    :title="'Edit Page'"
    :breadcrumbs="['Pages' => route('admin.pages.index'), 'Edit #' . $page->id => '']">
    <x-slot:actions>
        <a href="{{ route('admin.pages.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to Pages
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('admin.pages.update', $page->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @php $translation = $page; @endphp

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <x-admin.form-card :title="'Page Content'" :icon="'bi bi-file-earmark-richtext'">
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">{{ 'Page Title' }} <span class="text-danger">*</span></label>
                    <input type="text"
                           name="title"
                           class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $page->title) }}"
                           required
                           autofocus>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold text-dark mb-2">{{ 'Body Content' }}</label>
                    <textarea name="content"
                              id="content_en"
                              class="form-control ck-editor @error('content') is-invalid @enderror">{{ old('content', $page->content) }}</textarea>
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
                <div class="mb-3 text-center">
                    <div class="image-preview mb-3 bg-light rounded py-3 border-2 border-dashed"
                         id="image_preview_en"
                         style="display:{{ $page->image_url ? 'block' : 'none' }};">
                        <img id="image_preview_img_en"
                            src="{{ $page->image_url ? (\Illuminate\Support\Str::startsWith($page->image_url, ['http://', 'https://']) ? $page->image_url : Storage::url($page->image_url)) : '#' }}"
                            class="img-fluid rounded shadow-sm" style="max-height: 160px; object-fit: cover;">
                    </div>

                    <div class="d-grid">
                        <label class="btn btn-outline-primary btn-sm" for="image_file_en">
                            <i class="bi bi-upload me-1"></i> {{ 'Change Image' }}
                        </label>
                        <input type="file" id="image_file_en"
                            name="image" accept="image/*"
                            class="form-control d-none @error('image') is-invalid @enderror"
                            onchange="previewImage()">
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
                        Update and synchronize changes to this page.
                    </p>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ 'Update Page' }}
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
document.querySelectorAll('.ck-editor').forEach(el => {
    ClassicEditor.create(el).catch(console.error);
});

function previewImage() {
    const input = document.getElementById('image_file_en');
    const previewDiv = document.getElementById('image_preview_en');
    const previewImg = document.getElementById('image_preview_img_en');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewDiv.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
