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
        padding: 12px 16px; border-radius: 10px;
        border: 1px solid var(--border); background: var(--card-bg);
        text-decoration: none; color: var(--text-main);
        font-size: .875rem; font-weight: 500;
        transition: border-color .18s, box-shadow .18s, transform .18s;
        margin-bottom: 10px;
    }
    .action-btn:hover {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(43, 108, 176, .08);
        transform: translateX(3px); color: var(--accent);
    }
    .action-btn-icon {
        width: 36px; height: 36px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: .95rem; flex-shrink: 0;
    }

    .content-row {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 0; border-bottom: 1px solid var(--border);
    }
    .content-row:last-child { border-bottom: none; }
    .content-row-icon {
        width: 34px; height: 34px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: .85rem; flex-shrink: 0;
    }
    .content-row-title { font-size: .875rem; font-weight: 500; flex: 1; min-width: 0; }
    .content-row-title small { display: block; font-size: .75rem; color: var(--text-muted); font-weight: 400; }
</style>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Welcome back, {{ explode(' ', auth()->user()->name)[0] }} 👋</h4>
        <p class="text-muted mb-0" style="font-size:.875rem;">Here's an overview of your class activity.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('teacher.quizzes.generate') }}" class="btn btn-primary btn-sm px-3">
            <i class="bi bi-stars me-1"></i> Generate Quiz
        </a>
        <a href="{{ route('teacher.contents.create') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-plus me-1"></i> Add Material
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('teacher.quizzes.index') }}" class="card stat-card text-decoration-none d-block" style="padding:18px 20px;">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#ebf8ff; color:#2b6cb0;"><i class="bi bi-pencil-square"></i></div>
                <div>
                    <div class="stat-label">Quizzes</div>
                    <div class="stat-value">{{ $quizzes->count() }}</div>
                    <div class="stat-sub">Created</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('teacher.flashcard-sets.index') }}" class="card stat-card text-decoration-none d-block" style="padding:18px 20px;">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#d1fae5; color:#059669;"><i class="bi bi-collection"></i></div>
                <div>
                    <div class="stat-label">Materials</div>
                    <div class="stat-value">{{ $totalContentsCount }}</div>
                    <div class="stat-sub">Uploaded</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('teacher.students.index') }}" class="card stat-card text-decoration-none d-block" style="padding:18px 20px;">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#e0f2fe; color:#0284c7;"><i class="bi bi-people"></i></div>
                <div>
                    <div class="stat-label">Students</div>
                    <div class="stat-value">{{ $studentsCount }}</div>
                    <div class="stat-sub">Registered</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('teacher.flashcard-sets.index') }}" class="card stat-card text-decoration-none d-block" style="padding:18px 20px;">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fef3c7; color:#d97706;"><i class="bi bi-card-text"></i></div>
                <div>
                    <div class="stat-label">Flashcard Sets</div>
                    <div class="stat-value">{{ \App\Models\FlashcardSet::where('user_id', auth()->id())->count() }}</div>
                    <div class="stat-sub">Sets</div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-clock-history me-2 text-muted"></i>Recent Quizzes</span>
                <a href="{{ route('teacher.quizzes.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:.78rem;">View all</a>
            </div>
            <div class="card-body p-0">
                @if($quizzes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Title</th>
                                    <th>Difficulty</th>
                                    <th>Questions</th>
                                    <th>Created</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quizzes->take(6) as $quiz)
                                <tr>
                                    <td class="ps-4"><span style="font-weight:500;">{{ Str::limit($quiz->title, 28) }}</span></td>
                                    <td>
                                        @php $d = $quiz->difficulty ?? 'easy'; @endphp
                                        <span style="padding:3px 9px; border-radius:6px; font-size:.72rem; font-weight:700;
                                            background:{{ $d === 'hard' ? '#fee2e2' : ($d === 'medium' ? '#fef3c7' : '#d1fae5') }};
                                            color:{{ $d === 'hard' ? '#991b1b' : ($d === 'medium' ? '#92400e' : '#065f46') }};">
                                            {{ ucfirst($d) }}
                                        </span>
                                    </td>
                                    <td style="color:var(--text-muted);">{{ $quiz->questions->count() }}</td>
                                    <td style="color:var(--text-muted); font-size:.8rem;">{{ $quiz->created_at->format('d M') }}</td>
                                    <td>
                                        <a href="{{ route('teacher.quizzes.show', $quiz) }}" class="btn btn-sm btn-outline-secondary" style="font-size:.75rem; padding:3px 10px;">View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-clipboard-x" style="font-size:2.5rem; color:var(--border);"></i>
                        <p class="text-muted mt-3 mb-0" style="font-size:.875rem;">No quizzes yet.</p>
                        <a href="{{ route('teacher.quizzes.generate') }}" class="btn btn-primary btn-sm mt-3">Generate one with AI</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5 d-flex flex-column gap-3">
        <div class="card">
            <div class="card-header"><i class="bi bi-lightning me-2 text-muted"></i>Quick Actions</div>
            <div class="card-body">
                <a href="{{ route('teacher.quizzes.generate') }}" class="action-btn">
                    <div class="action-btn-icon" style="background:#ebf8ff; color:#2b6cb0;"><i class="bi bi-stars"></i></div>
                    Generate Quiz with AI
                    <i class="bi bi-chevron-right ms-auto" style="font-size:.8rem; color:var(--text-muted);"></i>
                </a>
                <a href="{{ route('teacher.quizzes.create') }}" class="action-btn">
                    <div class="action-btn-icon" style="background:#fef3c7; color:#d97706;"><i class="bi bi-pencil"></i></div>
                    Create Quiz Manually
                    <i class="bi bi-chevron-right ms-auto" style="font-size:.8rem; color:var(--text-muted);"></i>
                </a>
                <a href="{{ route('teacher.flashcard-sets.create') }}" class="action-btn">
                    <div class="action-btn-icon" style="background:#d1fae5; color:#059669;"><i class="bi bi-card-text"></i></div>
                    New Flashcard Set
                    <i class="bi bi-chevron-right ms-auto" style="font-size:.8rem; color:var(--text-muted);"></i>
                </a>
                <a href="{{ route('teacher.contents.create') }}" class="action-btn" style="margin-bottom:0;">
                    <div class="action-btn-icon" style="background:#e0f2fe; color:#0284c7;"><i class="bi bi-file-earmark-plus"></i></div>
                    Upload Material
                    <i class="bi bi-chevron-right ms-auto" style="font-size:.8rem; color:var(--text-muted);"></i>
                </a>
            </div>
        </div>

        <div class="card flex-fill">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-collection me-2 text-muted"></i>Recent Materials</span>
                <a href="{{ route('teacher.contents.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:.78rem;">View all</a>
            </div>
            <div class="card-body">
                @if($recentContents->count() == 0)
                    <p class="text-muted text-center py-3 mb-0" style="font-size:.875rem;">No materials uploaded yet.</p>
                @else
                    @foreach($recentContents->take(5) as $content)
                        @php $isFC = class_basename($content) === 'FlashcardSet'; @endphp
                        <div class="content-row">
                            <div class="content-row-icon"
                                 style="background:{{ $isFC ? '#d1fae5' : '#ebf8ff' }}; color:{{ $isFC ? '#059669' : '#2b6cb0' }};">
                                <i class="bi {{ $isFC ? 'bi-card-text' : 'bi-file-earmark-text' }}"></i>
                            </div>
                            <div class="content-row-title">
                                {{ Str::limit($content->title, 28) }}
                                <small>{{ $isFC ? 'Flashcard Set' : 'Material' }} · {{ $content->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="d-flex gap-1">
                                @if($isFC)
                                    <a href="{{ route('teacher.flashcard-sets.edit', $content) }}" class="btn btn-sm btn-outline-secondary" style="padding:3px 8px; font-size:.72rem;">Edit</a>
                                @else
                                    <a href="{{ route('teacher.contents.edit', $content) }}" class="btn btn-sm btn-outline-secondary" style="padding:3px 8px; font-size:.72rem;">Edit</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
