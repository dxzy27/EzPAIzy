@extends('layouts.dashboard')

@section('content')
<style>
    .stat-icon {
        width: 52px; height: 52px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; flex-shrink: 0;
    }
    .stat-label { font-size: .78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; }
    .stat-value { font-size: 1.9rem; font-weight: 700; line-height: 1.1; color: var(--text-main); }
    .stat-sub   { font-size: .8rem; color: var(--text-muted); margin-top: 2px; }

    .action-btn {
        display: flex; align-items: center; gap: 10px;
        padding: 14px 18px; border-radius: 12px;
        border: 1px solid var(--border); background: var(--card-bg);
        text-decoration: none; color: var(--text-main);
        font-size: .875rem; font-weight: 500;
        transition: border-color .18s, box-shadow .18s, transform .18s;
        margin-bottom: 12px;
    }
    .action-btn:hover {
        border-color: var(--accent);
        box-shadow: 0 10px 20px -10px rgba(139, 92, 246, 0.15);
        transform: translateY(-2px); color: var(--accent);
    }
    .action-btn-icon {
        width: 36px; height: 36px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }
</style>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Admin Console 👋</h4>
        <p class="text-muted mb-0" style="font-size:.875rem;">Welcome to the EzPAIzy system dashboard.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="bi bi-person-plus-fill me-1"></i> Add Account
        </a>
        <a href="{{ route('admin.question-bank.create') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-plus-circle me-1"></i> Add Global Question
        </a>
    </div>
</div>

{{-- Stats Row --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.users.index', ['role' => 'teacher']) }}" class="card stat-card text-decoration-none d-block p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#eef2ff; color:#4f46e5;"><i class="bi bi-person-badge"></i></div>
                <div>
                    <div class="stat-label">Teachers</div>
                    <div class="stat-value">{{ $stats['total_teachers'] }}</div>
                    <div class="stat-sub">Accounts</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.users.index', ['role' => 'student']) }}" class="card stat-card text-decoration-none d-block p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#ecfdf5; color:#059669;"><i class="bi bi-people"></i></div>
                <div>
                    <div class="stat-label">Students</div>
                    <div class="stat-value">{{ $stats['total_students'] }}</div>
                    <div class="stat-sub">Registered</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.users.index') }}" class="card stat-card text-decoration-none d-block p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fef2f2; color:#dc2626;"><i class="bi bi-person-x"></i></div>
                <div>
                    <div class="stat-label">Suspended</div>
                    <div class="stat-value">{{ $stats['suspended_users'] }}</div>
                    <div class="stat-sub">Blocked Accounts</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.question-bank.index') }}" class="card stat-card text-decoration-none d-block p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fffbeb; color:#d97706;"><i class="bi bi-database"></i></div>
                <div>
                    <div class="stat-label">Question Bank</div>
                    <div class="stat-value">{{ $stats['question_bank_count'] }}</div>
                    <div class="stat-sub">Global Questions</div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Quick Actions --}}
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-lightning-charge me-2 text-warning"></i> Quick Management Tools</h5>
            <p class="text-muted small mb-4">Direct shortcuts to critical administrative functions and operations.</p>
            
            <div class="row g-2">
                <div class="col-12">
                    <a href="{{ route('admin.users.index') }}" class="action-btn">
                        <div class="action-btn-icon" style="background:#e0e7ff; color:#3b82f6;"><i class="bi bi-people-fill"></i></div>
                        <div>
                            <div class="fw-bold">User Management</div>
                            <div class="text-muted small">Manage student & teacher credentials & permissions.</div>
                        </div>
                    </a>
                </div>
                <div class="col-12">
                    <a href="{{ route('admin.moderation.index') }}" class="action-btn">
                        <div class="action-btn-icon" style="background:#fee2e2; color:#ef4444;"><i class="bi bi-shield-fill-check"></i></div>
                        <div>
                            <div class="fw-bold">Content Control Panel</div>
                            <div class="text-muted small">Moderate study materials, flashcards and quiz questions.</div>
                        </div>
                    </a>
                </div>
                <div class="col-12">
                    <a href="{{ route('admin.question-bank.index') }}" class="action-btn">
                        <div class="action-btn-icon" style="background:#fef3c7; color:#f59e0b;"><i class="bi bi-folder-fill"></i></div>
                        <div>
                            <div class="fw-bold">Global Question Bank</div>
                            <div class="text-muted small">Access and curate system questions shared across PAI teachers.</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Flagged / Moderation Summary --}}
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-flag-fill me-2 text-danger"></i> Moderation Summary</h5>
            <p class="text-muted small mb-4">Current flagged items waiting for administrative action.</p>

            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <div>
                        <div class="fw-bold">Flagged Study Materials</div>
                        <div class="text-muted small">Teacher uploads, ebooks, notes</div>
                    </div>
                    <span class="badge bg-danger rounded-pill">{{ $stats['flagged_contents'] }}</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <div>
                        <div class="fw-bold">Flagged Flashcard Sets</div>
                        <div class="text-muted small">Student & teacher flashcard study aids</div>
                    </div>
                    <span class="badge bg-danger rounded-pill">{{ $stats['flagged_flashcards'] }}</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <div>
                        <div class="fw-bold">Flagged Quizzes</div>
                        <div class="text-muted small">AI-generated and manual quizzes</div>
                    </div>
                    <span class="badge bg-danger rounded-pill">{{ $stats['flagged_quizzes'] }}</span>
                </div>
            </div>

            <div class="mt-4 text-center">
                <a href="{{ route('admin.moderation.index') }}" class="btn btn-sm btn-outline-danger w-100">
                    Go to Content Control
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
