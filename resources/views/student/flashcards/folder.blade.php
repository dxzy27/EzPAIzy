@extends('layouts.dashboard')

@section('content')
<div class="container py-5">
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

    <div class="d-flex justify-content-between align-items-center mt-2 mb-3 flex-wrap gap-2">
         <h5 class="text-muted fw-bold mb-0">SETS IN {{ strtoupper($topic) }}</h5>
         <div class="input-group shadow-sm" style="border-radius: 50px; overflow: hidden; width: 260px; height: 36px;">
            <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search text-muted" style="font-size: 0.85rem;"></i></span>
            <input type="text" id="set-search" class="form-control border-0 ps-2" placeholder="Search sets..." style="font-size: 0.85rem;">
         </div>
    </div>

    @if($flashcardSets->count() > 0)
        <div class="row">
            @foreach($flashcardSets as $set)
                <div class="col-md-4 mb-4 set-card-col" data-title="{{ strtolower($set->title) }}">
                    <div class="card h-100 shadow-sm content-card">
                        <div class="card-body p-3 pb-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="card-title mb-0 fw-bold text-dark fs-6">{{ $set->title }}</h6>
                                @php
                                    $isFavorited = in_array($set->id, $favoritedFlashcardIds ?? []);
                                @endphp
                                <button class="btn btn-link favorite-btn p-0 d-flex align-items-center justify-content-center border-0 text-decoration-none" 
                                        data-id="{{ $set->id }}" 
                                        data-favorited="{{ $isFavorited ? 'true' : 'false' }}"
                                        title="{{ $isFavorited ? 'Remove from Revision' : 'Add to Revision' }}"
                                        style="width: 28px; height: 28px; background: transparent; box-shadow: none;">
                                    <i class="bi {{ $isFavorited ? 'bi-star-fill' : 'bi-star' }} text-warning" style="font-size: 1.15rem; line-height: 1; transition: transform 0.15s ease;"></i>
                                </button>
                            </div>
                             <span class="badge mb-2" style="font-size: 0.75rem; padding: 0.3rem 0.6rem; font-weight: 600; background-color: #ECECEC; color: #4B5563; border-radius: 6px;">{{ $set->topic }}</span>
                            @if(!empty($set->description))
                                <p class="card-text text-muted small mb-2">{{ Str::limit($set->description, 60) }}</p>
                            @endif
                            <div class="mb-2">
                                <div class="d-flex justify-content-between text-muted small mb-1" style="font-size: 0.75rem;">
                                    <span>Mastery Progress</span>
                                    <span class="fw-bold text-dark">{{ $set->stats->total > 0 ? round((($set->stats->mastered + $set->stats->review) / $set->stats->total) * 100) : 0 }}%</span>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 50px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $set->stats->total > 0 ? (($set->stats->mastered + $set->stats->review) / $set->stats->total) * 100 : 0 }}%" title="Mastered"></div>
                                    <div class="progress-bar" role="progressbar" style="width: {{ $set->stats->total > 0 ? ($set->stats->learning / $set->stats->total) * 100 : 0 }}%; background-color: #f97316;" title="Still Learning"></div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <span class="badge d-inline-flex align-items-center gap-1 py-1 px-2" style="border-radius: 30px; font-weight: 500; font-size: 0.7rem; background-color: #F3F4F6; border: 1px solid #E5E7EB; color: #6B7280;">
                                        <span style="width: 5px; height: 5px; border-radius: 50%; background-color: #6B7280; display: inline-block;"></span>
                                        {{ $set->stats->new }} New
                                    </span>
                                    <span class="badge d-inline-flex align-items-center gap-1 py-1 px-2" style="background-color: rgba(249, 115, 22, 0.08); color: #f97316; border: 1px solid rgba(249, 115, 22, 0.15); border-radius: 30px; font-weight: 500; font-size: 0.7rem;">
                                        <span style="width: 5px; height: 5px; border-radius: 50%; background-color: #f97316; display: inline-block;"></span>
                                        Learning {{ $set->stats->learning }}
                                    </span>
                                    <span class="badge d-inline-flex align-items-center gap-1 py-1 px-2" style="background-color: rgba(25, 135, 84, 0.08); color: #198754; border: 1px solid rgba(25, 135, 84, 0.15); border-radius: 30px; font-weight: 500; font-size: 0.7rem;">
                                        <span style="width: 5px; height: 5px; border-radius: 50%; background-color: #198754; display: inline-block;"></span>
                                        Mastered {{ $set->stats->mastered + $set->stats->review }}
                                    </span>
                                </div>
                            </div>
                            <hr class="my-3" style="opacity: 0.1; border-color: #000;">
                            <div class="d-flex flex-column align-items-center gap-1">
                                <a href="{{ route('student.flashcards.show', $set) }}" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center" style="height: 42px; font-weight: 600; border-radius: 10px; font-size: 0.9rem;">
                                    Open Flashcards <i class="bi bi-arrow-right ms-2"></i>
                                </a>
                                <form action="{{ route('student.flashcards.reset', $set) }}" method="POST" class="m-0 w-100 text-center reset-progress-form">
                                    @csrf
                                    <button type="button" class="btn btn-link btn-sm p-0 reset-link" onclick="confirmReset(this)">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Reset Progress
                                    </button>
                                </form>
                            </div>
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

<!-- Reset Confirmation Modal -->
<div class="modal fade" id="resetConfirmModal" tabindex="-1" aria-labelledby="resetConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0 pb-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center pt-0 pb-4 px-4">
        <div class="mb-3">
            <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-circle" style="width: 60px; height: 60px;">
                <i class="bi bi-exclamation-triangle fs-2"></i>
            </div>
        </div>
        <h5 class="fw-bold mb-2">Reset Progress?</h5>
        <p class="text-muted small mb-4">This action cannot be undone. All your mastery progress for this set will be wiped.</p>
        <div class="d-flex gap-2 w-100">
            <button type="button" class="btn btn-light w-50 fw-bold" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger w-50 fw-bold" id="confirmResetBtn">Reset</button>
        </div>
      </div>
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

    // Live Search
    const searchInput = document.getElementById('set-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.set-card-col');
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
                        <h5>No flashcard sets match your search.</h5>
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

let currentResetForm = null;
function confirmReset(btn) {
    currentResetForm = btn.closest('form');
    const resetModal = new bootstrap.Modal(document.getElementById('resetConfirmModal'));
    resetModal.show();
}

document.getElementById('confirmResetBtn').addEventListener('click', function() {
    if (currentResetForm) {
        this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Resetting...';
        this.disabled = true;
        currentResetForm.submit();
    }
});
</script>
@endpush

<style>
    .reset-link {
        color: #7A7A7A !important;
        font-size: 12px !important;
        font-weight: 500;
        text-decoration: none !important;
        transition: color 0.15s;
    }
    .reset-link:hover {
        text-decoration: underline !important;
        color: #4a4a4a !important;
    }
    .favorite-btn:hover i {
        transform: scale(1.2);
    }
</style>
