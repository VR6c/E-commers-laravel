@extends('themes.xylo.layouts.auth')

@section('content')
<main class="xsf-auth">
    <aside class="xsf-auth__panel" aria-hidden="true">
        <p class="xsf-auth__panel-title">Welcome to our store</p>
        <p class="xsf-auth__panel-text">{{ 'Create an account and start shopping today.' }}</p>
        <p class="xsf-auth__panel-copy">{{ '© ' . date('Y') . ' All rights reserved.' }}</p>
    </aside>

    <div class="xsf-auth__main">
        <div class="xsf-auth__card">
            <a href="{{ route('xylo.home') }}" class="xsf-auth__brand" aria-label="{{ config('app.name') }}">
                <img src="{{ getSiteLogo() }}" alt="{{ config('app.name') }}">
            </a>
            <h1 class="xsf-auth__title">{{ 'Create an Account' }}</h1>
            <p class="xsf-auth__subtitle">{{ 'Fill in the form below to get started.' }}</p>

            <form method="POST" action="{{ route('customer.register') }}" class="xsf-auth__form">
                @csrf
                <div class="xsf-auth__field">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" autocomplete="name"  @error('name') aria-describedby="name-error" aria-invalid="true" @enderror>
                    @error('name')<p class="xsf-auth__error" id="name-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="xsf-auth__field">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" autocomplete="email"  @error('email') aria-describedby="email-error" aria-invalid="true" @enderror>
                    @error('email')<p class="xsf-auth__error" id="email-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="xsf-auth__field">
                    <label for="password" class="form-label">Password</label>
                    <div class="xsf-auth__pw">
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password"  @error('password') aria-describedby="password-error" aria-invalid="true" @enderror>
                        <button type="button" class="xsf-auth__pw-toggle" data-password-toggle aria-controls="password" aria-label="Show password"><i class="fa-solid fa-eye" aria-hidden="true"></i></button>
                    </div>
                    @error('password')<p class="xsf-auth__error" id="password-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="xsf-auth__field">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <div class="xsf-auth__pw">
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" autocomplete="new-password" >
                        <button type="button" class="xsf-auth__pw-toggle" data-password-toggle aria-controls="password_confirmation" aria-label="Show password"><i class="fa-solid fa-eye" aria-hidden="true"></i></button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary xsf-auth__submit">{{ 'Sign Up' }}</button>
            </form>
            <div class="xsf-auth__links">
                <p>{{ 'Already have an account?' }} <a href="{{ route('customer.login') }}" class="fw-semibold">{{ 'Log in here' }}</a></p>
            </div>
        </div>
    </div>
</main>
@endsection

@section('js')
<script>
    document.querySelectorAll('[data-password-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.getAttribute('aria-controls'));
            if (!input) return;
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            btn.querySelector('i').className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
        });
    });
</script>
@endsection
