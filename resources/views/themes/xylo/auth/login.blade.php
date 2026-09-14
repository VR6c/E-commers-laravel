@extends('themes.xylo.layouts.auth')

@section('content')
<div class="auth-screen">
    <div class="auth-card auth-card--split">
        {{-- Branding panel --}}
        <div class="auth-card__panel">
            <div class="fs-1 fw-bold text-warning mb-4">*</div>
            <h2 class="fw-bold display-6 mb-3 lh-sm">
                {{ 'Welcome Back' }} <br> {{ 'to our Store' }}
            </h2>
            <p class="fs-6 opacity-75 mb-0">{{ 'Sign in to access your account.' }}</p>
            <p class="small opacity-50 mt-auto pt-4">{{ '© ' . date('Y') . ' All rights reserved.' }}</p>
        </div>

        {{-- Form --}}
        <div class="auth-card__form">
            <div class="auth-card__brand">
                <img src="{{ getSiteLogo() }}" alt="{{ config('app.name') }}">
            </div>
            <h1 class="auth-card__title">{{ 'Sign In' }}</h1>
            <p class="auth-card__subtitle">{{ 'Enter your credentials below.' }}</p>

            <form method="POST" action="{{ route('customer.login') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">{{ 'Email Address' }}</label>
                    <input type="text" name="email" id="email" value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror" placeholder="{{ 'Email Address' }}">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">{{ 'Password' }}</label>
                    <input type="password" name="password" id="password"
                        class="form-control @error('password') is-invalid @enderror" placeholder="{{ 'Password' }}">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="btn btn-primary btn-lg">{{ 'Sign In' }}</button>
            </form>

            <p class="auth-card__footer">
                {{ 'Don\'t have an account?' }}
                <a href="{{ route('customer.register') }}" class="fw-semibold text-decoration-none">{{ 'Sign Up' }}</a>
                <br>
                <a href="{{ route('customer.password.request') }}" class="text-decoration-none">{{ 'Forgot password?' }}</a>
            </p>
        </div>
    </div>
</div>
@endsection
