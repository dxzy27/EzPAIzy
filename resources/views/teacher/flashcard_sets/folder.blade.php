@extends('layouts.dashboard')

@section('title', 'Flashcard Sets - ' . $topic)

@push('styles')
<style>
    .content-card {
        background: linear-gradient(180deg, #FFFFFF 0%, #F8FBFD 100%) !important;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.5) !important;
        box-shadow: 0 8px 32px 0 rgba(31, 110, 104, 0.03) !important;
        border-radius: 16px !important;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .content-card:hover {
        box-shadow: 0 15px 35px rgba(31, 110, 104, 0.08) !important;
        transform: translateY(-4px);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1040px; margin: 0 auto;">
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
        <div class="col-md-4 text-end mt-3 mt-md-0">
            <a href="{{ route('teacher.flashcard-sets.create', ['topic' => $topic]) }}" class="btn btn-primary fw-semibold px-3" style="border-radius: 10px; font-size: 0.9rem;">
                <i class="bi bi-plus-lg me-1"></i> New Flashcard Set
            </a>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2 mb-3 flex-wrap gap-2">
         <h5 class="text-muted fw-bold mb-0" style="font-size: 0.82rem; letter-spacing: 0.5px;">SETS IN {{ strtoupper($topic) }}</h5>
         <div class="input-group shadow-sm" style="border-radius: 50px; overflow: hidden; width: 260px; height: 36px;">
            <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search text-muted" style="font-size: 0.85rem;"></i></span>
            <input type="text" id="set-search" class="form-control border-0 ps-2" placeholder="Search sets..." style="font-size: 0.85rem;">
         </div>
    </div>

    @if($flashcardSets->isEmpty())
    <div class="card border-0 shadow-sm text-center py-5" style="border-radius:16px;">
        <div class="text-muted">
            <i class="bi bi-folder-x fs-1 d-block mb-3 text-warning" style="opacity: .6;"></i>
            <h4 class="fw-bold text-dark">This folder is empty</h4>
            <p class="mb-4">No flashcard sets found in this folder.</p>
            <a href="{{ route('teacher.flashcard-sets.create', ['topic' => $topic]) }}" class="btn btn-primary fw-semibold px-4" style="border-radius: 10px;">
                <i class="bi bi-plus-lg me-1"></i> Create Your First Set
            </a>
        </div>
    </div>
    @else
    <div class="row g-3">
        @foreach($flashcardSets as $set)
        <div class="col-md-6 col-xl-4 set-card-col" data-title="{{ strtolower($set->title) }}">
            <div class="card content-card h-100 p-2">
                <div class="card-body d-flex flex-column p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge" style="font-size: 0.75rem; padding: 0.35rem 0.65rem; font-weight: 600; background-color: #e0f2fe; color: #0284c7; border-radius: 6px;">{{ $set->topic }}</span>
                        <small class="text-muted" style="font-size: 0.78rem;">{{ $set->created_at->diffForHumans() }}</small>
                    </div>
                    <h6 class="fw-bold mb-1 text-dark fs-6">{{ $set->title }}</h6>
                    @if($set->description)
                    <p class="text-muted small mb-2" style="font-size: 0.82rem; line-height: 1.4;">{{ Str::limit($set->description, 80) }}</p>
                    @endif
                    <div class="mt-auto pt-3 d-flex justify-content-between align-items-center border-top">
                        <span class="text-muted small fw-semibold">
                            <i class="bi bi-layers me-1 text-primary"></i>{{ $set->flashcards_count ?? $set->flashcards()->count() }} cards
                        </span>
                        <div class="d-flex gap-1">
                            <a href="{{ route('teacher.flashcard-sets.edit', $set) }}" class="btn btn-sm btn-outline-secondary px-2" title="Edit Set" style="border-radius: 8px;">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('teacher.flashcard-sets.destroy', $set) }}" method="POST"
                                  onsubmit="return confirm('Delete this flashcard set?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger px-2" title="Delete Set" style="border-radius: 8px;">
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
        // Live Search for sets
        const searchInput = document.getElementById('set-search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                const cards = document.querySelectorAll('.set-card-col');
                
                cards.forEach(card => {
                    const title = card.dataset.title || '';
                    if (title.includes(query)) {
                        card.style.setProperty('display', '', 'important');
                    } else {
                        card.style.setProperty('display', 'none', 'important');
                    }
                });
            });
        }

        const titleDisplay = document.getElementById('topic-title-display');
        const titleInput = document.getElementById('topic-title-input');
        const originalTopic = "{{ $topic }}";

        if (titleDisplay && titleInput) {
            // Double click to edit
            titleDisplay.addEventListener('dblclick', function() {
                titleDisplay.classList.add('d-none');
                titleInput.classList.remove('d-none');
                titleInput.focus();
                const val = titleInput.value;
                titleInput.value = '';
                titleInput.value = val;
            });

            function submitRename() {
                const newName = titleInput.value.trim();
                if (newName === '' || newName === originalTopic) {
                    titleInput.classList.add('d-none');
                    titleDisplay.classList.remove('d-none');
                    titleInput.value = originalTopic;
                    return;
                }

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
                    titleInput.blur();
                } else if (e.key === 'Escape') {
                    titleInput.value = originalTopic;
                    titleInput.blur();
                }
            });
        }
        
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });
</script>
@endpush

