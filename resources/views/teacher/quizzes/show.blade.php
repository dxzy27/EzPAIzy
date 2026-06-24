@extends('layouts.dashboard')

@section('title', $quiz->title . ' – Quiz Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('teacher.quizzes.index') }}" class="btn btn-outline-secondary btn-sm me-2">
            <i class="bi bi-arrow-left me-1"></i>Back to Quizzes
        </a>
        <h4 class="d-inline-block mb-0 fw-bold">{{ $quiz->title }}</h4>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('teacher.quizzes.edit', $quiz) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil me-1"></i>Edit Quiz
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
            <div class="card-body">
                <div class="text-muted small mb-1">Topic</div>
                <div class="fw-semibold">{{ $quiz->topic }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
            <div class="card-body">
                <div class="text-muted small mb-1">Difficulty</div>
                <span class="badge rounded-pill
                    @if($quiz->difficulty === 'easy') bg-success
                    @elseif($quiz->difficulty === 'medium') bg-warning text-dark
                    @else bg-danger @endif" style="font-size: 0.85rem; padding: 0.4rem 0.8rem; font-weight: 700;">
                    {{ ucfirst($quiz->difficulty) }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
            <div class="card-body">
                <div class="text-muted small mb-1">Questions</div>
                <div class="fw-semibold">{{ $quiz->questions->count() }} questions</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
        <h5 class="fw-bold mb-0">Questions</h5>
    </div>
    <div class="card-body px-4 pb-4">
        @forelse($quiz->questions as $i => $question)
        <div class="card mb-3 border" style="border-radius:10px;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="fw-semibold mb-2">
                        <span class="text-muted me-2">{{ $i + 1 }}.</span>
                        {{ $question->question_text }}
                    </div>
                    <span class="badge bg-secondary rounded-pill ms-2 flex-shrink-0">{{ $question->points }} pts</span>
                </div>

                @if($question->type === 'mcq' && $question->options)
                <div class="mt-2 ms-3">
                    @foreach($question->options as $key => $option)
                    <div class="d-flex align-items-center gap-2 mb-1">
                        @if($key === $question->correct_answer)
                        <i class="bi bi-check-circle-fill text-success"></i>
                        @else
                        <i class="bi bi-circle text-muted"></i>
                        @endif
                        <span class="{{ $key === $question->correct_answer ? 'text-success fw-semibold' : '' }}">
                            <strong>{{ strtoupper($key) }}.</strong> {{ $option }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="mt-2 ms-3">
                    <span class="text-muted small">Answer: </span>
                    <span class="text-success fw-semibold">{{ $question->correct_answer }}</span>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-5">
            <i class="bi bi-question-circle fs-1 d-block mb-2"></i>
            No questions in this quiz yet.
        </div>
        @endforelse
    </div>
</div>
@endsection
