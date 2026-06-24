@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-12">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('student.flashcards.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Folders">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <div>
                    <h1 class="h2 fw-bold text-dark mb-0">{{ $topic }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.flashcards.index') }}" class="text-decoration-none">Flashcards</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $topic }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <h5 class="text-muted fw-bold mt-2 mb-3">SETS IN {{ strtoupper($topic) }}</h5>

    @if($flashcardSets->count() > 0)
        <div class="row">
            @foreach($flashcardSets as $set)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm content-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0 fw-bold text-dark">{{ $set->title }}</h5>
                                @php
                                    $isFavorited = in_array($set->id, $favoritedFlashcardIds ?? []);
                                @endphp
                                <button class="btn btn-link p-0 text-warning favorite-btn" 
                                        data-id="{{ $set->id }}" 
                                        data-favorited="{{ $isFavorited ? 'true' : 'false' }}"
                                        title="{{ $isFavorited ? 'Remove from Revision' : 'Add to Revision' }}">
                                    <i class="bi {{ $isFavorited ? 'bi-star-fill' : 'bi-star' }} fs-5"></i>
                                </button>
                            </div>
                            <span class="badge bg-info mb-2 text-white">{{ $set->topic }}</span>
                            <p class="card-text text-muted">{{ Str::limit($set->description, 80) }}</p>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between text-muted small mb-1">
                                    <span>Mastery Progress</span>
                                    <span>{{ $set->stats->total > 0 ? round((($set->stats->mastered + $set->stats->review) / $set->stats->total) * 100) : 0 }}%</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $set->stats->total > 0 ? (($set->stats->mastered + $set->stats->review) / $set->stats->total) * 100 : 0 }}%" title="Mastered"></div>
                                    <div class="progress-bar" role="progressbar" style="width: {{ $set->stats->total > 0 ? ($set->stats->learning / $set->stats->total) * 100 : 0 }}%; background-color: #f97316;" title="Still Learning"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-2" style="font-size: 0.75rem;">
                                    <span class="text-secondary"><i class="bi bi-circle-fill text-secondary me-1"></i>{{ $set->stats->new }} New</span>
                                    <span style="color: #f97316; font-weight: 500;"><i class="bi bi-circle-fill me-1" style="color: #f97316;"></i>{{ $set->stats->learning }} Still Learning</span>
                                    <span class="text-success" style="font-weight: 500;"><i class="bi bi-circle-fill text-success me-1"></i>{{ $set->stats->mastered + $set->stats->review }} Mastered</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <a href="{{ route('student.flashcards.show', $set) }}" class="btn btn-primary w-100">
                                <i class="bi bi-play-circle"></i> Open Flashcards
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="row mt-4">
            <div class="col-md-12">
                {{ $flashcardSets->links() }}
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm text-center py-5" style="border-radius:14px;">
            <div class="text-muted">
                <i class="bi bi-folder-x fs-1 d-block mb-3 text-warning" style="opacity: .6;"></i>
                <h4 class="fw-bold text-dark">This folder is empty</h4>
                <p class="mb-0">No flashcard sets found in this folder.</p>
            </div>
        </div>
    @endif
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
