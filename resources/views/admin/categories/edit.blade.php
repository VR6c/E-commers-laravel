@extends('admin.layouts.admin')

@section('content')

<x-admin.page-header :title="'Category'">
    <x-slot:actions>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-light">
            <i class="bi bi-arrow-left me-1"></i> {{ 'Back' }}
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @php $translation = $category; @endphp

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">{{ 'Name' }}</label>
                        <input type="text" name="name"
                            class="form-control border-0 bg-light @error('name') is-invalid @enderror"
                            value="{{ old('name', $category->name ?? '') }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">{{ 'Description' }}</label>
                        <textarea name="description"
                            class="form-control ck-editor @error('description') is-invalid @enderror">{{ old('description', $category->description ?? '') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold">{{ 'Image' }}</label>
                        <div class="image-upload-wrapper border rounded-3 p-4 text-center bg-light">
                            <div id="image_preview_en" class="mb-3"
                                style="{{ ($category->image_url) ? '' : 'display:none;' }}">
                                <img id="image_preview_img_en"
                                    src="{{ ($category->image_url) ? (\Illuminate\Support\Str::startsWith($category->image_url, ['http://', 'https://']) ? $category->image_url : asset('storage/' . $category->image_url)) : '#' }}"
                                    alt="Preview" class="img-thumbnail shadow-sm" style="max-height: 150px;"
                                    onerror="this.onerror=null;this.src='{{ asset('images/no-product.png') }}';">
                            </div>
                            <div class="upload-controls">
                                <label class="btn btn-outline-primary shadow-sm" for="image_file_en">
                                    <i class="bi bi-cloud-arrow-up me-1"></i> {{ 'Choose File' }}
                                </label>
                                <input type="file" name="image" accept="image/*"
                                    class="form-control d-none @error('image') is-invalid @enderror"
                                    id="image_file_en"
                                    onchange="previewImage(this)">
                            </div>
                        </div>
                        @error('image')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">{{ 'Publishing' }}</h6>
                    <x-admin.combobox
                        name="status"
                        :label="'Status'"
                        wrapper-class="mb-3"
                        :selected="$category->status"
                        :options="['active' => 'Active', 'inactive' => 'Inactive']" />
                    <hr class="my-4">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary shadow-sm py-2">
                            <i class="bi bi-save me-1"></i> {{ 'Update Category' }}
                        </button>
                    </div>
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
    var file = input.files[0];
    var previewElement = document.getElementById('image_preview_en');
    var previewImg = document.getElementById('image_preview_img_en');
    if (file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            previewElement.style.display = 'block';
            previewImg.src = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        previewElement.style.display = 'none';
    }
}

document.querySelectorAll('.ck-editor').forEach((element) => {
    ClassicEditor.create(element).catch(error => { console.error(error); });
});
</script>
@endsection
