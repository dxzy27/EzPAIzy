@extends('layouts.dashboard')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1040px; margin: 0 auto;">
    <div class="row mb-4 align-items-center">
        <div class="col-12">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ url()->previous() === url()->current() ? route('teacher.quizzes.index') : url()->previous() }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <div>
                    <h1 class="h2 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-stars text-primary"></i> Generate Quiz with AI
                    </h1>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Use Artificial Intelligence to automatically generate quiz questions from your topics or text.</p>
                </div>
            </div>
        </div>
    </div>
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="{{ route('teacher.quizzes.process_generate') }}" method="POST" id="generate-form" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="title" class="form-label fw-bold">Quiz Title</label>
                            <input type="text" class="form-control form-control-lg fw-semibold" id="title" name="title" placeholder="Enter Quiz Title" required style="border-radius: 10px; font-size: 1.05rem;">
                        </div>

                        <div class="mb-4">
                            <label for="topic" class="form-label fw-bold">1. Select Topic</label>
                            <select name="topic" id="topic" class="form-select" required>
                                <option value="" disabled selected>Select a Topic</option>
                                @foreach($topics as $t)
                                    <option value="{{ $t->name }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">The questions will be focused on this specific learning area.</div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="difficulty" class="form-label fw-bold">2. Difficulty Level</label>
                                <select name="difficulty" id="difficulty" class="form-select" required>
                                    <option value="easy">Easy</option>
                                    <option value="medium">Medium</option>
                                    <option value="hard">Hard</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="question_count" class="form-label fw-bold">3. Number of Questions</label>
                                <input type="number" class="form-control" name="question_count" id="question_count" value="5" min="1" max="20" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">4. Context / Source Material (Optional)</label>
                            
                            <div class="mb-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="source_type" id="source_text" value="text" checked onchange="toggleSource('text')">
                                    <label class="form-check-label" for="source_text">Paste Text</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="source_type" id="source_file" value="file" onchange="toggleSource('file')">
                                    <label class="form-check-label" for="source_file">Upload File (PDF/Text)</label>
                                </div>
                            </div>
                            
                            <div id="text-input-container">
                                <textarea class="form-control" name="context" id="context" rows="5" placeholder="Paste relevant text, notes, or reading material here..."></textarea>
                            </div>
                            
                            <div id="file-input-container" style="display: none;">
                                <input type="file" class="form-control" name="file" id="file" accept=".pdf,.txt,.md">
                                <div class="form-text">Supported formats: PDF, Text. Max size: 200MB.</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="instructions" class="form-label fw-bold">5. Prompt</label>
                            <textarea class="form-control" name="instructions" id="instructions" rows="2"></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="ai_model" class="form-label fw-bold">6. Choose Action</label>

                            <div class="d-grid gap-2">
                                {{-- Compare Both --}}
                                <button type="button" class="btn btn-lg btn-dark" id="compare-btn"
                                    onclick="submitCompare()">
                                    ⚖️ Compare Both AIs
                                    <small class="d-block fw-normal opacity-75" style="font-size:.75rem;">
                                        GPT vs Gemini
                                    </small>
                                </button>

                                {{-- Single Model --}}
                                <div class="input-group">
                                    <select name="ai_model" id="ai_model" class="form-select">
                                        <option value="openai/gpt-oss-120b:free">🤖 GPT</option>
                                        <option value="google/gemini-2.5-flash">✨ Gemini</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary" id="generate-btn">
                                        <i class="bi bi-cpu me-1"></i> Generate with Selected
                                    </button>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function toggleSource(type) {
        if (type === 'text') {
            document.getElementById('text-input-container').style.display = 'block';
            document.getElementById('file-input-container').style.display = 'none';
        } else {
            document.getElementById('text-input-container').style.display = 'none';
            document.getElementById('file-input-container').style.display = 'block';
        }
    }

    // Single model generate — show spinner
    document.getElementById('generate-form').addEventListener('submit', function(e) {
        const btn = document.getElementById('generate-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generating...';
    });

    // Compare Both AIs — use the main form to ensure file uploads work
    function submitCompare() {
        const mainForm = document.getElementById('generate-form');
        const btn      = document.getElementById('compare-btn');

        // Validate topic is selected
        const topic = mainForm.querySelector('[name="topic"]').value;
        if (!topic) {
            alert('Please select a topic first.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Calling both AIs... this may take ~30 seconds';

        // Change the form action to the compare route and submit
        mainForm.action = "{{ route('teacher.quizzes.process_compare') }}";
        mainForm.submit();
    }
</script>
@endsection
