<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'EzPAIzy'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    @vite(['resources/sass/app.scss', 'resources/js/app.js', 'resources/js/student-dashboard.js'])
    @stack('styles')

    <style>
        @php
            $role = auth()->user()?->role;
            $isTeacher = $role === 'teacher';
            $isAdmin = $role === 'admin';
            $isStudent = $role === 'student';
            
            $style = $isStudent ? auth()->user()?->learning_style : null;
            
            if ($isAdmin) {
                $sidebarBg = '#1e1e2d';
                $sidebarHover = '#2c2c3e';
                $sidebarActive = '#8b5cf6';
                $sidebarActiveBg = 'rgba(139,92,246,.18)';
                $accent = '#8b5cf6';
                $accentSoft = '#f5f3ff';
                $pageBg = '#f8fafc';
            } elseif ($isTeacher) {
                $sidebarBg = '#1e3a5f';
                $sidebarHover = '#2a4f7c';
                $sidebarActive = '#60a5fa';
                $sidebarActiveBg = 'rgba(96,165,250,.18)';
                $accent = '#3b82f6';
                $accentSoft = '#eff6ff';
                $pageBg = '#f0f7ff';
            } else {
                // Students (both Basic UI and diagnosed styles)
                if ($style === 'auditory') {
                    $sidebarBg = '#3c1704'; // dark brown-orange
                    $sidebarHover = '#5a250a';
                    $sidebarActive = '#e5b181';
                    $sidebarActiveBg = 'rgba(229,177,129,.18)';
                    $accent = '#e5b181';
                    $accentSoft = '#fff7ed';
                    $pageBg = '#fffbf7';
                } elseif ($style === 'competitive') {
                    $sidebarBg = '#450a0a'; // dark red
                    $sidebarHover = '#630c14';
                    $sidebarActive = '#EF9086';
                    $sidebarActiveBg = 'rgba(239,144,134,.18)';
                    $accent = '#EF9086';
                    $accentSoft = '#fef2f2';
                    $pageBg = '#fffafb';
                } elseif ($style === 'read_write') {
                    $sidebarBg = '#383023'; // dark warm gray/tan
                    $sidebarHover = '#4c4130';
                    $sidebarActive = '#7d6867';
                    $sidebarActiveBg = 'rgba(125,104,103,.18)';
                    $accent = '#7d6867';
                    $accentSoft = '#faf7f2';
                    $pageBg = '#fcfbfa';
                } else {
                    // Basic UI (default green/teal)
                    $sidebarBg = '#134e4a';
                    $sidebarHover = '#1a6460';
                    $sidebarActive = '#2dd4bf';
                    $sidebarActiveBg = 'rgba(45,212,191,.18)';
                    $accent = '#14b8a6';
                    $accentSoft = '#f0fdfa';
                    $pageBg = '#f0fdfb';
                }
            }
        @endphp

        :root {
            --sidebar-bg:        {{ $sidebarBg }};
            --sidebar-hover:     {{ $sidebarHover }};
            --sidebar-active:    {{ $sidebarActive }};
            --sidebar-active-bg: {{ $sidebarActiveBg }};
            --accent:            {{ $accent }};
            --accent-soft:       {{ $accentSoft }};
            --page-bg:           {{ $pageBg }};
            --sidebar-w:         220px;
            --sidebar-collapsed: 68px;
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--page-bg);
            color: #1e293b;
            margin: 0;
        }

        /* ── Layout ──────────────────────────────────── */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── Sidebar ─────────────────────────────────── */
        .Sidebar {
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            z-index: 1050;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: width 0.3s cubic-bezier(0.4,0,0.2,1),
                        transform 0.3s cubic-bezier(0.4,0,0.2,1);
            box-shadow: 4px 0 20px rgba(0,0,0,.14);
        }

        /* Collapsed state */
        .Sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }

        /* Mobile: hidden off-screen */
        @media (max-width: 991px) {
            .Sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-w) !important;
            }
            .Sidebar.mobile-open {
                transform: translateX(0);
            }
        }

        /* ── Sidebar Brand ───────────────────────────── */
        .sidebar-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px 14px 14px;
            border-bottom: 1px solid rgba(255,255,255,.1);
            text-decoration: none;
            flex-shrink: 0;
            position: relative;
            min-height: 68px;
        }

        .sidebar-brand-text {
            font-size: 1.3rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -.3px;
            text-align: center;
        }
        .sidebar-brand-text .brand-accent { color: var(--sidebar-active); }

        .Sidebar.collapsed .sidebar-brand-text {
            display: none;
        }

        /* Mascot toggle button */
        .sidebar-toggle-mascot {
            cursor: pointer;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            border: none;
            background: none;
            padding: 0;
            border-radius: 50%;
            transition: transform 0.3s;
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
        }
        .sidebar-toggle-mascot img {
            height: 42px;
            width: auto;
            object-fit: contain;
            transition: transform 0.4s cubic-bezier(0.34,1.56,0.64,1);
            filter: drop-shadow(0 2px 4px rgba(0,0,0,.3));
        }
        .sidebar-toggle-mascot:hover img {
            transform: scale(1.12) rotate(-5deg);
        }
        /* When collapsed, mascot still visible and centered */
        .Sidebar.collapsed .sidebar-brand {
            justify-content: center;
            padding: 16px 0 14px;
        }
        .Sidebar.collapsed .sidebar-toggle-mascot {
            position: static;
            transform: none;
        }
        .Sidebar.collapsed .sidebar-toggle-mascot img {
            transform: scaleX(-1); /* Flip to indicate collapsed state */
        }
        .Sidebar.collapsed .sidebar-toggle-mascot:hover img {
            transform: scaleX(-1) scale(1.12);
        }

        /* ── Nav Links ───────────────────────────────── */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 10px 10px;
        }
        .sidebar-nav::-webkit-scrollbar { width: 3px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 2px; }

        .nav-section-label {
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,.35);
            padding: 12px 12px 4px;
            white-space: nowrap;
            overflow: hidden;
            transition: opacity .2s;
        }
        .Sidebar.collapsed .nav-section-label { opacity: 0; }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 10px;
            color: rgba(255,255,255,.72) !important;
            font-size: .875rem;
            font-weight: 500;
            text-decoration: none;
            transition: background .18s, color .18s;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            position: relative;
        }
        .nav-link .nav-icon {
            font-size: 1.05rem;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }
        .nav-link .nav-label {
            transition: opacity .2s;
            overflow: hidden;
        }
        .Sidebar.collapsed .nav-link { justify-content: center; padding: 10px 0; }
        .Sidebar.collapsed .nav-label { display: none; }
        .Sidebar.collapsed .nav-chevron { display: none; }

        .nav-link:hover:not(.active) {
            background: var(--sidebar-hover);
            color: #fff !important;
        }
        .nav-link.active {
            background: var(--sidebar-active-bg) !important;
            color: var(--sidebar-active) !important;
            font-weight: 600;
        }
        .nav-link.active .nav-icon { color: var(--sidebar-active); }

        /* Tooltip on collapsed */
        .Sidebar.collapsed .nav-link::after {
            content: attr(data-tooltip);
            position: absolute;
            left: calc(var(--sidebar-collapsed) - 4px);
            background: #1e293b;
            color: #fff;
            font-size: .78rem;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 7px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity .15s;
            z-index: 2000;
        }
        .Sidebar.collapsed .nav-link:hover::after { opacity: 1; }

        .submenu-list {
            list-style: none;
            padding: 2px 0 2px 22px;
            margin: 0;
            position: relative;
        }
        /* Vertical connector line */
        .submenu-list::before {
            content: "";
            position: absolute;
            left: 11px;
            top: -10px;
            bottom: 16px;
            width: 1.5px;
            background: rgba(255, 255, 255, 0.15);
        }
        /* Horizontal branch line */
        .submenu-list li {
            position: relative;
        }
        .submenu-list li::before {
            content: "";
            position: absolute;
            left: -11px;
            top: 17px;
            width: 11px;
            height: 1.5px;
            background: rgba(255, 255, 255, 0.15);
        }
        .submenu-list .nav-link {
            font-size: .85rem;
            padding: 7px 12px;
        }
        .Sidebar.collapsed .submenu-list { display: none; }

        /* Chevron */
        .nav-chevron {
            margin-left: auto;
            font-size: .72rem;
            opacity: .5;
            transition: transform .25s;
            flex-shrink: 0;
        }
        .nav-link[aria-expanded="true"] .nav-chevron { transform: rotate(180deg); }

        /* ── Sidebar Footer ──────────────────────────── */
        .sidebar-footer {
            padding: 12px 14px;
            border-top: 1px solid rgba(255,255,255,.08);
            flex-shrink: 0;
            overflow: hidden;
        }
        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            overflow: hidden;
        }
        .sidebar-avatar {
            width: 34px; height: 34px;
            border-radius: 9px;
            background: var(--sidebar-active);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .85rem;
            flex-shrink: 0;
            text-transform: uppercase;
        }
        .sidebar-user-info { overflow: hidden; transition: opacity .2s; }
        .sidebar-user-name {
            font-size: .8rem; font-weight: 600;
            color: rgba(255,255,255,.88);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sidebar-user-role {
            font-size: .7rem; color: rgba(255,255,255,.45);
            text-transform: capitalize;
        }
        .Sidebar.collapsed .sidebar-user-info { display: none; }
        .Sidebar.collapsed .sidebar-footer { padding: 12px 0; display: flex; justify-content: center; }

        /* ── Overlay (mobile) ────────────────────────── */
        .sidebar-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.4);
            z-index: 1040;
            backdrop-filter: blur(2px);
        }
        .sidebar-overlay.active { display: block; animation: fadeIn .25s forwards; }
        @keyframes fadeIn { from{opacity:0} to{opacity:1} }

        /* ── Mobile Toggle Btn ───────────────────────── */
        .mobile-toggle-btn {
            display: none;
            position: fixed;
            top: 14px; left: 14px;
            z-index: 1060;
            width: 40px; height: 40px;
            border-radius: 10px;
            background: var(--sidebar-bg);
            border: none; color: #fff;
            align-items: center; justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,.2);
            cursor: pointer;
        }
        @media (max-width: 991px) {
            .mobile-toggle-btn { display: flex; }
        }

        /* ── Main Content ────────────────────────────── */
        .main-content {
            flex: 1;
            padding: 0;
            transition: margin-left 0.3s cubic-bezier(0.4,0,0.2,1);
            min-width: 0;
        }

        @media (min-width: 992px) {
            .main-content { margin-left: var(--sidebar-w); }
            .main-content.sidebar-collapsed { margin-left: var(--sidebar-collapsed); }
            .sidebar-overlay { display: none !important; }
        }
        @media (max-width: 991px) {
            .main-content { margin-left: 0; }
        }

        /* ── Topbar ──────────────────────────────────── */
        .topbar {
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            background: #ffffff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.10), 0 2px 8px rgba(0, 0, 0, 0.07);
            position: sticky;
            top: 0;
            z-index: 999;
        }
        .topbar-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
        .page-body {
            padding: 32px 28px;
        }
        @media (max-width: 991px) {
            .topbar {
                padding-left: 70px;
            }
        }

        /* ── Topbar Right Profile ────────────────────── */
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .profile-avatar {
            width: 40px; height: 40px;
            border-radius: 11px;
            background: var(--accent);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,.14);
            transition: transform .2s, box-shadow .2s;
        }
        .profile-avatar:hover { transform: scale(1.07); box-shadow: 0 6px 18px rgba(0,0,0,.2); }

        .dropdown-menu {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,.1);
            padding: 6px; min-width: 180px;
        }
        .dropdown-item { border-radius: 8px; font-size: .875rem; padding: 8px 12px; }
        .dropdown-item:hover { background: var(--accent-soft); }
        .dropdown-header { font-size: .8rem; color: #64748b; padding: 6px 12px 2px; font-weight: 600; }

        /* ── Global Cards ─────────────────────────────── */
        .card {
            border-radius: 16px !important;
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
            background: #ffffff;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.02) !important;
            transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.22s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        .card:hover {
            box-shadow: 0 15px 35px -8px rgba(0, 0, 0, 0.06), 0 3px 10px rgba(0, 0, 0, 0.02) !important;
        }
        .card-header {
            background: transparent !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
            padding: 18px 24px !important;
            font-weight: 600 !important;
            color: #0f172a !important;
        }
        .card-body {
            padding: 24px !important;
        }
        .stat-card {
            transition: transform 0.22s, box-shadow 0.22s !important;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
        .folder-card { transition: transform .2s, box-shadow .2s; }
        .folder-card:hover { transform: translateY(-4px); box-shadow: 0 10px 20px rgba(0,0,0,.1) !important; }

        /* ── Premium Buttons ─────────────────────────── */
        .btn {
            border-radius: 10px !important;
            font-weight: 500 !important;
            padding: 8px 16px !important;
            transition: all 0.2s ease !important;
        }
        .btn-primary {
            background: var(--accent) !important;
            border-color: var(--accent) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15) !important;
        }
        .btn-primary:hover {
            background: {{ auth()->user()?->role === 'teacher' ? '#2563eb' : '#0d9488' }} !important;
            border-color: {{ auth()->user()?->role === 'teacher' ? '#2563eb' : '#0d9488' }} !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(13, 110, 253, 0.2) !important;
        }
        .btn-outline-secondary {
            border-color: #e2e8f0 !important;
            color: #475569 !important;
            background: transparent !important;
        }
        .btn-outline-secondary:hover {
            background: #f8fafc !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }

        /* ── Cleaner Tables ──────────────────────────── */
        .table {
            border-collapse: separate !important;
            border-spacing: 0 !important;
        }
        .table th {
            font-weight: 600 !important;
            font-size: 0.78rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            color: #64748b !important;
            background: #f8fafc !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06) !important;
            padding: 12px 16px !important;
        }
        .table td {
            padding: 16px 16px !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04) !important;
            vertical-align: middle !important;
        }
        .table tbody tr:last-child td {
            border-bottom: none !important;
        }

        /* ── Action Buttons (Quick Actions) ──────────── */
        .action-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px !important;
            border-radius: 12px !important;
            border: 1px solid #f1f5f9 !important;
            background: #ffffff !important;
            text-decoration: none;
            color: #1e293b !important;
            font-size: 0.88rem !important;
            font-weight: 500 !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            margin-bottom: 12px !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.01) !important;
        }
        .action-btn:hover {
            border-color: var(--accent) !important;
            background: var(--accent-soft) !important;
            color: var(--accent) !important;
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03) !important;
        }
        .action-btn-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }
    </style>
</head>
<body>
@php
    $sectionTitle = 'Dashboard';
    if (request()->routeIs('teacher.dashboard') || request()->routeIs('student.dashboard')) {
        $sectionTitle = 'Dashboard';
    } elseif (request()->routeIs('*flashcards*') || request()->routeIs('*flashcard-sets*')) {
        $sectionTitle = 'Flashcards';
    } elseif (request()->routeIs('*contents*')) {
        $sectionTitle = 'Other Materials';
    } elseif (request()->routeIs('*quizzes*')) {
        $sectionTitle = 'Quizzes';
    } elseif (request()->routeIs('*question-bank*')) {
        $sectionTitle = 'Question Bank';
    } elseif (request()->routeIs('*students*')) {
        $sectionTitle = 'Students';
    } elseif (request()->routeIs('*revision*')) {
        $sectionTitle = 'Revision';
    } elseif (request()->routeIs('*progress*')) {
        $sectionTitle = 'My Progress';
    } elseif (request()->routeIs('*daily_quran*')) {
        $sectionTitle = 'Daily Quran';
    } elseif (request()->routeIs('*diagnosis*')) {
        $sectionTitle = 'Diagnosis';
    }
@endphp
<div id="app" class="dashboard-container">

    <!-- Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    @include('components.sidebar')

    <!-- Mobile Toggle -->
    <button class="mobile-toggle-btn" id="mobileSidebarBtn" aria-label="Open menu">
        <i class="bi bi-list fs-5"></i>
    </button>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Topbar -->
        <header class="topbar">
            <h1 class="topbar-title">{{ $sectionTitle }}</h1>
            <div class="topbar-right">
                <div class="dropdown">
                    <a href="#" class="d-flex text-decoration-none" id="dropdownUserTop"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="profile-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUserTop">
                        <li><h6 class="dropdown-header">{{ auth()->user()->name }}</h6></li>
                        <li><span class="dropdown-header" style="padding-top:0;font-weight:400;color:#94a3b8;text-transform:capitalize;">{{ auth()->user()->role }}</span></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li>
                            <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                               onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right me-2"></i>Sign out
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <div class="page-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
</div>

@stack('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebar     = document.querySelector('.Sidebar');
    const overlay     = document.getElementById('sidebarOverlay');
    const mobileBtn   = document.getElementById('mobileSidebarBtn');
    const mainContent = document.getElementById('mainContent');
    const COLLAPSED_KEY = 'ezpaizy_sidebar_collapsed';

    // ── Desktop collapse/expand ──────────────────────────
    const mascotToggle = document.getElementById('mascotToggle');
    let collapsed = localStorage.getItem(COLLAPSED_KEY) === 'true';

    function applyCollapsed(animate) {
        if (collapsed) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('sidebar-collapsed');
        } else {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('sidebar-collapsed');
        }
    }

    applyCollapsed(false); // Apply on load

    if (mascotToggle) {
        mascotToggle.addEventListener('click', function (e) {
            e.preventDefault();
            // Only toggle on desktop
            if (window.innerWidth >= 992) {
                collapsed = !collapsed;
                localStorage.setItem(COLLAPSED_KEY, collapsed);
                applyCollapsed(true);
            }
        });
    }

    // ── Mobile open/close ────────────────────────────────
    function openMobile() {
        sidebar.classList.add('mobile-open');
        overlay.classList.add('active');
        mobileBtn.querySelector('i').className = 'bi bi-x-lg fs-5';
    }
    function closeMobile() {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
        mobileBtn.querySelector('i').className = 'bi bi-list fs-5';
    }

    mobileBtn.addEventListener('click', function () {
        sidebar.classList.contains('mobile-open') ? closeMobile() : openMobile();
    });
    overlay.addEventListener('click', closeMobile);
});
</script>
</body>
</html>
