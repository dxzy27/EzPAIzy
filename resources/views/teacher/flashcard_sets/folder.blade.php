@extends('layouts.dashboard')

@section('title', 'Flashcard Sets - ' . $topic)

@section('content')
<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('teacher.flashcard-sets.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Folders">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <div>
                    <h1 class="h2 fw-bold text-dark mb-0">{{ $topic }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('teacher.flashcard-sets.index') }}" class="text-decoration-none">Flashcards</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $topic }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('teacher.flashcard-sets.create', ['topic' => $topic]) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>New Flashcard Set
            </a>
        </div>
    </div>

    @if($flashcardSets->isEmpty())
    <div class="card border-0 shadow-sm text-center py-5" style="border-radius:14px;">
        <div class="text-muted">
            <i class="bi bi-folder-x fs-1 d-block mb-3 text-warning" style="opacity: .6;"></i>
            <h4 class="fw-bold text-dark">This folder is empty</h4>
            <p class="mb-4">No flashcard sets found in this folder.</p>
            <a href="{{ route('teacher.flashcard-sets.create', ['topic' => $topic]) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Create Your First Set
            </a>
        </div>
    </div>
    @else
    <div class="row g-3">
        @foreach($flashcardSets as $set)
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-warning text-dark rounded-pill">{{ $set->topic }}</span>
                        <small class="text-muted">{{ $set->created_at->diffForHumans() }}</small>
                    </div>
                    <h6 class="fw-bold mb-1">{{ $set->title }}</h6>
                    @if($set->description)
                    <p class="text-muted small mb-2">{{ Str::limit($set->description, 80) }}</p>
                    @endif
                    <div class="mt-auto pt-3 d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            <i class="bi bi-layers me-1"></i>{{ $set->flashcards_count ?? $set->flashcards()->count() }} cards
                        </span>
                        <div class="d-flex gap-2">
                            <a href="{{ route('teacher.flashcard-sets.edit', $set) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('teacher.flashcard-sets.destroy', $set) }}" method="POST"
                                  onsubmit="return confirm('Delete this flashcard set?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $flashcardSets->links() }}
    </div>
    @endif
</div>
@endsection
