@extends('admin.layouts.admin')

@section('title', 'Edit Site Settings — Admin')

@section('content')

<x-admin.page-header
    :title="'Edit Site Settings'"
    :breadcrumbs="['Settings' => route('admin.site-settings.index'), 'Edit' => '']">
    <x-slot:actions>
        <a href="{{ route('admin.site-settings.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to Overview
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('admin.site-settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Main Column -->
        <div class="col-lg-8">
            <x-admin.form-card :title="'General Configuration'" :icon="'bi bi-globe2'" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label for="site_name" class="form-label fw-semibold text-dark">{{ 'Site Name' }} <span class="text-danger">*</span></label>
                        <input type="text"
                               name="site_name"
                               id="site_name"
                               class="form-control @error('site_name') is-invalid @enderror"
                               value="{{ old('site_name', $settings->site_name ?? '') }}"
                               placeholder="e.g. My Modern E-Commerce"
                               required>
                        @error('site_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tagline" class="form-label fw-semibold text-dark">{{ 'Tagline' }}</label>
                        <input type="text"
                               name="tagline"
                               id="tagline"
                               class="form-control @error('tagline') is-invalid @enderror"
                               value="{{ old('tagline', $settings->tagline ?? '') }}"
                               placeholder="e.g. Premium quality, best prices">
                        @error('tagline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-0">
                    <label for="footer_text" class="form-label fw-semibold text-dark">{{ 'Footer Copyright Notice' }}</label>
                    <textarea name="footer_text"
                              id="footer_text"
                              rows="3"
                              class="form-control @error('footer_text') is-invalid @enderror"
                              placeholder="e.g. All rights reserved.">{{ old('footer_text', $settings->footer_text ?? '') }}</textarea>
                    @error('footer_text')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </x-admin.form-card>

            <x-admin.form-card :title="'Search Engine Optimization (SEO)'" :icon="'bi bi-search'" class="mb-4">
                <div class="mb-3">
                    <label for="meta_title" class="form-label fw-semibold text-dark">{{ 'Meta Title' }}</label>
                    <input type="text"
                           name="meta_title"
                           id="meta_title"
                           class="form-control"
                           value="{{ old('meta_title', $settings->meta_title ?? '') }}"
                           placeholder="Search engine title tag">
                </div>

                <div class="mb-3">
                    <label for="meta_keywords" class="form-label fw-semibold text-dark">{{ 'Meta Keywords' }}</label>
                    <input type="text"
                           name="meta_keywords"
                           id="meta_keywords"
                           class="form-control"
                           value="{{ old('meta_keywords', $settings->meta_keywords ?? '') }}"
                           placeholder="comma-separated keywords (e.g. fashion, electronics, buy online)">
                </div>

                <div class="mb-0">
                    <label for="meta_description" class="form-label fw-semibold text-dark">{{ 'Meta Description' }}</label>
                    <textarea name="meta_description"
                              id="meta_description"
                              rows="3"
                              class="form-control"
                              placeholder="Brief summary for Google search results...">{{ old('meta_description', $settings->meta_description ?? '') }}</textarea>
                </div>
            </x-admin.form-card>
        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4">
            <x-admin.form-card :title="'Store Logo'" :icon="'bi bi-image'" class="mb-4">
                <x-admin.image-uploader
                    name="logo"
                    :current-image="$settings->logo"
                    :hint="'Recommended PNG or SVG with transparent background. Max 2MB'"
                    aspect-ratio="wide" />
            </x-admin.form-card>

            <x-admin.form-card :title="'Contact Information'" :icon="'bi bi-headset'" class="mb-4">
                <div class="mb-3">
                    <label for="contact_email" class="form-label fw-semibold text-dark">{{ 'Support Email' }}</label>
                    <input type="email"
                           name="contact_email"
                           id="contact_email"
                           class="form-control"
                           value="{{ old('contact_email', $settings->contact_email ?? '') }}"
                           placeholder="support@store.com">
                </div>

                <div class="mb-3">
                    <label for="contact_phone" class="form-label fw-semibold text-dark">{{ 'Phone Number' }}</label>
                    <input type="text"
                           name="contact_phone"
                           id="contact_phone"
                           class="form-control"
                           value="{{ old('contact_phone', $settings->contact_phone ?? '') }}"
                           placeholder="+855 12 345 678">
                </div>

                <div class="mb-0">
                    <label for="address" class="form-label fw-semibold text-dark">{{ 'Physical Address' }}</label>
                    <textarea name="address"
                              id="address"
                              rows="3"
                              class="form-control"
                              placeholder="Store location / address...">{{ old('address', $settings->address ?? '') }}</textarea>
                </div>
            </x-admin.form-card>

            <x-admin.form-card :title="'Save Changes'" :icon="'bi bi-check-circle'">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                        <i class="bi bi-save me-1 fs-6"></i> {{ 'Save Settings' }}
                    </button>
                    <a href="{{ route('admin.site-settings.index') }}" class="btn btn-secondary py-2">
                        {{ 'Cancel' }}
                    </a>
                </div>
            </x-admin.form-card>
        </div>
    </div>
</form>

@endsection
