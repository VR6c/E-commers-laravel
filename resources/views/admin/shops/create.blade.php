@extends('admin.layouts.admin')

@section('title', 'Create Shop — Admin')

@section('content')

<x-admin.page-header
    :title="'Create Shop'"
    :breadcrumbs="['Shops' => route('admin.shops.index'), 'Create' => '']">
    <x-slot:actions>
        <a href="{{ route('admin.shops.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to Shops
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('admin.shops.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <x-admin.form-card :title="'Shop Information'" :icon="'bi bi-shop'">
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">{{ 'Shop Name' }} <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           required
                           placeholder="e.g. Urban Threads Flagship, AudioCraft Store"
                           autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold text-dark">{{ 'Description' }}</label>
                    <textarea name="description"
                              class="form-control @error('description') is-invalid @enderror"
                              rows="5"
                              placeholder="Brief summary of shop offerings, specialty, and seller profile...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </x-admin.form-card>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Logo Upload -->
            <x-admin.form-card :title="'Shop Logo'" :icon="'bi bi-image'">
                <div class="mb-3 text-center">
                    <div class="image-preview mb-3 bg-light rounded py-4 border-2 border-dashed"
                         id="logo_preview"
                         style="display:none;">
                        <img id="logo_preview_img" src="#" class="img-fluid rounded shadow-sm" style="max-height: 140px; object-fit: contain;">
                    </div>

                    <div class="placeholder-preview mb-3 text-center bg-light rounded py-4 border-2 border-dashed"
                         onclick="document.getElementById('logo_input').click()"
                         id="logo_placeholder"
                         style="cursor: pointer;">
                        <i class="bi bi-shop text-primary" style="font-size: 2.25rem;"></i>
                        <p class="text-dark fw-medium small mt-2 mb-1">Click to upload shop logo</p>
                        <span class="text-muted" style="font-size: 0.75rem;">PNG, JPG, SVG up to 2MB</span>
                    </div>

                    <div class="d-grid">
                        <label class="btn btn-outline-primary btn-sm" for="logo_input">
                            <i class="bi bi-upload me-1"></i> {{ 'Choose Logo' }}
                        </label>
                        <input type="file"
                               name="logo"
                               id="logo_input"
                               class="form-control d-none @error('logo') is-invalid @enderror"
                               accept="image/*"
                               onchange="previewLogo(this)">
                    </div>
                    @error('logo')
                        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </x-admin.form-card>

            <div class="mt-4">
                <!-- Publishing & Status -->
                <x-admin.form-card :title="'Visibility & Action'" :icon="'bi bi-toggle-on'">
                    <div class="mb-4">
                        <x-admin.combobox
                            name="status"
                            :label="'Shop Status'"
                            :options="['active' => 'Active', 'inactive' => 'Inactive']"
                            required />
                    </div>

                    <hr class="my-4" style="border-color: var(--border-subtle);">

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ 'Save Shop' }}
                        </button>
                        <a href="{{ route('admin.shops.index') }}" class="btn btn-secondary py-2">
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
<script>
function previewLogo(input) {
    const preview = document.getElementById('logo_preview');
    const previewImg = document.getElementById('logo_preview_img');
    const placeholder = document.getElementById('logo_placeholder');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
            if(placeholder) placeholder.style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
