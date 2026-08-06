@extends('layouts.dashboard')

@push('styles')
<style>
    .option-card {
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid #e9ecef;
    }
    .option-card:hover {
        border-color: #0d6efd !important;
        background-color: #f0f7ff !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15) !important;
    }
    .option-card.selected {
        border-color: #0d6efd !important;
        background-color: #e7f1ff !important;
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
@php $isReadWrite = auth()->user()?->learning_style === 'read_write'; @endphp

<div class="container-fluid px-4 py-5" style="min-height: 100vh; background-color: transparent;">
    <div class="row justify-content-center">
        <div class="{{ $isReadWrite ? 'col-lg-8' : 'col-lg-12' }} quiz-container">
            <!-- Header & Progress -->
            <div class="bg-white p-3 rounded-4 mb-4 border" style="box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-color: #e2e8f0 !important;">
                <div class="d-flex align-items-center mb-3">
                    <a href="javascript:history.back()" class="text-dark text-decoration-none fw-bold fs-5 d-flex align-items-center" style="opacity: 0.9; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'">
                        <i class="bi bi-arrow-left me-2 text-muted"></i> {{ $quiz->title }}
                    </a>
                </div>
                <div class="d-flex justify-content-between text-muted mt-1 mb-2 fw-bold" style="font-size: 0.8rem; padding: 0 2px;">
                    <span id="progress-text">Question 1 of {{ $quiz->questions->count() }}</span>
                </div>
                <div class="progress border-0" style="height: 6px; border-radius: 50px; background-color: #f1f5f9;">
                    <div class="progress-bar bg-primary" id="progress-bar" role="progressbar" style="width: 0%; border-radius: 50px; transition: width 0.3s ease;"></div>
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
                    
                    <form action="{{ route('student.submit', ['topic' => $quiz->topic, 'difficulty' => $quiz->difficulty, 'title' => $quiz->title]) }}" method="POST" id="submit-form">
                        @csrf
                        <input type="hidden" name="score" id="score-input">
                        <input type="hidden" name="answers" id="answers-input">
                        <input type="hidden" name="title" value="{{ $quiz->title }}">
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="bi bi-check-circle me-2"></i> Submit Result
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if($isReadWrite)
            <div class="col-lg-4">
                {{-- Notepad Widget --}}
                @php
                    $existingNote = \App\Models\StudentNote::where('user_id', auth()->id())
                        ->where('resource_type', 'quiz')
                        ->where('topic', $quiz->topic)
                        ->where('difficulty', $quiz->difficulty)
                        ->first();
                @endphp
                <div class="card border-success shadow-sm sticky-top" style="top: 20px; z-index: 100;">
                    <div class="card-header bg-success text-white d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-1"></i> Quiz Notes & Acronyms</h6>
                        <span id="save-status" class="small text-white-50">Auto-saved</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.72rem;">Quiz Details</small>
                            <span class="badge bg-light text-dark border me-1">{{ $quiz->topic ?? 'General' }}</span>
                            <span class="badge bg-secondary text-capitalize">{{ $quiz->difficulty }}</span>
                        </div>
                        <div class="mb-3">
                            <label for="note-title" class="form-label small fw-bold text-uppercase text-muted mb-1" style="font-size: 0.72rem;">Note Title</label>
                            <input type="text" id="note-title" class="form-control form-control-sm fw-bold" 
                                   value="{{ $existingNote ? $existingNote->title : 'Notes: ' . $quiz->title }}" 
                                   placeholder="Title of your note...">
                        </div>
                        <div class="mb-3">
                            <label for="note-content" class="form-label small fw-bold text-uppercase text-muted mb-1" style="font-size: 0.72rem;">Acronyms & Notes</label>
                            <textarea id="note-content" class="form-control form-control-sm" rows="12" 
                                      placeholder="Write your quiz acronyms, summaries, and key points here...">{{ $existingNote ? $existingNote->content : '' }}</textarea>
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
                topic: "{{ $quiz->topic ?? 'General' }}",
                difficulty: "{{ $quiz->difficulty }}",
                title: title,
                content: content,
                resource_type: 'quiz',
                resource_id: null
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                statusSpan.textContent = 'Auto-saved';
                statusSpan.style.color = 'rgba(255,255,255,0.7)';
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
    const learningStyle = "{{ auth()->user()?->learning_style }}";
    let currentQuestionIndex = 0;
    let userAnswers = {};
    let score = 0;
    let lastSpokenText = null;

    function renderQuestion(index) {
        if (typeof synth !== 'undefined') {
            synth.cancel();
        }
        lastSpokenText = null;

        if(index >= questions.length) {
            showResults();
            return;
        }

        const q = questions[index];
        const type = q.type || 'mcq';
        const progressPct = ((index + 1) / questions.length) * 100;
        progressBar.style.width = `${progressPct}%`;
        progressText.innerText = `Question ${index + 1} of ${questions.length}`;

        const isKinestheticEasy = (learningStyle.toLowerCase() === 'kinesthetic' || learningStyle.toLowerCase() === 'kinaesthetic');
        let inputHtml = '';

        if (type === 'mcq' && q.options) {
            // MCQ Rendering
            const opts = q.options;
            let optIdx = 0;
            ['a', 'b', 'c', 'd'].forEach(key => {
                if(opts[key]) {
                    const savedOptVal = localStorage.getItem(`hl_quiz_${q.id}_opt_${optIdx}`) || opts[key];
                    const draggableAttr = isKinestheticEasy ? `draggable="true" ondragstart="handleDragStart(event, '${key}')"` : '';
                    inputHtml += `
                        <div class="card option-card mb-3" ${draggableAttr} onclick="selectOption('${key}', true)" id="opt-${key}" style="${isKinestheticEasy ? 'cursor: grab; user-select: none;' : ''}">
                            <div class="card-body d-flex align-items-center">
                                <div class="btn btn-sm btn-outline-primary me-3 text-uppercase fw-bold" style="width: 32px; height: 32px; padding: 0; line-height: 30px; text-align: center;">${key}</div>
                                <span class="fs-5">${savedOptVal}</span>
                            </div>
                        </div>
                    `;
                    optIdx++;
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

        let kbatGuideHtml = '';
        if (quizDifficulty === 'hard') {
            kbatGuideHtml = `
                <div class="alert alert-info border-0 p-3 mb-4 shadow-sm" style="background-color: #f0f7ff; border-left: 4px solid #0284c7 !important; border-radius: 10px;">
                    <h6 class="fw-bold text-primary mb-2" style="font-size: 0.95rem;"><i class="bi bi-info-circle-fill me-1"></i> How to answer KBAT questions:</h6>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <div class="bg-white border rounded px-3 py-2 small shadow-sm d-flex align-items-center"><strong class="text-dark me-1">Isi</strong> <span class="text-muted">(1m)</span></div>
                        <div class="bg-white border rounded px-3 py-2 small shadow-sm d-flex align-items-center"><strong class="text-dark me-1">Huraian</strong> <span class="text-muted">(1m)</span></div>
                        <div class="bg-white border rounded px-3 py-2 small shadow-sm d-flex align-items-center"><strong class="text-dark me-1">Huraian Lengkap</strong> <span class="text-muted">&nbsp;- Contoh / Kesan (1m)</span></div>
                        <div class="bg-white border rounded px-3 py-2 small shadow-sm d-flex align-items-center"><strong class="text-dark me-1">Kesimpulan</strong> <span class="text-muted">(1m)</span></div>
                    </div>
                </div>
            `;
        }

        const savedTitle = localStorage.getItem(`hl_quiz_${q.id}_title`) || q.question_text;

        let dropzoneHtml = '';
        if (type === 'mcq' && isKinestheticEasy) {
            dropzoneHtml = `
                <div class="mt-4">
                    <label class="form-label text-success small text-uppercase fw-bold">Answer :</label>
                    <div id="answer-dropzone" 
                         class="border border-success rounded p-3 text-center text-success bg-success bg-opacity-10 d-flex align-items-center justify-content-center" 
                         style="min-height: 85px; border-style: dashed !important; border-width: 2px !important; transition: all 0.2s ease-in-out;"
                         ondragover="handleDragOver(event)"
                         ondragleave="handleDragLeave(event)"
                         ondrop="handleDrop(event)">
                         <strong>Drag & drop your answer card here or tap to select</strong>
                    </div>
                </div>
            `;
        }

        const html = `
            <div class="card border-0 shadow-sm question-card animated fadeIn">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                        <div class="text-dark mb-0" style="font-size: 28px; font-weight: 600; line-height: 1.45;">${savedTitle}</div>
                        @if(auth()->user()?->learning_style === 'auditory')
                        <button type="button" class="btn btn-light rounded-circle shadow-sm border d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; padding: 0;" onclick="speakQuestionAndChoices(${index})" title="Listen to question and choices">
                            <i class="bi bi-volume-up-fill text-primary fs-5"></i>
                        </button>
                        @endif
                    </div>
                    ${kbatGuideHtml}
                    <div class="options-list">
                        ${inputHtml}
                    </div>
                    ${dropzoneHtml}
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

            const dropzone = document.getElementById('answer-dropzone');
            if (dropzone) {
                const optText = el.querySelector('.fs-5').innerHTML;
                dropzone.innerHTML = `
                    <div class="d-flex align-items-center w-100 justify-content-between p-1 animated bounceIn">
                        <div class="card option-card selected mb-0 flex-grow-1 border-success bg-success bg-opacity-10" style="border: 1px solid #198754 !important;">
                            <div class="card-body d-flex align-items-center py-2 px-3">
                                <div class="btn btn-sm btn-success me-3 text-uppercase fw-bold" style="width: 32px; height: 32px; padding: 0; line-height: 30px; text-align: center; background-color: #198754; border-color: #198754; color: white;">${key}</div>
                                <span class="fs-5 text-success fw-bold text-start">${optText}</span>
                            </div>
                        </div>
                    </div>
                `;
                dropzone.style.borderColor = '#198754';
                dropzone.style.borderStyle = 'solid';
            }
        }
    };

    window.handleDragStart = function(e, key) {
        e.dataTransfer.setData("text/plain", key);
        const card = document.getElementById(`opt-${key}`);
        if (card) {
            card.style.opacity = '0.5';
        }
    };

    document.addEventListener('dragend', function(e) {
        document.querySelectorAll('.option-card').forEach(el => {
            el.style.opacity = '';
        });
    });

    window.handleDragOver = function(e) {
        e.preventDefault();
        const dropzone = document.getElementById('answer-dropzone');
        if (dropzone) {
            dropzone.style.backgroundColor = 'rgba(25, 135, 84, 0.2)';
            dropzone.style.borderColor = '#198754';
        }
    };

    window.handleDragLeave = function(e) {
        const dropzone = document.getElementById('answer-dropzone');
        if (dropzone) {
            dropzone.style.backgroundColor = 'rgba(25, 135, 84, 0.1)';
            dropzone.style.borderColor = '#198754';
        }
    };

    window.handleDrop = function(e) {
        e.preventDefault();
        const key = e.dataTransfer.getData("text/plain");
        if (key) {
            selectOption(key, true);
        }
        const dropzone = document.getElementById('answer-dropzone');
        if (dropzone) {
            dropzone.style.backgroundColor = 'rgba(25, 135, 84, 0.1)';
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
        if (typeof window.speechSynthesis !== 'undefined') {
            window.speechSynthesis.cancel();
        }
        currentQuestionIndex++;
        renderQuestion(currentQuestionIndex);
    };

    window.prevQuestion = function() {
        if(currentQuestionIndex > 0) {
            if (typeof window.speechSynthesis !== 'undefined') {
                window.speechSynthesis.cancel();
            }
            currentQuestionIndex--;
            renderQuestion(currentQuestionIndex);
        }
    };
    function isShortAnswerCorrect(studentAns, correctAns) {
        if (!studentAns || !correctAns) return false;
        const cleanString = (str) => {
            return str
                .toLowerCase()
                .replace(/[.,\/#!$%\^&\*;:{}=\-_`~()]/g, "")
                .replace(/\s+/g, " ")
                .trim();
        };
        const cleanStudent = cleanString(studentAns);
        const alternatives = correctAns.split('|');
        return alternatives.some(alt => cleanStudent === cleanString(alt));
    }

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
                    // Flexible comparison for text
                    if (isShortAnswerCorrect(userAns, correctAns)) {
                        correctCount++;
                    }
                }
            }
        });
        
        const finalScore = Math.round((correctCount / questions.length) * 100);
        
        if (quizDifficulty === 'hard' || quizDifficulty === 'medium') {
            const resultHeader = document.querySelector('#result-screen h4');
            if (resultHeader) {
                resultHeader.innerHTML = "Answers Submitted for Review";
            }
            document.querySelector('#result-screen h2').innerText = "Quiz Completed!";
            scoreInput.value = 0; 
            document.getElementById('feedback-text').innerText = "This quiz contains subjective questions and will be graded manually by your teacher.";
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
<div class="modal fade" id="auditoryTipModal" tabindex="-1" aria-labelledby="auditoryTipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 18px; border-left: 4px solid #e5b181 !important;">
            <div class="modal-header bg-light border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="auditoryTipModalLabel">
                    <span style="font-size: 1.5rem;">🎵</span> AUDITORY STUDY TIP
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-0 text-dark" style="font-size: 1rem; line-height: 1.6; color: #7c2d12 !important;">
                    After taking a quiz today, recite the questions and correct answers aloud. Explaining the concepts in your own words helps solidify the knowledge.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-primary px-4 fw-bold" style="border-radius: 10px; background-color: #e5b181; border-color: #e5b181;" data-bs-dismiss="modal">Start Quiz</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function showTipModal() {
            if (window.bootstrap && window.bootstrap.Modal) {
                const modalEl = document.getElementById('auditoryTipModal');
                if (modalEl) {
                    const myModal = new window.bootstrap.Modal(modalEl);
                    myModal.show();
                }
            } else {
                setTimeout(showTipModal, 50);
            }
        }
        showTipModal();
    });

// ── Auditory Quiz Read-Aloud ────────────────────────────────────────
(function() {
    const synth = window.speechSynthesis;
    const questions = @json($quiz->questions);
    let availableVoices = [];

    function loadVoices() {
        availableVoices = synth.getVoices();
    }
    loadVoices();
    if (speechSynthesis.onvoiceschanged !== undefined) {
        speechSynthesis.onvoiceschanged = loadVoices;
    }

    window.speakQuestionAndChoices = function(index) {
        const q = questions[index];
        if (!q) return;

        let plainText = q.question_text || '';
        
        if (q.type === 'mcq' && q.options) {
            const labels = { a: 'A', b: 'B', c: 'C', d: 'D' };
            ['a', 'b', 'c', 'd'].forEach(key => {
                if (q.options[key]) {
                    plainText += '. ' + labels[key] + ': ' + q.options[key];
                }
            });
        }

        // Clean formatting
        plainText = plainText.replace(/<[^>]*>?/gm, ''); // strip html
        plainText = plainText.replace(/[\r\n]+/g, ' ').replace(/\s{2,}/g, ' ').trim();

        if (synth.speaking && lastSpokenText === plainText) {
            synth.cancel();
            lastSpokenText = null;
            return;
        }

        synth.cancel();
        lastSpokenText = plainText;

        if (availableVoices.length === 0) {
            availableVoices = synth.getVoices();
        }

        if (!plainText) return;

        // Split by punctuation for speech chunks
        let chunks = plainText.split(/(?<=[.!?])\s+/);
        let safeChunks = [];
        for (let chunk of chunks) {
            chunk = chunk.trim();
            if (!chunk) continue;
            if (chunk.length > 200) {
                let parts = chunk.match(/.{1,180}(?:\s|$)/g) || [chunk];
                safeChunks.push(...parts);
            } else {
                safeChunks.push(chunk);
            }
        }

        // Speak the text
        setTimeout(() => {
            safeChunks.forEach(chunkText => {
                chunkText = chunkText.trim();
                if (!chunkText) return;
                const u = new SpeechSynthesisUtterance(chunkText);
                
                let malayVoice = availableVoices.find(v => v.lang.includes('ms') || v.name.toLowerCase().includes('malay'));
                let indoVoice = availableVoices.find(v => v.lang.includes('id') || v.name.toLowerCase().includes('indonesia'));

                if (malayVoice) {
                    u.voice = malayVoice;
                    u.lang = malayVoice.lang;
                } else if (indoVoice) {
                    u.voice = indoVoice;
                    u.lang = indoVoice.lang;
                } else {
                    u.lang = 'id-ID'; 
                }
                
                u.rate = 0.95;
                
                u.onend = function() {
                    if (!synth.speaking) {
                        lastSpokenText = null;
                    }
                };

                synth.speak(u);
            });
        }, 50);
    };

    window.addEventListener('beforeunload', () => synth.cancel());
})();
</script>
@endif

@if(auth()->user()?->learning_style === 'visual')
<style>
    .visual-hl {
        border-radius: 3px;
        padding: 1px 0;
    }
    #visual-highlighter-toolbar button:hover {
        transform: scale(1.15);
        transition: transform 0.1s ease;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let toolbarEl = null;

        function initHighlighter() {
            toolbarEl = document.createElement('div');
            toolbarEl.id = 'visual-highlighter-toolbar';
            toolbarEl.style.position = 'absolute';
            toolbarEl.style.display = 'none';
            toolbarEl.style.zIndex = '99999';
            toolbarEl.style.background = '#ffffff';
            toolbarEl.style.border = '1px solid #dee2e6';
            toolbarEl.style.borderRadius = '30px';
            toolbarEl.style.padding = '6px 12px';
            toolbarEl.style.boxShadow = '0 4px 15px rgba(0,0,0,0.15)';
            toolbarEl.style.alignItems = 'center';
            toolbarEl.style.gap = '8px';
            
            const highlights = [
                { color: '#fef08a', name: 'Yellow' },
                { color: '#bbf7d0', name: 'Green' },
                { color: '#bfdbfe', name: 'Blue' },
                { color: '#fbcfe8', name: 'Pink' }
            ];
            
            const underlines = [
                { color: '#ef4444', name: 'Red Underline' },
                { color: '#3b82f6', name: 'Blue Underline' },
                { color: '#10b981', name: 'Green Underline' }
            ];
            
            highlights.forEach(hl => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.style.width = '20px';
                btn.style.height = '20px';
                btn.style.borderRadius = '50%';
                btn.style.backgroundColor = hl.color;
                btn.style.border = '1px solid rgba(0,0,0,0.15)';
                btn.style.cursor = 'pointer';
                btn.title = `Highlight ${hl.name}`;
                btn.onclick = (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    highlightSelection(hl.color, false);
                };
                toolbarEl.appendChild(btn);
            });
            
            const divider1 = document.createElement('div');
            divider1.style.width = '1px';
            divider1.style.height = '16px';
            divider1.style.backgroundColor = '#dee2e6';
            toolbarEl.appendChild(divider1);
            
            underlines.forEach(ul => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.style.width = '20px';
                btn.style.height = '20px';
                btn.style.backgroundColor = 'transparent';
                btn.style.border = 'none';
                btn.style.cursor = 'pointer';
                btn.style.display = 'flex';
                btn.style.alignItems = 'center';
                btn.style.justifyContent = 'center';
                btn.title = ul.name;
                btn.onclick = (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    highlightSelection(ul.color, true);
                };
                
                const icon = document.createElement('span');
                icon.innerHTML = 'U';
                icon.style.textDecoration = 'underline';
                icon.style.fontWeight = 'bold';
                icon.style.color = ul.color;
                icon.style.fontSize = '12px';
                btn.appendChild(icon);
                
                toolbarEl.appendChild(btn);
            });

            const divider2 = document.createElement('div');
            divider2.style.width = '1px';
            divider2.style.height = '16px';
            divider2.style.backgroundColor = '#dee2e6';
            toolbarEl.appendChild(divider2);
            
            const clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.style.width = '20px';
            clearBtn.style.height = '20px';
            clearBtn.style.border = 'none';
            clearBtn.style.background = 'transparent';
            clearBtn.style.cursor = 'pointer';
            clearBtn.title = 'Remove Highlights';
            clearBtn.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                clearSelectionHighlights();
            };
            clearBtn.innerHTML = '<i class="bi bi-eraser" style="font-size: 12px; color: #6c757d;"></i>';
            toolbarEl.appendChild(clearBtn);
            
            document.body.appendChild(toolbarEl);
            
            document.addEventListener('mouseup', handleTextSelection);
            document.addEventListener('keyup', handleTextSelection);
            
            document.addEventListener('mousedown', function(e) {
                if (toolbarEl && !toolbarEl.contains(e.target)) {
                    setTimeout(() => {
                        const selection = window.getSelection();
                        if (selection.isCollapsed) {
                            hideHighlighterToolbar();
                        }
                    }, 150);
                }
            });
        }

        function highlightSelection(color, isUnderline) {
            const selection = window.getSelection();
            if (!selection.rangeCount) return;
            const range = selection.getRangeAt(0);
            
            if (selection.toString().trim() === '') return;
            
            let container = range.commonAncestorContainer;
            if (container.nodeType === 3) container = container.parentNode;
            
            if (!container.closest('.question-card') && !container.closest('.option-card') && !container.closest('.quiz-container')) {
                return;
            }

            try {
                const span = document.createElement('span');
                if (isUnderline) {
                    span.style.borderBottom = `3px solid ${color}`;
                    span.style.paddingBottom = '1px';
                } else {
                    span.style.backgroundColor = color;
                }
                span.className = 'visual-hl';
                range.surroundContents(span);
            } catch (e) {
                try {
                    const span = document.createElement('span');
                    if (isUnderline) {
                        span.style.borderBottom = `3px solid ${color}`;
                        span.style.paddingBottom = '1px';
                    } else {
                        span.style.backgroundColor = color;
                    }
                    span.className = 'visual-hl';
                    span.appendChild(range.extractContents());
                    range.insertNode(span);
                } catch (err) {
                    console.error('Highlight failed:', err);
                }
            }
            
            saveCurrentQuizHighlights();
            selection.removeAllRanges();
            hideHighlighterToolbar();
        }

        function saveCurrentQuizHighlights() {
            const currentCard = questions[currentQuestionIndex];
            if (!currentCard) return;
            const qTitle = document.querySelector('.question-card h4');
            if (qTitle) {
                localStorage.setItem(`hl_quiz_${currentCard.id}_title`, qTitle.innerHTML);
            }
            const optionSpans = document.querySelectorAll('.option-card .fs-5');
            optionSpans.forEach((span, idx) => {
                localStorage.setItem(`hl_quiz_${currentCard.id}_opt_${idx}`, span.innerHTML);
            });
        }

        function clearSelectionHighlights() {
            const selection = window.getSelection();
            if (!selection.rangeCount) return;
            const range = selection.getRangeAt(0);
            
            const allHls = document.querySelectorAll('.visual-hl');
            allHls.forEach(hl => {
                if (range.intersectsNode(hl)) {
                    const parent = hl.parentNode;
                    while (hl.firstChild) {
                        parent.insertBefore(hl.firstChild, hl);
                    }
                    parent.removeChild(hl);
                }
            });
            
            saveCurrentQuizHighlights();
            selection.removeAllRanges();
            hideHighlighterToolbar();
        }

        function handleTextSelection() {
            const selection = window.getSelection();
            if (selection.isCollapsed || !selection.rangeCount) {
                setTimeout(() => {
                    if (window.getSelection().isCollapsed) {
                        hideHighlighterToolbar();
                    }
                }, 100);
                return;
            }
            
            const range = selection.getRangeAt(0);
            let container = range.commonAncestorContainer;
            if (container.nodeType === 3) container = container.parentNode;
            
            if (!container.closest('.question-card') && !container.closest('.option-card') && !container.closest('.quiz-container')) {
                hideHighlighterToolbar();
                return;
            }
            
            const rect = range.getBoundingClientRect();
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
            
            toolbarEl.style.display = 'flex';
            toolbarEl.style.top = `${rect.top + scrollTop - toolbarEl.offsetHeight - 10}px`;
            toolbarEl.style.left = `${rect.left + scrollLeft + (rect.width / 2) - (toolbarEl.offsetWidth / 2)}px`;
        }

        function hideHighlighterToolbar() {
            if (toolbarEl) {
                toolbarEl.style.display = 'none';
            }
        }

        initHighlighter();
    });
</script>
@endif

@endsection
