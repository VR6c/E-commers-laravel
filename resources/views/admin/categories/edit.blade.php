@extends('admin.layouts.admin')

@section('title', 'Edit Category — Admin')

@section('content')

<x-admin.page-header
    :title="'Edit Category'"
    :subtitle="$category->name"
    :breadcrumbs="['Categories' => route('admin.categories.index'), 'Edit' => '']">
    <x-slot:actions>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form id="categoryEditForm" action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <x-admin.form-card :title="'General Information'" :icon="'bi bi-folder2-open'">
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">{{ 'Category Name' }} <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $category->name) }}"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">{{ 'Description' }}</label>
                    <textarea id="description_en"
                              name="description"
                              class="form-control ck-editor @error('description') is-invalid @enderror"
                              rows="4">{{ old('description', $category->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-0">
                    <x-admin.image-uploader
                        name="image"
                        :label="'Category Image'"
                        :current-image="$category->image_url"
                        aspect-ratio="wide" />
                </div>
            </x-admin.form-card>
        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4">
            <x-admin.form-card :title="'Publishing & Status'" :icon="'bi bi-gear'">
                <x-admin.combobox
                    name="status"
                    :label="'Status'"
                    wrapper-class="mb-4"
                    :options="['active' => 'Active', 'inactive' => 'Inactive']"
                    :selected="old('status', $category->status ? 'active' : 'inactive')" />

                <div class="mb-3 text-muted small">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Created:</span>
                        <span class="text-dark fw-medium">{{ $category->created_at ? $category->created_at->format('M d, Y') : '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Last Updated:</span>
                        <span class="text-dark fw-medium">{{ $category->updated_at ? $category->updated_at->format('M d, Y') : '—' }}</span>
                    </div>
                </div>

                <hr class="my-4" style="border-color: var(--border-subtle);">

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                        <i class="bi bi-save me-1 fs-6"></i> {{ 'Update Category' }}
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary py-2">
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

document.getElementById('categoryEditForm').addEventListener('submit', function () {
    if (ckEditor) {
        document.getElementById('description_en').value = ckEditor.getData();
    }
});
</script>
@endsection
