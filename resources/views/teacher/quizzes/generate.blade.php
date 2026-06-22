@extends('layouts.dashboard')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="currentColor" class="text-primary me-2" style="vertical-align: text-bottom;">
                    <path d="M22.2819 9.8211a5.9847 5.9847 0 0 0-.5157-4.9108 6.0462 6.0462 0 0 0-6.5098-2.9A6.0651 6.0651 0 0 0 4.9807 4.1818a5.9847 5.9847 0 0 0-3.9977 2.9 6.0462 6.0462 0 0 0 .7427 7.0966 5.98 5.98 0 0 0 .511 4.9107 6.0462 6.0462 0 0 0 6.5146 2.9001A5.9847 5.9847 0 0 0 13.2599 24.991a6.0462 6.0462 0 0 0 3.9977-2.9001 5.9847 5.9847 0 0 0-.7427-7.0966 5.98 5.98 0 0 0-.2223-1.1732h.005zm-9.022 12.6081a4.4755 4.4755 0 0 1-2.5399-.7956l.0009-.0004-.0004-.0003h-1e-4c-1.353-.8273-2.126-2.2778-2.0232-3.794l.0003-.01.0718-.89 2.5843 1.4921-.005.0084a.6923.6923 0 0 0 .9794.2274.6973.6973 0 0 0 .2539-.9305l-.0133-.0212-3.3444-1.9309.0245-.2765a4.5422 4.5422 0 0 1 2.9822-3.861l.019-.0045 1.62-.3876v3.003h-1e-4a.6975.6975 0 0 0 .6975.6975.6975.6975 0 0 0 .6975-.6975h-.0001V8.214l1.2483.7208v5.5724a.6975.6975 0 0 0 .6975.6975.6975.6975 0 0 0 .6975-.6975V7.7493l.3662-.1282a4.4755 4.4755 0 0 1 2.5399.7956l-.0009.0004.0004.0003h1e-4c1.353.8273 2.126 2.2778 2.0232 3.794l-.0003.01-.0718.89-2.5843-1.4921.005-.0084a.6923.6923 0 0 0-.9794-.2274.6973.6973 0 0 0-.2539.9305l.0133.0212 3.3444 1.9309-.0245.2765a4.5422 4.5422 0 0 1-2.9822 3.861l-.019.0045-1.62.3876v-3.003h1e-4a.6975.6975 0 0 0-.6975-.6975.6975.6975 0 0 0-.6975.6975h.0001v6.3364l-1.2483-.7208v-5.5724a.6975.6975 0 0 0-.6975-.6975.6975.6975 0 0 0-.6975.6975v6.7621l-.3662.1282z"/>
                </svg>
                Generate Quiz with AI
            </h1>
            <p class="text-muted">Use Artificial Intelligence to automatically generate quiz questions from your topics or text.</p>
        </div>
    </div>
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="{{ route('teacher.quizzes.process_generate') }}" method="POST" id="generate-form" enctype="multipart/form-data">
                        @csrf
                        
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
                                <div class="form-text">Supported formats: PDF, Text. Max size: 10MB.</div>
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
                                    ⚖️ Compare Both AIs Side-by-Side
                                    <small class="d-block fw-normal opacity-75" style="font-size:.75rem;">
                                        GPT-5.2 vs Gemini 2.5 Flash — recommended for FYP
                                    </small>
                                </button>

                                {{-- Single Model --}}
                                <div class="input-group">
                                    <select name="ai_model" id="ai_model" class="form-select">
                                        <option value="openai/gpt-5.2">🤖 GPT-5.2 (OpenAI)</option>
                                        <option value="google/gemini-2.5-flash">✨ Gemini 2.5 Flash (Google)</option>
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
