@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('teacher.contents.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Folders">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <div>
                    <h1 class="h2 fw-bold text-dark mb-0">{{ $topic }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('teacher.contents.index') }}" class="text-decoration-none">Other Materials</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $topic }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('teacher.contents.create', ['topic' => $topic]) }}" class="btn btn-primary">+ Add Content</a>
        </div>
    </div>

    @if($contents->count() > 0 || (isset($flashcardSets) && count($flashcardSets) > 0))
        
        <!-- Materials Section -->
        @if($contents->count() > 0)
            <h5 class="text-muted fw-bold mt-2 mb-3">FILES & NOTES</h5>
            <div class="row mb-4">
                @foreach($contents as $content)
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title fw-bold text-dark">{{ $content->title }}</h5>
                                @if($content->file_path)
                                    <div class="mb-2">
                                        <span class="badge bg-secondary"><i class="bi bi-paperclip"></i> {{ strtoupper($content->file_type ?? 'FILE') }}</span>
                                    </div>
                                @endif
                                <p class="card-text text-muted">{{ Str::limit($content->content, 120) }}</p>
                                <p class="text-muted small">Created: {{ $content->created_at->format('M d, Y') }}</p>
                            </div>
                            <div class="card-footer bg-light d-flex justify-content-between align-items-center">
                                <a href="{{ route('teacher.contents.show', $content) }}" class="btn btn-sm btn-info text-white">View</a>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('teacher.contents.edit', $content) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('teacher.contents.destroy', $content) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <!-- Pagination -->
            <div class="row mt-4 mb-4">
                <div class="col-md-12">
                    {{ $contents->links() }}
                </div>
            </div>
        @endif

    @else
        <div class="card border-0 shadow-sm text-center py-5" style="border-radius:14px;">
            <div class="text-muted">
                <i class="bi bi-folder-x fs-1 d-block mb-3 text-warning" style="opacity: .6;"></i>
                <h4 class="fw-bold text-dark">This folder is empty</h4>
                <p class="mb-4">You haven't created any contents in this folder yet.</p>
                <a href="{{ route('teacher.contents.create', ['topic' => $topic]) }}" class="btn btn-primary">Create Your First Content</a>
            </div>
        </div>
    @endif
</div>
@endsection
