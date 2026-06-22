@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h2 fw-bold text-dark">
                <i class="bi bi-folder-fill text-warning me-2"></i>{{ $topic }}
            </h1>
            <p class="text-muted">Learning materials under this folder</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('student.contents.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> All Folders
            </a>
        </div>
    </div>

    @if($contents->count() > 0)
        <div class="row">
            @foreach($contents as $content)
                <div class="col-md-6 mb-4">
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
                                <i class="bi bi-eye me-1"></i> Read Content
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

    <div class="row mt-4">
        <div class="col-md-12">
            <a href="{{ route('student.contents.index') }}" class="btn btn-secondary">Back to Folders</a>
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
});
</script>
@endpush
