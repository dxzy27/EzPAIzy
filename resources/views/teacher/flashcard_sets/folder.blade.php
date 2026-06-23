@extends('layouts.dashboard')

@section('title', 'Flashcard Sets - ' . $topic)

@section('content')
<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('teacher.flashcard-sets.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Folders">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <div>
                    <h1 class="h2 fw-bold text-dark mb-0 d-inline-block pe-2" id="topic-title-display" title="Double click to rename" style="cursor: pointer;" data-bs-toggle="tooltip">{{ $topic }} <i class="bi bi-pencil ms-2 text-muted" style="font-size: 0.8rem; opacity: 0.5;"></i></h1>
                    <input type="text" id="topic-title-input" class="form-control d-none fw-bold text-dark mb-0" value="{{ $topic }}" style="font-size: 1.75rem; padding: 0.2rem 0.5rem; max-width: 300px;">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('teacher.flashcard-sets.index') }}" class="text-decoration-none">Flashcards</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $topic }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('teacher.flashcard-sets.create', ['topic' => $topic]) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>New Flashcard Set
            </a>
        </div>
    </div>

    @if($flashcardSets->isEmpty())
    <div class="card border-0 shadow-sm text-center py-5" style="border-radius:14px;">
        <div class="text-muted">
            <i class="bi bi-folder-x fs-1 d-block mb-3 text-warning" style="opacity: .6;"></i>
            <h4 class="fw-bold text-dark">This folder is empty</h4>
            <p class="mb-4">No flashcard sets found in this folder.</p>
            <a href="{{ route('teacher.flashcard-sets.create', ['topic' => $topic]) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Create Your First Set
            </a>
        </div>
    </div>
    @else
    <div class="row g-3">
        @foreach($flashcardSets as $set)
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-warning text-dark rounded-pill">{{ $set->topic }}</span>
                        <small class="text-muted">{{ $set->created_at->diffForHumans() }}</small>
                    </div>
                    <h6 class="fw-bold mb-1">{{ $set->title }}</h6>
                    @if($set->description)
                    <p class="text-muted small mb-2">{{ Str::limit($set->description, 80) }}</p>
                    @endif
                    <div class="mt-auto pt-3 d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            <i class="bi bi-layers me-1"></i>{{ $set->flashcards_count ?? $set->flashcards()->count() }} cards
                        </span>
                        <div class="d-flex gap-2">
                            <a href="{{ route('teacher.flashcard-sets.edit', $set) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('teacher.flashcard-sets.destroy', $set) }}" method="POST"
                                  onsubmit="return confirm('Delete this flashcard set?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $flashcardSets->links() }}
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
                    type: 'flashcard'
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
