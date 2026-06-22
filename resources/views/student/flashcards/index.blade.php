@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h2 fw-bold text-dark">Flashcards</h1>
            <p class="text-muted">Master key terms with flashcards</p>
        </div>
    </div>

    <!-- Topics Folders -->
    @if(count($topics) > 0)
    <div class="d-flex justify-content-between align-items-center mb-2">
         <h5 class="text-muted fw-bold mb-0">TOPICS</h5>
    </div>
    <div class="row mb-5">
        @foreach($topics as $topic)
            <div class="col-md-2 mb-3">
                <a href="{{ route('student.flashcards.folder', ['topic' => $topic->name]) }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm border-0 {{ request('topic') == $topic->name ? 'bg-primary text-white' : 'bg-light text-dark' }} folder-card">
                        <div class="card-body text-center d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-folder-fill fs-1 mb-2 {{ request('topic') == $topic->name ? 'text-white' : 'text-warning' }}"></i>
                            <span class="fw-bold small text-wrap" style="line-height: 1.2;">{{ $topic->name }}</span>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
    @endif

    <div class="row mt-4">
        <div class="col-md-12 text-center py-5 text-muted">
            <i class="bi bi-folder2-open display-2 mb-3 d-block text-warning" style="opacity: .6;"></i>
            <h5 class="fw-bold">Open a Folder</h5>
            <p class="small text-muted mb-0">Select one of the folders above to view its flashcard sets.</p>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <a href="{{ route('student.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const favoriteBtns = document.querySelectorAll('.favorite-btn');
    
    favoriteBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const setId = this.dataset.id;
            const isFavorited = this.dataset.favorited === 'true';
            const icon = this.querySelector('i');
            
            // Optimistic UI Update
            if (isFavorited) {
                // Remove
                this.dataset.favorited = 'false';
                this.title = 'Add to Revision';
                icon.classList.remove('bi-star-fill');
                icon.classList.add('bi-star');
                
                fetch(`/student/favorites/flashcard/${setId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
            } else {
                // Add
                this.dataset.favorited = 'true';
                this.title = 'Remove from Revision';
                icon.classList.remove('bi-star');
                icon.classList.add('bi-star-fill');
                
                fetch(`/student/favorites/flashcard/${setId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
            }
        });
    });
});
</script>
@endpush
