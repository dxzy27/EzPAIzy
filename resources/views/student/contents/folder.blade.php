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
                    <div class="card h-100 shadow-sm content-card border-0" style="border-radius: 16px; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0 fw-bold text-dark" style="font-size: 1.15rem; line-height: 1.4;">{{ $content->title }}</h5>
                                @php
                                    $isFavorited = in_array($content->id, $favoritedContentIds ?? []);
                                @endphp
                                <button class="btn btn-link p-0 text-warning favorite-btn ms-2" 
                                        data-id="{{ $content->id }}" 
                                        data-favorited="{{ $isFavorited ? 'true' : 'false' }}"
                                        title="{{ $isFavorited ? 'Remove from Revision' : 'Add to Revision' }}">
                                    <i class="bi {{ $isFavorited ? 'bi-star-fill' : 'bi-star' }} fs-5"></i>
                                </button>
                            </div>
                            
                            <p class="card-text text-muted mb-3" style="font-size: 0.9rem;">{{ Str::limit($content->content, 120) }}</p>
                            
                            <!-- Metadata Icons -->
                            <div class="d-flex flex-wrap gap-3 mb-4 text-muted" style="font-size: 0.85rem;">
                                <div class="d-flex align-items-center gap-1">
                                    <span>👨</span> <span>{{ $content->teacher->name ?? 'Unknown' }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <span>📅</span> <span>{{ $content->created_at->format('M d, Y') }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <span>📖</span> <span>{{ strtoupper($content->file_type ?? 'TEXT') }}</span>
                                </div>
                            </div>

                            <!-- Page Tracking / Progress -->
                            <div class="p-3 bg-light rounded-3 border border-light-subtle d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fs-4">📖</span>
                                    <div>
                                        <div class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Last Read</div>
                                        <div class="fw-bold text-dark small">
                                            @if($content->progress)
                                                Page {{ $content->progress->current_page }} of {{ $content->progress->total_pages }}
                                            @else
                                                Not started yet
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if($content->progress)
                                    @php
                                        $percent = min(100, max(0, round(($content->progress->current_page / $content->progress->total_pages) * 100)));
                                    @endphp
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">{{ $percent }}% done</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1">0%</span>
                                @endif
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="card-footer bg-white border-0 pt-0 pb-4 px-4 d-flex justify-content-between align-items-center">
                            @php
                                $fileType = strtoupper($content->file_type ?? 'TEXT');
                                $badgeStyle = '';
                                $badgeText = '';
                                if ($fileType === 'PDF') {
                                    $badgeStyle = 'background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2;';
                                    $badgeText = '🟥 PDF';
                                } elseif (in_array($fileType, ['DOC', 'DOCX'])) {
                                    $badgeStyle = 'background-color: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb;';
                                    $badgeText = '🟦 DOCX';
                                } elseif (in_array($fileType, ['PPT', 'PPTX'])) {
                                    $badgeStyle = 'background-color: #fffde7; color: #e65100; border: 1px solid #ffe082;';
                                    $badgeText = '🟨 PPT';
                                } else {
                                    $badgeStyle = 'background-color: #f5f5f5; color: #616161; border: 1px solid #e0e0e0;';
                                    $badgeText = '📝 ' . $fileType;
                                }
                            @endphp
                            <span class="badge px-3 py-2 rounded-pill fw-bold" style="{{ $badgeStyle }} font-size: 0.78rem;">
                                {!! $badgeText !!}
                            </span>
                            
                            <a href="{{ route('student.contents.show', $content) }}" class="btn btn-sm btn-primary px-3 rounded-pill fw-bold shadow-sm d-inline-flex align-items-center gap-1 py-2">
                                @if($content->progress)
                                    Continue Reading <i class="bi bi-arrow-right"></i>
                                @else
                                    Open Material <i class="bi bi-arrow-right"></i>
                                @endif
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
