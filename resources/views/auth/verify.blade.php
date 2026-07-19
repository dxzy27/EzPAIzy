@extends('layouts.app')

@section('content')

{{-- ── Top Navigation Bar ──────────────────────────────────────── --}}
<nav class="ez-topnav">
    <div class="ez-topnav-brand">
        <img src="{{ asset('images/logo.png') }}"    alt="Mascot"  class="brand-mascot">
        <img src="{{ asset('images/EzPAIzy.png') }}?v={{ time() }}" alt="EzPAIzy" class="brand-wordmark">
    </div>
</nav>

{{-- ── Page Body ────────────────────────────────────────────────── --}}
<div class="page-wrap">
    <div class="auth-card">

        {{-- Badge --}}
        <div class="page-badge">
            <i class="bi bi-envelope-fill me-1"></i> Verification Required
        </div>

        {{-- MRSM Logo --}}
        <div class="text-center mb-3">
            <img src="{{ asset('images/mrsm.png') }}" alt="MRSM Logo" class="auth-center-logo">
        </div>

        {{-- Heading --}}
        <h1 class="card-heading text-center">
            Verify Your <span class="accent">Email</span>
        </h1>
        <p class="auth-subtitle text-center mb-4">Please verify your email address to access the portal</p>

        {{-- Success Resent Alert --}}
        @if (session('resent'))
            <div class="reg-success-alert mb-4">
                <i class="bi bi-check-circle-fill me-2"></i>
                A fresh verification link has been sent to your email address.
            </div>
        @endif

        {{-- Info Box --}}
        <div class="info-container mb-4">
            <p class="text-muted text-center m-0" style="font-size: .93rem; line-height: 1.5;">
                Before proceeding, please check your email inbox for the verification link we sent you.
            </p>
        </div>

        {{-- Resend Form --}}
        <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
            @csrf
            
            <button type="submit" class="btn-submit w-100">
                <span>RESEND VERIFICATION EMAIL</span>
                <svg class="btn-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M1 8H15M15 8L8 1M15 8L8 15" stroke="currentColor" stroke-width="2"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
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
        background: url("{{ asset('images/signup bg.png') }}") no-repeat center center fixed !important;
        background-size: cover !important;
    }
    nav.navbar { display: none !important; }

    /* ── Top Nav ─────────────────────────────────── */
    .ez-topnav {
        position: fixed; top: 0; left: 0; right: 0;
        height: 64px; 
        background: rgba(255, 255, 255, 0.4) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 40px; z-index: 100;
        border-bottom: 1px solid rgba(255, 255, 255, 0.25) !important;
    }
    .ez-topnav-brand { display: flex; align-items: center; gap: 10px; }
    .brand-mascot    { height: 44px; width: auto; object-fit: contain; }
    .brand-wordmark  { height: 28px; width: auto; object-fit: contain; }
    .ez-topnav-actions { display: flex; align-items: center; }
    .topnav-btn {
        background: rgba(255, 255, 255, 0.6) !important; 
        color: #1e293b; font-size: .85rem; font-weight: 600;
        text-decoration: none; padding: 6px 18px; border-radius: 6px;
        border: 1px solid rgba(255, 255, 255, 0.5); transition: all .2s;
    }
    .topnav-btn:hover { background: rgba(255, 255, 255, 0.95) !important; color: #1e293b; }

    /* ── Page Layout ─────────────────────────────── */
    .page-wrap {
        min-height: 100vh; padding-top: 64px;
        display: flex; align-items: center; justify-content: center;
        padding-bottom: 40px;
    }

    /* ── Auth Card ───────────────────────────────── */
    .auth-card {
        position: relative;
        background: rgba(235, 244, 255, 0.78) !important;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        width: 100%; max-width: 480px;
        padding: 36px 36px 32px;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.45) !important;
        border-top: 5px solid var(--accent) !important;
        box-shadow: 0 15px 35px rgba(20, 184, 166, 0.08) !important;
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

    /* ── Info Box ────────────────────────────────── */
    .info-container {
        background: rgba(255, 255, 255, 0.5) !important;
        padding: 16px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.5) !important;
    }

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
