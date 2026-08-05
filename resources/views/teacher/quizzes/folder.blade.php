@extends('layouts.dashboard')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1040px; margin: 0 auto;">
    <!-- Top Navigation Header Matching Student Web Reference -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-7 mb-3 mb-md-0">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('teacher.quizzes.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Folders">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h1 class="fw-extrabold text-dark mb-0 pe-1" id="topic-title-display" title="Double click to rename" style="font-size: 2rem; font-weight: 800; cursor: pointer;" data-bs-toggle="tooltip">
                            <i class="bi bi-folder-fill text-warning me-2"></i>{{ $topic }} <i class="bi bi-pencil ms-1 text-muted" style="font-size: 0.85rem; opacity: 0.5;"></i>
                        </h1>
                        <input type="text" id="topic-title-input" class="form-control d-none fw-bold text-dark mb-0" value="{{ $topic }}" style="font-size: 1.75rem; padding: 0.2rem 0.5rem; max-width: 300px;">
                    </div>
                    <nav aria-label="breadcrumb" class="mt-1">
                        <ol class="breadcrumb mb-0" style="font-size: 0.88rem;">
                            <li class="breadcrumb-item"><a href="{{ route('teacher.quizzes.index') }}" class="text-decoration-none text-primary fw-semibold">Quizzes</a></li>
                            <li class="breadcrumb-item active text-muted fw-semibold" aria-current="page">{{ $topic }}</li>
                            <li class="breadcrumb-item text-muted" style="font-size: 0.82rem;">{{ $quizzes->total() }} {{ Str::plural('Quiz', $quizzes->total()) }} • {{ $totalQuestionsCount }} Questions</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="col-md-5 text-end d-flex justify-content-end align-items-center gap-2">
            <a href="{{ route('teacher.quizzes.generate') }}" class="btn btn-dark fw-bold px-3 py-2 d-inline-flex align-items-center" style="border-radius: 10px; font-size: 0.88rem; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <i class="bi bi-cpu me-1.5 fs-6 text-warning"></i> Generate with AI
            </a>
            <div class="dropdown">
                <button class="btn btn-primary fw-bold px-3 py-2 dropdown-toggle d-inline-flex align-items-center" type="button" id="createQuizDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 10px; font-size: 0.88rem; box-shadow: 0 4px 12px rgba(59,130,246,0.25);">
                    <i class="bi bi-plus-lg me-1 fs-6"></i> Create Quiz
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="createQuizDropdown" style="border-radius: 12px; padding: 6px;">
                    <li><a class="dropdown-item fw-medium py-2 rounded-2" href="{{ route('teacher.quizzes.create', ['difficulty' => 'easy', 'topic' => $topic]) }}">Easy</a></li>
                    <li><a class="dropdown-item fw-medium py-2 rounded-2" href="{{ route('teacher.quizzes.create', ['difficulty' => 'medium', 'topic' => $topic]) }}">Medium</a></li>
                    <li><a class="dropdown-item fw-medium py-2 rounded-2" href="{{ route('teacher.quizzes.create', ['difficulty' => 'hard', 'topic' => $topic]) }}">Hard</a></li>
                </ul>
            </div>
        </div>
    </div>

    @if($quizzes->isEmpty())
    <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 16px;">
        <div class="text-muted p-4">
            <i class="bi bi-journal-x display-3 d-block mb-3 text-warning" style="opacity: .6;"></i>
            <h4 class="fw-bold text-dark">This folder is empty</h4>
            <p class="mb-4 text-muted">No quizzes found in this folder. Select Create Quiz above or Generate with AI.</p>
        </div>
    </div>
    @else
    <!-- Responsive Scalable Grid -->
    <div class="row g-3.5 mb-4">
        @foreach($quizzes as $quiz)
            @php
                $diffClass = $quiz->difficulty == 'easy' ? 'success' : ($quiz->difficulty == 'medium' ? 'warning' : 'danger');
                $diffDot = $quiz->difficulty == 'easy' ? '🟢' : ($quiz->difficulty == 'medium' ? '🟡' : '🔴');
            @endphp
            <div class="col-md-6 mb-3">
                <div class="card h-100 border-0 shadow-sm overflow-hidden quiz-item-card" style="border-radius: 16px; background: #ffffff; transition: transform 0.25s ease, box-shadow 0.25s ease;">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <!-- Top Row: Badge above Card Title (Matching Student Web Reference) -->
                        <div>
                            <div class="mb-2">
                                <span class="badge px-3 py-1.5 rounded-pill fw-bold text-uppercase bg-{{ $diffClass }} bg-opacity-10 text-{{ $diffClass }}" style="font-size: 0.78rem;">
                                    {{ $diffDot }} {{ strtoupper($quiz->difficulty) }}
                                </span>
                            </div>
                            <h5 class="fw-bold text-dark mb-3" style="font-size: 1.15rem; line-height: 1.35;">{{ $quiz->title }}</h5>

                            <!-- Rich Info Icons with Breathing Room -->
                            <div class="d-flex flex-column gap-2.5 mb-4 text-muted" style="font-size: 0.9rem;">
                                <div class="d-flex align-items-center gap-2.5">
                                    <i class="bi bi-file-earmark-text text-primary fs-6"></i>
                                    <span><strong>{{ $quiz->questions_count }}</strong> {{ Str::plural('Question', $quiz->questions_count) }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2.5">
                                    <i class="bi bi-people text-info fs-6"></i>
                                    <span><strong>{{ $quiz->attempts_count }}</strong> {{ Str::plural('Attempt', $quiz->attempts_count) }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2.5">
                                    <i class="bi bi-trophy text-warning fs-6"></i>
                                    <span>Avg Score: <strong>{{ $quiz->avg_score }}%</strong></span>
                                </div>
                                <div class="d-flex align-items-center gap-2.5 mt-1 pt-2.5 border-top border-light">
                                    <i class="bi bi-clock text-secondary fs-6"></i>
                                    <span style="font-size: 0.82rem;">Updated {{ $quiz->updated_at->format('M j, Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Action Buttons with Equal Heights & High-Contrast Clean Palette -->
                        <div class="d-flex align-items-center gap-2 pt-2">
                            <a href="{{ route('teacher.quizzes.show', ['topic' => $quiz->topic, 'difficulty' => $quiz->difficulty]) }}" class="btn btn-white border shadow-xs btn-sm fw-semibold flex-fill py-2 d-inline-flex align-items-center justify-content-center gap-1.5" style="border-radius: 10px; font-size: 0.88rem; height: 38px; background: #ffffff;">
                                <i class="bi bi-eye text-secondary fs-6"></i><span>View</span>
                            </a>
                            <a href="{{ route('teacher.quizzes.edit', ['topic' => $quiz->topic, 'difficulty' => $quiz->difficulty]) }}" class="btn btn-primary btn-sm fw-semibold flex-fill py-2 d-inline-flex align-items-center justify-content-center gap-1.5" style="border-radius: 10px; font-size: 0.88rem; height: 38px;">
                                <i class="bi bi-pencil-square fs-6"></i><span>Edit</span>
                            </a>
                            <form action="{{ route('teacher.quizzes.destroy', ['topic' => $quiz->topic, 'difficulty' => $quiz->difficulty]) }}" method="POST" class="d-inline flex-fill" onsubmit="return confirm('Are you sure you want to delete this quiz?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-light text-danger border-0 btn-sm fw-semibold w-100 py-2 d-inline-flex align-items-center justify-content-center gap-1.5" title="Delete Quiz" style="border-radius: 10px; font-size: 0.88rem; height: 38px;">
                                    <i class="bi bi-trash3 fs-6"></i><span>Delete</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row mt-3">
        <div class="col-12">
            {{ $quizzes->links() }}
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const titleDisplay = document.getElementById('topic-title-display');
        const titleInput = document.getElementById('topic-title-input');
        const originalTopic = "{{ $topic }}";

        if (titleDisplay && titleInput) {
            // Double click to edit
            titleDisplay.addEventListener('dblclick', function() {
                titleDisplay.classList.add('d-none');
                titleInput.classList.remove('d-none');
                titleInput.focus();
                // Put cursor at the end
                const val = titleInput.value;
                titleInput.value = '';
                titleInput.value = val;
            });

            // Submit on blur or enter
            function submitRename() {
                const newName = titleInput.value.trim();
                if (newName === '' || newName === originalTopic) {
                    // Cancel
                    titleInput.classList.add('d-none');
                    titleDisplay.classList.remove('d-none');
                    titleInput.value = originalTopic;
                    return;
                }

                // Disable input while submitting
                titleInput.disabled = true;

                fetch(`/topics/${encodeURIComponent(originalTopic)}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        new_name: newName,
                        type: 'quiz'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        alert('Failed to rename topic.');
                        titleInput.disabled = false;
                        titleInput.focus();
                    }
                })
                .catch(error => {
                    console.error(error);
                    alert('An error occurred while renaming.');
                    titleInput.disabled = false;
                    titleInput.focus();
                });
            }

            titleInput.addEventListener('blur', submitRename);
            titleInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    titleInput.blur(); // Triggers blur which calls submitRename
                } else if (e.key === 'Escape') {
                    titleInput.value = originalTopic;
                    titleInput.blur();
                }
            });
        }
        
        // Initialize tooltip if bootstrap is available
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });
</script>
@endpush
