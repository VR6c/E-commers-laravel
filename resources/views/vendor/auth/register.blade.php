@extends('vendor.layouts.login')

@section('title', 'Create Vendor Account')
@section('form-title', 'Become a Vendor')
@section('form-subtitle', 'Apply for a vendor account — reviewed before approval')

@section('content')

    @if (session('success'))
        <div class="vl-alert vl-alert--success">
            <i class="fas fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="vl-alert">
            <i class="fas fa-circle-exclamation"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('vendor.register.submit') }}" id="vendor-register-form">
        @csrf

        {{-- Name --}}
        <div class="vl-field">
            <label class="vl-label" for="name">Full Name <span style="color:#ef4444;">*</span></label>
            <div class="vl-input-wrap">
                <i class="fas fa-user vl-input-icon"></i>
                <input type="text"
                       id="name"
                       name="name"
                       class="vl-input @error('name') is-invalid @enderror"
                       value="{{ old('name') }}"
                       placeholder="Your full name"
                       required
                       autofocus>
            </div>
        </div>

        {{-- Email --}}
        <div class="vl-field">
            <label class="vl-label" for="email">Email Address <span style="color:#ef4444;">*</span></label>
            <div class="vl-input-wrap">
                <i class="fas fa-envelope vl-input-icon"></i>
                <input type="email"
                       id="email"
                       name="email"
                       class="vl-input @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       placeholder="you@example.com"
                       required>
            </div>
        </div>

        {{-- Phone --}}
        <div class="vl-field">
            <label class="vl-label" for="phone">
                Phone Number
                <span style="font-weight:400;opacity:.6;">(optional)</span>
            </label>
            <div class="vl-input-wrap">
                <i class="fas fa-phone vl-input-icon"></i>
                <input type="text"
                       id="phone"
                       name="phone"
                       class="vl-input @error('phone') is-invalid @enderror"
                       value="{{ old('phone') }}"
                       placeholder="+855 xx xxx xxx">
            </div>
        </div>

        {{-- Password --}}
        <div class="vl-field">
            <label class="vl-label" for="password">Password <span style="color:#ef4444;">*</span></label>
            <div class="vl-input-wrap">
                <i class="fas fa-lock vl-input-icon"></i>
                <input type="password"
                       id="password"
                       name="password"
                       class="vl-input @error('password') is-invalid @enderror"
                       placeholder="At least 8 characters"
                       required>
                <button type="button" class="vl-toggle-pw" id="togglePw" tabindex="-1" aria-label="Toggle password">
                    <i class="fas fa-eye" id="togglePwIcon"></i>
                </button>
            </div>
        </div>

        {{-- Confirm Password --}}
        <div class="vl-field">
            <label class="vl-label" for="password_confirmation">Confirm Password <span style="color:#ef4444;">*</span></label>
            <div class="vl-input-wrap">
                <i class="fas fa-lock vl-input-icon"></i>
                <input type="password"
                       id="password_confirmation"
                       name="password_confirmation"
                       class="vl-input"
                       placeholder="Repeat your password"
                       required>
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit" class="vl-submit" id="registerBtn">
            <span class="spinner-border spinner-border-sm d-none" id="registerLoader" role="status" aria-hidden="true"></span>
            <i class="fas fa-user-plus" id="registerIcon"></i>
            Create Vendor Account
        </button>

    </form>

    <p class="vl-card-footer-note">
        Already have an account?
        <a href="{{ route('vendor.login') }}">Sign in here</a>
    </p>

@endsection

@section('js')
<script>
    document.getElementById('togglePw').addEventListener('click', function () {
        const pw   = document.getElementById('password');
        const icon = document.getElementById('togglePwIcon');
        if (pw.type === 'password') {
            pw.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            pw.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });

    document.getElementById('vendor-register-form').addEventListener('submit', function () {
        const btn = document.getElementById('registerBtn');
        btn.disabled = true;
        document.getElementById('registerLoader').classList.remove('d-none');
        document.getElementById('registerIcon').classList.add('d-none');
    });
</script>
@endsection
