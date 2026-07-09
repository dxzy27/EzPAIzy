@extends('layouts.dashboard')

@section('content')
<div class="card p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Content Moderation & Control</h4>
            <p class="text-muted mb-0" style="font-size: .875rem;">Flag inappropriate resources or permanently remove materials, flashcards, and quizzes.</p>
        </div>
    </div>

    {{-- Alert Messages --}}


    {{-- Navigation Tabs --}}
    <ul class="nav nav-tabs border-bottom mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'materials' ? 'active fw-bold text-primary border-primary border-bottom border-2' : 'text-muted' }}" href="{{ route('admin.moderation.index', ['tab' => 'materials']) }}">
                <i class="bi bi-file-earmark-text me-2"></i>Study Materials
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'flashcards' ? 'active fw-bold text-primary border-primary border-bottom border-2' : 'text-muted' }}" href="{{ route('admin.moderation.index', ['tab' => 'flashcards']) }}">
                <i class="bi bi-card-text me-2"></i>Flashcard Sets
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'questions' ? 'active fw-bold text-primary border-primary border-bottom border-2' : 'text-muted' }}" href="{{ route('admin.moderation.index', ['tab' => 'questions']) }}">
                <i class="bi bi-database me-2"></i>Question Bank
            </a>
        </li>
    </ul>

    {{-- Tab Contents --}}
    @if($tab === 'materials')
        {{-- Study Materials Moderation Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Material Info</th>
                        <th>Topic / Folder</th>
                        <th>Created By</th>
                        <th>Created At</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contents as $content)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $content->title }}</div>
                                <div class="text-muted small">{{ Str::limit(strip_tags($content->content), 50) }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1">{{ $content->topic ?? 'General' }}</span>
                            </td>
                            <td>
                                <span class="small">{{ $content->teacher->name ?? 'System' }}</span>
                            </td>
                            <td>
                                <span class="text-muted small">{{ $content->created_at->format('M d, Y') }}</span>
                            </td>
                            <td>
                                @if($content->is_flagged)
                                    <span class="badge bg-danger-soft text-danger" style="background:#fee2e2; color:#b91c1c; padding: 5px 10px; border-radius:6px; font-weight:500;">Flagged</span>
                                @else
                                    <span class="badge bg-success-soft text-success" style="background:#ecfdf5; color:#047857; padding: 5px 10px; border-radius:6px; font-weight:500;">Approved</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    {{-- Flag Toggle --}}
                                    <form action="{{ route('admin.moderation.content.toggle-flag', $content) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $content->is_flagged ? 'btn-success' : 'btn-outline-danger' }}" title="{{ $content->is_flagged ? 'Approve Content' : 'Flag Content' }}">
                                            @if($content->is_flagged)
                                                <i class="bi bi-check-circle me-1"></i>Approve
                                            @else
                                                <i class="bi bi-flag-fill me-1"></i>Flag
                                            @endif
                                        </button>
                                    </form>

                                    {{-- Delete Content --}}
                                    <form action="{{ route('admin.moderation.content.destroy', $content) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this material? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Material">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-file-earmark-text display-6 d-block mb-3 text-muted"></i>
                                No study materials found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $contents->appends(request()->query())->links() }}</div>

    @elseif($tab === 'flashcards')
        {{-- Flashcards Moderation Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Flashcard Set</th>
                        <th>Topic / Folder</th>
                        <th>Created By</th>
                        <th>Created At</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($flashcardSets as $set)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $set->title }}</div>
                                <div class="text-muted small">{{ Str::limit($set->description, 50) }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1">{{ $set->topic ?? 'General' }}</span>
                            </td>
                            <td>
                                <span class="small">{{ $set->user->name ?? 'System' }}</span>
                            </td>
                            <td>
                                <span class="text-muted small">{{ $set->created_at->format('M d, Y') }}</span>
                            </td>
                            <td>
                                @if($set->is_flagged)
                                    <span class="badge bg-danger-soft text-danger" style="background:#fee2e2; color:#b91c1c; padding: 5px 10px; border-radius:6px; font-weight:500;">Flagged</span>
                                @else
                                    <span class="badge bg-success-soft text-success" style="background:#ecfdf5; color:#047857; padding: 5px 10px; border-radius:6px; font-weight:500;">Approved</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    {{-- Flag Toggle --}}
                                    <form action="{{ route('admin.moderation.flashcard.toggle-flag', $set) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $set->is_flagged ? 'btn-success' : 'btn-outline-danger' }}" title="{{ $set->is_flagged ? 'Approve Set' : 'Flag Set' }}">
                                            @if($set->is_flagged)
                                                <i class="bi bi-check-circle me-1"></i>Approve
                                            @else
                                                <i class="bi bi-flag-fill me-1"></i>Flag
                                            @endif
                                        </button>
                                    </form>

                                    {{-- Delete Flashcard Set --}}
                                    <form action="{{ route('admin.moderation.flashcard.destroy', $set) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this flashcard set? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Set">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-card-text display-6 d-block mb-3 text-muted"></i>
                                No flashcard sets found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $flashcardSets->appends(request()->query())->links() }}</div>

    @elseif($tab === 'questions')
        {{-- Question Bank Moderation Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 45%;">Question Text</th>
                        <th>Topic</th>
                        <th>Difficulty</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($questions as $q)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark text-wrap" style="max-width: 450px;">{{ $q->question_text }}</div>
                                @if($q->options)
                                    <div class="small mt-1">
                                        <strong>Options:</strong>
                                        <div class="ps-2 mt-1">
                                            @foreach((array)$q->options as $key => $val)
                                                @if(!empty($val) && in_array(strtolower($key), ['a', 'b', 'c', 'd']))
                                                    <div class="text-primary" style="font-size: 0.825rem; font-weight: 500;">
                                                        <strong>{{ strtolower($key) }}.</strong> {{ $val }}
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                <div class="text-success small mt-1">
                                    <strong>Ans:</strong> {{ $q->correct_answer }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1">{{ $q->topic ?? 'General' }}</span>
                            </td>
                            <td>
                                <span class="badge" style="font-size: 0.85rem; font-weight: 700; padding: 0.35rem 0.7rem; background: {{ $q->difficulty === 'easy' ? '#d1fae5; color:#065f46;' : ($q->difficulty === 'medium' ? '#fef3c7; color:#92400e;' : '#fee2e2; color:#991b1b;') }}">{{ ucfirst($q->difficulty) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary text-uppercase">{{ $q->type }}</span>
                            </td>
                            <td>
                                @if($q->is_flagged)
                                    <span class="badge bg-danger-soft text-danger" style="background:#fee2e2; color:#b91c1c; padding: 5px 10px; border-radius:6px; font-weight:500;">Flagged</span>
                                @else
                                    <span class="badge bg-success-soft text-success" style="background:#ecfdf5; color:#047857; padding: 5px 10px; border-radius:6px; font-weight:500;">Approved</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    {{-- Flag Toggle --}}
                                    <form action="{{ route('admin.moderation.question.toggle-flag', $q) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $q->is_flagged ? 'btn-success' : 'btn-outline-danger' }}" title="{{ $q->is_flagged ? 'Approve Question' : 'Flag Question' }}">
                                            @if($q->is_flagged)
                                                <i class="bi bi-check-circle me-1"></i>Approve
                                            @else
                                                <i class="bi bi-flag-fill me-1"></i>Flag
                                            @endif
                                        </button>
                                    </form>

                                    {{-- Delete Question --}}
                                    <form action="{{ route('admin.moderation.question.destroy', $q) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this question from the Question Bank? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Question">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-database display-6 d-block mb-3 text-muted"></i>
                                No questions found in the Question Bank.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $questions->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
