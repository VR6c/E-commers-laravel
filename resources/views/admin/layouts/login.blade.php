<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ getSiteLogo() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Admin') . ' — Sign In')</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Khmer:wght@100..900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    {{-- Core --}}
    @if (!App::environment('testing'))
        @vite(['resources/sass/app.scss'])
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* ── Login page tokens ─────────────────────────────────────── */
        :root {
            --auth-bg-1: #0b0f1e;
            --auth-bg-2: #11172a;
            --auth-bg-3: #1a1040;
            --auth-primary: #6366f1;
            --auth-accent:  #a855f7;
            --auth-card:    rgba(255,255,255,0.975);
            --auth-radius:  1.75rem;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(ellipse 80% 60% at 10% 10%, rgba(99,102,241,0.22) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 90% 90%, rgba(168,85,247,0.18) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 50% 50%, rgba(14,165,233,0.08) 0%, transparent 70%),
                linear-gradient(135deg, var(--auth-bg-1) 0%, var(--auth-bg-2) 50%, var(--auth-bg-3) 100%);
            position: relative;
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Animated floating orbs */
        body::before,
        body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            animation: orb-float 12s ease-in-out infinite alternate;
        }

        body::before {
            width: 520px; height: 520px;
            background: radial-gradient(circle, rgba(99,102,241,0.16) 0%, transparent 70%);
            top: -100px; left: -100px;
        }

        body::after {
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(168,85,247,0.14) 0%, transparent 70%);
            bottom: -80px; right: -80px;
            animation-delay: -6s;
            animation-direction: alternate-reverse;
        }

        @keyframes orb-float {
            0%   { transform: translate(0, 0) scale(1); }
            50%  { transform: translate(30px, 20px) scale(1.06); }
            100% { transform: translate(-10px, 30px) scale(0.96); }
        }

        /* Subtle grid overlay */
        .auth-grid {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.022) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.022) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }

        /* Auth card */
        .auth-wrap {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            padding: 1.25rem;
            animation: card-enter 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes card-enter {
            from { opacity: 0; transform: translateY(24px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0)  scale(1); }
        }

        .auth-card {
            background: var(--auth-card);
            border-radius: var(--auth-radius);
            padding: 2.75rem 2.5rem 2.25rem;
            box-shadow:
                0 0 0 1px rgba(255,255,255,0.10),
                0 32px 64px -16px rgba(0,0,0,0.55),
                0 16px 32px -8px rgba(0,0,0,0.30);
        }

        /* Brand / logo */
        .auth-logo-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.75rem;
        }

        .auth-logo-icon {
            width: 54px; height: 54px;
            border-radius: 16px;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #fff;
            box-shadow: 0 8px 24px rgba(99,102,241,0.50);
            flex-shrink: 0;
        }

        .auth-logo-img {
            max-height: 48px;
            max-width: 160px;
            object-fit: contain;
        }

        /* Headings */
        .auth-heading {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.6rem;
            font-weight: 800;
            color: #0f172a;
            text-align: center;
            letter-spacing: -0.03em;
            line-height: 1.2;
            margin-bottom: 0.35rem;
        }

        .auth-subheading {
            font-size: 0.875rem;
            color: #64748b;
            text-align: center;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        /* Divider */
        .auth-divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1.5rem 0;
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 500;

            &::before, &::after {
                content: '';
                flex: 1;
                height: 1px;
                background: #e2e8f0;
            }
        }

        /* Form */
        .auth-form .form-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.35rem;
        }

        .auth-form .form-control {
            height: 46px;
            border-radius: 0.75rem;
            border: 1.5px solid #e2e8f0;
            font-size: 0.9rem;
            color: #0f172a;
            background: #f8fafc;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
            padding-left: 2.75rem;
        }

        .auth-form .form-control:focus {
            border-color: #6366f1;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.16);
            outline: none;
        }

        .auth-form .form-control.is-invalid {
            border-color: #f43f5e;
            box-shadow: 0 0 0 3px rgba(244,63,94,0.12);
        }

        /* Input group icon */
        .input-icon-wrap {
            position: relative;
        }

        .input-icon-wrap .input-icon {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.88rem;
            pointer-events: none;
            transition: color 0.15s;
        }

        .input-icon-wrap:focus-within .input-icon {
            color: #6366f1;
        }

        /* Toggle password */
        .input-icon-right {
            position: absolute;
            right: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            cursor: pointer;
            font-size: 0.88rem;
            background: none;
            border: none;
            padding: 0;
            transition: color 0.15s;

            &:hover { color: #6366f1; }
        }

        /* Remember me */
        .form-check-input:checked {
            background-color: #6366f1;
            border-color: #6366f1;
        }

        .form-check-label {
            font-size: 0.83rem;
            color: #475569;
            font-weight: 500;
        }

        /* Submit button */
        .auth-btn {
            width: 100%;
            height: 48px;
            border: none;
            border-radius: 0.875rem;
            background: linear-gradient(135deg, #6366f1 0%, #7c3aed 100%);
            color: #fff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.925rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(99,102,241,0.45);
            transition: all 0.18s ease;
            position: relative;
            overflow: hidden;
        }

        .auth-btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.12) 0%, transparent 60%);
            pointer-events: none;
        }

        .auth-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(99,102,241,0.55);
        }

        .auth-btn:active {
            transform: translateY(0);
            box-shadow: 0 4px 12px rgba(99,102,241,0.35);
        }

        /* Alerts */
        .auth-alert {
            border-radius: 0.75rem;
            border: none;
            font-size: 0.83rem;
            padding: 0.65rem 1rem;
            background: #fff1f2;
            color: #be123c;
            border-left: 3px solid #f43f5e;
        }

        /* Footer note */
        .auth-footer-note {
            text-align: center;
            font-size: 0.77rem;
            color: #94a3b8;
            margin-top: 1.5rem;
        }

        /* Brand badge at bottom */
        .auth-brand-badge {
            position: fixed;
            bottom: 1.25rem;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 2rem;
            padding: 0.4rem 1rem;
            font-size: 0.72rem;
            color: rgba(255,255,255,0.35);
            white-space: nowrap;
            backdrop-filter: blur(8px);
        }
    </style>

    @yield('css')
</head>
<body>
<div class="auth-grid" aria-hidden="true"></div>

@yield('content')

<p class="auth-brand-badge">
    <i class="bi bi-shield-check me-1"></i>
    {{ config('app.name', 'Admin Panel') }} &mdash; Secure Access
</p>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

{{-- Password visibility toggle --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const target = document.getElementById(this.dataset.target);
            if (!target) return;
            const isText = target.type === 'text';
            target.type = isText ? 'password' : 'text';
            this.querySelector('i').className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
        });
    });
});
</script>

@yield('js')
</body>
</html>
