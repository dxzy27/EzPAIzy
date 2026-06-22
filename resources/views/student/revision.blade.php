@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>📚 My Revision List</h1>
            <p class="text-muted">Learning materials you've saved for review</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('student.dashboard') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    @if($favorites->count() > 0)
        <div class="row">
            @foreach($favorites as $fav)
                @php
                    $item = $fav->content ?? $fav->flashcardSet;
                    // Skip if item was deleted but favorite record remains (safety check)
                    if(!$item) continue;
                    
                    $isContent = !empty($fav->content);
                    $typeLabel = $isContent ? 'Content' : 'Flashcard Set';
                    $icon = $isContent ? 'bi-file-text' : 'bi-card-list';
                    $bgClass = $isContent ? 'border-primary' : 'border-warning';
                    $viewRoute = $isContent ? route('student.contents.show', $item) : route('student.flashcards.show', $item);
                    $deleteApiUrl = $isContent ? "/student/favorites/{$item->id}" : "/favorites/flashcard/{$item->id}";
                @endphp
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm {{ $bgClass }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge {{ $isContent ? 'bg-primary' : 'bg-warning text-dark' }} mb-2"><i class="bi {{ $icon }} me-1"></i>{{ $typeLabel }}</span>
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
                                <i class="bi bi-person"></i> By: {{ $isContent ? ($item->teacher->name ?? 'Unknown') : ($item->user->name ?? 'Unknown') }}<br>
                                <i class="bi bi-calendar"></i> Created: {{ $item->created_at->format('M d, Y') }}<br>
                                <i class="bi bi-star-fill text-warning"></i> Added: {{ $fav->created_at->format('M d, Y') }}
                            </p>
                        </div>
                        <div class="card-footer bg-light">
                            <a href="{{ $viewRoute }}" class="btn btn-sm {{ $isContent ? 'btn-primary' : 'btn-warning' }}">
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
                Browse <a href="{{ route('student.contents.index') }}" class="alert-link">Learning Materials</a> or 
                <a href="{{ route('student.flashcards.index') }}" class="alert-link">Flashcards</a>
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
                        const remainingCards = document.querySelectorAll('.col-md-6');
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
});
</script>

<style>
.col-md-6 {
    transition: opacity 0.3s ease;
}
</style>
@endsection
