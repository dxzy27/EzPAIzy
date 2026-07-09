@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Dashboard">
                    <i class="bi bi-house-door fs-5"></i>
                </a>
                <div>
                    <h1 class="h2 fw-bold text-dark mb-0">📚 My Revision List</h1>
                    <p class="text-muted mb-0">Learning materials you've saved for review</p>
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
                    $isQuiz = !empty($fav->topic) && !empty($fav->difficulty);
                    if ($isQuiz) {
                        $item = new \stdClass();
                        $item->topic = $fav->topic;
                        $item->difficulty = $fav->difficulty;
                        $item->title = $fav->topic . ' (' . ucfirst($fav->difficulty) . ')';
                        $item->description = 'Practice questions for ' . $fav->topic . ' (' . $fav->difficulty . ')';
                        $item->created_at = $fav->created_at;
                        $item->teacher = new \stdClass();
                        $item->teacher->name = 'PAI Teacher';
                    } else {
                        $item = $fav->content ?? $fav->flashcardSet;
                    }
                    
                    // Skip if item was deleted but favorite record remains (safety check)
                    if(!$item) continue;
                    
                    $isContent = !empty($fav->content);
                    $isFlashcard = !empty($fav->flashcardSet);
                    
                    $typeLabel = $isContent ? 'Content' : ($isFlashcard ? 'Flashcard Set' : 'Quiz');
                    $icon = $isContent ? 'bi-file-text' : ($isFlashcard ? 'bi-card-list' : 'bi-patch-question');
                    $bgClass = $isContent ? 'border-primary' : ($isFlashcard ? 'border-warning' : 'border-info');
                    $btnClass = $isContent ? 'btn-primary' : ($isFlashcard ? 'btn-warning' : 'btn-info text-white');
                    
                    $viewRoute = $isContent 
                        ? route('student.contents.show', $item) 
                        : ($isFlashcard ? route('student.flashcards.show', $item) : route('student.quiz.take', ['topic' => $item->topic, 'difficulty' => $item->difficulty]));
                        
                    $deleteApiUrl = $isContent 
                        ? "/student/favorites/{$item->id}" 
                        : ($isFlashcard ? "/student/favorites/flashcard/{$item->id}" : "/student/favorites/quiz/{$item->topic}/{$item->difficulty}");
                @endphp
                <div class="col-md-6 mb-4 revision-card-col" data-title="{{ strtolower($item->title) }}">
                    <div class="card h-100 shadow-sm {{ $bgClass }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge {{ $isContent ? 'bg-primary' : ($isFlashcard ? 'bg-warning text-dark' : 'bg-info text-white') }} mb-2"><i class="bi {{ $icon }} me-1"></i>{{ $typeLabel }}</span>
                                    <h5 class="card-title">{{ $item->title }}</h5>
                                </div>
                                <button 
                                    class="btn btn-sm btn-outline-danger remove-favorite-btn" 
                                    data-url="{{ $deleteApiUrl }}"
                                    title="Remove from revision">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                            <p class="card-text text-muted">{{ Str::limit($isContent ? $item->content : $item->description, 150) }}</p>
                            <p class="text-muted small">
                                <i class="bi bi-person"></i> By: {{ $isContent ? ($item->teacher->name ?? 'Unknown') : ($isQuiz ? ($item->teacher->name ?? 'Unknown') : ($item->user->name ?? 'Unknown')) }}<br>
                                <i class="bi bi-calendar"></i> Created: {{ $item->created_at->format('M d, Y') }}<br>
                                <i class="bi bi-star-fill text-warning"></i> Added: {{ $fav->created_at->format('M d, Y') }}
                            </p>
                        </div>
                        <div class="card-footer bg-light">
                            <a href="{{ $viewRoute }}" class="btn btn-sm {{ $btnClass }}">
                                <i class="bi bi-eye"></i> View {{ $typeLabel }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info" role="alert">
            <h4 class="alert-heading"><i class="bi bi-info-circle"></i> No Content in Revision List</h4>
            <p>You haven't added any learning materials to your revision list yet.</p>
            <hr>
            <p class="mb-0">
                Browse <a href="{{ route('student.contents.index') }}" class="alert-link">Learning Materials</a>, 
                <a href="{{ route('student.flashcards.index') }}" class="alert-link">Flashcards</a>, or 
                <a href="{{ route('student.quizzes') }}" class="alert-link">Quizzes</a>
                and click the star button to save them.
            </p>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const removeButtons = document.querySelectorAll('.remove-favorite-btn');
    
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (!confirm('Remove this item from your revision list?')) {
                return;
            }
            
            const url = this.dataset.url;
            const card = this.closest('.col-md-6');
            
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
                    // Remove card with animation
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.remove();
                        
                         // Check if no more favorites
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

            // Handle no results message
            let noResultsMsg = document.getElementById('no-results-msg');
            if (!hasVisible) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.id = 'no-results-msg';
                    noResultsMsg.className = 'col-12 text-center py-5';
                    noResultsMsg.innerHTML = `
                        <i class="bi bi-search display-3 text-muted mb-3"></i>
                        <h5>No revision items match your search.</h5>
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

<style>
.col-md-6 {
    transition: opacity 0.3s ease;
}
</style>
@endsection
