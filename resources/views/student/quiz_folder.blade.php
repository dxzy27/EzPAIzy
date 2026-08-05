@extends('layouts.dashboard')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ url()->previous() === url()->current() ? route('student.quizzes') : url()->previous() }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <div>
                    <h1 class="fw-extrabold text-dark mb-0" style="font-size: 2.1rem; font-weight: 800;">
                        <i class="bi bi-folder-fill text-warning me-2"></i>{{ $topic }}
                    </h1>
                    <nav aria-label="breadcrumb" class="mt-1">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.quizzes') }}" class="text-decoration-none">Quizzes</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $topic }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
             <div class="input-group shadow-sm ms-auto" style="border-radius: 50px; overflow: hidden; max-width: 300px;">
                <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="quiz-search" class="form-control border-0 ps-2" placeholder="Search quizzes...">
             </div>
        </div>
    </div>

    @if($quizzes->count() > 0)
        <div class="row g-4">
            @foreach($quizzes as $quiz)
                @php
                    // Topic Badge Colors Mapping
                    $topicNorm = strtolower(trim($quiz->topic));
                    $topicStyle = 'background-color: #f5f5f5; color: #616161;'; // Default grey
                    
                    if (str_contains($topicNorm, 'quran') || str_contains($topicNorm, 'qur\'an')) {
                        $topicStyle = 'background-color: #f3e5f5; color: #7b1fa2;'; // Soft Purple
                    } elseif (str_contains($topicNorm, 'hadis') || str_contains($topicNorm, 'hadith')) {
                        $topicStyle = 'background-color: #e3f2fd; color: #1565c0;'; // Soft Blue
                    } elseif (str_contains($topicNorm, 'akidah') || str_contains($topicNorm, 'aqidah')) {
                        $topicStyle = 'background-color: #e0f2f1; color: #00796b;'; // Soft Teal
                    } elseif (str_contains($topicNorm, 'fiqah') || str_contains($topicNorm, 'fiqh')) {
                        $topicStyle = 'background-color: #e8f5e9; color: #2e7d32;'; // Soft Green
                    } elseif (str_contains($topicNorm, 'sirah') || str_contains($topicNorm, 'sejarah')) {
                        $topicStyle = 'background-color: #fff3e0; color: #e65100;'; // Soft Orange
                    } elseif (str_contains($topicNorm, 'akhlak') || str_contains($topicNorm, 'adab')) {
                        $topicStyle = 'background-color: #ffebee; color: #c62828;'; // Soft Rose/Red
                    }
                    
                    $difficultyColor = match($quiz->difficulty) {
                        'easy' => 'success',
                        'medium' => 'warning',
                        'hard' => 'danger',
                        default => 'primary'
                    };
                    $difficultyEmoji = match($quiz->difficulty) {
                        'easy' => '🟢',
                        'medium' => '🟡',
                        'hard' => '🔴',
                        default => '🔵'
                    };
                @endphp
                <div class="col-md-6 col-lg-4 col-xl-3 quiz-card-col" data-title="{{ strtolower($quiz->title) }}" data-difficulty="{{ strtolower($quiz->difficulty) }}">
                    <div class="card h-100 shadow-sm border-0 content-card" style="transition: transform 0.2s, box-shadow 0.2s; border-radius: 16px; overflow: hidden;">
                        <div class="card-body d-flex flex-column justify-content-between" style="padding: 1.15rem;">
                            <div>
                                <!-- Badges Row -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex gap-1.5 align-items-center">
                                        <span class="badge px-3 py-1.5 rounded-pill fw-bold text-uppercase bg-{{ $difficultyColor }} bg-opacity-10 text-{{ $difficultyColor }}" style="font-size: 0.78rem;">
                                            {{ $difficultyEmoji }} {{ ucfirst($quiz->difficulty) }}
                                        </span>
                                    </div>
                                    <button class="btn btn-link p-0 text-warning favorite-btn d-inline-flex align-items-center justify-content-center" 
                                            data-topic="{{ $quiz->topic }}" 
                                            data-difficulty="{{ $quiz->difficulty }}" 
                                            data-favorited="{{ in_array($quiz->topic . '-' . $quiz->difficulty, $favoritedQuizMap ?? []) ? 'true' : 'false' }}"
                                            title="{{ in_array($quiz->topic . '-' . $quiz->difficulty, $favoritedQuizMap ?? []) ? 'Remove from Revision' : 'Add to Revision' }}"
                                            style="height: 28px; width: 28px; line-height: 1;">
                                        <i class="bi {{ in_array($quiz->topic . '-' . $quiz->difficulty, $favoritedQuizMap ?? []) ? 'bi-star-fill' : 'bi-star' }} fs-5"></i>
                                    </button>
                                </div>
                                
                                <!-- Title -->
                                <h5 class="card-title fw-extrabold text-dark mb-2" style="font-size: 1.15rem; line-height: 1.4; font-weight: 800;">{{ $quiz->title }}</h5>
                                
                                <!-- Inline Metadata Details -->
                                <div class="text-muted mb-3" style="font-size: 0.8rem; line-height: 1.5;">
                                    👤 By: {{ $quiz->teacher->name ?? 'Unknown Teacher' }}
                                </div>

                                @php
                                    $p = $quiz->progress->first();
                                @endphp
                                <div class="mb-3 mt-2">
                                    @if($p)
                                        @if(($quiz->difficulty === 'hard' || $quiz->difficulty === 'medium') && $p->status === 'pending')
                                            <div class="d-flex justify-content-between text-muted small mb-2 align-items-center">
                                                <span class="fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Status</span>
                                                <span class="badge bg-warning bg-opacity-10 text-warning px-2.5 py-1 rounded-pill fw-bold text-uppercase" style="font-size: 0.7rem;">
                                                    <i class="bi bi-clock-history me-1"></i> Awaiting grading
                                                </span>
                                            </div>
                                            <div class="progress" style="height: 9px; border-radius: 4px;">
                                                <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%; border-radius: 4px;" title="Pending Review"></div>
                                            </div>
                                        @else
                                            @php
                                                $scoreClass = $p->score >= 70 ? 'text-success' : ($p->score >= 50 ? 'text-warning' : 'text-danger');
                                                $barBg = $p->score >= 70 ? 'bg-success' : ($p->score >= 50 ? 'bg-warning' : 'bg-danger');
                                                $statusText = $p->score >= 70 ? 'Excellent' : ($p->score >= 50 ? 'Good' : 'Need Practice');
                                            @endphp
                                            <div class="d-flex justify-content-between text-muted small mb-2 align-items-center">
                                                <span class="fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Status</span>
                                                <span class="badge bg-success bg-opacity-10 text-success px-2.5 py-1 rounded-pill fw-bold text-uppercase" style="font-size: 0.7rem;">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Completed
                                                </span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-end mb-1">
                                                <span class="text-muted small fw-bold">Best Score</span>
                                                <span class="{{ $scoreClass }} fw-bold" style="font-size: 1.35rem; line-height: 1;">{{ $p->score }}%</span>
                                            </div>
                                            <div class="progress" style="height: 9px; border-radius: 4px;">
                                                <div class="progress-bar {{ $barBg }}" role="progressbar" style="width: {{ $p->score }}%; border-radius: 4px;" title="{{ $statusText }}"></div>
                                            </div>
                                        @endif
                                    @else
                                        <div class="d-flex justify-content-between text-muted small mb-2 align-items-center">
                                            <span class="fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Status</span>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-2.5 py-1 rounded-pill fw-bold text-uppercase" style="font-size: 0.7rem;">
                                                <i class="bi bi-dash-circle me-1"></i> Not Started
                                            </span>
                                        </div>
                                        <div class="text-muted-light small italic">Not attempted yet</div>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <!-- Divider -->
                                <hr class="my-2" style="border-top: 1px solid rgba(0,0,0,0.06); opacity: 1;">
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted small">
                                        <i class="bi bi-question-circle me-1"></i> {{ $quiz->questions_count }} Question{{ $quiz->questions_count !== 1 ? 's' : '' }}
                                    </span>
                                    <!-- Dynamic styled topic badge -->
                                    <span class="badge px-2.5 py-1 rounded-pill fw-bold text-uppercase" style="{{ $topicStyle }} font-size: 0.72rem;">{{ $quiz->topic ?? 'General' }}</span>
                                </div>
                                
                                @if($quiz->questions_count > 0)
                                    @if($p)
                                        <a href="{{ route('student.quiz.take', ['topic' => $quiz->topic, 'difficulty' => $quiz->difficulty, 'title' => $quiz->title]) }}" class="btn btn-outline-primary w-100 rounded-pill py-2.5 fw-bold d-flex align-items-center justify-content-center gap-2">
                                            <i class="bi bi-arrow-repeat"></i> Retake Quiz
                                        </a>
                                    @else
                                        <a href="{{ route('student.quiz.take', ['topic' => $quiz->topic, 'difficulty' => $quiz->difficulty, 'title' => $quiz->title]) }}" class="btn btn-success text-white w-100 py-2.5 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                                            Take Quiz <i class="bi bi-arrow-right"></i>
                                        </a>
                                    @endif
                                @else
                                    <button class="btn btn-success text-white w-100 py-2.5 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" onclick="alert('There are no available quizzes right now')">
                                        Take Quiz <i class="bi bi-arrow-right"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row mt-5">
            <div class="col-md-12 d-flex justify-content-center">
                {{ $quizzes->links() }}
            </div>
        </div>
    @else
        <div class="alert alert-info text-center py-5" role="alert" style="border-radius: 16px;">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const favoriteBtns = document.querySelectorAll('.favorite-btn');
    
    favoriteBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const topic = this.dataset.topic;
            const difficulty = this.dataset.difficulty;
            const isFavorited = this.dataset.favorited === 'true';
            const icon = this.querySelector('i');
            
            // Optimistic UI Update
            if (isFavorited) {
                // Remove
                this.dataset.favorited = 'false';
                this.title = 'Add to Revision';
                icon.classList.remove('bi-star-fill');
                icon.classList.add('bi-star');
                
                fetch("{{ url('/student/favorites/quiz') }}/" + encodeURIComponent(topic) + "/" + difficulty, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
            } else {
                // Add
                this.dataset.favorited = 'true';
                this.title = 'Remove from Revision';
                icon.classList.remove('bi-star');
                icon.classList.add('bi-star-fill');
                
                fetch("{{ url('/student/favorites/quiz') }}/" + encodeURIComponent(topic) + "/" + difficulty, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
            }
        });
    });
    
    // Live Search
    const searchInput = document.getElementById('quiz-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.quiz-card-col');
            let hasVisible = false;
            
            cards.forEach(card => {
                const title = card.dataset.title || '';
                const difficulty = card.dataset.difficulty || '';
                if (title.includes(query) || difficulty.includes(query)) {
                    card.style.setProperty('display', '', 'important');
                    hasVisible = true;
                } else {
                    card.style.setProperty('display', 'none', 'important');
                }
            });

            // Handle no results message
            let noResultsMsg = document.getElementById('no-results-msg');
            if (!hasVisible) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.id = 'no-results-msg';
                    noResultsMsg.className = 'col-12 text-center py-5';
                    noResultsMsg.innerHTML = `
                        <i class="bi bi-search display-3 text-muted mb-3"></i>
                        <h5>No quizzes match your search.</h5>
                        <p class="text-muted small">Try checking for typos or searching a different term.</p>
                    `;
                    document.querySelector('.row.g-4').appendChild(noResultsMsg);
                }
            } else {
                if (noResultsMsg) {
                    noResultsMsg.remove();
                }
            }
        });
    }
});
</script>
@endpush
