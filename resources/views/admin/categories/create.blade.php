@extends('admin.layouts.admin')

@section('title', 'Create Category — Admin')

@section('content')

<x-admin.page-header
    :title="'Create Category'"
    :breadcrumbs="['Categories' => route('admin.categories.index'), 'Create' => '']">
    <x-slot:actions>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form id="categoryForm" action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <x-admin.form-card :title="'General Information'" :icon="'bi bi-folder2-open'">
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">{{ 'Category Name' }} <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           placeholder="e.g. Electronics, Fashion, Home Decor"
                           required
                           autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">{{ 'Description' }}</label>
                    <textarea id="description_en"
                              name="description"
                              class="form-control ck-editor @error('description') is-invalid @enderror"
                              rows="4"
                              placeholder="Describe this category...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-0">
                    <x-admin.image-uploader
                        name="image"
                        :label="'Category Banner / Image'"
                        :hint="'Recommended 800x800px. PNG, JPG or WebP (Max 5MB)'"
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
                    :selected="old('status', 'active')" />

                <hr class="my-4" style="border-color: var(--border-subtle);">

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                        <i class="bi bi-check-lg me-1 fs-6"></i> {{ 'Save Category' }}
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

document.getElementById('categoryForm').addEventListener('submit', function () {
    if (ckEditor) {
        document.getElementById('description_en').value = ckEditor.getData();
    }
});
</script>
@endsection
