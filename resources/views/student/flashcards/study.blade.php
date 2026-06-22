@extends('layouts.dashboard')

@push('styles')
<style>
    .flashcard-container {
        perspective: 1000px;
        height: 400px;
        width: 100%;
        max-width: 600px;
        margin: 0 auto;
        cursor: pointer;
    }
    
    .flashcard-inner {
        position: relative;
        width: 100%;
        height: 100%;
        text-align: center;
        transition: transform 0.6s cubic-bezier(0.4, 0.2, 0.2, 1);
        transform-style: preserve-3d;
    }
    
    .flashcard-inner.is-flipped {
        transform: rotateY(180deg);
    }
    
    .flashcard-face {
        position: absolute;
        width: 100%;
        height: 100%;
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
        border-radius: 1rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        background-color: #1abc9c !important;
        color: white !important;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 2rem;
        font-size: 1.5rem;
    }
    
    .flashcard-front {
        border: 1px solid #16a085;
    }
    
    .flashcard-back {
        background-color: #16a085 !important;
        color: white !important;
        transform: rotateY(180deg);
        border: 1px solid #12876f;
    }

    .controls {
        max-width: 600px;
        margin: 20px auto;
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="row mb-5">
        <div class="col-12">
            <a href="{{ route('student.flashcards.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
                <i class="bi bi-arrow-left"></i> Back to Sets
            </a>
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                <div>
                    <h1>Study: {{ $flashcardSet->title }}</h1>
                    <p class="text-muted"><i class="bi bi-psychology text-primary"></i> Smart Review Mode</p>
                </div>
                @if(auth()->user()?->learning_style === 'auditory')
                <div class="d-flex align-items-center gap-2 mt-1"
                     style="background:#e0f2fe;border:1.5px solid #bae6fd;border-radius:12px;padding:8px 14px;">
                    <i class="bi bi-ear-fill" style="color:#0891b2;font-size:1rem;"></i>
                    <span style="font-size:.82rem;font-weight:600;color:#0c4a6e;">Auto-read cards</span>
                    <div class="form-check form-switch mb-0 ms-1">
                        <input class="form-check-input" type="checkbox" id="tts-toggle"
                               style="cursor:pointer;width:2.2em;height:1.1em;"
                               checked onchange="ttsToggleChanged(this.checked)">
                    </div>
                    <button onclick="ttsRepeat()" title="Read again"
                            style="background:rgba(8,145,178,.15);border:1px solid #7dd3fc;
                                   color:#0c4a6e;border-radius:8px;padding:3px 10px;
                                   font-size:.8rem;cursor:pointer;">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Flashcard Display -->
    <div id="flashcard-app">
        <!-- Rendered by JS -->
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cards = {!! json_encode($dueCards) !!};
        const app = document.getElementById('flashcard-app');
        
        let currentIndex = 0;
        let isFlipped = false;
        let isSubmitting = false;

        function render() {
            if (cards.length === 0 || currentIndex >= cards.length) {
                app.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-emoji-sunglasses display-1 text-warning mb-3"></i>
                        <h2>You're all caught up!</h2>
                        <p class="text-muted fs-5">There are no cards due for review right now. Great job!</p>
                        <div class="d-flex justify-content-center gap-3 mt-4">
                            <a href="{{ route('student.flashcards.index') }}" class="btn btn-primary">Back to Flashcards</a>
                            <form action="{{ route('student.flashcards.reset', $flashcardSet->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to reset your progress for this set? All spaced repetition data will be deleted.')">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset Progress
                                </button>
                            </form>
                        </div>
                    </div>
                `;
                return;
            }

            const currentCard = cards[currentIndex];
            
            let controlsHtml = '';
            
            if (!isFlipped) {
                controlsHtml = `
                    <div class="text-center mt-4">
                        <p class="text-muted mb-2">Think of the answer, then tap the card to flip and type it.</p>
                    </div>
                `;
            } else {
                controlsHtml = `
                    <div id="typing-controls" class="mt-4 text-center">
                        <button class="btn btn-outline-secondary" onclick="revealAnswer()">I give up, show answer</button>
                    </div>
                    <div id="grading-controls" class="mt-4 text-center d-none">
                        <p class="fw-bold mb-3" id="grading-message">How well did you remember this?</p>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <button class="btn btn-danger px-4 py-2" onclick="submitReview(${currentCard.id}, 0)" ${isSubmitting ? 'disabled' : ''}>
                                Again
                            </button>
                            <button class="btn btn-warning px-4 py-2" onclick="submitReview(${currentCard.id}, 3)" ${isSubmitting ? 'disabled' : ''}>
                                Hard
                            </button>
                            <button class="btn btn-success px-4 py-2" onclick="submitReview(${currentCard.id}, 4)" ${isSubmitting ? 'disabled' : ''}>
                                Good
                            </button>
                            <button class="btn btn-primary px-4 py-2" onclick="submitReview(${currentCard.id}, 5)" ${isSubmitting ? 'disabled' : ''}>
                                Easy
                            </button>
                        </div>
                    </div>
                `;
            }

            let backFaceHtml = '';
            if (isFlipped) {
                // Generate the hidden answer display
                const answerWords = currentCard.definition.split(' ').map(word => '_'.repeat(word.length)).join(' &nbsp; ');
                
                backFaceHtml = `
                    <small class="text-white-50 text-uppercase fw-bold mb-3" style="font-size: 0.8rem;">${currentCard.term}</small>
                    <p id="placeholder-text" class="fs-4 fw-bold mt-3" style="letter-spacing: 3px; font-family: monospace;">${answerWords}</p>
                    
                    <input type="text" id="answer-input" class="form-control text-center mt-4 mx-auto" 
                           style="max-width: 80%; background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.5);" 
                           autocomplete="off" autocorrect="off" spellcheck="false" 
                           placeholder="Type the exact answer..." oninput="checkTyping(this.value)">
                           
                    <div id="revealed-answer" class="d-none mt-3">
                        <p class="fs-3 text-white fw-bold">${currentCard.definition}</p>
                    </div>
                `;
            }

            app.innerHTML = `
                <div class="text-center mb-3">
                    <span class="badge bg-primary">Reviewing Card ${currentIndex + 1} of ${cards.length}</span>
                </div>
                
                <div class="flashcard-container" onclick="flipCard()">
                    <div class="flashcard-inner ${isFlipped ? 'is-flipped' : ''}">
                        <div class="flashcard-face flashcard-front">
                            <small class="text-white-50 text-uppercase fw-bold mb-3" style="font-size: 0.8rem;">Term</small>
                            <p>${currentCard.term}</p>
                            <small class="text-white-50 mt-auto" style="font-size: 0.8rem;"><i class="bi bi-hand-index-thumb"></i> Tap to flip</small>
                        </div>
                        <div class="flashcard-face flashcard-back" style="cursor: default;" onclick="event.stopPropagation()">
                            ${backFaceHtml}
                        </div>
                    </div>
                </div>

                <div class="controls">
                    ${controlsHtml}
                </div>
            `;

            if (isFlipped) {
                setTimeout(() => {
                    const input = document.getElementById('answer-input');
                    if (input) input.focus();
                }, 300); // Wait for flip animation
            }
        }

        window.flipCard = function() {
            if (!isFlipped && !isSubmitting) {
                isFlipped = true;
                render();
            }
        };

        window.checkTyping = function(val) {
            const currentCard = cards[currentIndex];
            const correct = currentCard.definition.trim();
            const correctLower = correct.toLowerCase();
            const valLower = val.trim().toLowerCase();
            
            // Build the visual string: show typed characters, underscores for the rest
            let display = '';
            let valIndex = 0;
            
            for (let i = 0; i < correct.length; i++) {
                if (correct[i] === ' ') {
                    display += ' &nbsp; ';
                } else if (valIndex < val.length) {
                    display += val[valIndex];
                    valIndex++;
                } else {
                    display += '_';
                }
            }
            
            document.getElementById('placeholder-text').innerHTML = display;

            if (valLower === correctLower) {
                // Success!
                document.getElementById('answer-input').classList.add('d-none');
                document.getElementById('placeholder-text').classList.add('d-none');
                document.getElementById('revealed-answer').classList.remove('d-none');
                
                document.getElementById('typing-controls').classList.add('d-none');
                const gradingControls = document.getElementById('grading-controls');
                gradingControls.classList.remove('d-none');
                document.getElementById('grading-message').innerText = 'Perfect! How easy was that?';
            }
        };

        window.revealAnswer = function() {
            document.getElementById('answer-input').classList.add('d-none');
            document.getElementById('placeholder-text').classList.add('d-none');
            document.getElementById('revealed-answer').classList.remove('d-none');
            
            document.getElementById('typing-controls').classList.add('d-none');
            const gradingControls = document.getElementById('grading-controls');
            gradingControls.classList.remove('d-none');
            document.getElementById('grading-message').innerText = 'Answer Revealed. Be honest, grade yourself:';
        };

        window.submitReview = function(flashcardId, quality) {
            if (isSubmitting) return;
            isSubmitting = true;
            render(); // Re-render to show disabled buttons

            fetch(`/student/flashcards/${flashcardId}/review`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ quality: quality })
            })
            .then(response => response.json())
            .then(data => {
                isSubmitting = false;
                currentIndex++;
                isFlipped = false;
                render();
            })
            .catch(error => {
                console.error('Error:', error);
                isSubmitting = false;
                alert('An error occurred. Please try again.');
                render();
            });
        };

        render();
    });
</script>

@if(auth()->user()?->learning_style === 'auditory')
<script>
// ── Auditory TTS for Flashcards ────────────────────────────────────
(function() {
    const synth = window.speechSynthesis;
    let autoRead = true;
    let lastTerm = '';

    window.ttsToggleChanged = function(checked) {
        autoRead = checked;
    };

    function speak(text) {
        synth.cancel();
        const u = new SpeechSynthesisUtterance(text);
        u.lang = 'en-US';
        u.rate = 0.95;
        synth.speak(u);
    }

    window.ttsRepeat = function() {
        if (lastTerm) speak(lastTerm);
    };

    // Hook into the existing render / flipCard functions via MutationObserver
    const app = document.getElementById('flashcard-app');
    const observer = new MutationObserver(function() {
        if (!autoRead) return;

        // Check if a new card front is showing (not flipped)
        const front = app.querySelector('.flashcard-front p');
        const back  = app.querySelector('.flashcard-back #revealed-answer p');

        if (front && front.textContent.trim() !== lastTerm) {
            lastTerm = front.textContent.trim();
            // Small delay so flip animation completes
            setTimeout(() => speak(lastTerm), 400);
        } else if (back) {
            const def = back.textContent.trim();
            setTimeout(() => speak(def), 300);
        }
    });

    if (app) observer.observe(app, { childList: true, subtree: true });

    window.addEventListener('beforeunload', () => synth.cancel());
})();
</script>
@endif
@endpush
