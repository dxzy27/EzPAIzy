@extends('layouts.dashboard')

@section('content')
<div class="container py-5">
    <div class="row mb-4 align-items-center">
        <div class="col-md-12">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
                <div>
                    <h1 class="h2 fw-bold text-dark mb-0">Flashcards</h1>
                    <p class="text-muted mb-0">Master key terms with flashcards</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Topics Folders -->
    @if(count($topics) > 0)
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
         <h5 class="text-muted fw-bold mb-0">TOPICS</h5>
         <div class="input-group shadow-sm" style="border-radius: 50px; overflow: hidden; width: 260px; height: 36px;">
            <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search text-muted" style="font-size: 0.85rem;"></i></span>
            <input type="text" id="topics-search" class="form-control border-0 ps-2" placeholder="Search topics..." style="font-size: 0.85rem;">
         </div>
    </div>
    <div class="row mb-5">
        @foreach($topics as $topic)
            <div class="col-sm-6 col-md-3 mb-4 topic-folder-col" data-topic-name="{{ strtolower($topic->name) }}">
                <a href="{{ route('student.flashcards.folder', ['topic' => $topic->name]) }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm border-0 {{ request('topic') == $topic->name ? 'bg-primary text-white' : 'bg-light text-dark' }} folder-card">
                        <div class="card-body text-center d-flex flex-column align-items-center justify-content-center p-4">
                            <i class="bi bi-folder-fill mb-3 {{ request('topic') == $topic->name ? 'text-white' : 'text-warning' }}" style="font-size: 3.2rem;"></i>
                            <span class="fw-bold text-wrap {{ request('topic') == $topic->name ? 'text-white' : 'text-dark' }}" style="line-height: 1.25; font-size: 0.98rem; letter-spacing: -0.1px;">{{ $topic->name }}</span>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
    @endif

    <div class="row mt-2">
        <div class="col-md-12 text-center py-3 text-muted">
            <i class="bi bi-folder2-open display-4 mb-2 d-block text-warning" style="opacity: .65;"></i>
            <h6 class="fw-bold text-dark">Open a Folder</h6>
            <p class="small text-muted mb-0">Select one of the folders above to view its flashcard sets.</p>
        </div>
    </div>


</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('topics-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const folders = document.querySelectorAll('.topic-folder-col');
            folders.forEach(function(folder) {
                const name = folder.getAttribute('data-topic-name');
                if (name.includes(query)) {
                    folder.style.setProperty('display', '', 'important');
                } else {
                    folder.style.setProperty('display', 'none', 'important');
                }
            });
        });
    }

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
