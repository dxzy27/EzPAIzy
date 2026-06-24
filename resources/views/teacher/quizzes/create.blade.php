@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Create <span class="badge bg-{{ $difficulty == 'easy' ? 'success' : ($difficulty == 'medium' ? 'warning' : 'danger') }}">{{ ucfirst($difficulty) }}</span> Quiz</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('teacher.quizzes.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="difficulty" value="{{ $difficulty }}">

                        <div class="mb-3">
                            <label for="title" class="form-label">Quiz Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="topic" class="form-label">Topic</label>
                            <select name="topic" id="topic" class="form-select @error('topic') is-invalid @enderror" required>
                                <option value="" disabled selected>Select a Topic</option>
                                @foreach($topics as $t)
                                    <option value="{{ $t->name }}" {{ (old('topic') ?? request('topic')) == $t->name ? 'selected' : '' }}>{{ $t->name }}</option>
                                @endforeach
                            </select>
                            @error('topic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(true)
                        <div class="mb-4">
                            <h4 class="mb-3">Questions {{ $difficulty == 'medium' || $difficulty == 'hard' ? '(Short Answer/KBAT)' : '(MCQ)' }}</h4>
                            <div id="questions-container"></div>
                            <button type="button" class="btn btn-outline-primary w-100" id="add-question-btn">
                                <i class="bi bi-plus-lg"></i> Add Question
                            </button>
                        </div>
                        @endif

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Create Quiz</button>
                            <a href="javascript:history.back()" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('questions-container');
        const addBtn = document.getElementById('add-question-btn');
        const difficulty = "{{ $difficulty }}"; // 'easy', 'medium', 'hard'
        let questionCount = 0;

        function addQuestion(data = null) {
            questionCount++;
            const current = questionCount;
            
            const text = data ? (data.question_text || data.text) : ''; // Handle both API (text) and DB (question_text) naming if needed
            const correct = data ? (data.correct_answer) : '';
            const type = data ? (data.type || 'mcq') : (difficulty === 'medium' ? 'short_answer' : 'mcq');

            // Common Header
            let html = `
                <div class="card mb-3 border bg-light question-card" id="question-${current}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <h6 class="fw-bold">Question ${current}</h6>
                            <button type="button" class="btn btn-sm btn-danger remove-question" onclick="this.closest('.question-card').remove()">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        
                        <!-- Question Text -->
                        <div class="mb-3">
                            <input type="text" name="questions[${current}][text]" class="form-control" placeholder="Enter question text" value="${text}" required>
                        </div>
            `;

            if (difficulty === 'medium' || difficulty === 'hard') {
                // Medium/Hard Difficulty: Short Answer / KBAT
                html += `<input type="hidden" name="questions[${current}][type]" value="short_answer">`;
                
                html += `
                    <!-- Correct Answer Input -->
                    <div class="mb-2">
                        <label class="form-label small text-muted">Correct Answer / Suggested Key Points</label>
                        <input type="text" name="questions[${current}][correct]" class="form-control" placeholder="Enter the correct answer or suggested key points (Hard difficulty is manually graded)" value="${correct}" required>
                    </div>
                `;
            } else {
                // Easy/Other: Default Information (MCQ)
                const optA = data && data.options ? (data.options.a || '') : '';
                const optB = data && data.options ? (data.options.b || '') : '';
                const optC = data && data.options ? (data.options.c || '') : '';
                const optD = data && data.options ? (data.options.d || '') : '';

                html += `
                    <input type="hidden" name="questions[${current}][type]" value="mcq">
                    
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text">A</span>
                                <input type="text" name="questions[${current}][options][a]" class="form-control" placeholder="Option A" value="${optA}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text">B</span>
                                <input type="text" name="questions[${current}][options][b]" class="form-control" placeholder="Option B" value="${optB}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text">C</span>
                                <input type="text" name="questions[${current}][options][c]" class="form-control" placeholder="Option C" value="${optC}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text">D</span>
                                <input type="text" name="questions[${current}][options][d]" class="form-control" placeholder="Option D" value="${optD}" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted">Correct Answer</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="questions[${current}][correct]" id="q${current}-a" value="a" ${correct === 'a' ? 'checked' : ''} required>
                            <label class="btn btn-outline-success" for="q${current}-a">A</label>

                            <input type="radio" class="btn-check" name="questions[${current}][correct]" id="q${current}-b" value="b" ${correct === 'b' ? 'checked' : ''}>
                            <label class="btn btn-outline-success" for="q${current}-b">B</label>

                            <input type="radio" class="btn-check" name="questions[${current}][correct]" id="q${current}-c" value="c" ${correct === 'c' ? 'checked' : ''}>
                            <label class="btn btn-outline-success" for="q${current}-c">C</label>

                            <input type="radio" class="btn-check" name="questions[${current}][correct]" id="q${current}-d" value="d" ${correct === 'd' ? 'checked' : ''}>
                            <label class="btn btn-outline-success" for="q${current}-d">D</label>
                        </div>
                    </div>
                `;
            }

            html += `
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        addBtn.addEventListener('click', () => addQuestion());
        
        // Load generated questions if available
        const generatedQuestions = @json(session('generated_questions'));
        
        if (generatedQuestions && generatedQuestions.length > 0) {
            generatedQuestions.forEach(q => {
                const data = {
                    question_text: q.text,
                    options: q.options,
                    correct_answer: q.correct_answer,
                    type: q.type // Ensure type is passed if available
                };
                addQuestion(data);
            });
        } else {
            addQuestion(); // Add empty first question
        }
    });
</script>

@endsection
