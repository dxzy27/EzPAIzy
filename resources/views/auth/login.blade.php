@extends('layouts.app')

@section('content')

{{-- ── Top Navigation Bar ──────────────────────────────────────── --}}
<nav class="ez-topnav">
    <div class="ez-topnav-brand">
        <img src="{{ asset('images/logo.png') }}"    alt="Mascot"  class="brand-mascot">
        <img src="{{ asset('images/EzPAIzy.png') }}" alt="EzPAIzy" class="brand-wordmark">
    </div>
    <div class="ez-topnav-actions">
        <a href="{{ route('register') }}" class="topnav-btn">Sign up</a>
    </div>
</nav>

{{-- ── Page Body ────────────────────────────────────────────────── --}}
<div class="page-wrap">
    <div class="auth-card">

        {{-- Badge --}}
        <div class="page-badge">
            <i class="bi bi-shield-lock-fill me-1"></i> Sign In
        </div>

        {{-- MRSM Logo --}}
        <div class="text-center mb-3">
            <img src="{{ asset('images/mrsm.png') }}" alt="MRSM Logo" class="auth-center-logo">
        </div>

        {{-- Heading --}}
        <h1 class="card-heading text-center">
            Welcome <span class="accent">back</span>
        </h1>
        <p class="auth-subtitle text-center">
            Don't have an account?&nbsp;
            <a href="{{ route('register') }}" class="accent-link">Sign up</a>
        </p>

        {{-- Form --}}
        <form method="POST" action="{{ route('login') }}" class="mt-4">
            @csrf

            {{-- Registration success / pending approval message --}}
            @if (session('status'))
                <div class="reg-success-alert mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('status') }}
                </div>
            @endif

            {{-- Email --}}
            <div class="custom-input-group">
                <span class="input-icon"><i class="bi bi-envelope"></i></span>
                <input id="email" type="email" name="email"
                       class="custom-input has-icon @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="Email Address"
                       required autocomplete="email" autofocus>
                @error('email')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            {{-- Password --}}
            <div class="custom-input-group mt-3">
                <span class="input-icon"><i class="bi bi-lock"></i></span>
                <div class="password-wrapper">
                    <input id="password" type="password" name="password"
                           class="custom-input has-icon @error('password') is-invalid @enderror"
                           placeholder="Password"
                           required autocomplete="current-password">
                    <i class="bi bi-eye hide-password-icon" id="passIcon"></i>
                </div>
                @error('password')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            {{-- Role --}}
            <div class="custom-input-group mt-3">
                <span class="input-icon"><i class="bi bi-person-badge"></i></span>
                <select id="role" name="role"
                        class="custom-input has-icon @error('role') is-invalid @enderror" required>
                    <option value="" disabled {{ old('role') ? '' : 'selected' }}>Login As…</option>
                    <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                    <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                </select>
                @error('role')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            {{-- Remember / Forgot --}}
            <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                <label class="d-flex align-items-center gap-2 small text-muted" style="cursor:pointer;">
                    <input class="form-check-input m-0" type="checkbox" name="remember" id="remember"
                           {{ old('remember') ? 'checked' : '' }}>
                    Keep me signed in
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="accent-link" style="font-size:.82rem;">Forgot password?</a>
                @endif
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-submit w-100 mt-3">
                <span>LOG IN</span>
                <svg class="btn-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M1 8H15M15 8L8 1M15 8L8 15" stroke="currentColor" stroke-width="2"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

            <div class="text-center mt-4">
                <span class="text-muted small">New here? </span>
                <a href="{{ route('register') }}" class="accent-link" style="font-size:.85rem;">Create an account</a>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    *, *::before, *::after { box-sizing: border-box; }

    /* LOGIN THEME → Soft Blue */
    :root {
        --accent:      #60a5fa;
        --accent-dark: #3b82f6;
        --accent-soft: rgba(96,165,250,.15);
        --badge-bg:    #eff6ff;
        --badge-color: #3b82f6;
        --btn-bg:      #3b82f6;
        --btn-hover:   #2563eb;
    }

    body {
        margin: 0;
        font-family: 'Outfit', sans-serif;
        /* Very soft blue — light and airy */
        background: linear-gradient(135deg, #f0f7ff 0%, #e0edff 50%, #ede9fe 100%) !important;
    }
    nav.navbar { display: none !important; }

    /* ── Top Nav ─────────────────────────────────── */
    .ez-topnav {
        position: fixed; top: 0; left: 0; right: 0;
        height: 64px; background: #fff;
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 40px; z-index: 100;
        box-shadow: 0 1px 8px rgba(0,0,0,.06);
        border-bottom: 1px solid #e2e8f0;
    }
    .ez-topnav-brand { display: flex; align-items: center; gap: 10px; }
    .brand-mascot    { height: 44px; width: auto; object-fit: contain; }
    .brand-wordmark  { height: 28px; width: auto; object-fit: contain; }
    .ez-topnav-actions { display: flex; align-items: center; }
    .topnav-btn {
        background: #fff; color: #334155; font-size: .85rem; font-weight: 600;
        text-decoration: none; padding: 6px 18px; border-radius: 6px;
        border: 1px solid #cbd5e1; transition: all .2s;
    }
    .topnav-btn:hover { background: #f8fafc; border-color: #94a3b8; color: #1e293b; }

    /* ── Page Layout ─────────────────────────────── */
    .page-wrap {
        min-height: 100vh; padding-top: 64px;
        display: flex; align-items: center; justify-content: center;
        padding-bottom: 40px;
    }

    /* ── Auth Card ───────────────────────────────── */
    .auth-card {
        position: relative;
        background: #fff; width: 100%; max-width: 480px;
        padding: 36px 36px 32px;
        border-radius: 20px;
        border-top: 5px solid var(--accent);   /* ← blue top stripe */
        box-shadow: 0 16px 48px rgba(96,165,250,.15);
    }

    /* ── Badge ───────────────────────────────────── */
    .page-badge {
        display: inline-flex; align-items: center;
        background: var(--badge-bg); color: var(--badge-color);
        font-size: .78rem; font-weight: 700; letter-spacing: .5px;
        padding: 4px 12px; border-radius: 20px;
        margin-bottom: 16px; text-transform: uppercase;
    }

    /* ── Logo ────────────────────────────────────── */
    .auth-center-logo { height: 88px; width: auto; object-fit: contain; display: block; margin: 0 auto; }

    /* ── Heading ─────────────────────────────────── */
    .card-heading {
        font-size: 1.9rem; font-weight: 800; color: #1e293b;
        margin: 12px 0 6px; line-height: 1.2; letter-spacing: -.5px;
    }
    .accent { color: var(--accent); }

    .auth-subtitle { color: #64748b; font-size: .9rem; margin: 0; }
    .accent-link {
        color: var(--accent); font-weight: 600; text-decoration: none;
    }
    .accent-link:hover { text-decoration: underline; }

    /* ── Inputs ──────────────────────────────────── */
    .custom-input-group { position: relative; }

    .input-icon {
        position: absolute; left: 14px; top: 50%;
        transform: translateY(-50%);
        color: #94a3b8; font-size: .95rem; pointer-events: none;
        z-index: 1;
    }

    .custom-input {
        width: 100%; padding: 12px 16px;
        border: 1.5px solid #e2e8f0; border-radius: 10px;
        font-size: .93rem; font-family: 'Outfit', sans-serif;
        color: #334155; background: #f8fafc;
        outline: none; transition: border-color .2s, box-shadow .2s, background .2s;
        appearance: none;
    }
    .custom-input.has-icon { padding-left: 40px; }
    .custom-input::placeholder { color: #94a3b8; }
    .custom-input:focus {
        border-color: var(--accent);
        background: #fff;
        box-shadow: 0 0 0 3px var(--accent-soft);
    }
    .custom-input.is-invalid { border-color: #f87171; background: #fff8f8; }

    .invalid-feedback { font-size: .8rem; color: #ef4444; margin-top: 4px; padding-left: 4px; display: block; }

    .password-wrapper { position: relative; }
    .password-wrapper .custom-input { padding-right: 42px; }
    .hide-password-icon {
        position: absolute; right: 14px; top: 50%;
        transform: translateY(-50%);
        color: #94a3b8; cursor: pointer; font-size: 1rem;
    }
    .hide-password-icon:hover { color: var(--accent); }

    /* ── Button ──────────────────────────────────── */
    .btn-submit {
        position: relative; background: var(--btn-bg); color: #fff;
        font-weight: 700; font-size: .95rem; font-family: 'Outfit', sans-serif;
        padding: 13px 20px; border-radius: 10px; border: none;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        letter-spacing: .5px; transition: background .2s, transform .15s;
    }
    .btn-submit span { margin: 0 auto; }
    .btn-arrow { position: absolute; right: 16px; }
    .btn-submit:hover { background: var(--btn-hover); transform: translateY(-1px); }
    .btn-submit:active { transform: translateY(0); }

    /* ── Registration success alert ──────────────── */
    .reg-success-alert {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        background: #f0fdf4;
        border: 1.5px solid #86efac;
        color: #166534;
        border-radius: 10px;
        padding: 12px 16px;
        font-size: .88rem;
        font-weight: 500;
        line-height: 1.45;
    }
    .reg-success-alert i { color: #22c55e; font-size: 1rem; flex-shrink: 0; margin-top: 1px; }

</style>
@endpush

@push('scripts')
<script>
    const icon = document.getElementById('passIcon');
    const inp  = document.getElementById('password');
    if (icon) {
        icon.addEventListener('click', () => {
            const show = inp.type === 'password';
            inp.type = show ? 'text' : 'password';
            icon.className = show ? 'bi bi-eye-slash hide-password-icon' : 'bi bi-eye hide-password-icon';
        });
    }
</script>
@endpush
