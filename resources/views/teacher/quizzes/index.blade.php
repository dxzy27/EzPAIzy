@extends('layouts.dashboard')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-column mb-4">
        <div class="align-self-start mb-3">
            <a href="{{ route('teacher.dashboard') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h2 fw-bold text-dark mb-0">My Quizzes</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('teacher.quizzes.generate') }}" class="btn btn-dark">
                    <i class="bi bi-cpu me-1"></i> Generate with AI
                </a>
            </div>
        </div>
    </div>



    <!-- Topics Header -->
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h5 class="text-muted fw-bold mb-0">TOPICS</h5>
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createTopicModal">
                <i class="bi bi-folder-plus me-1"></i> Add Folder
            </button>
        </div>
    </div>

    <!-- Topics Folders -->
    @if($topics->count() > 0)
    <div class="row mb-4">
        @foreach($topics as $topic)
            <div class="col-md-2 mb-3 position-relative group-action">
                <a href="{{ route('teacher.quizzes.folder', ['topic' => $topic->name]) }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm border-0 {{ request('topic') == $topic->name ? 'bg-primary text-white' : 'bg-light text-dark' }} folder-card">
                        <div class="card-body text-center d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-folder-fill fs-1 mb-2 {{ request('topic') == $topic->name ? 'text-white' : 'text-warning' }}"></i>
                            <span class="fw-bold small text-wrap" style="line-height: 1.2;">{{ $topic->name }}</span>
                        </div>
                    </div>
                </a>
                <form action="{{ route('teacher.topics.destroy', $topic) }}" method="POST" class="position-absolute top-0 end-0 mt-1 me-2" onsubmit="return confirm('Delete this folder? Contents will remain.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm text-danger p-0 border-0 bg-transparent" title="Delete Folder">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </form>
            </div>
        @endforeach
    </div>
    @endif
    
    <!-- Create Topic Modal -->
    <div class="modal fade" id="createTopicModal" tabindex="-1" aria-labelledby="createTopicModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('teacher.topics.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="quiz">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createTopicModalLabel">Add New Folder</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Folder Name</label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="e.g. Chapter 1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Folder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- No folder selected prompt -->
    @if($topics->count() > 0)
    <div class="row mt-4">
        <div class="col-md-12 text-center py-5 text-muted">
            <i class="bi bi-folder2-open display-2 mb-3 d-block text-warning" style="opacity: .6;"></i>
            <h5 class="fw-bold">Open a Folder</h5>
            <p class="small text-muted mb-0">Select one of the folders above to view its quizzes.</p>
        </div>
    </div>
    @endif
    

</div>
@endsection
