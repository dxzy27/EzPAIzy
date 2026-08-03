@extends('layouts.dashboard')

@section('content')
<div class="container py-5">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ url()->previous() === url()->current() ? route('student.dashboard') : url()->previous() }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back">
                    <i class="bi bi-arrow-left fs-5"></i>
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
                    
                    $typeLabel = '';
                    $bgStyle = '';
                    $badgeStyle = '';
                    $btnClass = '';
                    
                    if ($isContent) {
                        $typeLabel = '📄 Other';
                        $bgStyle = 'border-left: 5px solid #1565c0;';
                        $badgeStyle = 'background-color: #e3f2fd; color: #1565c0;';
                        $btnClass = 'btn-primary';
                    } elseif ($isFlashcard) {
                        $typeLabel = '🎴 Flashcard';
                        $bgStyle = 'border-left: 5px solid #ff8f00;';
                        $badgeStyle = 'background-color: #fff8e1; color: #ff8f00;';
                        $btnClass = 'btn-warning text-dark';
                    } else {
                        $typeLabel = '❓ Quiz';
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
                    <div class="card h-100 shadow-sm border-0" style="border-radius: 16px; {{ $bgStyle }} overflow: hidden;">
                        <div class="card-body d-flex flex-column justify-content-between" style="padding: 1.15rem;">
                            <div>
                                <!-- Top row: Badge and Trash Icon -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge px-2.5 py-1.5 rounded-pill fw-bold small d-inline-flex align-items-center gap-1" style="{{ $badgeStyle }}">
                                        {!! $typeLabel !!}
                                    </span>
                                    <button class="btn btn-link p-0 text-muted remove-favorite-btn" 
                                            style="font-size: 1.1rem; line-height: 1;"
                                            data-url="{{ $deleteApiUrl }}"
                                            title="Remove from revision">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                                
                                <!-- Title -->
                                <h5 class="card-title fw-extrabold text-dark mb-2" style="font-size: 1.15rem; line-height: 1.4; font-weight: 800;">{{ $item->title }}</h5>
                                
                                <!-- Inline Metadata Details -->
                                <div class="text-muted mb-3" style="font-size: 0.8rem; line-height: 1.5;">
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <span>👤 By: {{ $isContent ? ($item->teacher->name ?? 'Unknown') : ($isQuiz ? ($item->teacher->name ?? 'Unknown') : ($item->user->name ?? 'Unknown')) }}</span>
                                        <span class="text-muted-opacity">•</span>
                                        @if($isFlashcard)
                                            <span>🎴 {{ $item->flashcards()->count() }} Cards</span>
                                        @elseif($isContent)
                                            <span>📄 {{ strtoupper($item->file_type ?? 'TEXT') }}</span>
                                        @else
                                            @php
                                                $qCount = \App\Models\Question::where('topic', $item->topic)->where('difficulty', $item->difficulty)->count();
                                            @endphp
                                            <span>❓ {{ $qCount }} Questions</span>
                                        @endif
                                    </div>
                                    <div class="mt-1 d-flex flex-wrap align-items-center gap-2" style="font-size: 0.76rem;">
                                        <span class="text-primary fw-bold">⭐ Saved {{ $fav->created_at->diffForHumans() }}</span>
                                        <span class="text-black-50">|</span>
                                        <span class="text-black-50">Uploaded: {{ $item->created_at->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Subtle Divider -->
                            <hr class="my-2" style="border-top: 1px solid rgba(0,0,0,0.06); opacity: 1;">
                            
                            <!-- Primary Action Button -->
                            <div class="d-grid">
                                <a href="{{ $viewRoute }}" class="btn btn-sm {{ $btnClass }} rounded-pill fw-bold shadow-sm d-inline-flex align-items-center justify-content-center gap-1" style="padding-top: 0.35rem; padding-bottom: 0.35rem; font-size: 0.85rem;">
                                    @if($isQuiz)
                                        Take Quiz <i class="bi bi-arrow-right"></i>
                                    @else
                                        Open {{ $isContent ? 'Material' : 'Flashcard Set' }} <i class="bi bi-arrow-right"></i>
                                    @endif
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info" role="alert">
            <h4 class="alert-heading fw-bold"><i class="bi bi-info-circle"></i> No Content in Revision List</h4>
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
@endpush

<style>
.revision-card-col {
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.remove-favorite-btn:hover {
    color: #dc3545 !important;
}
</style>
