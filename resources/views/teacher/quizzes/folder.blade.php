@extends('layouts.dashboard')

@section('content')
<div class="container py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('teacher.quizzes.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Folders">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <div>
                    <h1 class="h2 fw-bold text-dark mb-0 d-inline-block pe-2" id="topic-title-display" title="Double click to rename" style="cursor: pointer;" data-bs-toggle="tooltip">{{ $topic }} <i class="bi bi-pencil ms-2 text-muted" style="font-size: 0.8rem; opacity: 0.5;"></i></h1>
                    <input type="text" id="topic-title-input" class="form-control d-none fw-bold text-dark mb-0" value="{{ $topic }}" style="font-size: 1.75rem; padding: 0.2rem 0.5rem; max-width: 300px;">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('teacher.quizzes.index') }}" class="text-decoration-none">Quizzes</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $topic }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end d-flex justify-content-end gap-2">
            <a href="{{ route('teacher.quizzes.generate') }}" class="btn btn-dark">
                <i class="bi bi-cpu me-1"></i> Generate with AI
            </a>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="createQuizDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    + Create Quiz
                </button>
                <ul class="dropdown-menu" aria-labelledby="createQuizDropdown">
                    <li><a class="dropdown-item" href="{{ route('teacher.quizzes.create', ['difficulty' => 'easy', 'topic' => $topic]) }}">Easy</a></li>
                    <li><a class="dropdown-item" href="{{ route('teacher.quizzes.create', ['difficulty' => 'medium', 'topic' => $topic]) }}">Medium</a></li>
                    <li><a class="dropdown-item" href="{{ route('teacher.quizzes.create', ['difficulty' => 'hard', 'topic' => $topic]) }}">Hard</a></li>
                </ul>
            </div>
        </div>
    </div>



    @if($quizzes->isEmpty())
    <div class="card border-0 shadow-sm text-center py-5" style="border-radius:14px;">
        <div class="text-muted">
            <i class="bi bi-folder-x fs-1 d-block mb-3 text-warning" style="opacity: .6;"></i>
            <h4 class="fw-bold text-dark">This folder is empty</h4>
            <p class="mb-4">No quizzes found in this folder. Select Create Quiz above or Generate with AI.</p>
        </div>
    </div>
    @else
    <div class="row">
        @foreach($quizzes as $quiz)
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            {{ $quiz->title }}
                            <span class="badge bg-{{ $quiz->difficulty == 'easy' ? 'success' : ($quiz->difficulty == 'medium' ? 'warning' : 'danger') }} ms-2" style="font-size: 0.6em;">{{ ucfirst($quiz->difficulty) }}</span>
                        </h5>
                        <p class="text-muted small mb-1">Questions: {{ $quiz->questions_count ?? $quiz->questions()->count() }}</p>
                        <p class="text-muted small">Created: {{ $quiz->created_at->format('M d, Y') }}</p>
                    </div>
                    <div class="card-footer bg-light">
                        <a href="{{ route('teacher.quizzes.edit', $quiz) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('teacher.quizzes.destroy', $quiz) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
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
