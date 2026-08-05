@extends('layouts.dashboard')

@push('styles')
<style>
    .folder-card {
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s ease !important;
        border-radius: 16px !important;
    }
    .folder-card:hover {
        transform: translateY(-4px) !important;
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.08) !important;
    }
    .delete-folder-btn {
        opacity: 0;
        transition: opacity 0.2s ease, transform 0.2s ease;
    }
    .topic-folder-col:hover .delete-folder-btn {
        opacity: 0.7;
    }
    .delete-folder-btn:hover {
        opacity: 1 !important;
        transform: scale(1.15);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1040px; margin: 0 auto;">
    <div class="row mb-4 align-items-center">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Dashboard">
                        <i class="bi bi-arrow-left fs-5"></i>
                    </a>
                    <div>
                        <h1 class="h2 fw-bold text-dark mb-0">My Quizzes</h1>
                        <p class="text-muted mb-0" style="font-size: 0.9rem;">Organize and generate quizzes for your students</p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary fw-semibold px-3" style="border-radius: 10px; font-size: 0.9rem;" data-bs-toggle="modal" data-bs-target="#createTopicModal">
                        <i class="bi bi-folder-plus me-1"></i> Add Folder
                    </button>
                    <a href="{{ route('teacher.quizzes.generate') }}" class="btn btn-primary fw-semibold px-3" style="border-radius: 10px; font-size: 0.9rem;">
                        <i class="bi bi-stars me-1"></i> AI Generate
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Topics Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
         <h5 class="text-muted fw-bold mb-0" style="font-size: 0.82rem; letter-spacing: 0.5px;">TOPICS</h5>
         <div class="input-group shadow-sm" style="border-radius: 50px; overflow: hidden; width: 260px; height: 36px;">
            <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search text-muted" style="font-size: 0.85rem;"></i></span>
            <input type="text" id="topics-search" class="form-control border-0 ps-2" placeholder="Search topics..." style="font-size: 0.85rem;">
         </div>
    </div>

    <!-- Topics Folders Grid -->
    @if($topics->count() > 0)
    <div class="row g-3 mb-5">
        @foreach($topics as $topic)
            <div class="col-sm-6 col-md-3 mb-3 position-relative group-action topic-folder-col" data-topic-name="{{ strtolower($topic->name) }}">
                <a href="{{ route('teacher.quizzes.folder', ['topic' => $topic->name]) }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm border-0 {{ request('topic') == $topic->name ? 'bg-primary text-white' : 'bg-light text-dark' }} folder-card">
                        <div class="card-body text-center d-flex flex-column align-items-center justify-content-center p-4">
                            <i class="bi bi-folder-fill mb-3 {{ request('topic') == $topic->name ? 'text-white' : 'text-warning' }}" style="font-size: 3.2rem;"></i>
                            <span class="fw-bold text-wrap {{ request('topic') == $topic->name ? 'text-white' : 'text-dark' }}" style="line-height: 1.25; font-size: 0.98rem; letter-spacing: -0.1px;">{{ $topic->name }}</span>
                        </div>
                    </div>
                </a>
                <form action="{{ route('teacher.topics.destroy', $topic) }}" method="POST" class="position-absolute top-0 end-0 mt-2 me-3" onsubmit="return confirm('Delete this folder? Contents will remain.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm text-danger p-0 border-0 bg-transparent delete-folder-btn" title="Delete Folder">
                        <i class="bi bi-x-circle-fill fs-6"></i>
                    </button>
                </form>
            </div>
        @endforeach
    </div>
    @else
    <div class="card border-0 shadow-sm text-center py-5" style="border-radius:16px;">
        <div class="text-muted">
            <i class="bi bi-folder-x fs-1 d-block mb-3 text-warning" style="opacity: .6;"></i>
            <h4 class="fw-bold text-dark">No Folders Found</h4>
            <p class="mb-3">Create a folder to start organizing quiz topics.</p>
            <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#createTopicModal">
                <i class="bi bi-folder-plus me-1"></i> Add Folder
            </button>
        </div>
    </div>
    @endif
    
    <!-- Create Topic Modal -->
    <div class="modal fade" id="createTopicModal" tabindex="-1" aria-labelledby="createTopicModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <form action="{{ route('teacher.topics.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="quiz">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="createTopicModalLabel">Add New Folder</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold" style="font-size: 0.9rem;">Folder Name</label>
                            <input type="text" class="form-control" id="name" name="name" required style="border-radius: 10px;">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light fw-semibold" data-bs-dismiss="modal" style="border-radius: 10px;">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-semibold" style="border-radius: 10px;">Create Folder</button>
                    </div>
                </form>
            </div>
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
    });
</script>
@endpush
