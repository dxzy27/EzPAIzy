@extends('layouts.dashboard')

@section('content')
<div class="container-fluid px-4 py-5">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h1 class="display-6 fw-bold text-dark mb-2">Available Quizzes</h1>
            <p class="text-muted lead mb-0">Test your knowledge with these quizzes.</p>
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
                <a href="{{ route('student.quizzes.folder', ['topic' => $topic->name]) }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm border-0 bg-light text-dark folder-card" style="transition: transform 0.2s;">
                        <div class="card-body text-center d-flex flex-column align-items-center justify-content-center p-3">
                            <i class="bi bi-folder-fill fs-1 mb-2 text-warning"></i>
                            <span class="fw-bold small text-wrap" style="line-height: 1.2;">{{ $topic->name }}</span>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
    @else
        <div class="row mt-4">
            <div class="col-md-12 text-center py-5 text-muted bg-white rounded shadow-sm">
                <i class="bi bi-folder-x display-2 mb-3 d-block text-muted" style="opacity: .6;"></i>
                <h5 class="fw-bold">No Folders Available</h5>
                <p class="small text-muted mb-0">Your instructors haven't created any quiz folders yet.</p>
            </div>
        </div>
    @endif

    @if(count($topics) > 0)
    <div class="row mt-4">
        <div class="col-md-12 text-center py-5 text-muted">
            <i class="bi bi-folder2-open display-2 mb-3 d-block text-warning" style="opacity: .6;"></i>
            <h5 class="fw-bold">Open a Folder</h5>
            <p class="small text-muted mb-0">Select one of the folders above to view its quizzes.</p>
        </div>
    </div>
    @endif

    <div class="row mt-4">
        <div class="col-md-12">
            <a href="{{ route('student.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</div>

<style>
    .folder-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important;
    }
</style>
@endsection
