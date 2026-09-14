@extends('vendor.layouts.master')

@section('title', 'Edit Social Media Link')

@section('content')

<x-admin.page-header
    :title="'Edit Social Media Link'"
    icon="bi bi-pencil-square"
    :subtitle="'Update details for ' . $socialMediaLink->platform"
    :breadcrumbs="['Social Media' => route('vendor.social-media-links.index'), 'Edit Link' => '#']">
    <x-slot:actions>
        <a href="{{ route('vendor.social-media-links.index') }}"
           class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 shadow-sm"
           style="border-radius:10px; font-size:.85rem;">
            <i class="bi bi-arrow-left"></i> Back to Links
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('vendor.social-media-links.update', $socialMediaLink->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">

        {{-- Main Fields --}}
        <div class="col-lg-8">
            <x-admin.form-card :title="'Link Information'" icon="bi bi-link-45deg" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="platform">
                                Platform <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="platform" name="platform"
                                   class="form-control @error('platform') is-invalid @enderror"
                                   value="{{ old('platform', $socialMediaLink->platform) }}"
                                   placeholder="e.g. Facebook Page" required>
                            @error('platform')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="name">
                                Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="name" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $socialMediaLink->name ?? '') }}"
                                   placeholder="e.g. My Facebook" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold" for="link">
                        Link URL <span class="text-danger">*</span>
                    </label>
                    <input type="url" id="link" name="link"
                           class="form-control @error('link') is-invalid @enderror"
                           value="{{ old('link', $socialMediaLink->link) }}"
                           placeholder="https://facebook.com/yourpage" required>
                    @error('link')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </x-admin.form-card>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <x-admin.form-card :title="'Settings'" icon="bi bi-gear-fill" class="mb-4">
                <div class="mb-0">
                    <label class="form-label fw-semibold" for="type">
                        Platform Type <span class="text-danger">*</span>
                    </label>
                    <select id="type" name="type"
                            class="form-select @error('type') is-invalid @enderror" required>
                        <option value="" disabled>Select type</option>
                        @foreach(['facebook'=>'Facebook','instagram'=>'Instagram','tiktok'=>'TikTok','youtube'=>'YouTube','x'=>'X (Twitter)'] as $value => $label)
                            <option value="{{ $value }}"
                                {{ old('type', $socialMediaLink->type) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </x-admin.form-card>

            <x-admin.form-card class="mb-4">
                <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2 py-2 fw-semibold shadow-sm" style="border-radius:10px;">
                    <i class="bi bi-check-circle-fill"></i> Update Link
                </button>
                <a href="{{ route('vendor.social-media-links.index') }}"
                   class="btn btn-outline-secondary w-100 mt-2 d-flex align-items-center justify-content-center gap-2 py-2 fw-semibold"
                   style="border-radius:10px;">
                    <i class="bi bi-x-lg"></i> Cancel
                </a>
            </x-admin.form-card>
        </div>

    </div>
</form>

@endsection
