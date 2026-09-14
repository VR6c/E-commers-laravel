@extends('admin.layouts.admin')

@section('title', 'Edit Social Link — Admin')

@section('content')

<x-admin.page-header
    :title="'Edit Social Media Link'"
    :breadcrumbs="['Social Media' => route('admin.social-media-links.index'), 'Edit #' . $socialMediaLink->id => '']">
    <x-slot:actions>
        <a href="{{ route('admin.social-media-links.index') }}" class="btn btn-secondary shadow-xs">
            <i class="bi bi-arrow-left me-1"></i> Back to Links
        </a>
    </x-slot:actions>
</x-admin.page-header>

<form action="{{ route('admin.social-media-links.update', $socialMediaLink->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <x-admin.form-card :title="'Link Details'" :icon="'bi bi-share'">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="platform" class="form-label fw-semibold text-dark">{{ 'Platform Display Label' }} <span class="text-danger">*</span></label>
                        <input type="text"
                               name="platform"
                               id="platform"
                               class="form-control @error('platform') is-invalid @enderror"
                               value="{{ old('platform', $socialMediaLink->platform) }}"
                               required
                               autofocus>
                        @error('platform')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="link" class="form-label fw-semibold text-dark">{{ 'Destination URL' }} <span class="text-danger">*</span></label>
                        <input type="url"
                               name="link"
                               id="link"
                               class="form-control @error('link') is-invalid @enderror"
                               value="{{ old('link', $socialMediaLink->link) }}"
                               required>
                        @error('link')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label fw-semibold text-dark">{{ 'Internal Reference / Handle' }}</label>
                    <input type="text"
                           name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $socialMediaLink->name ?? '') }}"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </x-admin.form-card>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <x-admin.form-card :title="'Type & Action'" :icon="'bi bi-gear'">
                <div class="mb-4">
                    <x-admin.combobox
                        name="type"
                        id="type"
                        :label="'Platform Icon Type'"
                        :selected="$socialMediaLink->type"
                        :placeholder="'Select Type'"
                        :placeholder-disabled="true"
                        required
                        :options="[
                            'facebook' => 'Facebook',
                            'instagram' => 'Instagram',
                            'tiktok' => 'TikTok',
                            'youtube' => 'YouTube',
                            'x' => 'X (Twitter)',
                        ]" />
                </div>

                <hr class="my-4" style="border-color: var(--border-subtle);">

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary shadow-sm py-2-5">
                        <i class="bi bi-check-circle-fill me-1"></i> {{ 'Update Link' }}
                    </button>
                    <a href="{{ route('admin.social-media-links.index') }}" class="btn btn-secondary py-2">
                        {{ 'Cancel' }}
                    </a>
                </div>
            </x-admin.form-card>
        </div>
    </div>
</form>
@endsection
