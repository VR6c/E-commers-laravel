@extends('admin.layouts.admin')

@section('title', 'Edit Shop — Admin')

@section('content')

<x-admin.page-header
    :title="'Edit Shop'"
    :breadcrumbs="['Shops' => route('admin.shops.index'), 'Edit #' . $shop->id => '']">
    <x-slot:actions>
        <a href="{{ route('admin.shops.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to Shops
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('admin.shops.update', $shop->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <x-admin.form-card :title="'Shop Information'" :icon="'bi bi-shop'">
                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">{{ 'Shop Name' }} <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $shop->name) }}"
                           required
                           autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold text-dark">{{ 'Description' }}</label>
                    <textarea name="description"
                              class="form-control @error('description') is-invalid @enderror"
                              rows="5">{{ old('description', $shop->description) }}</textarea>
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
                    <div class="image-preview mb-3 bg-light rounded py-4 border-2 border-dashed" id="logo_preview">
                        <img id="logo_preview_img"
                             src="{{ $shop->logo ? (\Illuminate\Support\Str::startsWith($shop->logo, ['http://', 'https://']) ? $shop->logo : asset('storage/'.$shop->logo)) : '#' }}"
                             class="img-fluid rounded shadow-sm"
                             style="max-height: 140px; object-fit: contain; display: {{ $shop->logo ? 'block' : 'none' }}; margin: 0 auto;">

                        @if(!$shop->logo)
                        <div id="logo_placeholder">
                            <i class="bi bi-shop text-muted" style="font-size: 2.25rem;"></i>
                            <p class="text-muted small mt-2 mb-0">{{ 'No logo uploaded' }}</p>
                        </div>
                        @endif
                    </div>

                    <div class="d-grid">
                        <label class="btn btn-outline-primary btn-sm" for="logo_input">
                            <i class="bi bi-upload me-1"></i> {{ 'Change Logo' }}
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
                <!-- Visibility & Status -->
                <x-admin.form-card :title="'Visibility & Action'" :icon="'bi bi-toggle-on'">
                    <div class="mb-4">
                        <x-admin.combobox
                            name="status"
                            :label="'Shop Status'"
                            :selected="strtolower($shop->status)"
                            :options="['active' => 'Active', 'inactive' => 'Inactive']"
                            required />
                    </div>

                    <hr class="my-4" style="border-color: var(--border-subtle);">

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ 'Update Shop' }}
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
            previewImg.style.display = 'block';
            if(placeholder) placeholder.style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
