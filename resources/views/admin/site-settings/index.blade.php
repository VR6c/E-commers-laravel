@extends('admin.layouts.admin')

@section('title', 'Site Settings Overview — Admin')

@section('content')

<x-admin.page-header
    :title="'Site Settings'"
    :breadcrumbs="['Settings' => '']">
    <x-slot:actions>
        <a href="{{ route('admin.site-settings.edit') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-pencil-square me-1"></i> {{ 'Edit Settings' }}
        </a>
    </x-slot:actions>
</x-admin.page-header>

<div class="row g-4">
    <div class="col-lg-8">
        {{-- General Configuration Card --}}
        <x-admin.form-card :title="'General Configuration'" :icon="'bi bi-globe2'" class="mb-4">
            <div class="row g-3 py-2">
                <div class="col-sm-4 text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em;">{{ 'Site Name' }}</div>
                <div class="col-sm-8 fw-semibold text-dark">{{ $settings->site_name ?? 'Not configured' }}</div>

                <div class="col-12"><hr class="my-1" style="border-color: var(--border-subtle);"></div>

                <div class="col-sm-4 text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em;">{{ 'Tagline' }}</div>
                <div class="col-sm-8 text-secondary">{{ $settings->tagline ?? 'Not configured' }}</div>

                <div class="col-12"><hr class="my-1" style="border-color: var(--border-subtle);"></div>

                <div class="col-sm-4 text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em;">{{ 'Footer Text' }}</div>
                <div class="col-sm-8 text-secondary">{{ $settings->footer_text ?? 'Not configured' }}</div>
            </div>
        </x-admin.form-card>

        {{-- SEO Configuration Card --}}
        <x-admin.form-card :title="'Search Engine Optimization (SEO)'" :icon="'bi bi-search'" class="mb-4">
            <div class="row g-3 py-2">
                <div class="col-sm-4 text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em;">{{ 'Meta Title' }}</div>
                <div class="col-sm-8 fw-semibold text-dark">{{ $settings->meta_title ?? 'Not configured' }}</div>

                <div class="col-12"><hr class="my-1" style="border-color: var(--border-subtle);"></div>

                <div class="col-sm-4 text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em;">{{ 'Meta Keywords' }}</div>
                <div class="col-sm-8 text-secondary">
                    @if(!empty($settings->meta_keywords))
                        @foreach(explode(',', $settings->meta_keywords) as $keyword)
                            <span class="badge bg-light text-dark border me-1 mb-1">{{ trim($keyword) }}</span>
                        @endforeach
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>

                <div class="col-12"><hr class="my-1" style="border-color: var(--border-subtle);"></div>

                <div class="col-sm-4 text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em;">{{ 'Meta Description' }}</div>
                <div class="col-sm-8 text-secondary">{{ $settings->meta_description ?? 'Not configured' }}</div>
            </div>
        </x-admin.form-card>
    </div>

    <div class="col-lg-4">
        {{-- Brand Logo Card --}}
        <x-admin.form-card :title="'Store Brand Logo'" :icon="'bi bi-image'" class="mb-4">
            <div class="text-center py-2">
                @if($settings->logo)
                    <img src="{{ \Illuminate\Support\Str::startsWith($settings->logo, ['http://','https://']) ? $settings->logo : asset('storage/' . $settings->logo) }}"
                         alt="{{ $settings->site_name ?? 'Logo' }}"
                         class="img-thumbnail rounded-3 shadow-xs mb-2"
                         style="max-height: 100px; width: auto; object-fit: contain;">
                @else
                    <div class="p-4 bg-light rounded-3 text-muted">
                        <i class="bi bi-image fs-1 mb-1 d-block opacity-50"></i>
                        <small>No custom logo uploaded</small>
                    </div>
                @endif
            </div>
        </x-admin.form-card>

        {{-- Contact Details Card --}}
        <x-admin.form-card :title="'Contact & Support'" :icon="'bi bi-headset'" class="mb-4">
            <div class="mb-3">
                <div class="text-muted small fw-semibold text-uppercase mb-1" style="letter-spacing: 0.05em;">{{ 'Support Email' }}</div>
                <div class="fw-semibold text-dark">{{ $settings->contact_email ?? 'Not configured' }}</div>
            </div>

            <div class="mb-3">
                <div class="text-muted small fw-semibold text-uppercase mb-1" style="letter-spacing: 0.05em;">{{ 'Phone Number' }}</div>
                <div class="fw-semibold text-dark">{{ $settings->contact_phone ?? 'Not configured' }}</div>
            </div>

            <div class="mb-0">
                <div class="text-muted small fw-semibold text-uppercase mb-1" style="letter-spacing: 0.05em;">{{ 'Store Address' }}</div>
                <div class="text-secondary small">{{ $settings->address ?? 'Not configured' }}</div>
            </div>
        </x-admin.form-card>
    </div>
</div>

@endsection
