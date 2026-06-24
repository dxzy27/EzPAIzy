@extends('layouts.dashboard')

@push('styles')
<style>
    /* Lined notepad paper styling */
    .notepad-textarea {
        background-color: #fdfdfd;
        background-image: linear-gradient(#e2e8f0 1px, transparent 1px);
        background-size: 100% 2rem;
        line-height: 2rem;
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-family: 'Outfit', sans-serif;
        font-size: 0.95rem;
        color: #334155;
        resize: vertical;
    }
    .notepad-textarea:focus {
        background-color: #ffffff;
        border-color: #7c3aed;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
    }
    .note-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        overflow: hidden;
    }
    .note-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -4px rgba(0, 0, 0, 0.05);
    }
    .folder-banner {
        background: linear-gradient(135deg, #6d28d9 0%, #4f46e5 100%) !important;
        color: white !important;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    .folder-banner::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='300' height='120' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='260' cy='20' r='80' fill='rgba(255,255,255,.06)'/%3E%3Ccircle cx='20' cy='110' r='50' fill='rgba(255,255,255,.04)'/%3E%3C/svg%3E") no-repeat right top;
        pointer-events: none;
    }
</style>
@endpush

@section('content')
<div class="container">
    {{-- Back button & Actions --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    {{-- Folder Banner --}}
    <div class="folder-banner shadow-sm" style="background: linear-gradient(135deg, #6d28d9 0%, #4f46e5 100%) !important; color: white !important;">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <span class="badge bg-white text-primary fw-bold text-uppercase px-3 py-2 mb-2" style="font-size: 0.75rem;">📂 Topic Folder</span>
                <h1 class="fw-bold mb-0 text-white" style="color: white !important;">{{ $topic }}</h1>
                <p class="text-white mb-0 mt-1" style="color: rgba(255,255,255,0.85) !important;">You have {{ $notes->count() }} study note{{ $notes->count() !== 1 ? 's' : '' }} in this folder</p>
            </div>
            <div class="d-none d-md-block" style="font-size: 4rem; opacity: 0.2;">
                📝
            </div>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius: 12px;">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Search and Filter Controls --}}
    @if($notes->count() > 0)
        <div class="card mb-4 border-0 shadow-sm" style="border-radius: 14px; background-color: #f8fafc;">
            <div class="card-body p-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px; border-color: #cbd5e1;">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="note-search" class="form-control border-start-0 bg-white" 
                                   style="border-radius: 0 10px 10px 0; border-color: #cbd5e1; font-size: 0.9rem;"
                                   placeholder="Search notes by title or acronym content..." oninput="applyFolderFilters()">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px; border-color: #cbd5e1;">
                                <i class="bi bi-funnel text-muted"></i>
                            </span>
                            <select id="type-filter" class="form-select border-start-0 bg-white" 
                                    style="border-radius: 0 10px 10px 0; border-color: #cbd5e1; font-size: 0.9rem;"
                                    onchange="applyFolderFilters()">
                                <option value="">All Note Types</option>
                                <option value="flashcard">🃏 Flashcards</option>
                                <option value="quiz">📝 Quizzes</option>
                                <option value="content">📄 Other Materials</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Notes Grid --}}
    @if($notes->count() > 0)
        <div class="row" id="notes-grid-container">
            @foreach($notes as $note)
                @php
                    $resourceType = $note->resource_type ?: 'content';
                @endphp
                <div class="col-lg-6 mb-4 note-item-card" 
                     id="note-card-{{ $note->id }}"
                     data-type="{{ $resourceType }}"
                     data-title="{{ strtolower($note->title) }}"
                     data-content="{{ strtolower($note->content) }}">
                    <div class="card note-card shadow-sm h-100">
                        <div class="card-header bg-white border-bottom-0 pt-3 px-3 d-flex justify-content-between align-items-start">
                            <div>
                                <div class="d-flex flex-wrap gap-1 align-items-center mb-1">
                                    {{-- Type badge --}}
                                    <span class="badge bg-light text-dark border small">
                                        @if($note->resource_type === 'quiz')
                                            📝 Quiz
                                        @elseif($note->resource_type === 'flashcard')
                                            🃏 Flashcards
                                        @else
                                            📄 Other Materials
                                        @endif
                                    </span>
                                    
                                    {{-- Difficulty badge --}}
                                    @if($note->difficulty)
                                        @php
                                            $diffColor = match($note->difficulty) {
                                                'easy' => 'success',
                                                'medium' => 'warning',
                                                'hard' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $diffColor }} text-capitalize small">
                                            {{ $note->difficulty }}
                                        </span>
                                    @endif

                                    {{-- Go to link --}}
                                    @if($note->resource_id)
                                        @php
                                            $goToUrl = match($note->resource_type) {
                                                'quiz' => route('student.quiz.take', $note->resource_id),
                                                'flashcard' => route('student.flashcards.show', $note->resource_id),
                                                default => route('student.contents.show', $note->resource_id)
                                            };
                                            $goToLabel = match($note->resource_type) {
                                                'quiz' => 'Go to Quiz',
                                                'flashcard' => 'Go to Flashcard',
                                                default => 'Go to Material'
                                            };
                                            $goToIcon = match($note->resource_type) {
                                                'quiz' => 'bi-pencil-square',
                                                'flashcard' => 'bi-card-text',
                                                default => 'bi-file-earmark-text'
                                            };
                                        @endphp
                                        <a href="{{ $goToUrl }}" class="badge bg-primary text-white text-decoration-none d-inline-flex align-items-center gap-1 border-0" style="padding: 4px 8px; font-size: 0.72rem; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#1d4ed8'" onmouseout="this.style.backgroundColor='#3b82f6'">
                                            <i class="bi {{ $goToIcon }}"></i> {{ $goToLabel }}
                                        </a>
                                    @endif
                                </div>
                                <small class="text-muted" style="font-size: 0.75rem;">
                                    <i class="bi bi-clock me-1"></i>Last updated: {{ $note->updated_at->format('M d, Y g:i A') }}
                                </small>
                            </div>
                            
                            {{-- Save status & Delete --}}
                            <div class="d-flex align-items-center gap-2">
                                <span id="save-status-{{ $note->id }}" class="small text-muted" style="font-size: 0.8rem;">Saved</span>
                                
                                <form action="{{ route('student.notes.destroy', $note) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this note?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link text-danger p-0" title="Delete Note">
                                        <i class="bi bi-trash3-fill fs-5"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card-body px-3 pb-3 pt-1">
                            {{-- Note Title Input --}}
                            <div class="mb-3">
                                <label for="note-title-{{ $note->id }}" class="form-label small fw-bold text-uppercase text-muted mb-1" style="font-size: 0.7rem;">Note Title</label>
                                <input type="text" id="note-title-{{ $note->id }}" class="form-control form-control-sm fw-bold border-0 bg-light py-2" 
                                       value="{{ $note->title }}" 
                                       placeholder="Enter note title...">
                            </div>

                            {{-- Note Content Textarea --}}
                            <div class="mb-3">
                                <label for="note-content-{{ $note->id }}" class="form-label small fw-bold text-uppercase text-muted mb-1" style="font-size: 0.7rem;">Notes & Acronyms</label>
                                <textarea id="note-content-{{ $note->id }}" class="form-control notepad-textarea" rows="8" 
                                          placeholder="Write your acronyms and summaries here...">{{ $note->content }}</textarea>
                            </div>

                            {{-- Manual Save button --}}
                            <div class="d-grid">
                                <button type="button" onclick="saveNoteCard({{ $note->id }}, '{{ addslashes($note->topic) }}', '{{ $note->resource_type }}', {{ $note->resource_id ?? 'null' }})" class="btn text-white fw-bold btn-sm" style="background-color: #6d28d9; border-color: #6d28d9;">
                                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        {{-- No results empty state (initially hidden) --}}
        <div class="card text-center p-5 shadow-sm border-0 mb-4" id="no-results-state" style="display: none;">
            <div style="font-size: 3rem;">🔍</div>
            <h4 class="fw-bold mt-3">No matching notes found</h4>
            <p class="text-muted">Try adjusting your search query or switching your note type filter.</p>
        </div>
    @else
        <div class="card text-center p-5 shadow-sm border-0">
            <div style="font-size: 3rem;">📂</div>
            <h4 class="fw-bold mt-3">This folder is empty</h4>
            <p class="text-muted">You haven't saved any notes for this topic yet. Go to your learning materials or quizzes and write some notes!</p>
            <div class="mt-2">
                <a href="{{ route('student.contents.index') }}" class="btn btn-primary me-2">Browse Materials</a>
                <a href="{{ route('student.quizzes') }}" class="btn btn-outline-primary">Browse Quizzes</a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    const saveTimeouts = {};

    function applyFolderFilters() {
        const query = document.getElementById('note-search').value.toLowerCase().trim();
        const type = document.getElementById('type-filter').value;
        const cards = document.querySelectorAll('.note-item-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const cardType = card.getAttribute('data-type');
            const title = card.getAttribute('data-title');
            const content = card.getAttribute('data-content');

            const matchesQuery = !query || title.includes(query) || content.includes(query);
            const matchesType = !type || cardType === type;

            if (matchesQuery && matchesType) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Show/hide empty state if no notes match
        const emptyState = document.getElementById('no-results-state');
        const notesGrid = document.getElementById('notes-grid-container');
        
        if (visibleCount === 0) {
            emptyState.style.display = 'block';
            if (notesGrid) notesGrid.style.display = 'none';
        } else {
            emptyState.style.display = 'none';
            if (notesGrid) notesGrid.style.display = 'flex';
        }
    }

    function saveNoteCard(noteId, topic, resourceType, resourceId) {
        const titleInput = document.getElementById(`note-title-${noteId}`);
        const contentTextarea = document.getElementById(`note-content-${noteId}`);
        const statusSpan = document.getElementById(`save-status-${noteId}`);

        if (!titleInput || !contentTextarea || !statusSpan) return;

        const title = titleInput.value.trim();
        const content = contentTextarea.value.trim();

        if (!title) {
            statusSpan.textContent = 'Title required';
            statusSpan.style.color = '#ef4444';
            return;
        }

        statusSpan.textContent = 'Saving...';
        statusSpan.style.color = '#4b5563';

        fetch("{{ route('student.notes.save') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                topic: topic,
                difficulty: null,
                title: title,
                content: content,
                resource_type: resourceType || null,
                resource_id: resourceId || null
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                statusSpan.textContent = 'Saved';
                statusSpan.style.color = '#10b981';

                // Update data attributes for live search
                const cardEl = document.getElementById(`note-card-${noteId}`);
                if (cardEl) {
                    cardEl.setAttribute('data-title', title.toLowerCase());
                    cardEl.setAttribute('data-content', content.toLowerCase());
                }
            } else {
                statusSpan.textContent = 'Save failed';
                statusSpan.style.color = '#ef4444';
            }
        })
        .catch(err => {
            console.error(err);
            statusSpan.textContent = 'Connection error';
            statusSpan.style.color = '#ef4444';
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Register auto-save for each note card
        @foreach($notes as $note)
            (function() {
                const noteId = {{ $note->id }};
                const topic = '{{ addslashes($note->topic) }}';
                const resourceType = '{{ $note->resource_type }}';
                const resourceId = {{ $note->resource_id ?? 'null' }};

                const titleInput = document.getElementById(`note-title-${noteId}`);
                const contentTextarea = document.getElementById(`note-content-${noteId}`);
                const statusSpan = document.getElementById(`save-status-${noteId}`);

                if (titleInput && contentTextarea) {
                    const triggerAutoSave = () => {
                        statusSpan.textContent = 'Unsaved changes';
                        statusSpan.style.color = '#f59e0b';

                        clearTimeout(saveTimeouts[noteId]);
                        saveTimeouts[noteId] = setTimeout(() => {
                            saveNoteCard(noteId, topic, resourceType, resourceId);
                        }, 1500);
                    };

                    titleInput.addEventListener('input', triggerAutoSave);
                    contentTextarea.addEventListener('input', triggerAutoSave);
                }
            })();
        @endforeach
    });
</script>
@endpush
