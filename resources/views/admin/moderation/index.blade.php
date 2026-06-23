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
            <a class="nav-link {{ $tab === 'quizzes' ? 'active fw-bold text-primary border-primary border-bottom border-2' : 'text-muted' }}" href="{{ route('admin.moderation.index', ['tab' => 'quizzes']) }}">
                <i class="bi bi-pencil-square me-2"></i>Quizzes
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
        <div class="mt-3">{{ $contents->links() }}</div>

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
        <div class="mt-3">{{ $flashcardSets->links() }}</div>

    @elseif($tab === 'quizzes')
        {{-- Quizzes Moderation Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Quiz Title</th>
                        <th>Topic</th>
                        <th>Difficulty</th>
                        <th>Created By</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quizzes as $quiz)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $quiz->title }}</div>
                                <div class="text-muted small">{{ $quiz->questions()->count() }} Questions</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1">{{ $quiz->topic ?? 'General' }}</span>
                            </td>
                            <td>
                                <span class="badge" style="background: {{ $quiz->difficulty === 'easy' ? '#d1fae5; color:#065f46;' : ($quiz->difficulty === 'medium' ? '#fef3c7; color:#92400e;' : '#fee2e2; color:#991b1b;') }}">{{ ucfirst($quiz->difficulty) }}</span>
                            </td>
                            <td>
                                <span class="small">{{ $quiz->teacher->name ?? 'System' }}</span>
                            </td>
                            <td>
                                @if($quiz->is_flagged)
                                    <span class="badge bg-danger-soft text-danger" style="background:#fee2e2; color:#b91c1c; padding: 5px 10px; border-radius:6px; font-weight:500;">Flagged</span>
                                @else
                                    <span class="badge bg-success-soft text-success" style="background:#ecfdf5; color:#047857; padding: 5px 10px; border-radius:6px; font-weight:500;">Approved</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    {{-- Flag Toggle --}}
                                    <form action="{{ route('admin.moderation.quiz.toggle-flag', $quiz) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $quiz->is_flagged ? 'btn-success' : 'btn-outline-danger' }}" title="{{ $quiz->is_flagged ? 'Approve Quiz' : 'Flag Quiz' }}">
                                            @if($quiz->is_flagged)
                                                <i class="bi bi-check-circle me-1"></i>Approve
                                            @else
                                                <i class="bi bi-flag-fill me-1"></i>Flag
                                            @endif
                                        </button>
                                    </form>

                                    {{-- Delete Quiz --}}
                                    <form action="{{ route('admin.moderation.quiz.destroy', $quiz) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this quiz? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Quiz">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-pencil-square display-6 d-block mb-3 text-muted"></i>
                                No quizzes found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $quizzes->links() }}</div>
    @endif
</div>
@endsection
