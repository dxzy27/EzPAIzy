@extends('layouts.dashboard')

@section('content')
<div class="container-fluid px-4 py-5">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <a href="{{ route('student.quizzes') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Folders">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <h1 class="display-6 fw-bold text-dark mb-0">
                    <i class="bi bi-folder-fill text-warning me-2"></i>{{ $topic }}
                </h1>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.quizzes') }}" class="text-decoration-none">Quizzes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $topic }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-none d-md-block">
             <div class="input-group shadow-sm" style="border-radius: 50px; overflow: hidden; width: 300px;">
                <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search text-muted"></i></span>
                <input type="text" class="form-control border-0 ps-2" placeholder="Search quizzes...">
             </div>
        </div>
    </div>

    @if($quizzes->count() > 0)
        <div class="row g-4">
            @foreach($quizzes as $quiz)
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card h-100 shadow-sm border-0 content-card" style="transition: transform 0.2s, box-shadow 0.2s; border-radius: 12px; overflow: hidden;">
                        <div class="card-body d-flex flex-column p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge rounded-pill bg-light text-dark shadow-sm border" style="font-size: 0.85rem; padding: 0.4rem 0.8rem; font-weight: 600;">
                                    <i class="bi bi-folder2-open me-1 text-primary"></i> {{ $quiz->topic ?? 'General' }}
                                </span>
                                @php
                                    $difficultyColor = match($quiz->difficulty) {
                                        'easy' => 'success',
                                        'medium' => 'warning',
                                        'hard' => 'danger',
                                        default => 'primary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $difficultyColor }} bg-opacity-10 text-{{ $difficultyColor }}" style="font-size: 0.85rem; padding: 0.4rem 0.8rem; font-weight: 700;">
                                    {{ ucfirst($quiz->difficulty) }}
                                </span>
                            </div>
                            
                            <h5 class="card-title fw-bold text-dark mb-2">{{ $quiz->title }}</h5>
                            <p class="text-muted small mb-3">
                                <i class="bi bi-person-circle me-1"></i> {{ $quiz->teacher->name ?? 'Unknown Teacher' }}
                            </p>

                            @php
                                $p = $quiz->progress->first();
                            @endphp
                            <div class="mb-3 mt-2">
                                @if($p)
                                    @if($quiz->difficulty === 'hard' && $p->status === 'pending')
                                        <div class="d-flex justify-content-between text-muted small mb-1">
                                            <span>Quiz Progress</span>
                                            <span class="text-warning fw-semibold">Pending Review</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%" title="Pending Review"></div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-2" style="font-size: 0.75rem;">
                                            <span class="text-warning" style="font-weight: 500;"><i class="bi bi-clock-history me-1"></i>Awaiting grading</span>
                                            <span class="text-secondary">Attempted</span>
                                        </div>
                                    @else
                                        @php
                                            $scoreClass = $p->score >= 70 ? 'text-success' : ($p->score >= 50 ? 'text-warning' : 'text-danger');
                                            $barBg = $p->score >= 70 ? 'bg-success' : ($p->score >= 50 ? 'bg-warning' : 'bg-danger');
                                            $statusText = $p->score >= 70 ? 'Passed' : ($p->score >= 50 ? 'Average' : 'Failed');
                                        @endphp
                                        <div class="d-flex justify-content-between text-muted small mb-1">
                                            <span>Quiz Score</span>
                                            <span class="{{ $scoreClass }} fw-bold">{{ $p->score }}%</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar {{ $barBg }}" role="progressbar" style="width: {{ $p->score }}%" title="{{ $statusText }}"></div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-2" style="font-size: 0.75rem;">
                                            <span class="{{ $scoreClass }}" style="font-weight: 500;">
                                                <i class="bi @if($p->score >= 70) bi-check-circle-fill @elseif($p->score >= 50) bi-exclamation-circle-fill @else bi-x-circle-fill @endif me-1"></i>{{ $statusText }}
                                            </span>
                                            <span class="text-secondary">Completed</span>
                                        </div>
                                    @endif
                                @else
                                    <div class="d-flex justify-content-between text-muted small mb-1">
                                        <span>Quiz Score</span>
                                        <span class="text-secondary">Not Attempted</span>
                                    </div>
                                    <div class="progress" style="height: 6px; background-color: #e9ecef;">
                                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2" style="font-size: 0.75rem;">
                                        <span class="text-muted"><i class="bi bi-circle me-1"></i>Not started</span>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-auto pt-3 border-top">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted small">
                                        <i class="bi bi-question-circle me-1"></i> {{ $quiz->questions_count }} Question{{ $quiz->questions_count !== 1 ? 's' : '' }}
                                    </span>
                                    <small class="text-muted">{{ $quiz->created_at->diffForHumans() }}</small>
                                </div>
                                
                                @if($quiz->questions_count > 0)
                                    @if($p)
                                        <a href="{{ route('student.quiz.take', $quiz) }}" class="btn btn-outline-primary w-100 shadow-sm" style="border-radius: 8px;">
                                            <i class="bi bi-arrow-repeat me-1"></i> Retake Quiz
                                        </a>
                                    @else
                                        <a href="{{ route('student.quiz.take', $quiz) }}" class="btn btn-primary w-100 shadow-sm" style="border-radius: 8px;">
                                            <i class="bi bi-play-fill me-1"></i> Take Quiz
                                        </a>
                                    @endif
                                @else
                                    <button class="btn btn-outline-secondary w-100" data-bs-toggle="modal" data-bs-target="#quizModal{{ $quiz->id }}" style="border-radius: 8px;">
                                        <i class="bi bi-pencil-square me-1"></i> {{ $p ? 'Retake & Log Score' : 'Log Score' }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Manual Score Modal (Legacy) -->
                    @if($quiz->questions_count == 0)
                    <div class="modal fade" id="quizModal{{ $quiz->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header bg-light border-0">
                                    <h5 class="modal-title fw-bold">{{ $quiz->title }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('student.submit', $quiz) }}" method="POST">
                                    <div class="modal-body p-4">
                                        @csrf
                                        <div class="alert alert-info d-flex align-items-center mb-3">
                                            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                                            <div>
                                                This quiz has no online questions. Please take it offline and enter your score here.
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="score{{ $quiz->id }}" class="form-label fw-bold">Your Score (0-100)</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control form-control-lg" id="score{{ $quiz->id }}" name="score" min="0" max="100" required placeholder="85">
                                                <span class="input-group-text bg-light text-muted">/ 100</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary px-4">Submit Score</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="row mt-5">
            <div class="col-md-12 d-flex justify-content-center">
                {{ $quizzes->links() }}
            </div>
        </div>
    @else
        <div class="alert alert-info text-center py-5" role="alert" style="border-radius: 12px;">
            <i class="bi bi-info-circle display-4 d-block mb-3 text-info"></i>
            <h4 class="alert-heading fw-bold">No Quizzes in this Folder</h4>
            <p>Your instructor hasn't added any quizzes to the <strong>{{ $topic }}</strong> folder yet.</p>
        </div>
    @endif
</div>

<style>
    .content-card:hover {
        transform: translateY(-5px) !important;
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important;
    }
</style>
@endsection
