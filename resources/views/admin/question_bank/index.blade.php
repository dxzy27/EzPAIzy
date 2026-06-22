@extends('layouts.dashboard')

@section('content')
<div class="card p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Global Question Bank</h4>
            <p class="text-muted mb-0" style="font-size: .875rem;">Manage the shared repository of questions available to all PAI teachers for generating quizzes.</p>
        </div>
        <div>
            <a href="{{ route('admin.question-bank.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Add Question
            </a>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filters --}}
    <form action="{{ route('admin.question-bank.index') }}" method="GET" class="row g-3 mb-4 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label small fw-bold text-muted">Search Question</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search question text..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small fw-bold text-muted">Difficulty Filter</label>
            <select name="difficulty" class="form-select">
                <option value="">All Difficulties</option>
                <option value="easy" {{ request('difficulty') === 'easy' ? 'selected' : '' }}>Easy</option>
                <option value="medium" {{ request('difficulty') === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="hard" {{ request('difficulty') === 'hard' ? 'selected' : '' }}>Hard</option>
            </select>
        </div>
        <div class="col-6 col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-outline-secondary w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
            <a href="{{ route('admin.question-bank.index') }}" class="btn btn-light w-100">Clear</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Question</th>
                    <th>Topic</th>
                    <th>Type</th>
                    <th>Difficulty</th>
                    <th>Correct Answer</th>
                    <th>Points</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($questions as $q)
                    <tr>
                        <td style="max-width: 300px;">
                            <div class="fw-bold text-dark text-wrap">{{ $q->question_text }}</div>
                            @if($q->type === 'mcq' && is_array($q->options))
                                <div class="mt-2 row g-1 small text-muted">
                                    @foreach($q->options as $key => $option)
                                        <div class="col-6"><strong>{{ strtoupper($key) }}:</strong> {{ $option }}</div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border px-2 py-1">{{ $q->topic }}</span>
                        </td>
                        <td>
                            <span class="small text-capitalize">{{ $q->type === 'mcq' ? 'MCQ' : 'Short Answer' }}</span>
                        </td>
                        <td>
                            <span class="badge" style="background: {{ $q->difficulty === 'easy' ? '#d1fae5; color:#065f46;' : ($q->difficulty === 'medium' ? '#fef3c7; color:#92400e;' : '#fee2e2; color:#991b1b;') }}">{{ ucfirst($q->difficulty) }}</span>
                        </td>
                        <td>
                            <code class="text-dark fw-bold">{{ strtoupper($q->correct_answer) }}</code>
                        </td>
                        <td>
                            <span class="small">{{ $q->points }} pts</span>
                        </td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                {{-- Edit --}}
                                <a href="{{ route('admin.question-bank.edit', $q) }}" class="btn btn-sm btn-outline-secondary" title="Edit Question">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                {{-- Delete --}}
                                <form action="{{ route('admin.question-bank.destroy', $q) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this question from the global bank?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove Question">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-folder2-open display-6 d-block mb-3 text-muted"></i>
                            No questions registered in global bank.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $questions->links() }}
    </div>
</div>
@endsection
