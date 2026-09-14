@extends('themes.xylo.layouts.auth')

@section('content')
<div class="auth-screen">
    <div class="auth-card auth-card--split">
        {{-- Branding panel --}}
        <div class="auth-card__panel">
            <div class="fs-1 fw-bold text-warning mb-4">*</div>
            <h2 class="fw-bold display-6 mb-3 lh-sm">
                {{ 'Welcome' }} <br> {{ 'to our Store' }}
            </h2>
            <p class="fs-6 opacity-75 mb-0">{{ 'Create an account and start shopping today.' }}</p>
            <p class="small opacity-50 mt-auto pt-4">{{ '© ' . date('Y') . ' All rights reserved.' }}</p>
        </div>

        {{-- Form --}}
        <div class="auth-card__form">
            <div class="auth-card__brand">
                <img src="{{ getSiteLogo() }}" alt="{{ config('app.name') }}">
            </div>
            <h1 class="auth-card__title">{{ 'Create an Account' }}</h1>
            <p class="auth-card__subtitle">{{ 'Fill in the form below to get started.' }}</p>

            <form method="POST" action="{{ route('customer.register') }}">
                @csrf
                <div class="mb-3">
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="form-control @error('name') is-invalid @enderror" placeholder="{{ 'Full Name' }}">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror" placeholder="{{ 'Email Address' }}">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <input type="password" name="password"
                        class="form-control @error('password') is-invalid @enderror" placeholder="{{ 'Password' }}">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-4">
                    <input type="password" name="password_confirmation"
                        class="form-control" placeholder="{{ 'Confirm Password' }}">
                </div>
                <button type="submit" class="btn btn-primary btn-lg">{{ 'Sign Up' }}</button>
            </form>

            <p class="auth-card__footer">
                {{ 'Already have an account?' }}
                <a href="{{ route('customer.login') }}" class="fw-semibold text-decoration-none">{{ 'Log in here' }}</a>
            </p>
        </div>
    </div>
</div>
@endsection
