@extends('themes.xylo.layouts.master')

@section('content')
<section class="xsf-section">
    <div class="container">
        <div class="xsf-account-layout">
            <div class="xsf-account-layout__aside">
                @include('themes.xylo.customer._nav', ['active' => 'profile'])
            </div>

            <div class="xsf-account-layout__main">
                <div class="xsf-account-head">
                    <h1>{{ 'My Profile' }}</h1>
                </div>

                <div class="xsf-account-card">
                    <div class="xsf-account-card__body">
                        <form action="{{ route('customer.profile.update') }}" method="POST" enctype="multipart/form-data" id="customer-profile-form" class="xsf-profile-form">
                            @csrf
                            @method('PUT')

                            {{-- Avatar --}}
                            <div>
                                <div class="xsf-profile-identity">
                                    <div class="xsf-account-avatar">
                                        <img id="profilePreview"
                                            src="{{ $customer->profile_image ? (\Illuminate\Support\Str::startsWith($customer->profile_image, ['http://', 'https://']) ? $customer->profile_image : asset('storage/' . $customer->profile_image)) : 'https://ui-avatars.com/api/?name=' . urlencode($customer->name) . '&background=0D8ABC&color=fff&size=120' }}"
                                            alt="Profile photo preview"
                                            onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($customer->name) }}&background=0D8ABC&color=fff&size=120';">
                                        <label for="profile_image" class="xsf-account-avatar__btn" title="{{ 'Change Photo' }}">
                                            <i class="fa-solid fa-camera" aria-hidden="true"></i>
                                            <span class="visually-hidden">{{ 'Change Photo' }}</span>
                                        </label>
                                        <input type="file" id="profile_image" name="profile_image" accept="image/*" class="visually-hidden">
                                    </div>
                                    <div>
                                        <p class="xsf-profile-identity__name">{{ $customer->name }}</p>
                                        <p class="xsf-profile-identity__email">{{ $customer->email }}</p>
                                    </div>
                                </div>
                                @error('profile_image')<div class="text-danger small mt-2" role="alert">{{ $message }}</div>@enderror
                            </div>

                            {{-- Details --}}
                            <div>
                                <h2 class="xsf-account-section-title">{{ 'Personal Details' }}</h2>

                                <div class="mb-3">
                                    <label class="form-label" for="profile_name">{{ 'Full Name' }}</label>
                                    <input type="text" id="profile_name" name="name" value="{{ old('name', $customer->name) }}" class="form-control @error('name') is-invalid @enderror" autocomplete="name" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="profile_email">{{ 'Email Address' }}</label>
                                    <input type="email" id="profile_email" name="email" value="{{ old('email', $customer->email) }}" class="form-control @error('email') is-invalid @enderror" autocomplete="email" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="profile_phone">{{ 'Phone Number' }}</label>
                                    <input type="tel" id="profile_phone" name="phone" value="{{ old('phone', $customer->phone) }}" class="form-control @error('phone') is-invalid @enderror" autocomplete="tel">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="profile_address">{{ 'Address' }}</label>
                                    <textarea id="profile_address" name="address" rows="2" class="form-control @error('address') is-invalid @enderror" autocomplete="street-address">{{ old('address', $customer->address) }}</textarea>
                                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Password --}}
                            <div>
                                <h2 class="xsf-account-section-title">{{ 'Security & Password' }}</h2>
                                <div class="mb-3">
                                    <label class="form-label" for="profile_current_password">{{ 'Current Password' }}</label>
                                    <input type="password" id="profile_current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password">
                                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="profile_password">{{ 'New Password' }}</label>
                                    <input type="password" id="profile_password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="profile_password_confirmation">{{ 'Confirm New Password' }}</label>
                                    <input type="password" id="profile_password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password">
                                </div>
                            </div>

                            <div>
                                <button type="submit" class="btn btn-primary w-100 w-md-auto px-4">
                                    {{ 'Save Changes' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="xsf-danger-zone">
                    <h2 class="xsf-danger-zone__title"><i class="fa-solid fa-triangle-exclamation me-1" aria-hidden="true"></i>{{ 'Danger Zone' }}</h2>
                    <p class="xsf-danger-zone__text">{{ 'Once you delete your account, there is no going back.' }}</p>
                    <button type="button" class="btn btn-danger btn-sm"
                        onclick="if(confirm('{{ 'Are you sure you want to delete your account?' }}')) document.getElementById('delete-account-form').submit();">
                        <i class="fa-solid fa-trash-can me-1" aria-hidden="true"></i>{{ 'Delete Account' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<form id="delete-account-form" action="{{ route('customer.profile.destroy') }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('js')
@if (session('success'))
    <script>
        toastr.success("{{ session('success') }}", "{{ 'Profile Updated' }}", {
            closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: 5000
        });
    </script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('profile_image');
    const previewImg = document.getElementById('profilePreview');
    if (fileInput) {
        fileInput.addEventListener('change', e => {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = ev => previewImg.src = ev.target.result;
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
@endsection
