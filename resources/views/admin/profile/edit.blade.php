@extends('admin.layouts.admin')

@section('title', 'My Profile — Admin')

@section('content')

<x-admin.page-header
    :title="'My Profile'"
    :subtitle="'Manage your personal details and security credentials'"
    :breadcrumbs="['Profile' => '']">
</x-admin.page-header>

<form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" autocomplete="off" id="admin-profile-form">
    @csrf
    @method('PATCH')

    <div class="row g-4">
        <!-- Left Column: Avatar & Account Meta -->
        <div class="col-lg-4">
            <x-admin.form-card :title="'Profile Photo'" :icon="'bi bi-person-bounding-box'" class="text-center mb-4">
                <div class="position-relative d-inline-block my-3">
                    <img id="profilePreview"
                         src="{{ $admin->profile_image
                             ? (\Illuminate\Support\Str::startsWith($admin->profile_image, ['http://', 'https://'])
                                 ? $admin->profile_image
                                 : asset('storage/' . $admin->profile_image))
                             : 'https://ui-avatars.com/api/?name=' . urlencode($admin->name) . '&background=4f46e5&color=fff&size=140' }}"
                         alt="Profile"
                         class="rounded-circle shadow-sm"
                         style="width: 120px; height: 120px; object-fit: cover; border: 3px solid var(--border-color);">
                    
                    <label for="profile_image"
                           class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 shadow-sm d-flex align-items-center justify-content-center"
                           style="width: 36px; height: 36px; cursor: pointer;"
                           title="{{ 'Change Photo' }}">
                        <i class="bi bi-camera-fill"></i>
                    </label>
                    <input type="file" id="profile_image" name="profile_image" accept="image/*" class="d-none">
                </div>

                @error('profile_image')
                    <div class="text-danger small mb-2">{{ $message }}</div>
                @enderror

                <h5 class="fw-bold text-dark mb-1">{{ $admin->name }}</h5>
                <p class="text-muted small mb-2">{{ $admin->email }}</p>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1-5" style="font-size: 0.75rem; font-weight: 600;">
                    Super Administrator
                </span>
            </x-admin.form-card>

            <!-- Danger Zone Card -->
            <x-admin.form-card :title="'Danger Zone'" :icon="'bi bi-exclamation-triangle-fill'" class="border-danger-subtle">
                <p class="text-muted small mb-3" style="line-height: 1.45;">
                    Deleting your account permanently removes all personal administrative access.
                </p>
                <button type="button"
                        class="btn btn-outline-danger btn-sm w-100 py-2"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteAccountModal">
                    <i class="bi bi-trash-fill me-1"></i> {{ 'Delete Account' }}
                </button>
            </x-admin.form-card>
        </div>

        <!-- Right Column: Personal Details & Password -->
        <div class="col-lg-8">
            <x-admin.form-card :title="'Personal Information'" :icon="'bi bi-person-lines-fill'" class="mb-4">
                <!-- Name -->
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold text-dark">{{ 'Full Name' }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name', $admin->name) }}"
                               placeholder="Your full name"
                               required>
                    </div>
                    @error('name')
                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold text-dark">{{ 'Email Address' }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               id="email"
                               name="email"
                               value="{{ old('email', $admin->email) }}"
                               placeholder="your.email@store.com"
                               required>
                    </div>
                    @error('email')
                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Phone -->
                <div class="mb-0">
                    <label for="phone" class="form-label fw-semibold text-dark">{{ 'Phone Number' }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                        <input type="text"
                               class="form-control @error('phone') is-invalid @enderror"
                               id="phone"
                               name="phone"
                               value="{{ old('phone', $admin->phone) }}"
                               placeholder="+855 12 345 678">
                    </div>
                    @error('phone')
                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </x-admin.form-card>

            <!-- Password Card -->
            <x-admin.form-card :title="'Change Password'" :icon="'bi bi-shield-lock'" :subtitle="'Leave blank to keep your current password'" class="mb-4">
                <div class="mb-3">
                    <label for="current_password" class="form-label fw-semibold text-dark">{{ 'Current Password' }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password"
                               class="form-control @error('current_password') is-invalid @enderror"
                               id="current_password"
                               name="current_password"
                               placeholder="Enter existing password to verify">
                    </div>
                    @error('current_password')
                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label fw-semibold text-dark">{{ 'New Password' }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password"
                                   name="password"
                                   placeholder="Minimum 8 characters">
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label fw-semibold text-dark">{{ 'Confirm New Password' }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                            <input type="password"
                                   class="form-control"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   placeholder="Repeat new password">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-2">
                    <button type="submit" class="btn btn-primary px-4 py-2-5 shadow-sm" id="saveProfileBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="profileLoader" role="status"></span>
                        <i class="bi bi-check-circle-fill me-1"></i>
                        {{ 'Save Profile Changes' }}
                    </button>
                </div>
            </x-admin.form-card>
        </div>
    </div>
</form>

<form id="delete-account-form" action="{{ route('admin.profile.destroy') }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

<x-admin.delete-modal
    id="deleteAccountModal"
    confirm-id="confirmDeleteAccountBtn"
    :title="'Delete Admin Account?'"
    :message="'Are you absolutely sure you want to permanently delete your administrator account? This cannot be undone.'"
    :confirm-label="'Delete My Account'" />

@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('admin-profile-form');
    const btn = document.getElementById('saveProfileBtn');
    const loader = document.getElementById('profileLoader');
    if (form && btn && loader) {
        form.addEventListener('submit', function() {
            btn.setAttribute('disabled', 'disabled');
            loader.classList.remove('d-none');
        });
    }

    const fileInput = document.getElementById('profile_image');
    const previewImg = document.getElementById('profilePreview');
    if (fileInput && previewImg) {
        fileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) { previewImg.src = e.target.result; };
                reader.readAsDataURL(file);
            }
        });
    }

    const confirmDeleteBtn = document.getElementById('confirmDeleteAccountBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            document.getElementById('delete-account-form').submit();
        });
    }
});
</script>
@endsection
