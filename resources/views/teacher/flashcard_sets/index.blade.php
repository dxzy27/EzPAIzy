@extends('layouts.dashboard')

@section('title', 'Flashcard Sets')

@push('styles')
<style>
    .folder-card {
        transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.22s ease, border-color 0.22s ease !important;
        border-radius: 16px !important;
        border: 1.5 solid transparent !important;
        cursor: pointer;
    }
    .folder-card:hover {
        transform: translateY(-4px) !important;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08) !important;
        border-color: #3b82f6 !important;
    }
    .folder-card:hover .folder-icon {
        transform: scale(1.08);
    }
    .folder-icon {
        transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .delete-folder-btn {
        opacity: 0;
        transform: scale(0.85);
        transition: opacity 0.2s ease, transform 0.2s ease;
        z-index: 10;
    }
    .topic-folder-col:hover .delete-folder-btn {
        opacity: 0.85;
        transform: scale(1);
    }
    .delete-folder-btn:hover {
        opacity: 1 !important;
        transform: scale(1.15) !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1040px; margin: 0 auto;">
    <!-- Main Header Bar -->
    <div class="row mb-4 align-items-center">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Dashboard">
                        <i class="bi bi-arrow-left fs-5"></i>
                    </a>
                    <div>
                        <h1 class="h2 fw-bold text-dark mb-0">Flashcard Sets</h1>
                        <p class="text-muted mb-0" style="font-size: 0.88rem;">Manage and organize flashcard study sets for your students</p>
                    </div>
                </div>
                <!-- Action Buttons & Aligned Search Bar -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="input-group shadow-sm" style="border-radius: 10px; overflow: hidden; width: 220px; height: 38px;">
                        <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search text-muted" style="font-size: 0.85rem;"></i></span>
                        <input type="text" id="topics-search" class="form-control border-0 ps-2" placeholder="Search topics..." style="font-size: 0.85rem;">
                    </div>
                    <button type="button" class="btn btn-outline-primary fw-semibold px-3" style="border-radius: 10px; font-size: 0.88rem; height: 38px;" data-bs-toggle="modal" data-bs-target="#createTopicModal">
                        <i class="bi bi-folder-plus me-1"></i> Add Folder
                    </button>
                    <a href="{{ route('teacher.flashcard-sets.create') }}" class="btn btn-primary fw-semibold px-3 d-inline-flex align-items-center" style="border-radius: 10px; font-size: 0.88rem; height: 38px;">
                        <i class="bi bi-plus-lg me-1"></i> New Flashcard Set
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Topics Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
         <h6 class="text-muted fw-bold mb-0" style="font-size: 0.82rem; letter-spacing: 0.5px;">CURRICULUM TOPICS</h6>
    </div>

    @php
        $topicColors = [
            'al-quran' => ['bg' => '#f3e5f5', 'text' => '#7b1fa2', 'icon' => '#9c27b0'],
            'hadis'    => ['bg' => '#e3f2fd', 'text' => '#1565c0', 'icon' => '#1e88e5'],
            'akidah'   => ['bg' => '#e0f2f1', 'text' => '#00796b', 'icon' => '#009688'],
            'fiqah'    => ['bg' => '#e8f5e9', 'text' => '#2e7d32', 'icon' => '#4caf50'],
            'sirah'    => ['bg' => '#fff3e0', 'text' => '#e65100', 'icon' => '#ff9800'],
            'akhlak'   => ['bg' => '#ffebee', 'text' => '#c62828', 'icon' => '#f44336'],
        ];
        $fallbackColor = ['bg' => '#f5f5f5', 'text' => '#424242', 'icon' => '#757575'];
    @endphp

    <!-- Topics Folders Grid -->
    @if(count($topics) > 0)
    <div class="row g-3 mb-5">
        @foreach($topics as $topic)
            @php
                $key = strtolower(trim($topic->name));
                $color = $topicColors[$key] ?? $fallbackColor;
            @endphp
            <div class="col-6 col-md-4 col-lg-3 mb-2 position-relative topic-folder-col" data-topic-name="{{ strtolower($topic->name) }}">
                <a href="{{ route('teacher.flashcard-sets.folder', ['topic' => $topic->name]) }}" class="text-decoration-none">
                    <div class="card h-100 shadow-sm folder-card" style="background-color: {{ $color['bg'] }};">
                        <div class="card-body text-center d-flex flex-column align-items-center justify-content-center p-4">
                            <i class="bi bi-folder-fill mb-2.5 folder-icon" style="font-size: 3rem; color: {{ $color['icon'] }};"></i>
                            <span class="fw-bold text-wrap" style="line-height: 1.25; font-size: 0.95rem; color: {{ $color['text'] }};">{{ $topic->name }}</span>
                        </div>
                    </div>
                </a>
                <form action="{{ route('teacher.topics.destroy', $topic) }}" method="POST" class="position-absolute top-0 end-0 mt-2 me-3" onsubmit="return confirm('Delete this folder? Sets will remain but lose their folder assignment.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm text-danger p-0 border-0 bg-transparent delete-folder-btn" title="Delete Folder">
                        <i class="bi bi-x-circle-fill fs-5"></i>
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
            <p class="mb-3">Create a folder to start organizing your flashcard sets.</p>
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
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="createTopicModalLabel">Add New Folder</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <input type="hidden" name="type" value="flashcard">
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold" style="font-size: 0.9rem;">Folder Name</label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="e.g. Al-Quran (Tajweed)" style="border-radius: 10px;">
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

