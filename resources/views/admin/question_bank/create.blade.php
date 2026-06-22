@extends('layouts.dashboard')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="{{ route('admin.question-bank.index') }}" class="btn btn-icon btn-light" style="border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h4 class="mb-0 fw-bold">Add Question to Global Bank</h4>
                    <p class="text-muted mb-0" style="font-size: .875rem;">Create a system-wide question that teachers can pull into quizzes.</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.question-bank.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    {{-- Question Text --}}
                    <div class="col-12">
                        <label class="form-label small fw-bold">Question Text</label>
                        <textarea name="question_text" class="form-control" rows="3" placeholder="Enter question description..." required>{{ old('question_text') }}</textarea>
                    </div>

                    {{-- Question Type --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Question Type</label>
                        <select name="type" id="typeSelect" class="form-select" required>
                            <option value="mcq" {{ old('type') === 'mcq' ? 'selected' : '' }}>Multiple Choice (MCQ)</option>
                            <option value="short_answer" {{ old('type') === 'short_answer' ? 'selected' : '' }}>Short Answer / Subjective</option>
                        </select>
                    </div>

                    {{-- Topic --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Topic</label>
                        <input type="text" name="topic" class="form-control" placeholder="e.g. Aqidah, Feqah, Akhlak" value="{{ old('topic') }}" required>
                    </div>

                    {{-- Difficulty --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Difficulty</label>
                        <select name="difficulty" class="form-select" required>
                            <option value="easy" {{ old('difficulty') === 'easy' ? 'selected' : '' }}>Easy</option>
                            <option value="medium" {{ old('difficulty') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="hard" {{ old('difficulty') === 'hard' ? 'selected' : '' }}>Hard</option>
                        </select>
                    </div>

                    {{-- Points --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Points Awarded</label>
                        <input type="number" name="points" class="form-control" value="{{ old('points', 10) }}" min="1" required>
                    </div>

                    {{-- MCQ Options Section --}}
                    <div class="col-12" id="optionsSection">
                        <div class="card bg-light p-3 border-0">
                            <h6 class="fw-bold mb-3 small text-muted">MCQ OPTIONS</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Option A</label>
                                    <input type="text" name="options[a]" class="form-control" placeholder="Option A value" value="{{ old('options.a') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Option B</label>
                                    <input type="text" name="options[b]" class="form-control" placeholder="Option B value" value="{{ old('options.b') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Option C</label>
                                    <input type="text" name="options[c]" class="form-control" placeholder="Option C value" value="{{ old('options.c') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Option D</label>
                                    <input type="text" name="options[d]" class="form-control" placeholder="Option D value" value="{{ old('options.d') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Correct Answer (Dynamic placeholder) --}}
                    <div class="col-12">
                        <label class="form-label small fw-bold">Correct Answer</label>
                        <div id="correctAnswerMcqGroup">
                            <select name="correct_answer_mcq" id="correctAnswerMcq" class="form-select">
                                <option value="a" {{ old('correct_answer') === 'a' ? 'selected' : '' }}>Option A</option>
                                <option value="b" {{ old('correct_answer') === 'b' ? 'selected' : '' }}>Option B</option>
                                <option value="c" {{ old('correct_answer') === 'c' ? 'selected' : '' }}>Option C</option>
                                <option value="d" {{ old('correct_answer') === 'd' ? 'selected' : '' }}>Option D</option>
                            </select>
                        </div>
                        <div id="correctAnswerShortGroup" style="display:none;">
                            <input type="text" name="correct_answer_short" id="correctAnswerShort" class="form-control" placeholder="Enter correct textual answer" value="{{ old('correct_answer') }}">
                        </div>
                        <input type="hidden" name="correct_answer" id="correctAnswerHidden" value="{{ old('correct_answer', 'a') }}">
                    </div>

                    <div class="col-12 text-end mt-4">
                        <a href="{{ route('admin.question-bank.index') }}" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Save Question</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('typeSelect');
        const optionsSection = document.getElementById('optionsSection');
        const mcqGroup = document.getElementById('correctAnswerMcqGroup');
        const shortGroup = document.getElementById('correctAnswerShortGroup');
        const mcqInput = document.getElementById('correctAnswerMcq');
        const shortInput = document.getElementById('correctAnswerShort');
        const hiddenInput = document.getElementById('correctAnswerHidden');

        function updateFormLayout() {
            if (typeSelect.value === 'mcq') {
                optionsSection.style.display = 'block';
                mcqGroup.style.display = 'block';
                shortGroup.style.display = 'none';
                hiddenInput.value = mcqInput.value;
            } else {
                optionsSection.style.display = 'none';
                mcqGroup.style.display = 'none';
                shortGroup.style.display = 'block';
                hiddenInput.value = shortInput.value;
            }
        }

        typeSelect.addEventListener('change', updateFormLayout);
        mcqInput.addEventListener('change', function() {
            if (typeSelect.value === 'mcq') {
                hiddenInput.value = mcqInput.value;
            }
        });
        shortInput.addEventListener('input', function() {
            if (typeSelect.value === 'short_answer') {
                hiddenInput.value = shortInput.value;
            }
        });

        // Seed initial values
        updateFormLayout();
    });
</script>
@endsection
