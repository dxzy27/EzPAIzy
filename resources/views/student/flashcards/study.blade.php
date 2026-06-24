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
        background-color: #1e293b !important; /* Premium Slate/Navy for Student Front */
        color: white !important;
        display: flex;
        flex-direction: column;
        padding: 2rem;
        font-size: 1.5rem;
        overflow-y: auto;
    }
    
    .flashcard-face::-webkit-scrollbar {
        width: 8px;
    }
    .flashcard-face::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
    }
    .flashcard-face::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 4px;
    }
    .flashcard-face::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }
    
    .flashcard-content-wrapper {
        min-height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }
    
    .flashcard-content {
        margin: auto 0;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .flashcard-front {
        border: 1px solid #0f172a;
    }
    
    .flashcard-back {
        background-color: #334155 !important; /* Lighter Slate for Student Back */
        color: white !important;
        transform: rotateY(180deg);
        border: 1px solid #1e293b;
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
                    <div class="btn-group mt-2" role="group" aria-label="Mode toggle">
                        <button type="button" class="btn btn-sm btn-outline-primary active" id="btn-mode-read" onclick="setMode('read')">
                            <i class="bi bi-book"></i> Read Mode
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-mode-review" onclick="setMode('review')">
                            <i class="bi bi-psychology"></i> Review Mode
                        </button>
                    </div>
                </div>
                <!-- Auto-read removed as requested -->
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
    let cards = [];
    let currentIndex = 0;
    
    document.addEventListener('DOMContentLoaded', function() {
        cards = {!! json_encode($dueCards) !!};
        const app = document.getElementById('flashcard-app');
        
        let mode = 'read';
        
        let isFlipped = false;
        let isSubmitting = false;
        let typedAnswer = '';

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
            const normalizedDefinition = currentCard.definition.replace(/\s+/g, ' ').trim();
            const isList = /(?:\s+|^)\d+\.\s/.test(normalizedDefinition);
            const alignClass = isList ? 'text-start d-inline-block w-100' : 'text-center';
            const formattedDef = normalizedDefinition.replace(/(?:\s+)(\d+\.)\s/g, '<div style="margin-top: 15px;"></div>$1 ');
            
            let controlsHtml = '';
            
            if (!isFlipped) {
                controlsHtml = `
                    <div class="text-center mt-4">
                        <p class="text-muted mb-2">Think of the answer, then tap the card to flip${mode === 'review' ? ' and type it' : ''}.</p>
                    </div>
                `;
            } else {
                if (mode === 'read') {
                    controlsHtml = ``;
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
            }

            // Always add Next/Prev buttons
            controlsHtml += `
                <div class="mt-4 text-center d-flex justify-content-center gap-3">
                    <button class="btn btn-outline-secondary px-4 py-2" onclick="prevCard()" ${currentIndex === 0 ? 'disabled' : ''}>
                        Previous
                    </button>
                    <button class="btn btn-primary px-4 py-2" onclick="nextCard()">
                        Next
                    </button>
                </div>
            `;

            let backFaceHtml = '';
            if (isFlipped) {
                if (mode === 'read') {
                    backFaceHtml = `
                        <div class="d-flex justify-content-between position-absolute w-100" style="top: 1rem; left: 0; padding: 0 1.5rem; z-index: 10;">
                            <span class="badge bg-warning bg-opacity-25 text-warning border border-warning fw-bold" onclick="flipCard(event)" style="cursor:pointer;">BACK</span>
                            <div class="d-flex align-items-center gap-2">
                                @if(auth()->user()?->learning_style === 'auditory')
                                <button type="button" class="btn btn-sm btn-light rounded-circle" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;" onclick="event.stopPropagation(); speakText(cards[currentIndex].definition);" onmousedown="event.stopPropagation();" onpointerdown="event.stopPropagation();" title="Read Answer">
                                    <i class="bi bi-volume-up-fill text-primary" style="pointer-events:none;"></i>
                                </button>
                                @endif
                                <small class="text-white-50" style="font-size: 0.8rem; cursor:pointer;" onclick="flipCard(event)"><i class="bi bi-hand-index-thumb"></i> Tap to flip</small>
                            </div>
                        </div>
                        <div class="flashcard-content-wrapper mt-3" onclick="flipCard(event)" style="cursor:pointer;">
                            <div class="flashcard-content">
                                <div class="${alignClass}">
                                    <div class="fs-3 text-white fw-bold mt-3" style="line-height: 1.4;">${formattedDef}</div>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    // Generate the hidden answer display
                    const numberMarkerIndices = new Set();
                    const regex = /(?:^|\s)(\d+\.)(?=\s)/g;
                    let match;
                    while ((match = regex.exec(normalizedDefinition)) !== null) {
                        const start = match.index + (match[0].startsWith(' ') ? 1 : 0);
                        for (let j = 0; j < match[1].length; j++) {
                            numberMarkerIndices.add(start + j);
                        }
                    }

                    let answerWords = '';
                    for (let i = 0; i < normalizedDefinition.length; i++) {
                        if (normalizedDefinition[i] === ' ') {
                            if (i === 0 || normalizedDefinition[i-1] !== ' ') {
                                if (normalizedDefinition.substring(i).match(/^\s+\d+\.\s/)) {
                                    answerWords += '<div style="margin-top: 15px;"></div>';
                                    continue;
                                }
                            }
                            answerWords += ' &nbsp; ';
                        } else if (numberMarkerIndices.has(i)) {
                            answerWords += normalizedDefinition[i];
                        } else {
                            answerWords += '_';
                        }
                    }
                    
                    backFaceHtml = `
                        <div class="d-flex justify-content-between position-absolute w-100" style="top: 1rem; left: 0; padding: 0 1.5rem; z-index: 10;">
                            <span class="badge bg-warning bg-opacity-25 text-warning border border-warning fw-bold" onclick="flipCard(event)" style="cursor:pointer;">BACK</span>
                            <div class="d-flex align-items-center gap-2">
                                @if(auth()->user()?->learning_style === 'auditory')
                                <button id="review-speak-btn" type="button" class="btn btn-sm btn-light rounded-circle d-none" style="width:30px;height:30px;padding:0;align-items:center;justify-content:center;" onclick="event.stopPropagation(); speakText(cards[currentIndex].definition);" onmousedown="event.stopPropagation();" onpointerdown="event.stopPropagation();" title="Read Answer">
                                    <i class="bi bi-volume-up-fill text-primary" style="pointer-events:none;"></i>
                                </button>
                                @endif
                                <small class="text-white-50" style="font-size: 0.8rem; cursor:pointer;" onclick="flipCard(event)"><i class="bi bi-hand-index-thumb"></i> Tap to flip</small>
                            </div>
                        </div>
                        <div class="flashcard-content-wrapper mt-3" onclick="flipCard(event)" style="cursor:pointer;">
                            <div class="flashcard-content">
                                <div class="${alignClass}">
                                    <div id="placeholder-text" class="fs-4 fw-bold mt-3" style="letter-spacing: 3px; font-family: monospace; line-height: 1.4;">${answerWords}</div>
                                </div>
                                
                                <input type="text" id="answer-input" class="form-control text-center mt-4 mx-auto" 
                                       style="max-width: 80%; background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.5);" 
                                       autocomplete="off" autocorrect="off" spellcheck="false" 
                                       value="${typedAnswer.replace(/"/g, '&quot;')}"
                                       placeholder="Type the exact answer..." oninput="checkTyping(this.value)" onclick="event.stopPropagation()">
                                       
                                <div id="revealed-answer" class="d-none mt-3 ${alignClass}">
                                    <div class="fs-3 text-white fw-bold" style="line-height: 1.4;">${formattedDef}</div>
                                </div>
                            </div>
                        </div>
                    `;
                }
            }

            app.innerHTML = `
                <div class="text-center mb-3">
                    <span class="badge bg-primary">Reviewing Card ${currentIndex + 1} of ${cards.length}</span>
                </div>
                
                <div class="flashcard-container">
                    <div class="flashcard-inner ${isFlipped ? 'is-flipped' : ''}">
                        <div class="flashcard-face flashcard-front">
                            <div class="d-flex justify-content-between position-absolute w-100" style="top: 1rem; left: 0; padding: 0 1.5rem; z-index: 10;">
                                <span class="badge bg-info bg-opacity-25 text-info border border-info fw-bold" onclick="flipCard(event)" style="cursor:pointer;">FRONT</span>
                                <div class="d-flex align-items-center gap-2">
                                    @if(auth()->user()?->learning_style === 'auditory')
                                    <button type="button" class="btn btn-sm btn-light rounded-circle" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;" onclick="event.stopPropagation(); speakText(cards[currentIndex].term);" onmousedown="event.stopPropagation();" onpointerdown="event.stopPropagation();" title="Read Question">
                                        <i class="bi bi-volume-up-fill text-primary" style="pointer-events:none;"></i>
                                    </button>
                                    @endif
                                    <small class="text-white-50" style="font-size: 0.8rem; cursor:pointer;" onclick="flipCard(event)"><i class="bi bi-hand-index-thumb"></i> Tap to flip</small>
                                </div>
                            </div>
                            <div class="flashcard-content-wrapper mt-3" onclick="flipCard(event)" style="cursor:pointer;">
                                <div class="flashcard-content">
                                    <div class="fs-3 text-white fw-bold mt-3" style="line-height: 1.4;">${currentCard.term}</div>
                                </div>
                            </div>
                        </div>
                        <div class="flashcard-face flashcard-back">
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

        window.setMode = function(newMode) {
            mode = newMode;
            document.getElementById('btn-mode-read').classList.toggle('active', mode === 'read');
            document.getElementById('btn-mode-review').classList.toggle('active', mode === 'review');
            isFlipped = false;
            render();
        };

        window.flipCard = function(e) {
            if (e && e.target) {
                if (e.target.closest('button')) return;
                if (e.target.closest('.btn')) return;
                if (e.target.closest('input')) return;
            }
            if (isSubmitting) return;
            isFlipped = !isFlipped;
            render();
        };

        window.nextCard = function() {
            if (currentIndex < cards.length - 1) {
                currentIndex++;
                isFlipped = false;
                typedAnswer = '';
                render();
            } else {
                // finished
                currentIndex++;
                render();
            }
        };

        window.prevCard = function() {
            if (currentIndex > 0) {
                currentIndex--;
                isFlipped = false;
                typedAnswer = '';
                render();
            }
        };

        window.checkTyping = function(val) {
            typedAnswer = val;
            const currentCard = cards[currentIndex];
            const correct = currentCard.definition.replace(/\s+/g, ' ').trim();
            const correctLower = correct.toLowerCase();
            const valLower = val.trim().toLowerCase();
            
            // Build the visual string: show typed characters, underscores for the rest
            const numberMarkerIndices = new Set();
            const regex = /(?:^|\s)(\d+\.)(?=\s)/g;
            let match;
            while ((match = regex.exec(correct)) !== null) {
                const start = match.index + (match[0].startsWith(' ') ? 1 : 0);
                for (let j = 0; j < match[1].length; j++) {
                    numberMarkerIndices.add(start + j);
                }
            }

            let display = '';
            let valIndex = 0;
            
            for (let i = 0; i < correct.length; i++) {
                if (correct[i] === ' ') {
                    if (i === 0 || correct[i-1] !== ' ') {
                        if (correct.substring(i).match(/^\s+\d+\.\s/)) {
                            display += '<div style="margin-top: 15px;"></div>';
                            continue;
                        }
                    }
                    display += ' &nbsp; ';
                } else if (numberMarkerIndices.has(i)) {
                    display += correct[i];
                    if (valIndex < val.length && val[valIndex].toLowerCase() === correct[i].toLowerCase()) {
                        valIndex++;
                    }
                } else if (valIndex < val.length) {
                    display += val[valIndex];
                    valIndex++;
                } else {
                    display += '_';
                }
            }
            
            document.getElementById('placeholder-text').innerHTML = display;

            const cleanCorrect = correctLower.replace(/(?:^|\s)\d+\.\s/g, ' ').replace(/\s+/g, ' ').trim();
            const cleanVal = valLower.replace(/(?:^|\s)\d+\.\s/g, ' ').replace(/\s+/g, ' ').trim();

            if (valLower === correctLower || (cleanVal === cleanCorrect && cleanVal.length > 0)) {
                // Success!
                document.getElementById('answer-input').classList.add('d-none');
                document.getElementById('placeholder-text').classList.add('d-none');
                document.getElementById('revealed-answer').classList.remove('d-none');
                const speakBtn = document.getElementById('review-speak-btn');
                if(speakBtn) { speakBtn.classList.remove('d-none'); speakBtn.style.display = 'flex'; }
                
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
            const speakBtn = document.getElementById('review-speak-btn');
            if(speakBtn) { speakBtn.classList.remove('d-none'); speakBtn.style.display = 'flex'; }
            
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
    const synth = window.speechSynthesis;
    let availableVoices = [];

    // Chrome loads voices asynchronously
    function loadVoices() {
        availableVoices = synth.getVoices();
    }
    loadVoices();
    if (speechSynthesis.onvoiceschanged !== undefined) {
        speechSynthesis.onvoiceschanged = loadVoices;
    }

    window.speakText = function(text) {
        synth.cancel();
        const temp = document.createElement("div");
        temp.innerHTML = text;
        let plainText = temp.textContent || temp.innerText || "";
        plainText = plainText.replace(/[\r\n]+/g, ' ').replace(/\s{2,}/g, ' ').trim();
        
        // Chrome silently drops TTS requests if the text is longer than ~200-250 characters.
        // Flashcard answers can be long, so we must chunk them by words.
        let chunks = [];
        if (plainText.length > 200) {
            let words = plainText.split(' ');
            let currentChunk = '';
            words.forEach(word => {
                if ((currentChunk + word).length > 180) {
                    chunks.push(currentChunk.trim());
                    currentChunk = word + ' ';
                } else {
                    currentChunk += word + ' ';
                }
            });
            if (currentChunk.trim()) chunks.push(currentChunk.trim());
        } else {
            chunks = [plainText];
        }

        chunks.forEach(chunkText => {
            if (!chunkText) return;
            const u = new SpeechSynthesisUtterance(chunkText);
            
            let malayVoice = availableVoices.find(v => v.lang.includes('ms-MY') || v.lang.includes('ms_MY') || v.name.toLowerCase().includes('malay'));
            let indoVoice = availableVoices.find(v => v.lang.includes('id-ID') || v.lang.includes('id_ID') || v.name.toLowerCase().includes('indonesia'));

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
            synth.speak(u);
        });
    };
    
    window.addEventListener('beforeunload', () => synth.cancel());
</script>
@endif
@endpush
