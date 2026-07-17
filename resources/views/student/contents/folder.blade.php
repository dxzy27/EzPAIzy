@extends('layouts.dashboard')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('student.contents.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Folders">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <div>
                    <h1 class="h2 fw-bold text-dark mb-0">
                        <i class="bi bi-folder-fill text-warning me-2"></i>{{ $topic }}
                    </h1>
                    <p class="text-muted mb-0">Learning materials under this folder</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end d-none d-md-block">
             <div class="input-group shadow-sm ms-auto" style="border-radius: 50px; overflow: hidden; width: 300px;">
                <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="material-search" class="form-control border-0 ps-2" placeholder="Search materials...">
             </div>
        </div>
    </div>

    @if($contents->count() > 0)
        <div class="row">
            @foreach($contents as $content)
                <div class="col-md-6 mb-4 material-card-col" data-title="{{ strtolower($content->title) }}">
                    <div class="card h-100 shadow-sm content-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0 fw-bold text-dark">{{ $content->title }}</h5>
                                @php
                                    $isFavorited = in_array($content->id, $favoritedContentIds ?? []);
                                @endphp
                                <button class="btn btn-link p-0 text-warning favorite-btn" 
                                        data-id="{{ $content->id }}" 
                                        data-favorited="{{ $isFavorited ? 'true' : 'false' }}"
                                        title="{{ $isFavorited ? 'Remove from Revision' : 'Add to Revision' }}">
                                    <i class="bi {{ $isFavorited ? 'bi-star-fill' : 'bi-star' }} fs-5"></i>
                                </button>
                            </div>
                            <p class="card-text text-muted">{{ Str::limit($content->content, 150) }}</p>
                            <p class="text-muted small mb-1">
                                <i class="bi bi-person-circle text-secondary me-1"></i> Teacher: {{ $content->teacher->name ?? 'Unknown' }}
                            </p>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-calendar text-secondary me-1"></i>
                                Created: {{ $content->created_at->format('M d, Y') }}
                            </p>
                        </div>
                        <div class="card-footer bg-light border-0 d-flex justify-content-between align-items-center">
                            <span class="badge bg-secondary text-white">{{ $content->file_type ?? 'Text' }}</span>
                            <a href="{{ route('student.contents.show', $content) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye me-1"></i> Read
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="row mt-4">
            <div class="col-md-12">
                {{ $contents->links() }}
            </div>
        </div>
    @else
        <div class="alert alert-info text-center py-5" role="alert">
            <i class="bi bi-info-circle display-4 d-block mb-3 text-info"></i>
            <h4 class="alert-heading fw-bold">No Materials in this Folder</h4>
            <p>Your instructor hasn't added any learning materials to the <strong>{{ $topic }}</strong> folder yet.</p>
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
            const contentId = this.dataset.id;
            const isFavorited = this.dataset.favorited === 'true';
            const icon = this.querySelector('i');
            
            // Optimistic UI Update
            if (isFavorited) {
                // Remove
                this.dataset.favorited = 'false';
                this.title = 'Add to Revision';
                icon.classList.remove('bi-star-fill');
                icon.classList.add('bi-star');
                
                fetch(`/student/favorites/${contentId}`, {
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
                
                fetch(`/student/favorites/${contentId}`, {
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
    const searchInput = document.getElementById('material-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.material-card-col');
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
                        <h5>No materials match your search.</h5>
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
