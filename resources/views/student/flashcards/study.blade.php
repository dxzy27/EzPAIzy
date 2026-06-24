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
        transform: rotateY(0deg) translateZ(1px);
    }
    
    .flashcard-back {
        background-color: #334155 !important; /* Lighter Slate for Student Back */
        color: white !important;
        transform: rotateY(180deg) translateZ(1px);
        border: 1px solid #1e293b;
    }

    /* Pointer events control based on active card face to prevent click-through issues */
    .flashcard-inner:not(.is-flipped) .flashcard-front {
        pointer-events: auto !important;
    }
    .flashcard-inner:not(.is-flipped) .flashcard-back {
        pointer-events: none !important;
    }
    .flashcard-inner.is-flipped .flashcard-front {
        pointer-events: none !important;
    }
    .flashcard-inner.is-flipped .flashcard-back {
        pointer-events: auto !important;
    }

    .btn-grade-still {
        background-color: rgba(239, 68, 68, 0.15) !important;
        border: 1px solid rgba(239, 68, 68, 0.4) !important;
        color: #f87171 !important;
        font-weight: 600;
        border-radius: 50px;
        transition: all 0.2s ease-in-out;
        padding: 0.6rem 1.8rem;
    }
    
    .btn-grade-still:hover:not(:disabled) {
        background-color: rgba(239, 68, 68, 0.25) !important;
        border-color: rgba(239, 68, 68, 0.6) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
    }
    
    .btn-grade-know {
        background-color: rgba(16, 185, 129, 0.15) !important;
        border: 1px solid rgba(16, 185, 129, 0.4) !important;
        color: #34d399 !important;
        font-weight: 600;
        border-radius: 50px;
        transition: all 0.2s ease-in-out;
        padding: 0.6rem 1.8rem;
    }
    
    .btn-grade-know:hover:not(:disabled) {
        background-color: rgba(16, 185, 129, 0.25) !important;
        border-color: rgba(16, 185, 129, 0.6) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
    }

    .controls {
        max-width: 600px;
        margin: 20px auto;
    }

    .text-success-green {
        color: #22c55e !important;
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
    let peekTimeout = null;
    
    document.addEventListener('DOMContentLoaded', function() {
        cards = {!! json_encode($dueCards) !!};
        const app = document.getElementById('flashcard-app');
        
        let mode = 'read';
        
        let isFlipped = false;
        let isSubmitting = false;
        let typedAnswer = '';
        let currentItems = [];

        function parseDefinitionItems(definition) {
            let normalized = definition.trim();
            // Match standard lists starting with a digit like "1. ", " 2. ", etc.
            let regex = /(?:^|\s+)(\d+\.)\s+/g;
            let items = [];
            let match;
            let matches = [];

            while ((match = regex.exec(normalized)) !== null) {
                matches.push({
                    number: match[1],
                    index: match.index,
                    fullMatchLength: match[0].length
                });
            }

            if (matches.length > 0) {
                for (let i = 0; i < matches.length; i++) {
                    let start = matches[i].index + matches[i].fullMatchLength;
                    let end = (i + 1 < matches.length) ? matches[i + 1].index : normalized.length;
                    let text = normalized.substring(start, end).trim();
                    items.push({
                        number: matches[i].number,
                        text: text,
                        cleanText: text.toLowerCase().replace(/[^a-z0-9]/g, ''),
                        revealed: false
                    });
                }
            } else {
                items.push({
                    number: '',
                    text: normalized,
                    cleanText: normalized.toLowerCase().replace(/[^a-z0-9]/g, ''),
                    revealed: false
                });
            }
            return items;
        }

        function getPlaceholderHtml(items, activeIndex = -1, typedVal = '') {
            let isList = items.length > 1 || (items[0] && items[0].number);
            let alignClass = isList ? 'text-start d-inline-block w-100' : 'text-center';
            let html = `<div class="${alignClass} px-3">`;
            items.forEach((item, idx) => {
                let displayHtml = '';
                
                if (item.revealed) {
                    displayHtml = `<span class="text-success-green fw-bold">${item.text}</span>`;
                } else if (idx === activeIndex && typedVal.length > 0) {
                    // Align typed text with correct item text in real-time
                    let correctText = item.text;
                    let typedText = typedVal;
                    let display = '';
                    let tIdx = 0;
                    
                    for (let i = 0; i < correctText.length; i++) {
                        let c = correctText[i];
                        if (c === ' ') {
                            display += ' &nbsp; ';
                            if (tIdx < typedText.length && typedText[tIdx] === ' ') {
                                tIdx++;
                            }
                        } else {
                            if (tIdx < typedText.length) {
                                if (typedText[tIdx] === ' ') {
                                    display += ' &nbsp; ';
                                } else {
                                    display += typedText[tIdx];
                                }
                                tIdx++;
                            } else {
                                if (/[a-zA-Z0-9]/.test(c)) {
                                    display += '_';
                                } else {
                                    display += c;
                                }
                            }
                        }
                    }
                    displayHtml = `<span class="text-white fw-bold">${display}</span>`;
                } else {
                    let underscores = '';
                    let words = item.text.split(/\s+/);
                    words.forEach((word, wIdx) => {
                        let wordUnderscores = '';
                        for (let c of word) {
                            if (/[a-zA-Z0-9]/.test(c)) {
                                wordUnderscores += '_';
                            } else {
                                wordUnderscores += c;
                            }
                        }
                        underscores += wordUnderscores + (wIdx < words.length - 1 ? ' &nbsp; ' : '');
                    });
                    displayHtml = `<span class="text-white-50">${underscores}</span>`;
                }

                if (isList && item.number) {
                    html += `
                        <div class="d-flex align-items-start fs-4 mb-2" style="font-family: monospace; letter-spacing: 2px;">
                            <span style="width: 45px; flex-shrink: 0; display: inline-block; text-align: left;">${item.number}</span>
                            ${displayHtml}
                        </div>
                    `;
                } else {
                    html += `
                        <div class="fs-4 mb-2" style="font-family: monospace; letter-spacing: 2px;">
                            ${displayHtml}
                        </div>
                    `;
                }
            });
            html += '</div>';
            return html;
        }

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
                    if (!currentCard._parsedItems) {
                        currentCard._parsedItems = parseDefinitionItems(currentCard.definition);
                    }
                    let allDone = currentCard._parsedItems.every(item => item.revealed);
                    let initialMsg = allDone ? 'Perfect! How easy was that?' : 'How well did you remember this?';

                    controlsHtml = `
                        <div id="grading-controls" class="mt-4 text-center">
                            <p class="fw-bold mb-3" id="grading-message">${initialMsg}</p>
                            <div class="d-flex justify-content-center gap-3">
                                <button class="btn btn-grade-still d-flex align-items-center gap-2" onclick="submitReview(${currentCard.id}, 1)" ${isSubmitting ? 'disabled' : ''}>
                                    <i class="bi bi-x-lg fs-5"></i> Still learning
                                </button>
                                <button class="btn btn-grade-know d-flex align-items-center gap-2" onclick="submitReview(${currentCard.id}, 5)" ${isSubmitting ? 'disabled' : ''}>
                                    <i class="bi bi-check-lg fs-5"></i> Know
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
                                <button type="button" class="btn btn-sm btn-light rounded-circle" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;" onclick="event.stopPropagation(); speakCurrentDefinition();" onmousedown="event.stopPropagation();" onpointerdown="event.stopPropagation();" title="Read Answer">
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
                    if (!currentCard._parsedItems) {
                        currentCard._parsedItems = parseDefinitionItems(currentCard.definition);
                    }
                    currentItems = currentCard._parsedItems;

                    let allDone = currentItems.every(item => item.revealed);
                    let activeIdx = currentItems.findIndex(item => !item.revealed);
                    let initialAnswerWords = getPlaceholderHtml(currentItems, activeIdx, typedAnswer);
                    
                    backFaceHtml = `
                        <div class="d-flex justify-content-between position-absolute w-100" style="top: 1rem; left: 0; padding: 0 1.5rem; z-index: 10;">
                            <span class="badge bg-warning bg-opacity-25 text-warning border border-warning fw-bold" onclick="flipCard(event)" style="cursor:pointer;">BACK</span>
                            <div class="d-flex align-items-center gap-2">
                                <button id="show-answer-btn" type="button" class="btn btn-outline-light text-white-50 border-secondary px-2 py-0.5 d-flex align-items-center justify-content-center ${allDone ? 'd-none' : ''}" style="font-size: 0.75rem; border: 1px solid rgba(255,255,255,0.25); border-radius: 4px; line-height: 1.2; height: 26px;" onclick="event.stopPropagation(); revealAnswer();">
                                    Show Answer
                                </button>
                                @if(auth()->user()?->learning_style === 'auditory')
                                <button id="review-speak-btn" type="button" class="btn btn-sm btn-light rounded-circle ${allDone ? '' : 'd-none'}" style="width:30px;height:30px;padding:0;display:${allDone ? 'flex' : 'none'};align-items:center;justify-content:center;" onclick="event.stopPropagation(); speakCurrentDefinition();" onmousedown="event.stopPropagation();" onpointerdown="event.stopPropagation();" title="Read Answer">
                                    <i class="bi bi-volume-up-fill text-primary" style="pointer-events:none;"></i>
                                </button>
                                @endif
                                <small class="text-white-50" style="font-size: 0.8rem; cursor:pointer;" onclick="flipCard(event)"><i class="bi bi-hand-index-thumb"></i> Tap to flip</small>
                            </div>
                        </div>
                        <div class="flashcard-content-wrapper mt-3" onclick="flipCard(event)" style="cursor:pointer;">
                            <div class="flashcard-content">
                                <div class="w-100">
                                    <div id="placeholder-text" class="mt-3">${initialAnswerWords}</div>
                                </div>
                                
                                <input type="text" id="answer-input" class="form-control text-center mt-4 mx-auto ${allDone ? 'd-none' : ''}" 
                                       style="max-width: 80%; background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.5);" 
                                       autocomplete="off" autocorrect="off" spellcheck="false" 
                                       value="${typedAnswer.replace(/"/g, '&quot;')}"
                                       placeholder="Type the exact answer..." oninput="checkTyping(this.value)" onclick="event.stopPropagation()">
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
                                    <button type="button" class="btn btn-sm btn-light rounded-circle" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;" onclick="event.stopPropagation(); speakCurrentTerm();" onmousedown="event.stopPropagation();" onpointerdown="event.stopPropagation();" title="Read Question">
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
            if (peekTimeout) {
                clearTimeout(peekTimeout);
                peekTimeout = null;
            }
            mode = newMode;
            document.getElementById('btn-mode-read').classList.toggle('active', mode === 'read');
            document.getElementById('btn-mode-review').classList.toggle('active', mode === 'review');
            isFlipped = false;
            // Reset parsed items so they can practice again
            cards.forEach(c => delete c._parsedItems);
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
            if (peekTimeout) {
                clearTimeout(peekTimeout);
                peekTimeout = null;
            }
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
            if (peekTimeout) {
                clearTimeout(peekTimeout);
                peekTimeout = null;
            }
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
            
            // Update display in real-time
            const placeholderEl = document.getElementById('placeholder-text');
            if (placeholderEl) {
                let activeIdx = currentItems.findIndex(item => !item.revealed);
                placeholderEl.innerHTML = getPlaceholderHtml(currentItems, activeIdx, val);
            }

            const cleanInput = val.trim().toLowerCase().replace(/[^a-z0-9]/g, '');
            if (cleanInput.length === 0) return;

            // ── FULL PHRASE MATCH CHECK ─────────────────────────────────────
            // We build the full correct answer without spaces/formatting
            const correctAllClean = currentCard.definition.toLowerCase().replace(/[^a-z0-9]/g, '');
            const correctItemsClean = currentItems.map(item => item.cleanText).join('');
            
            if (cleanInput === correctAllClean || cleanInput === correctItemsClean) {
                // Success for all items!
                currentItems.forEach(item => item.revealed = true);
                
                setTimeout(() => {
                    const inputEl = document.getElementById('answer-input');
                    if (inputEl) {
                        inputEl.value = '';
                        inputEl.classList.add('d-none');
                    }
                    typedAnswer = '';
                }, 10);

                const placeholderEl = document.getElementById('placeholder-text');
                if (placeholderEl) {
                    placeholderEl.innerHTML = getPlaceholderHtml(currentItems);
                }

                const speakBtn = document.getElementById('review-speak-btn');
                if (speakBtn) {
                    speakBtn.classList.remove('d-none');
                    speakBtn.style.display = 'flex';
                }
                
                const showAnswerBtn = document.getElementById('show-answer-btn');
                if (showAnswerBtn) showAnswerBtn.classList.add('d-none');
                
                const gradingMsg = document.getElementById('grading-message');
                if (gradingMsg) gradingMsg.innerText = 'Perfect! How easy was that?';
                return;
            }
            // ─────────────────────────────────────────────────────────────────

            let matchedIndex = -1;
            for (let i = 0; i < currentItems.length; i++) {
                if (!currentItems[i].revealed) {
                    let cleanText = currentItems[i].text.toLowerCase().replace(/[^a-z0-9]/g, '');
                    let cleanTextWithNumber = (currentItems[i].number + currentItems[i].text).toLowerCase().replace(/[^a-z0-9]/g, '');
                    
                    if (cleanInput === cleanText || cleanInput === cleanTextWithNumber) {
                        matchedIndex = i;
                        break;
                    }
                }
            }

            if (matchedIndex !== -1) {
                // Mark as revealed
                currentItems[matchedIndex].revealed = true;
                
                // Clear input with setTimeout to override browser default keystroke cycle
                setTimeout(() => {
                    const inputEl = document.getElementById('answer-input');
                    if (inputEl) {
                        inputEl.value = '';
                        inputEl.focus();
                    }
                    typedAnswer = '';
                }, 10);

                // Update display
                const placeholderEl = document.getElementById('placeholder-text');
                if (placeholderEl) {
                    let newActiveIdx = currentItems.findIndex(item => !item.revealed);
                    placeholderEl.innerHTML = getPlaceholderHtml(currentItems, newActiveIdx, '');
                }

                // Check if all are done
                let allDone = currentItems.every(item => item.revealed);
                if (allDone) {
                    setTimeout(() => {
                        const inputEl = document.getElementById('answer-input');
                        if (inputEl) inputEl.classList.add('d-none');
                    }, 20);
                    
                    const speakBtn = document.getElementById('review-speak-btn');
                    if (speakBtn) {
                        speakBtn.classList.remove('d-none');
                        speakBtn.style.display = 'flex';
                    }
                    
                    const showAnswerBtn = document.getElementById('show-answer-btn');
                    if (showAnswerBtn) showAnswerBtn.classList.add('d-none');
                    
                    const gradingMsg = document.getElementById('grading-message');
                    if (gradingMsg) gradingMsg.innerText = 'Perfect! How easy was that?';
                }
            }
        };

        window.revealAnswer = function() {
            if (peekTimeout) clearTimeout(peekTimeout);
            
            const placeholderEl = document.getElementById('placeholder-text');
            const showAnswerBtn = document.getElementById('show-answer-btn');
            const inputEl = document.getElementById('answer-input');
            
            if (!placeholderEl) return;
            
            // Build temporary fully-revealed view of all items
            const peekItems = currentItems.map(item => ({
                ...item,
                revealed: true
            }));
            
            // Render the fully revealed text in green
            placeholderEl.innerHTML = getPlaceholderHtml(peekItems);
            
            // Temporarily disable typing during the 0.9s peek
            if (inputEl) inputEl.disabled = true;
            
            if (showAnswerBtn) {
                showAnswerBtn.disabled = true;
                showAnswerBtn.innerText = 'Peeking...';
            }
            
            peekTimeout = setTimeout(() => {
                // Restore original state
                let activeIdx = currentItems.findIndex(item => !item.revealed);
                placeholderEl.innerHTML = getPlaceholderHtml(currentItems, activeIdx, typedAnswer);
                
                if (inputEl) {
                    inputEl.disabled = false;
                    inputEl.focus();
                }
                
                if (showAnswerBtn) {
                    showAnswerBtn.disabled = false;
                    showAnswerBtn.innerText = 'Show Answer';
                }
                
                peekTimeout = null;
            }, 900); // 0.9 seconds
        };

        window.submitReview = function(flashcardId, quality) {
            if (peekTimeout) {
                clearTimeout(peekTimeout);
                peekTimeout = null;
            }
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

        window.speakCurrentTerm = function() {
            if (typeof window.speakText === 'function' && cards && cards[currentIndex]) {
                window.speakText(cards[currentIndex].term);
            }
        };

        window.speakCurrentDefinition = function() {
            if (typeof window.speakText === 'function' && cards && cards[currentIndex]) {
                window.speakText(cards[currentIndex].definition);
            }
        };

        render();
    });
</script>

@if(auth()->user()?->learning_style === 'auditory')
<div class="modal fade" id="auditoryTipModal" tabindex="-1" aria-labelledby="auditoryTipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 18px; border-left: 4px solid #0891b2 !important;">
            <div class="modal-header bg-light border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="auditoryTipModalLabel">
                    <span style="font-size: 1.5rem;">🎵</span> AUDITORY STUDY TIP
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-0 text-dark" style="font-size: 1rem; line-height: 1.6; color: #0c4a6e !important;">
                    When practicing flashcards today, say the terms and definitions out loud. Hearing the vocabulary spoken makes it much easier for your brain to remember.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-primary px-4 fw-bold" style="border-radius: 10px; background-color: #0891b2; border-color: #0891b2;" data-bs-dismiss="modal">Start Practice</button>
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
        
        // Ensure browser has cleared old utterances
        setTimeout(() => {
            let plainText = text.replace(/<[^>]*>?/gm, ''); // strip html
            plainText = plainText.replace(/[\r\n]+/g, ' ').replace(/\s{2,}/g, ' ').trim();
            
            if (!plainText) return;

            // Split by punctuation using lookbehind to not lose trailing text without punctuation
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
                synth.speak(u);
            });
        }, 50);
    };
    
    window.addEventListener('beforeunload', () => synth.cancel());
</script>
@endif
@endpush
