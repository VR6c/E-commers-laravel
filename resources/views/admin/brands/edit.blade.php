@extends('admin.layouts.admin')

@section('title', 'Edit Brand — Admin')

@section('content')

<x-admin.page-header
    :title="'Edit Brand'"
    :subtitle="$brand->name"
    :breadcrumbs="['Brands' => route('admin.brands.index'), 'Edit' => '']">
    <x-slot:actions>
        <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data" id="brandEditForm">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <x-admin.form-card :title="'Brand Details'" :icon="'bi bi-tag'">
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">{{ 'Brand Name' }} <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $brand->name) }}"
                           required>
                    @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold text-dark">{{ 'Description' }}</label>
                    <textarea id="description"
                              name="description"
                              class="form-control ck-editor @error('description') is-invalid @enderror"
                              rows="4">{{ old('description', $brand->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </x-admin.form-card>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <x-admin.form-card :title="'Brand Logo'" :icon="'bi bi-image'">
                <x-admin.image-uploader
                    name="logo_url"
                    :current-image="$brand->logo_url"
                    :hint="'Square logo recommended. PNG, SVG or JPG (Max 5MB)'"
                    aspect-ratio="square" />

                <hr class="my-4" style="border-color: var(--border-subtle);">

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                        <i class="bi bi-save me-1 fs-6"></i> {{ 'Update Brand' }}
                    </button>
                    <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary py-2">
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
ClassicEditor.create(document.getElementById('description'))
    .then(editor => { ckEditor = editor; })
    .catch(error => { console.error('CKEditor error', error); });

document.getElementById('brandEditForm').addEventListener('submit', function () {
    if (ckEditor) {
        document.getElementById('description').value = ckEditor.getData();
    }
});
</script>
@endsection
