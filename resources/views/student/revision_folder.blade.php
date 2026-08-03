@extends('layouts.dashboard')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('student.revision') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Folders">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <div>
                    <h1 class="h2 fw-bold text-dark mb-0">
                        <i class="bi bi-folder-fill text-warning me-2"></i>{{ $topic }}
                    </h1>
                    <p class="text-muted mb-0">Saved items under this folder</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end d-none d-md-block">
             <div class="input-group shadow-sm ms-auto" style="border-radius: 50px; overflow: hidden; width: 300px;">
                <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="revision-search" class="form-control border-0 ps-2" placeholder="Search revision list...">
             </div>
        </div>
    </div>

    @if($favorites->count() > 0)
        <div class="row">
            @foreach($favorites as $fav)
                @php
                    $isQuiz = !empty($fav->quiz_topic) && !empty($fav->quiz_difficulty);
                    if ($isQuiz) {
                        $item = new \stdClass();
                        $item->topic = $fav->quiz_topic;
                        $item->difficulty = $fav->quiz_difficulty;
                        $item->title = $fav->quiz_topic . ' (' . ucfirst($fav->quiz_difficulty) . ')';
                        $item->description = 'Practice questions for ' . $fav->quiz_topic . ' (' . $fav->quiz_difficulty . ')';
                        $item->created_at = $fav->created_at;
                        $item->teacher = new \stdClass();
                        $item->teacher->name = $teacherName ?? 'PAI Teacher';
                    } else {
                        $item = $fav->content ?? $fav->flashcardSet;
                    }
                    
                    // Skip if item was deleted but favorite record remains (safety check)
                    if(!$item) continue;
                    
                    $isContent = !empty($fav->content);
                    $isFlashcard = !empty($fav->flashcardSet);
                    
                    $typeLabel = $isContent ? 'Other' : ($isFlashcard ? 'Flashcard Set' : 'Quiz');
                    $icon = $isContent ? 'bi-file-text' : ($isFlashcard ? 'bi-card-list' : 'bi-patch-question');
                    
                    $bgStyle = '';
                    $badgeStyle = '';
                    $btnClass = '';
                    
                    if ($isContent) {
                        $bgStyle = 'border-left: 5px solid #1565c0;';
                        $badgeStyle = 'background-color: #e3f2fd; color: #1565c0;';
                        $btnClass = 'btn-primary';
                    } elseif ($isFlashcard) {
                        $bgStyle = 'border-left: 5px solid #ff8f00;';
                        $badgeStyle = 'background-color: #fff8e1; color: #ff8f00;';
                        $btnClass = 'btn-warning text-dark';
                    } else {
                        $bgStyle = 'border-left: 5px solid #00a896;';
                        $badgeStyle = 'background-color: #e0f2f1; color: #00a896;';
                        $btnClass = 'btn-info text-white';
                    }
                    
                    $viewRoute = $isContent 
                        ? route('student.contents.show', $item) 
                        : ($isFlashcard ? route('student.flashcards.show', $item) : route('student.quiz.take', ['topic' => $item->topic, 'difficulty' => $item->difficulty]));
                        
                    $deleteApiUrl = $isContent 
                        ? "/student/favorites/{$item->id}" 
                        : ($isFlashcard ? "/student/favorites/flashcard/{$item->id}" : "/student/favorites/quiz/{$item->topic}/{$item->difficulty}");
                @endphp
                <div class="col-md-6 mb-4 revision-card-col" data-title="{{ strtolower($item->title) }}">
                    <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; {{ $bgStyle }} overflow: hidden;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge px-2.5 py-1.5 rounded-pill fw-bold mb-2 small d-inline-flex align-items-center gap-1" style="{{ $badgeStyle }}">
                                        <i class="bi {{ $icon }}"></i> {{ $typeLabel }}
                                    </span>
                                    <h5 class="card-title fw-bold text-dark mb-0">{{ $item->title }}</h5>
                                </div>
                                <button 
                                    class="btn btn-sm btn-outline-danger remove-favorite-btn rounded-circle p-2 d-inline-flex align-items-center justify-content-center" 
                                    style="width: 32px; height: 32px;"
                                    data-url="{{ $deleteApiUrl }}"
                                    title="Remove from revision">
                                    <i class="bi bi-x-lg" style="font-size: 0.75rem;"></i>
                                </button>
                            </div>
                            <p class="card-text text-muted mb-3" style="font-size: 0.9rem;">{{ Str::limit($isContent ? $item->content : $item->description, 120) }}</p>
                            
                            <!-- Inline Metadata with Bullets -->
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-3 text-muted small" style="font-size: 0.8rem;">
                                <span>👤 By: {{ $isContent ? ($item->teacher->name ?? 'Unknown') : ($isQuiz ? ($item->teacher->name ?? 'Unknown') : ($item->user->name ?? 'Unknown')) }}</span>
                                <span class="text-muted-opacity">•</span>
                                <span>📅 Created: {{ $item->created_at->format('M d, Y') }}</span>
                                <span class="text-muted-opacity">•</span>
                                <span>⭐ Added: {{ $fav->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pt-0 pb-4 px-4 d-flex justify-content-end">
                            <a href="{{ $viewRoute }}" class="btn btn-sm {{ $btnClass }} px-3 rounded-pill fw-bold shadow-sm d-inline-flex align-items-center gap-1">
                                View {{ $typeLabel }} <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info text-center py-5" role="alert">
            <i class="bi bi-folder2-open display-4 d-block mb-3 text-info"></i>
            <h4 class="alert-heading fw-bold">No Items in this Folder</h4>
            <p>You have removed all items from your revision list under the <strong>{{ $topic }}</strong> topic.</p>
            <a href="{{ route('student.revision') }}" class="btn btn-primary btn-sm rounded-pill mt-3 px-4 fw-bold">
                Back to folders
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const removeButtons = document.querySelectorAll('.remove-favorite-btn');
    
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (!confirm('Remove this item from your revision list?')) {
                return;
            }
            
            const url = this.dataset.url;
            const card = this.closest('.revision-card-col');
            
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        card.remove();
                         const remainingCards = document.querySelectorAll('.revision-card-col');
                         if (remainingCards.length === 0) {
                             location.reload();
                         }
                    }, 300);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    });

    // Live Search
    const searchInput = document.getElementById('revision-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.revision-card-col');
            let hasVisible = false;
            
            cards.forEach(card => {
                const title = card.dataset.title || '';
                if (title.includes(query)) {
                    card.style.setProperty('display', '', 'important');
                    hasVisible = true;
                } else {
                    card.style.setProperty('display', 'none', 'important');
                }
            });

            let noResultsMsg = document.getElementById('no-results-msg');
            if (!hasVisible) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.id = 'no-results-msg';
                    noResultsMsg.className = 'col-12 text-center py-5';
                    noResultsMsg.innerHTML = `
                        <i class="bi bi-search display-3 text-muted mb-3"></i>
                        <h5>No items match your search.</h5>
                        <p class="text-muted small">Try checking for typos or searching a different term.</p>
                    `;
                    document.querySelector('.row').appendChild(noResultsMsg);
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
