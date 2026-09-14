@extends('admin.layouts.login')

@section('title', config('app.name', 'Admin') . ' — Sign In')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">

        {{-- Logo --}}
        <div class="auth-logo-wrap">
            <img src="{{ getSiteLogo() }}"
                 alt="{{ config('app.name') }}" class="auth-logo-img">
        </div>

        {{-- Heading --}}
        <h1 class="auth-heading">Welcome back</h1>
        <p class="auth-subheading">Sign in to <strong>{{ config('app.name', 'Admin Panel') }}</strong></p>

        {{-- Alerts --}}
        @error('email')
            <div class="auth-alert mb-3">
                <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
            </div>
        @enderror
        @error('password')
            <div class="auth-alert mb-3">
                <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
            </div>
        @enderror

        {{-- Form --}}
        <form method="POST" action="{{ route('login') }}" autocomplete="off" class="auth-form">
            @csrf

            {{-- Email --}}
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email"
                           class="form-control @error('email') is-invalid @enderror"
                           name="email"
                           id="email"
                           value="{{ old('email') }}"
                           placeholder="you@example.com"
                           required
                           autofocus>
                </div>
            </div>

            {{-- Password --}}
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password"
                           class="form-control @error('password') is-invalid @enderror"
                           name="password"
                           id="password"
                           placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                           required>
                    <button type="button" class="input-icon-right toggle-password" data-target="password" aria-label="Toggle password visibility">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            {{-- Remember me --}}
            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="rememberMe" name="remember">
                <label class="form-check-label" for="rememberMe">Remember me</label>
            </div>

            {{-- Submit --}}
            <button type="submit" class="auth-btn" id="login-submit">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Sign In
            </button>
        </form>

        <p class="auth-footer-note mt-3">
            <i class="bi bi-shield-lock me-1"></i>
            Your connection is encrypted and secure.
        </p>
    </div>
</div>
@endsection
