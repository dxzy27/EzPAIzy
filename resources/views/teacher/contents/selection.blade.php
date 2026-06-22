@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row mb-5">
        <div class="col-md-12 text-center">
            <h1>Manage Contents</h1>
            <p class="text-muted">Select the type of content you want to manage or create</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <!-- Flashcards Option -->
        <div class="col-md-5 mb-4">
            <div class="card h-100 shadow-sm hover-card border-0">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="bi bi-card-text text-primary" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="card-title">Flashcards</h3>
                    <p class="card-text text-muted mb-4">Create and manage interactive flashcards for quick revision and memorization.</p>
                    <a href="{{ route('teacher.flashcard-sets.index') }}" class="btn btn-outline-primary btn-lg w-100 stretched-link">Manage Flashcards</a>
                </div>
            </div>
        </div>

        <!-- Other Contents Option -->
        <div class="col-md-5 mb-4">
            <div class="card h-100 shadow-sm hover-card border-0">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="bi bi-collection-play text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="card-title">Learning Materials</h3>
                    <p class="card-text text-muted mb-4">Manage videos, e-books, articles, and other educational resources.</p>
                    <a href="{{ route('teacher.contents.index') }}" class="btn btn-outline-success btn-lg w-100 stretched-link">Manage Materials</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-12 text-center">
             <a href="{{ route('teacher.dashboard') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
</div>

<style>
    .hover-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
</style>
@endsection
