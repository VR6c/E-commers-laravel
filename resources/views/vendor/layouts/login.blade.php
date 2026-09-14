<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Vendor Login') — {{ config('app.name', 'TVR') }}</title>
    <link rel="icon" type="image/png" href="{{ getSiteLogo() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    @if (!App::environment('testing'))
        @vite(['resources/sass/app.scss'])
    @endif

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        html {
            height: 100%;
        }

        body {
            min-height: 100vh;
            margin: 0; padding: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
            background: #f0f2f8;
            display: flex;
            flex-direction: column;
        }

        /* ── Centered card layout ── */
        .vl-wrapper {
            flex: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            background: #f0f2f8;
        }

        .vl-card {
            width: 100%;
            max-width: 440px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(15,23,42,.10), 0 2px 8px rgba(15,23,42,.06);
            overflow: hidden;
        }

        /* ── Card header band ── */
        .vl-card-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            padding: 32px 36px 28px;
            text-align: center;
            position: relative;
        }

        .vl-card-header::after {
            content: '';
            position: absolute;
            bottom: -1px; left: 0; right: 0;
            height: 20px;
            background: #fff;
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        }

        .vl-logo-wrap {
            width: 60px; height: 60px;
            background: rgba(255,255,255,.18);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 14px;
            border: 2px solid rgba(255,255,255,.25);
            backdrop-filter: blur(8px);
        }

        .vl-logo-wrap img {
            width: 36px; height: 36px;
            object-fit: contain;
        }

        .vl-card-header h1 {
            font-size: 1.35rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 6px;
            letter-spacing: -.025em;
        }

        .vl-card-header p {
            font-size: .82rem;
            color: rgba(255,255,255,.8);
            margin: 0;
        }

        /* ── Card body ── */
        .vl-card-body {
            padding: 28px 36px 36px;
        }

        /* ── Form elements ── */
        .vl-label {
            display: block;
            font-size: .74rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 5px;
            letter-spacing: .01em;
        }

        .vl-field { margin-bottom: 18px; }

        .vl-input-wrap {
            position: relative;
        }

        .vl-input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: .82rem;
            pointer-events: none;
        }

        .vl-input {
            width: 100%;
            background: #f7f8fc;
            border: 1.5px solid #e4e8f0;
            border-radius: 10px;
            color: #0f172a;
            font-size: .875rem;
            padding: 10px 12px 10px 36px;
            outline: none;
            font-family: inherit;
            transition: border-color .17s, box-shadow .17s, background .17s;
        }

        .vl-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,.14);
            background: #fff;
        }

        .vl-input.is-invalid {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239,68,68,.10);
        }

        .vl-toggle-pw {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none; border: none; padding: 4px;
            color: #94a3b8; cursor: pointer; font-size: .82rem;
            transition: color .15s;
        }
        .vl-toggle-pw:hover { color: #6366f1; }

        /* ── Alert ── */
        .vl-alert {
            display: flex; align-items: flex-start; gap: 8px;
            background: #fef2f2;
            border: 1px solid #fecdd3;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: .8rem;
            color: #dc2626;
            margin-bottom: 16px;
        }

        .vl-alert--success {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #15803d;
        }

        .vl-alert i { margin-top: 1px; flex-shrink: 0; }

        /* ── Remember row ── */
        .vl-bottom-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: .8rem;
        }

        .vl-check {
            display: flex; align-items: center; gap: 7px;
            color: #64748b;
            cursor: pointer;
        }

        .vl-check input[type="checkbox"] {
            width: 15px; height: 15px;
            accent-color: #6366f1;
            cursor: pointer;
        }

        /* ── Submit button ── */
        .vl-submit {
            width: 100%;
            padding: 11px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 4px 14px rgba(99,102,241,.35);
            transition: opacity .17s, transform .17s, box-shadow .17s;
            font-family: inherit;
        }

        .vl-submit:hover  { opacity: .92; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,.42); }
        .vl-submit:active { transform: translateY(0); }
        .vl-submit:disabled { opacity: .65; cursor: not-allowed; transform: none; }

        /* ── Footer note ── */
        .vl-card-footer-note {
            text-align: center;
            font-size: .75rem;
            color: #94a3b8;
            margin-top: 20px;
        }

        .vl-card-footer-note a {
            color: #6366f1;
            font-weight: 600;
            text-decoration: none;
        }

        .vl-card-footer-note a:hover { text-decoration: underline; }

        /* ── Bottom brand strip ── */
        .vl-brand-strip {
            text-align: center;
            padding: 16px;
            font-size: .72rem;
            color: #94a3b8;
        }
    </style>

    @yield('css')
</head>
<body>

<div class="vl-wrapper">
    <div style="width:100%;max-width:440px;">
        <div class="vl-card">
            <div class="vl-card-header">
                <div class="vl-logo-wrap">
                    <img src="{{ getSiteLogo() }}" alt="{{ config('app.name') }}">
                </div>
                <h1>@yield('form-title', 'Vendor Portal')</h1>
                <p>@yield('form-subtitle', 'Sign in to manage your store')</p>
            </div>

            <div class="vl-card-body">
                @yield('content')
            </div>
        </div>

        <div class="vl-brand-strip">
            {{ config('app.name', 'TVR') }} &copy; {{ date('Y') }} &mdash; Vendor Portal
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@yield('js')
</body>
</html>
