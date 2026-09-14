@extends('vendor.layouts.login')

@section('title', 'Sign In')
@section('form-title', 'Welcome Back')
@section('form-subtitle', 'Sign in to access your vendor dashboard')

@section('content')

    @if (session('success'))
        <div class="vl-alert vl-alert--success">
            <i class="fas fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('warning'))
        <div class="vl-alert" style="background:#fffbeb;border-color:#fde68a;color:#b45309;">
            <i class="fas fa-triangle-exclamation"></i>
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="vl-alert">
            <i class="fas fa-circle-exclamation"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="vl-alert">
            <i class="fas fa-circle-exclamation"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('vendor.login.submit') }}" id="vendor-login-form" autocomplete="off">
        @csrf

        {{-- Email --}}
        <div class="vl-field">
            <label class="vl-label" for="email">Email Address</label>
            <div class="vl-input-wrap">
                <i class="fas fa-envelope vl-input-icon"></i>
                <input type="email"
                       id="email"
                       name="email"
                       class="vl-input @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       placeholder="you@example.com"
                       required
                       autofocus>
            </div>
        </div>

        {{-- Password --}}
        <div class="vl-field">
            <label class="vl-label" for="password">Password</label>
            <div class="vl-input-wrap">
                <i class="fas fa-lock vl-input-icon"></i>
                <input type="password"
                       id="password"
                       name="password"
                       class="vl-input @error('password') is-invalid @enderror"
                       placeholder="Enter your password"
                       required>
                <button type="button"
                        class="vl-toggle-pw"
                        id="togglePw"
                        tabindex="-1"
                        aria-label="Toggle password visibility">
                    <i class="fas fa-eye" id="togglePwIcon"></i>
                </button>
            </div>
        </div>

        {{-- Remember me --}}
        <div class="vl-bottom-row">
            <label class="vl-check">
                <input type="checkbox" name="remember" id="rememberMe">
                Remember me
            </label>
        </div>

        {{-- Submit --}}
        <button type="submit" class="vl-submit" id="loginBtn">
            <span class="spinner-border spinner-border-sm d-none" id="loginLoader" role="status" aria-hidden="true"></span>
            <i class="fas fa-arrow-right-to-bracket" id="loginIcon"></i>
            Sign In
        </button>

    </form>

    <p class="vl-card-footer-note">
        Don't have an account?
        <a href="{{ route('vendor.register') }}">Create one here</a>
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

    document.getElementById('vendor-login-form').addEventListener('submit', function () {
        const btn = document.getElementById('loginBtn');
        btn.disabled = true;
        document.getElementById('loginLoader').classList.remove('d-none');
        document.getElementById('loginIcon').classList.add('d-none');
    });
</script>
@endsection
