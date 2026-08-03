@extends('layouts.dashboard')

@section('content')
<div class="container py-5">
    <div class="row mb-4 align-items-center">
        <div class="col-md-12">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Dashboard">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <div>
                    <h1 class="h2 fw-bold text-dark mb-0">📚 My Revision List</h1>
                    <p class="text-muted mb-0">Learning materials you've saved for review</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Topics Folders -->
    @if(count($topics) > 0)
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
         <h5 class="text-muted fw-bold mb-0">TOPICS</h5>
         <div class="input-group shadow-sm" style="border-radius: 50px; overflow: hidden; width: 260px; height: 36px;">
            <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search text-muted" style="font-size: 0.85rem;"></i></span>
            <input type="text" id="topics-search" class="form-control border-0 ps-2" placeholder="Search topics..." style="font-size: 0.85rem;">
         </div>
    </div>
    <div class="row g-3 mb-5">
        @foreach($topics as $topic)
            <div class="col-sm-6 col-md-3 mb-4 topic-folder-col" data-topic-name="{{ strtolower($topic->name) }}">
                <a href="{{ route('student.revision.folder', ['topic' => $topic->name]) }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm border-0 bg-light text-dark folder-card" style="border-radius: 16px; transition: transform 0.2s, box-shadow 0.2s;">
                        <div class="card-body text-center d-flex flex-column align-items-center justify-content-center p-4">
                            <i class="bi bi-folder-fill mb-3 text-warning" style="font-size: 3.2rem;"></i>
                            <span class="fw-bold text-wrap text-dark" style="line-height: 1.25; font-size: 0.98rem; letter-spacing: -0.1px;">{{ $topic->name }}</span>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
    @else
        <div class="alert alert-info" role="alert">
            <h4 class="alert-heading"><i class="bi bi-info-circle"></i> No Content in Revision List</h4>
            <p>You haven't added any learning materials to your revision list yet.</p>
            <hr>
            <p class="mb-0">
                Browse <a href="{{ route('student.contents.index') }}" class="alert-link">Learning Materials</a>, 
                <a href="{{ route('student.flashcards.index') }}" class="alert-link">Flashcards</a>, or 
                <a href="{{ route('student.quizzes') }}" class="alert-link">Quizzes</a>
                and click the star button to save them.
            </p>
        </div>
    @endif
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
