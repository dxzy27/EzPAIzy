@extends('layouts.app')

@section('content')

{{-- ── Top Navigation Bar ──────────────────────────────────────── --}}
<nav class="ez-topnav">
    <div class="ez-topnav-container">
        <div class="ez-topnav-brand">
            <img src="{{ asset('images/newlogo.png') }}?v={{ time() }}" alt="EzPAIzy Logo" class="brand-new-logo" style="height: 75px; width: auto; object-fit: contain;">
        </div>
        <div class="ez-topnav-actions">
            <a href="{{ route('login') }}" class="topnav-btn">Log in</a>
        </div>
    </div>
</nav>

{{-- ── Page Body ────────────────────────────────────────────────── --}}
<div class="page-wrap">
    <div class="auth-container">
        <!-- Left Side: Welcome Intro -->
        <div class="auth-intro-side">
            <h2 class="intro-title">Learn Smarter with <span class="accent-glow">Personalized Islamic Learning</span></h2>
            
            <p class="intro-text mb-4">
                Discover learning materials tailored to your learning style, generate AI-powered quizzes, and track your progress in one place.
            </p>
            
            {{-- VARK Learning Styles Badge Section --}}
            <div class="vark-badges d-flex flex-wrap gap-3 mb-4">
                <span class="vark-badge badge-visual">👁 Visual</span>
                <span class="vark-badge badge-auditory">🎧 Auditory</span>
                <span class="vark-badge badge-readwrite">📖 Read/Write</span>
                <span class="vark-badge badge-kinesthetic">🏃 Kinaesthetic</span>
            </div>

            {{-- Feature highlights --}}
            <div class="intro-features d-flex flex-column gap-2 mt-2">
                <div class="feature-item d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Personalized learning paths</span>
                </div>
                <div class="feature-item d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>AI Quiz Generation</span>
                </div>
                <div class="feature-item d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Track Your Progress</span>
                </div>
            </div>

            <div class="cta-text mt-4">
                Start your personalized learning journey today.
            </div>
        </div>
        
        <!-- Right Side: Form Card -->
        <div class="auth-form-side">
            <div class="auth-card">

                {{-- Badge --}}
                <div class="page-badge">
                    <i class="bi bi-person-plus-fill me-1"></i> Sign Up
                </div>

                {{-- MRSM Logo --}}
                <div class="text-center mb-3">
                    <img src="{{ asset('images/mrsm.png') }}" alt="MRSM Logo" class="auth-center-logo">
                </div>

                {{-- Heading --}}
                <h1 class="card-heading text-center">
                    Create an <span class="accent">Account</span>
                </h1>
                <p class="auth-subtitle text-center">Join us and start your learning journey</p>

                {{-- Form --}}
                <form method="POST" action="{{ route('register') }}" class="mt-4">
                    @csrf

                    {{-- Name --}}
                    <div class="custom-input-group">
                        <span class="input-icon"><i class="bi bi-person"></i></span>
                        <input id="name" type="text" name="name"
                               class="custom-input has-icon @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="Full Name"
                               required autocomplete="name" autofocus>
                        @error('name')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="custom-input-group mt-3">
                        <span class="input-icon"><i class="bi bi-envelope"></i></span>
                        <input id="email" type="email" name="email"
                               class="custom-input has-icon @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" placeholder="Email Address"
                               required autocomplete="email">
                        @error('email')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    {{-- Phone --}}
                    <div class="custom-input-group mt-3">
                        <span class="input-icon"><i class="bi bi-phone"></i></span>
                        <input id="phone_number" type="text" name="phone_number"
                               class="custom-input has-icon @error('phone_number') is-invalid @enderror"
                               value="{{ old('phone_number') }}" placeholder="Phone Number" required>
                        @error('phone_number')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    {{-- Address --}}
                    <div class="custom-input-group mt-3">
                        <span class="input-icon"><i class="bi bi-house"></i></span>
                        <input id="address" type="text" name="address"
                               class="custom-input has-icon @error('address') is-invalid @enderror"
                               value="{{ old('address') }}" placeholder="Home Address" required>
                        @error('address')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    {{-- Role & Class --}}
                    <div class="row gx-3 mt-3">
                        <div class="col-6">
                            <div class="custom-input-group">
                                <span class="input-icon"><i class="bi bi-person-badge"></i></span>
                                <select id="role" name="role"
                                        class="custom-input has-icon @error('role') is-invalid @enderror" required>
                                    <option value="" disabled {{ old('role') ? '' : 'selected' }}>Register As…</option>
                                    <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                                    <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                                </select>
                                @error('role')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="custom-input-group">
                                <span class="input-icon"><i class="bi bi-journal-bookmark"></i></span>
                                <select id="class_name" name="class_name"
                                        class="custom-input has-icon @error('class_name') is-invalid @enderror" required>
                                    <option value="" disabled {{ old('class_name') ? '' : 'selected' }}>Select Class…</option>
                                    @foreach($schoolClasses as $schoolClass)
                                        <option value="{{ $schoolClass->name }}" {{ old('class_name') == $schoolClass->name ? 'selected' : '' }}>{{ $schoolClass->name }}</option>
                                    @endforeach
                                </select>
                                @error('class_name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Password & Confirm --}}
                    <div class="row gx-3 mt-3">
                        <div class="col-6">
                            <div class="custom-input-group">
                                <span class="input-icon"><i class="bi bi-lock"></i></span>
                                <input id="password" type="password" name="password"
                                       class="custom-input has-icon @error('password') is-invalid @enderror"
                                       placeholder="Password" required autocomplete="new-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="custom-input-group">
                                <span class="input-icon"><i class="bi bi-lock-fill"></i></span>
                                <input id="password-confirm" type="password" name="password_confirmation"
                                       class="custom-input has-icon" placeholder="Confirm"
                                       required autocomplete="new-password">
                            </div>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn-submit w-100 mt-4">
                        <span>CREATE ACCOUNT</span>
                        <svg class="btn-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M1 8H15M15 8L8 1M15 8L8 15" stroke="currentColor" stroke-width="2"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div class="text-center mt-4">
                        <span class="text-muted small">Already have an account? </span>
                        <a href="{{ route('login') }}" class="accent-link" style="font-size:.85rem;">Sign in here</a>
                    </div>
                </form>
            </div>
        </div>
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

    /* REGISTER THEME → Soft Teal */
    :root {
        --accent:      #2dd4bf;
        --accent-dark: #14b8a6;
        --accent-soft: rgba(45,212,191,.15);
        --badge-bg:    #f0fdfa;
        --badge-color: #14b8a6;
        --btn-bg:      #14b8a6;
        --btn-hover:   #0d9488;
    }

    body {
        margin: 0;
        font-family: 'Outfit', sans-serif;
        background: linear-gradient(rgba(12, 65, 80, 0.70), rgba(12, 65, 80, 0.70)), url("{{ asset('images/login bg.png') }}") no-repeat center center fixed !important;
        background-size: cover !important;
    }
    nav.navbar { display: none !important; }

    .ez-topnav {
        position: fixed; top: 0; left: 0; right: 0;
        height: 90px; 
        background: rgba(255, 255, 255, 0.15) !important;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        display: flex; align-items: center; justify-content: center;
        z-index: 100;
        border-bottom: 1px solid rgba(255, 255, 255, 0.12) !important;
    }
    .ez-topnav-container {
        width: 100%;
        max-width: 1040px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
    }
    .ez-topnav-brand { display: flex; align-items: center; gap: 10px; }
    .brand-mascot    { height: 46px; width: auto; object-fit: contain; }
    .brand-wordmark  { height: 28px; width: auto; object-fit: contain; }
    .ez-topnav-actions { display: flex; align-items: center; }
    .topnav-btn {
        background: rgba(255, 255, 255, 0.6) !important; 
        color: #1e293b; font-size: .85rem; font-weight: 600;
        text-decoration: none; padding: 4px 16px; border-radius: 6px;
        border: 1px solid rgba(255, 255, 255, 0.5); transition: all .2s;
    }
    .topnav-btn:hover { background: rgba(255, 255, 255, 0.95) !important; color: #1e293b; }

    /* ── Page Layout ─────────────────────────────── */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .page-wrap {
        min-height: 100vh; padding-top: 90px;
        display: flex; align-items: center; justify-content: center;
        padding: 114px 20px 40px;
        animation: fadeIn 0.45s ease-out forwards;
    }

    /* ── Split Layout Container ─────────────────── */
    .auth-container {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        max-width: 1040px;
        padding: 0 20px;
        gap: 60px;
    }
    .auth-intro-side {
        flex: 1.1;
        color: #ffffff;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        transform: translateY(-40px);
    }
    .auth-form-side {
        flex: 0.9;
        display: flex;
        justify-content: center;
        width: 100%;
    }
    .intro-title {
        font-size: 2.75rem;
        font-weight: 800;
        line-height: 1.15;
        margin-bottom: 35px;
    }
    .intro-title .accent-glow {
        color: var(--accent);
    }
    .intro-text {
        font-size: 1.15rem;
        line-height: 1.75;
        opacity: 0.9;
    }
    
    .vark-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 14px;
        border-radius: 99px;
        font-size: 0.85rem;
        font-weight: 600;
        border: 1px solid rgba(255, 255, 255, 0.15);
        background: rgba(255, 255, 255, 0.05);
        color: rgba(255, 255, 255, 0.9);
        text-shadow: none;
        opacity: 0.8;
        transition: opacity 0.2s ease;
    }
    .vark-badge:hover {
        opacity: 1;
    }
    .vark-badge.badge-visual {
        background: rgba(6, 182, 212, 0.12);
        border-color: rgba(6, 182, 212, 0.3);
        color: #67e8f9;
    }
    .vark-badge.badge-auditory {
        background: rgba(249, 115, 22, 0.12);
        border-color: rgba(249, 115, 22, 0.3);
        color: #fdba74;
    }
    .vark-badge.badge-readwrite {
        background: rgba(234, 179, 8, 0.1);
        border-color: rgba(234, 179, 8, 0.25);
        color: #fef08a;
    }
    .vark-badge.badge-kinesthetic {
        background: rgba(236, 72, 153, 0.1);
        border-color: rgba(236, 72, 153, 0.25);
        color: #fbcfe8;
    }
    .cta-text {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--accent);
    }
    .feature-item {
        font-size: 1.05rem;
        font-weight: 600;
    }
    .feature-item i {
        color: var(--accent);
        font-size: 1.15rem;
    }
    
    @media (max-width: 991px) {
        .auth-container {
            flex-direction: column;
            gap: 30px;
            max-width: 520px;
        }
        .auth-intro-side {
            display: none;
        }
        .auth-form-side {
            flex: 1;
        }
    }

    /* ── Auth Card ───────────────────────────────── */
    .auth-card {
        background: #FAFBFC !important;
        width: 100%; max-width: 540px;
        padding: 36px 36px 32px;
        border-radius: 28px;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        border-top: 5px solid var(--accent) !important;   /* ← teal top stripe */
        box-shadow: 0 25px 70px rgba(0, 0, 0, 0.18), 0 0 60px rgba(45, 212, 191, 0.15) !important;
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
    .accent-link { color: var(--accent); font-weight: 600; text-decoration: none; }
    .accent-link:hover { text-decoration: underline; }

    /* ── Inputs ──────────────────────────────────── */
    .custom-input-group { position: relative; }

    .input-icon {
        position: absolute; left: 14px; top: 50%;
        transform: translateY(-50%);
        color: #94a3b8; font-size: .95rem; pointer-events: none; z-index: 1;
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

    /* ── Button ──────────────────────────────────── */
    .btn-submit {
        position: relative; background: var(--btn-bg); color: #fff;
        font-weight: 700; font-size: .95rem; font-family: 'Outfit', sans-serif;
        height: 54px; border-radius: 12px; border: none;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        letter-spacing: .5px; transition: background .2s, transform .15s;
    }
    .btn-submit span { margin: 0 auto; }
    .btn-arrow { position: absolute; right: 16px; }
    .btn-submit:hover { background: var(--btn-hover); transform: translateY(-1px); }
    .btn-submit:active { transform: translateY(0); }
</style>
@endpush
