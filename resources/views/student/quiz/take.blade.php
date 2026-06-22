@extends('layouts.dashboard')

@push('styles')
<style>
    .option-card {
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid #e9ecef;
    }
    .option-card:hover {
        border-color: #dee2e6;
        background-color: #f8f9fa;
    }
    .option-card.selected {
        border-color: #0d6efd;
        background-color: #e7f1ff;
    }
    .option-card.correct {
        border-color: #198754 !important;
        background-color: #d1e7dd !important;
    }
    .option-card.wrong {
        border-color: #dc3545 !important;
        background-color: #f8d7da !important;
    }
    
    .progress-bar {
        transition: width 0.3s ease;
    }
    
    .quiz-container {
        max-width: 800px;
        margin: 0 auto;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-5" style="min-height: 100vh; background-color: #f8f9fa;">
    <div class="quiz-container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="{{ route('student.quizzes') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Quizzes
            </a>
            <h5 class="text-muted mb-0">{{ $quiz->title }}</h5>
        </div>

        @if(auth()->user()?->learning_style === 'auditory')
        <div class="d-flex align-items-center gap-3 mb-4 px-4 py-3 rounded-3"
             style="background:#e0f2fe;border:1.5px solid #7dd3fc;">
            <i class="bi bi-ear-fill" style="color:#0891b2;font-size:1.3rem;"></i>
            <div>
                <div class="fw-bold" style="color:#0c4a6e;font-size:.88rem;">🎵 Auditory Mode Active</div>
                <div style="color:#075985;font-size:.8rem;">Each question will be read aloud automatically when it appears.</div>
            </div>
            <div class="ms-auto form-check form-switch mb-0">
                <label class="form-check-label small fw-semibold" style="color:#0c4a6e;" for="quiz-tts-toggle">Auto-read</label>
                <input class="form-check-input" type="checkbox" id="quiz-tts-toggle" checked
                       style="cursor:pointer;width:2.2em;height:1.1em;">
            </div>
        </div>
        @endif

        <!-- Progress -->
        <div class="mb-4">
            <div class="d-flex justify-content-between small text-muted mb-1">
                <span id="progress-text">Question 1 of {{ $quiz->questions->count() }}</span>
                <span id="timer"></span>
            </div>
            <div class="progress" style="height: 6px;">
                <div class="progress-bar bg-primary" id="progress-bar" role="progressbar" style="width: 0%"></div>
            </div>
        </div>

        <!-- Quiz Display -->
        <div id="quiz-content">
            <!-- Questions will be injected here -->
        </div>

        <!-- Completed Screen (Hidden by default) -->
        <div id="result-screen" class="card border-0 shadow-sm text-center p-5 d-none">
            <div class="card-body">
                <div class="mb-4">
                    <i class="bi bi-trophy-fill text-warning display-1"></i>
                </div>
                <h2 class="fw-bold mb-3">Quiz Completed!</h2>
                <h4 class="text-muted mb-4">Your Score: <span id="final-score" class="fw-bold text-primary">0</span>/100</h4>
                
                <p id="feedback-text" class="mb-4 lead"></p>
                
                <form action="{{ route('student.submit', $quiz) }}" method="POST" id="submit-form">
                    @csrf
                    <input type="hidden" name="score" id="score-input">
                    <input type="hidden" name="answers" id="answers-input">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="bi bi-check-circle me-2"></i> Submit Result
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const questions = @json($quiz->questions);
    const quizContent = document.getElementById('quiz-content');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const resultScreen = document.getElementById('result-screen');
    const finalScoreSpan = document.getElementById('final-score');
    const scoreInput = document.getElementById('score-input');
    const answersInput = document.getElementById('answers-input');
    const submitForm = document.getElementById('submit-form');
    
    const quizDifficulty = "{{ $quiz->difficulty }}";
    let currentQuestionIndex = 0;
    let userAnswers = {};
    let score = 0;

    function renderQuestion(index) {
        if(index >= questions.length) {
            showResults();
            return;
        }

        const q = questions[index];
        const type = q.type || 'mcq';
        const progressPct = ((index + 1) / questions.length) * 100;
        progressBar.style.width = `${progressPct}%`;
        progressText.innerText = `Question ${index + 1} of ${questions.length}`;

        let inputHtml = '';
        
        if (type === 'mcq' && q.options) {
            // MCQ Rendering
            const opts = q.options;
            ['a', 'b', 'c', 'd'].forEach(key => {
                if(opts[key]) {
                    inputHtml += `
                        <div class="card option-card mb-3" onclick="selectOption('${key}')" id="opt-${key}">
                            <div class="card-body d-flex align-items-center">
                                <div class="btn btn-sm btn-outline-primary me-3 text-uppercase fw-bold" style="width: 32px; height: 32px; padding: 0; line-height: 30px; text-align: center;">${key}</div>
                                <span class="fs-5">${opts[key]}</span>
                            </div>
                        </div>
                    `;
                }
            });
        } else {
            // Text Input (Fill in Blank / Short Answer)
            const savedVal = userAnswers[index] || '';
            inputHtml = `
                <div class="mb-4">
                    <label class="form-label text-muted small text-uppercase fw-bold">Your Answer</label>
                    <input type="text" class="form-control form-control-lg p-3" 
                        id="text-answer-input"
                        placeholder="Type your answer here..." 
                        value="${savedVal}" 
                        oninput="saveTextAnswer(this.value)"
                        onkeydown="if(event.key === 'Enter') { event.preventDefault(); nextQuestion(); }"
                        autocomplete="off">
                </div>
            `;
        }

        const html = `
            <div class="card border-0 shadow-sm question-card animated fadeIn">
                <div class="card-body p-4 p-md-5">
                    <h4 class="mb-4 fw-bold text-dark">${q.question_text}</h4>
                    <div class="options-list">
                        ${inputHtml}
                    </div>
                </div>
                <div class="card-footer bg-white border-0 p-4 d-flex justify-content-between">
                    <button class="btn btn-outline-secondary" onclick="prevQuestion()" ${index === 0 ? 'disabled' : ''}>Previous</button>
                    <button class="btn btn-primary px-4" id="next-btn" ${type !== 'mcq' && !userAnswers[index] ? 'disabled' : (type === 'mcq' && !userAnswers[index] ? 'disabled' : '')} onclick="nextQuestion()">Next</button>
                </div>
            </div>
        `;
        
        quizContent.innerHTML = html;
        
        // Restore previous selection for MCQ
        if (type === 'mcq' && userAnswers[index]) {
            selectOption(userAnswers[index], false);
        }
        // Focus text input
        if (type !== 'mcq') {
             setTimeout(() => {
                 const input = document.getElementById('text-answer-input');
                 if(input) input.focus();
             }, 100);
        }
        
        // Enable next button based on saved answer
        updateNextButton();
    }

    window.selectOption = function(key, autoAdvance = false) {
        document.querySelectorAll('.option-card').forEach(el => el.classList.remove('selected'));
        const el = document.getElementById(`opt-${key}`);
        if(el) {
            el.classList.add('selected');
            userAnswers[currentQuestionIndex] = key;
            updateNextButton();
        }
    };
    
    window.saveTextAnswer = function(text) {
        userAnswers[currentQuestionIndex] = text.trim();
        updateNextButton();
    };
    
    function updateNextButton() {
        const nextBtn = document.getElementById('next-btn');
        if (userAnswers[currentQuestionIndex] && userAnswers[currentQuestionIndex].length > 0) {
            nextBtn.removeAttribute('disabled');
        } else {
            nextBtn.setAttribute('disabled', 'disabled');
        }
    }

    window.nextQuestion = function() {
        if (!userAnswers[currentQuestionIndex]) return; // prevent empty
        currentQuestionIndex++;
        renderQuestion(currentQuestionIndex);
    };

    window.prevQuestion = function() {
        if(currentQuestionIndex > 0) {
            currentQuestionIndex--;
            renderQuestion(currentQuestionIndex);
        }
    };

    function showResults() {
        quizContent.style.display = 'none';
        progressBar.parentElement.parentElement.style.display = 'none'; // Hide progress header
        
        // Calculate Score
        let correctCount = 0;
        questions.forEach((q, idx) => {
            const userAns = userAnswers[idx];
            const correctAns = q.correct_answer;
            const type = q.type || 'mcq';
            
            if (userAns) {
                if (type === 'mcq') {
                    if (userAns === correctAns) correctCount++;
                } else {
                    // Case-insensitive comparison for text
                    if (userAns.toLowerCase() === correctAns.toLowerCase()) {
                        correctCount++;
                    }
                }
            }
        });
        
        const finalScore = Math.round((correctCount / questions.length) * 100);
        
        if (quizDifficulty === 'hard') {
            const resultHeader = document.querySelector('#result-screen h4');
            if (resultHeader) {
                resultHeader.innerHTML = "Answers Submitted for Review";
            }
            document.querySelector('#result-screen h2').innerText = "Quiz Completed!";
            scoreInput.value = 0; 
            document.getElementById('feedback-text').innerText = "This quiz contains KBAT questions and will be graded manually by your teacher.";
        } else {
            finalScoreSpan.innerText = finalScore;
            scoreInput.value = finalScore;
            
            const feedback = document.getElementById('feedback-text');
            if (finalScore >= 80) feedback.innerText = "Excellent attempt! You've mastered this topic.";
            else if (finalScore >= 50) feedback.innerText = "Good job! Keep practicing to improve.";
            else feedback.innerText = "Keep studying! You'll do better next time.";
        }
        
        // Save answers as JSON
        answersInput.value = JSON.stringify(userAnswers);
        
        resultScreen.classList.remove('d-none');
    }

    // Initialize
    if (questions.length > 0) {
        renderQuestion(0);
    } else {
        quizContent.innerHTML = '<div class="alert alert-warning">No questions found.</div>';
    }
});
</script>

@if(auth()->user()?->learning_style === 'auditory')
<script>
// ── Auditory Quiz Read-Aloud ────────────────────────────────────────
(function() {
    const synth = window.speechSynthesis;
    const labels = { a: 'A', b: 'B', c: 'C', d: 'D' };

    function isAutoRead() {
        const toggle = document.getElementById('quiz-tts-toggle');
        return toggle ? toggle.checked : true;
    }

    function readQuestion(q) {
        if (!isAutoRead()) return;
        synth.cancel();

        let text = q.question_text || '';

        // Append options A B C D for MCQ
        if (q.type === 'mcq' && q.options) {
            ['a','b','c','d'].forEach(k => {
                if (q.options[k]) text += '. ' + labels[k] + ': ' + q.options[k];
            });
        }

        const u = new SpeechSynthesisUtterance(text);
        u.lang = 'en-US';
        u.rate = 0.9;
        synth.speak(u);
    }

    // Monkey-patch the existing renderQuestion by observing DOM changes
    const qContent = document.getElementById('quiz-content');
    let lastText = '';
    const obs = new MutationObserver(function() {
        const heading = qContent ? qContent.querySelector('h4') : null;
        if (heading && heading.textContent.trim() !== lastText) {
            lastText = heading.textContent.trim();
            // Find current question index from progressText
            const pText = document.getElementById('progress-text');
            if (!pText) return;
            const match = pText.textContent.match(/(\d+)/);
            if (!match) return;
            const idx = parseInt(match[1]) - 1;
            const questions = @json($quiz->questions);
            if (questions[idx]) setTimeout(() => readQuestion(questions[idx]), 200);
        }
    });
    if (qContent) obs.observe(qContent, { childList: true, subtree: true });

    window.addEventListener('beforeunload', () => synth.cancel());
})();
</script>
@endif
@endsection
