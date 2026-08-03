@extends('layouts.dashboard')

@section('content')

@php $isAuditory = auth()->user()?->learning_style === 'auditory'; @endphp

<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                @if(auth()->user()->role === 'teacher')
                    <a href="{{ route('teacher.contents.folder', $content->topic) }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back">
                        <i class="bi bi-arrow-left fs-5"></i>
                    </a>
                @else
                    <a href="{{ route('student.contents.folder', $content->topic) }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back">
                        <i class="bi bi-arrow-left fs-5"></i>
                    </a>
                @endif
                <div>
                    <h1 class="h2 fw-bold text-dark mb-0">{{ $content->title }}</h1>
                    <p class="text-muted mb-0">Created: {{ $content->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>


        </div>
        <div class="col-md-4 text-end">
            @if(auth()->user()->role === 'teacher')
                <a href="{{ route('teacher.contents.edit', $content) }}" class="btn btn-warning">Edit</a>
                <form action="{{ route('teacher.contents.destroy', $content) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
            @endif
        </div>
    </div>

    @php $isReadWrite = auth()->user()?->learning_style === 'read_write'; @endphp

    <div class="row">
        <div class="{{ $isReadWrite ? 'col-md-8' : 'col-md-12' }}">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Content</h5>
                </div>
                <div class="card-body">
                    @if(auth()->user()->role === 'student')
                        <div class="p-3 bg-light rounded-3 border border-light-subtle mb-4 shadow-sm">
                            <div class="row align-items-center">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fs-3">📖</span>
                                        <div>
                                            <h6 class="fw-bold mb-1 text-dark">Track Your Reading Progress</h6>
                                            <p class="text-muted small mb-0">Update where you are so you can continue next time!</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <form id="progress-form" class="d-flex align-items-center justify-content-md-end gap-2">
                                        @csrf
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="small text-muted fw-bold">Page</span>
                                            <input type="number" id="current_page" name="current_page" class="form-control form-control-sm text-center fw-bold" style="width: 70px;" value="{{ $progress->current_page ?? 1 }}" min="1">
                                            <span class="small text-muted fw-bold">of</span>
                                            <input type="number" id="total_pages" name="total_pages" class="form-control form-control-sm text-center fw-bold" style="width: 70px;" value="{{ $progress->total_pages ?? 100 }}" min="1">
                                        </div>
                                        <button type="button" id="update-progress-btn" class="btn btn-sm btn-primary fw-bold px-3 rounded-pill shadow-sm">
                                            Save Progress
                                        </button>
                                    </form>
                                    <div id="progress-status-msg" class="text-md-end small mt-1 fw-semibold text-success d-none">
                                        <i class="bi bi-check-circle-fill"></i> Progress saved!
                                    </div>
                                </div>
                            </div>
                            <!-- Small Progress Bar -->
                            <div class="progress mt-3" style="height: 6px; border-radius: 3px;">
                                @php
                                    $curr = $progress->current_page ?? 1;
                                    $tot = $progress->total_pages ?? 100;
                                    $percentage = min(100, max(0, round(($curr / $tot) * 100)));
                                @endphp
                                <div id="progress-bar-indicator" class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    @endif

                    @if($content->file_path)
                        <div class="mb-4 text-center">
                            @if(in_array(strtolower($content->file_type), ['jpg', 'jpeg', 'png', 'gif']))
                                <img src="{{ asset('storage/' . $content->file_path) }}" class="img-fluid rounded border" alt="Attachment" style="max-height: 500px;">
                            @elseif(in_array(strtolower($content->file_type), ['mp4', 'webm', 'ogg']))
                                <video controls class="w-100 rounded border" style="max-height: 500px;">
                                    <source src="{{ asset('storage/' . $content->file_path) }}" type="video/{{ $content->file_type }}">
                                    Your browser does not support the video tag.
                                </video>
                            @elseif(strtolower($content->file_type) === 'pdf')
                                <div class="p-4 bg-light border rounded w-100 text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-file-earmark-pdf display-4 text-danger"></i>
                                        <h5 class="mt-2 fw-bold text-dark">{{ $content->original_filename ?? ($content->title . '.pdf') }}</h5>
                                    </div>
                                    <div class="d-flex justify-content-center gap-3">
                                        <button class="btn btn-primary d-inline-flex align-items-center gap-2" onclick="togglePdfViewer()">
                                            <i class="bi bi-book"></i> Read
                                        </button>
                                        <a href="{{ asset('storage/' . $content->file_path) }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2" download="{{ $content->original_filename ?? ($content->title . '.pdf') }}">
                                            <i class="bi bi-download"></i> Get PDF
                                        </a>
                                    </div>
                                    <div id="pdf-viewer-container" class="d-none mt-4 border rounded shadow-sm bg-white p-2">
                                        <iframe src="{{ asset('storage/' . $content->file_path) }}#navpanes=0" width="100%" height="700px" style="border: none;"></iframe>
                                    </div>
                                </div>
                                <script>
                                    function togglePdfViewer() {
                                        const container = document.getElementById('pdf-viewer-container');
                                        if (container.classList.contains('d-none')) {
                                            container.classList.remove('d-none');
                                        } else {
                                            container.classList.add('d-none');
                                        }
                                    }
                                </script>
                            @else
                                <div class="p-4 bg-light border rounded d-inline-block">
                                    <i class="bi bi-file-earmark-text display-4 text-primary"></i>
                                    <br>
                                    <a href="{{ asset('storage/' . $content->file_path) }}" class="btn btn-primary mt-2" download>
                                        <i class="bi bi-download me-1"></i> Download File ({{ strtoupper($content->file_type) }})
                                    </a>
                                </div>
                            @endif
                        </div>
                        <hr>
                    @endif

                    <label class="fw-bold mb-2">Description</label>
                    <div class="content-body" id="tts-content-body">
                        {!! nl2br(e($content->content)) !!}
                    </div>
                </div>
            </div>


        </div>

        @if($isReadWrite)
            <div class="col-md-4">
                {{-- Notepad Widget --}}
                @php
                    $existingNote = \App\Models\StudentNote::where('user_id', auth()->id())
                        ->where('resource_type', 'content')
                        ->where('resource_id', $content->id)
                        ->first();
                @endphp
                <div class="card border-success shadow-sm sticky-top" style="top: 20px; z-index: 100;">
                    <div class="card-header bg-success text-white d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-1"></i> Study Notepad</h6>
                        <span id="save-status" class="small text-white-50">Auto-saved</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.72rem;">Topic</small>
                            <span class="badge bg-light text-dark border">{{ $content->topic ?? 'General' }}</span>
                        </div>
                        <div class="mb-3">
                            <label for="note-title" class="form-label small fw-bold text-uppercase text-muted mb-1" style="font-size: 0.72rem;">Note Title</label>
                            <input type="text" id="note-title" class="form-control form-control-sm fw-bold" 
                                   value="{{ $existingNote ? $existingNote->title : 'Notes: ' . $content->title }}" 
                                   placeholder="Title of your note...">
                        </div>
                        <div class="mb-3">
                            <label for="note-content" class="form-label small fw-bold text-uppercase text-muted mb-1" style="font-size: 0.72rem;">Acronyms & Notes</label>
                            <textarea id="note-content" class="form-control form-control-sm" rows="12" 
                                      placeholder="Write your study acronyms, summaries, and key points here...">{{ $existingNote ? $existingNote->content : '' }}</textarea>
                        </div>
                        <div class="d-grid">
                            <button type="button" onclick="saveNote()" class="btn btn-success btn-sm fw-bold">
                                <i class="bi bi-cloud-arrow-up-fill me-1"></i> Save Note
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if($isReadWrite)
    <script>
        let saveTimeout = null;

        function saveNote() {
            const title = document.getElementById('note-title').value.trim();
            const content = document.getElementById('note-content').value.trim();
            const statusSpan = document.getElementById('save-status');

            if (!title) {
                statusSpan.textContent = 'Title required';
                statusSpan.style.color = '#ef4444';
                return;
            }

            statusSpan.textContent = 'Saving...';
            statusSpan.style.color = 'rgba(255,255,255,0.7)';

            fetch("{{ route('student.notes.save') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    topic: "{{ $content->topic ?? 'General' }}",
                    difficulty: null,
                    title: title,
                    content: content,
                    resource_type: 'content',
                    resource_id: {{ $content->id }}
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    statusSpan.textContent = 'Auto-saved';
                    statusSpan.style.color = 'rgba(255,255,255,0.7)';
                    
                    // Reload folders in sidebar silently by checking if new folder was created
                    // (we can reload sidebar or just let it update on next page load)
                } else {
                    statusSpan.textContent = 'Save failed';
                    statusSpan.style.color = '#ef4444';
                }
            })
            .catch(err => {
                statusSpan.textContent = 'Connection error';
                statusSpan.style.color = '#ef4444';
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const titleInput = document.getElementById('note-title');
            const contentInput = document.getElementById('note-content');

            if (titleInput && contentInput) {
                const triggerAutoSave = () => {
                    const statusSpan = document.getElementById('save-status');
                    statusSpan.textContent = 'Unsaved changes';
                    statusSpan.style.color = '#f59e0b';
                    
                    clearTimeout(saveTimeout);
                    saveTimeout = setTimeout(saveNote, 1500);
                };

                titleInput.addEventListener('input', triggerAutoSave);
                contentInput.addEventListener('input', triggerAutoSave);
            }
        });
    </script>
    @endif

</div>

@if(auth()->user()->role === 'student')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateBtn = document.getElementById('update-progress-btn');
    if (updateBtn) {
        updateBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const currentPageInput = document.getElementById('current_page');
            const totalPagesInput = document.getElementById('total_pages');
            const currentPage = parseInt(currentPageInput.value) || 1;
            const totalPages = parseInt(totalPagesInput.value) || 100;
            const statusMsg = document.getElementById('progress-status-msg');
            const progressBar = document.getElementById('progress-bar-indicator');

            updateBtn.disabled = true;
            updateBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

            fetch("{{ route('student.contents.progress', $content->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    current_page: currentPage,
                    total_pages: totalPages
                })
            })
            .then(res => res.json())
            .then(data => {
                updateBtn.disabled = false;
                updateBtn.innerHTML = 'Save Progress';
                if (data.success) {
                    statusMsg.classList.remove('d-none');
                    setTimeout(() => {
                        statusMsg.classList.add('d-none');
                    }, 2000);

                    const percent = Math.min(100, Math.max(0, Math.round((currentPage / totalPages) * 100)));
                    progressBar.style.width = percent + '%';
                    progressBar.setAttribute('aria-valuenow', percent);
                }
            })
            .catch(err => {
                updateBtn.disabled = false;
                updateBtn.innerHTML = 'Save Progress';
                console.error(err);
            });
        });
    }
});
</script>
@endpush
@endif

@endsection

