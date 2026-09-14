@extends('vendor.layouts.master')

@section('title', 'My Profile')

@section('content')

<x-admin.page-header
    :title="'My Profile'"
    icon="bi bi-person-circle"
    :subtitle="'Update your store details, contact info and password'"
    :breadcrumbs="['Profile' => '#']">
    <x-slot:actions>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold">
            <i class="bi bi-shop me-1"></i> Vendor Account
        </span>
    </x-slot:actions>
</x-admin.page-header>

<form method="POST" action="{{ route('vendor.profile.update') }}"
      enctype="multipart/form-data" autocomplete="off" id="vendor-profile-form">
@csrf
@method('PATCH')

<div class="row g-4">

    {{-- LEFT: Avatar + Danger Zone --}}
    <div class="col-lg-4 col-xl-3">

        <x-admin.form-card class="mb-4 text-center">
            <div class="py-2">
                <div style="position:relative;display:inline-block;margin-bottom:16px;">
                    <img id="profilePreview"
                         src="{{ $vendor->profile_image
                             ? (\Illuminate\Support\Str::startsWith($vendor->profile_image, ['http://','https://'])
                                 ? $vendor->profile_image
                                 : asset('storage/'.$vendor->profile_image))
                             : 'https://ui-avatars.com/api/?name='.urlencode($vendor->name).'&background=6366f1&color=fff&size=120' }}"
                         alt="Profile"
                         style="width:110px;height:110px;border-radius:50%;object-fit:cover;border:4px solid #eef2ff;box-shadow:0 0 0 3px rgba(99,102,241,.18),0 4px 16px rgba(0,0,0,.10);">

                    <label for="profile_image"
                           style="position:absolute;bottom:4px;right:4px;width:32px;height:32px;border-radius:50%;background:#4f46e5;color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.85rem;border:2px solid #fff;box-shadow:0 2px 8px rgba(99,102,241,.35);transition:background .18s;"
                           title="Change Photo">
                        <i class="bi bi-camera"></i>
                    </label>
                    <input type="file" id="profile_image" name="profile_image" accept="image/*" class="d-none">
                </div>

                @error('profile_image')
                    <div class="text-danger small mb-2">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                    </div>
                @enderror

                <h5 class="fw-bold text-dark mb-1">{{ $vendor->name }}</h5>
                <p class="text-muted small mb-0">{{ $vendor->email }}</p>
            </div>
        </x-admin.form-card>

        <div class="card border-danger-subtle bg-danger-subtle p-3 rounded-4">
            <h6 class="text-danger fw-bold d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-exclamation-triangle-fill"></i> Danger Zone
            </h6>
            <p class="text-danger small mb-3">
                Deleting your account will permanently remove your store data and catalog.
            </p>
            <button type="button" class="btn btn-outline-danger w-100 fw-semibold rounded-pill d-flex align-items-center justify-content-center gap-2"
                    onclick="if(confirm('Are you sure you want to delete your account? This action cannot be undone.')) document.getElementById('delete-account-form').submit();">
                <i class="bi bi-trash"></i> Delete Account
            </button>
        </div>

    </div>

    {{-- RIGHT: Details + Password --}}
    <div class="col-lg-8 col-xl-9">

        {{-- Personal Details --}}
        <x-admin.form-card :title="'Personal Details'" icon="bi bi-person-fill" class="mb-4">
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="name">Full Name <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                        <input type="text" id="name" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $vendor->name) }}"
                               placeholder="Full Name" required>
                    </div>
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="email">Email Address <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                        <input type="email" id="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $vendor->email) }}"
                               placeholder="Email Address" required>
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold" for="phone">Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                        <input type="text" id="phone" name="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $vendor->phone) }}"
                               placeholder="Phone Number">
                    </div>
                    @error('phone')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

            </div>
        </x-admin.form-card>

        {{-- Security & Password --}}
        <x-admin.form-card :title="'Security & Password'" icon="bi bi-shield-lock-fill" class="mb-4">
            <x-slot:headerActions>
                <small class="text-muted">Leave blank to keep current password</small>
            </x-slot:headerActions>

            <div class="row g-3">

                <div class="col-12">
                    <label class="form-label fw-semibold" for="current_password">Current Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                        <input type="password" id="current_password" name="current_password"
                               class="form-control @error('current_password') is-invalid @enderror"
                               placeholder="Current Password">
                    </div>
                    @error('current_password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="password">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="New Password">
                    </div>
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="password_confirmation">Confirm New Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-control @error('password_confirmation') is-invalid @enderror"
                               placeholder="Confirm New Password">
                    </div>
                    @error('password_confirmation')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

            </div>
        </x-admin.form-card>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm rounded-pill" id="saveProfileBtn">
                <span class="spinner-border spinner-border-sm d-none" id="profileLoader" role="status"></span>
                <i class="bi bi-check-circle-fill" id="saveIcon"></i>
                Save Changes
            </button>
        </div>

    </div>
</div>

</form>

<form id="delete-account-form" action="{{ route('vendor.profile.destroy') }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form   = document.getElementById('vendor-profile-form');
    const btn    = document.getElementById('saveProfileBtn');
    const loader = document.getElementById('profileLoader');
    const icon   = document.getElementById('saveIcon');

    form.addEventListener('submit', function () {
        btn.disabled = true;
        loader.classList.remove('d-none');
        icon.classList.add('d-none');
    });

    document.getElementById('profile_image').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = ev => { document.getElementById('profilePreview').src = ev.target.result; };
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endsection
