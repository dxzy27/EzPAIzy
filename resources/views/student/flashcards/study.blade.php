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
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        background-color: #ffffff !important; /* Premium White for Student Front */
        color: #0f172a !important; /* Dark slate text */
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
        background: rgba(0, 0, 0, 0.05);
        border-radius: 4px;
    }
    .flashcard-face::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 4px;
    }
    .flashcard-face::-webkit-scrollbar-thumb:hover {
        background: rgba(0, 0, 0, 0.3);
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
        background-color: #DDDDDD !important;
        border: 1px solid #cbd5e1;
        transform: rotateY(0deg) translateZ(1px);
    }
    
    .flashcard-back {
        background-color: #EDE9E6 !important; /* Lighter warm gray for Student Back */
        color: #0f172a !important; /* Dark slate text */
        transform: rotateY(180deg) translateZ(1px);
        border: 1px solid #cbd5e1;
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
<div class="container-fluid px-4">
    <div class="row mb-5">
        <div class="col-12">
            <a href="{{ $flashcardSet->topic ? route('student.flashcards.folder', $flashcardSet->topic) : route('student.flashcards.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center mb-3" style="width: 36px; height: 36px;" title="Back to Sets">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                <div>
                    <h1>Study: {{ $flashcardSet->title }}</h1>
                    @if(auth()->user()?->learning_style === 'kinesthetic')
                    <div class="btn-group mt-2" role="group" aria-label="Mode toggle">
                        <button type="button" class="btn btn-sm btn-outline-primary active" id="btn-mode-read" onclick="setMode('read')">
                            <i class="bi bi-book"></i> Read Mode
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-mode-review" onclick="setMode('review')">
                            <i class="bi bi-psychology"></i> Review Mode
                        </button>
                    </div>
                    @endif
                </div>
                <!-- Auto-read removed as requested -->
            </div>
        </div>
    </div>

    @php $isReadWrite = auth()->user()?->learning_style === 'read_write'; @endphp

    <div class="row">
        <div class="{{ $isReadWrite ? 'col-lg-8' : 'col-lg-12' }}">
            <!-- Flashcard Display -->
            <div id="flashcard-app">
                <!-- Rendered by JS -->
            </div>
        </div>

        @if($isReadWrite)
            @php
                $existingNote = \App\Models\StudentNote::where('user_id', auth()->id())
                    ->where('resource_type', 'flashcard')
                    ->where('resource_id', $flashcardSet->id)
                    ->first();
            @endphp
            <div class="col-lg-4">
                <div class="card border-success shadow-sm sticky-top" style="top: 20px; z-index: 100;">
                    <div class="card-header bg-success text-white d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-1"></i> Study Notepad</h6>
                        <span id="save-status" class="small text-white-50">Auto-saved</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.72rem;">Topic</small>
                            <span class="badge bg-light text-dark border">{{ $flashcardSet->topic ?? 'General' }}</span>
                        </div>
                        <div class="mb-3">
                            <label for="note-title" class="form-label small fw-bold text-uppercase text-muted mb-1" style="font-size: 0.72rem;">Note Title</label>
                            <input type="text" id="note-title" class="form-control form-control-sm fw-bold" 
                                   value="{{ $existingNote ? $existingNote->title : 'Notes: ' . $flashcardSet->title }}" 
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

        function getStatusBadgeHtml(status) {
            if (status === 'learning') {
                return `<span class="badge bg-danger bg-opacity-25 text-danger border border-danger fw-bold ms-2" style="font-size: 0.75rem;"><i class="bi bi-x-circle me-1"></i>Still learning</span>`;
            } else if (status === 'review' || status === 'mastered') {
                return `<span class="badge bg-success bg-opacity-25 text-success border border-success fw-bold ms-2" style="font-size: 0.75rem;"><i class="bi bi-check-circle me-1"></i>Know</span>`;
            }
            return '';
        }

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
                    displayHtml = `<span class="text-dark fw-bold">${display}</span>`;
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
                    displayHtml = `<span class="text-muted">${underscores}</span>`;
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

        function renderControls() {
            const controlsEl = document.querySelector('.controls');
            if (!controlsEl) return;
            if (cards.length === 0 || currentIndex >= cards.length) {
                controlsEl.innerHTML = '';
                return;
            }
            
            const currentCard = cards[currentIndex];
            let controlsHtml = '';
            
            let stillArrow = '';
            let knowArrow = '';
            @if(auth()->user()?->learning_style === 'kinesthetic')
            stillArrow = '<i class="bi bi-arrow-left me-2 fw-bold" style="font-size: 1.1rem;"></i>';
            knowArrow = '<i class="bi bi-arrow-right ms-2 fw-bold" style="font-size: 1.1rem;"></i>';
            @endif

            if (mode === 'review') {
                if (!isFlipped) {
                    controlsHtml = `
                        <div class="text-center mt-4">
                            <p class="text-muted mb-2">Think of the answer, then tap the card to flip and type it.</p>
                        </div>
                    `;
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
                                    ${stillArrow}<i class="bi bi-x-lg fs-5"></i> Still learning
                                </button>
                                <button class="btn btn-grade-know d-flex align-items-center gap-2" onclick="submitReview(${currentCard.id}, 5)" ${isSubmitting ? 'disabled' : ''}>
                                    <i class="bi bi-check-lg fs-5"></i> Know${knowArrow}
                                </button>
                            </div>
                        </div>
                    `;
                }
            } else {
                controlsHtml = `
                    <div id="grading-controls" class="mt-4 text-center">
                        <p class="fw-bold mb-3" id="grading-message">How well did you remember this?</p>
                        <div class="d-flex justify-content-center gap-3">
                            <button class="btn btn-grade-still d-flex align-items-center gap-2" onclick="submitReview(${currentCard.id}, 1)" ${isSubmitting ? 'disabled' : ''}>
                                ${stillArrow}<i class="bi bi-x-lg fs-5"></i> Still learning
                            </button>
                            <button class="btn btn-grade-know d-flex align-items-center gap-2" onclick="submitReview(${currentCard.id}, 5)" ${isSubmitting ? 'disabled' : ''}>
                                <i class="bi bi-check-lg fs-5"></i> Know${knowArrow}
                            </button>
                        </div>
                    </div>
                `;
            }

            // Always add Next/Prev buttons
            controlsHtml += `
                <div class="mt-3 text-center d-flex justify-content-center gap-3">
                    <button class="btn btn-outline-secondary px-4 py-2" onclick="prevCard()" ${currentIndex === 0 ? 'disabled' : ''}>
                        Previous
                    </button>
                    <button class="btn btn-primary px-4 py-2" onclick="nextCard()">
                        Next
                    </button>
                </div>
            `;
            
            controlsEl.innerHTML = controlsHtml;
        }

        function render() {
            if (cards.length === 0 || currentIndex >= cards.length) {
                app.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-emoji-sunglasses display-1 text-warning mb-3"></i>
                        <h2>You're all caught up!</h2>
                        <p class="text-muted fs-5">There are no cards due for review right now. Great job!</p>
                        <div class="d-flex justify-content-center gap-3 mt-4">
                            <a href="javascript:history.back()" class="btn btn-primary">Back to Flashcards</a>
                            <form action="{{ route('student.flashcards.reset', $flashcardSet->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to reset your progress for this set?')">
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
            
            let backFaceHtml = '';
            if (mode === 'read') {
                backFaceHtml = `
                    <div class="d-flex justify-content-between position-absolute w-100" style="top: 1rem; left: 0; padding: 0 1.5rem; z-index: 10;">
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge bg-warning bg-opacity-25 text-warning border border-warning fw-bold" onclick="flipCard(event)" style="cursor:pointer;">BACK</span>
                            ${getStatusBadgeHtml(currentCard.status)}
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if(auth()->user()?->learning_style === 'auditory')
                            <button type="button" class="btn btn-sm btn-light rounded-circle" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;" onclick="event.stopPropagation(); speakCurrentDefinition();" onmousedown="event.stopPropagation();" onpointerdown="event.stopPropagation();" title="Read Answer">
                                <i class="bi bi-volume-up-fill text-primary" style="pointer-events:none;"></i>
                            </button>
                            @endif
                            <small class="text-muted" style="font-size: 0.8rem; cursor:pointer;" onclick="flipCard(event)"><i class="bi bi-hand-index-thumb"></i> Tap to flip</small>
                        </div>
                    </div>
                    <div class="flashcard-content-wrapper mt-3" onclick="flipCard(event)" style="cursor:pointer;">
                        ${localStorage.getItem(`hl_flash_${currentCard.id}_back`) || `
                        <div class="flashcard-content">
                            <div class="${alignClass}">
                                <div class="fs-3 text-dark fw-bold mt-3" style="line-height: 1.4;">${formattedDef}</div>
                            </div>
                        </div>
                        `}
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
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge bg-warning bg-opacity-25 text-warning border border-warning fw-bold" onclick="flipCard(event)" style="cursor:pointer;">BACK</span>
                            ${getStatusBadgeHtml(currentCard.status)}
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button id="show-answer-btn" type="button" class="btn btn-outline-secondary text-muted border-secondary px-2 py-0.5 d-flex align-items-center justify-content-center ${allDone ? 'd-none' : ''}" style="font-size: 0.75rem; border-radius: 4px; line-height: 1.2; height: 26px;" onclick="event.stopPropagation(); revealAnswer();">
                                Show Answer
                            </button>
                            @if(auth()->user()?->learning_style === 'auditory')
                            <button id="review-speak-btn" type="button" class="btn btn-sm btn-light rounded-circle ${allDone ? '' : 'd-none'}" style="width:30px;height:30px;padding:0;display:${allDone ? 'flex' : 'none'};align-items:center;justify-content:center;" onclick="event.stopPropagation(); speakCurrentDefinition();" onmousedown="event.stopPropagation();" onpointerdown="event.stopPropagation();" title="Read Answer">
                                <i class="bi bi-volume-up-fill text-primary" style="pointer-events:none;"></i>
                            </button>
                            @endif
                            <small class="text-muted" style="font-size: 0.8rem; cursor:pointer;" onclick="flipCard(event)"><i class="bi bi-hand-index-thumb"></i> Tap to flip</small>
                        </div>
                    </div>
                    <div class="flashcard-content-wrapper mt-3" onclick="flipCard(event)" style="cursor:pointer;">
                        <div class="flashcard-content">
                            <div class="w-100">
                                <div id="placeholder-text" class="mt-3">${initialAnswerWords}</div>
                            </div>
                            
                            <input type="text" id="answer-input" class="form-control text-center mt-4 mx-auto ${allDone ? 'd-none' : ''}" 
                                   style="max-width: 80%; background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1;" 
                                   autocomplete="off" autocorrect="off" spellcheck="false" 
                                   value="${typedAnswer.replace(/"/g, '&quot;')}"
                                   placeholder="Type the exact answer..." oninput="checkTyping(this.value)" onclick="event.stopPropagation()">
                        </div>
                    </div>
                `;
            }

            app.innerHTML = `
                <div class="text-center mb-3">
                    <span class="badge bg-primary">Reviewing Card ${currentIndex + 1} of ${cards.length}</span>
                </div>
                
                <div class="flashcard-container">
                    <div class="flashcard-inner ${isFlipped ? 'is-flipped' : ''}">
                        <div class="flashcard-face flashcard-front">
                            <div class="d-flex justify-content-between position-absolute w-100" style="top: 1rem; left: 0; padding: 0 1.5rem; z-index: 10;">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge fw-bold" onclick="flipCard(event)" style="cursor:pointer; background-color: rgba(92, 79, 74, 0.15); color: #5C4F4A; border: 1px solid #5C4F4A;">FRONT</span>
                                    ${getStatusBadgeHtml(currentCard.status)}
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    @if(auth()->user()?->learning_style === 'auditory')
                                    <button type="button" class="btn btn-sm btn-light rounded-circle" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;" onclick="event.stopPropagation(); speakCurrentTerm();" onmousedown="event.stopPropagation();" onpointerdown="event.stopPropagation();" title="Read Question">
                                        <i class="bi bi-volume-up-fill text-primary" style="pointer-events:none;"></i>
                                    </button>
                                    @endif
                                    <small class="text-muted" style="font-size: 0.8rem; cursor:pointer;" onclick="flipCard(event)"><i class="bi bi-hand-index-thumb"></i> Tap to flip</small>
                                </div>
                            </div>
                            <div class="flashcard-content-wrapper mt-3" onclick="flipCard(event)" style="cursor:pointer;">
                                ${localStorage.getItem(`hl_flash_${currentCard.id}_front`) || `
                                <div class="flashcard-content">
                                    <div class="fs-3 text-dark fw-bold mt-3" style="line-height: 1.4;">${currentCard.term}</div>
                                </div>
                                `}
                            </div>
                        </div>
                        <div class="flashcard-face flashcard-back">
                            ${backFaceHtml}
                        </div>
                    </div>
                </div>

                <div class="controls"></div>
            `;

            renderControls();

            if (isFlipped && mode === 'review') {
                setTimeout(() => {
                    const input = document.getElementById('answer-input');
                    if (input) input.focus();
                }, 300); // Wait for flip animation
            }
        }

        window.setMode = function(newMode) {
            @if(auth()->user()?->learning_style !== 'kinesthetic')
                newMode = 'read';
            @endif
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
            // Do not flip if the user is selecting text
            const selection = window.getSelection();
            if (selection && selection.toString().trim().length > 0) {
                return;
            }
            if (isSubmitting) return;
            isFlipped = !isFlipped;
            
            const inner = document.querySelector('.flashcard-inner');
            if (inner) {
                if (isFlipped) {
                    inner.classList.add('is-flipped');
                    if (mode === 'review') {
                        setTimeout(() => {
                            const input = document.getElementById('answer-input');
                            if (input) input.focus();
                        }, 300);
                    }
                } else {
                    inner.classList.remove('is-flipped');
                }
            } else {
                render();
            }
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
                if (data.success && data.progress) {
                    cards[currentIndex].status = data.progress.status;
                }
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

        // ── SWIPE GESTURES FOR KINAESTHETIC LEARNERS ─────────────────────────
        @if(auth()->user()?->learning_style === 'kinesthetic')
        let startX = 0;
        let startY = 0;
        let isDragging = false;
        const dragThreshold = 120; // px
        
        function handleDragStart(e) {
            if (mode !== 'read') return;
            if (isSubmitting) return;
            // Prevent text selection or target interactions
            const target = e.target;
            if (target.closest('button') || target.closest('.btn') || target.closest('input') || target.closest('a') || target.closest('.badge')) return;
            
            isDragging = true;
            const clientX = e.type.startsWith('touch') ? e.touches[0].clientX : e.clientX;
            const clientY = e.type.startsWith('touch') ? e.touches[0].clientY : e.clientY;
            startX = clientX;
            startY = clientY;
            
            const cardInner = document.querySelector('.flashcard-inner');
            if (cardInner) {
                cardInner.style.transition = 'none';
            }
        }
        
        function handleDragMove(e) {
            if (!isDragging) return;
            const clientX = e.type.startsWith('touch') ? e.touches[0].clientX : e.clientX;
            const clientY = e.type.startsWith('touch') ? e.touches[0].clientY : e.clientY;
            
            const diffX = clientX - startX;
            const diffY = clientY - startY;
            
            // If dragging mostly vertically, don't trigger horizontal swipe
            if (Math.abs(diffY) > Math.abs(diffX) && Math.abs(diffX) < 10) {
                return;
            }
            
            e.preventDefault();
            
            const cardInner = document.querySelector('.flashcard-inner');
            if (cardInner) {
                const rotate = diffX * 0.05;
                const flipClass = isFlipped ? ' rotateY(180deg)' : '';
                cardInner.style.transform = `translateX(${diffX}px) translateY(${diffY * 0.2}px) rotate(${rotate}deg)${flipClass}`;
                
                if (diffX < -20) {
                    cardInner.style.boxShadow = `0 10px 30px rgba(100, 116, 139, ${Math.min(0.8, -diffX / 150)})`;
                } else if (diffX > 20) {
                    cardInner.style.boxShadow = `0 10px 30px rgba(6, 182, 212, ${Math.min(0.8, diffX / 150)})`;
                } else {
                    cardInner.style.boxShadow = '';
                }
            }
        }
        
        function handleDragEnd(e) {
            if (!isDragging) return;
            isDragging = false;
            
            const clientX = e.type.startsWith('touch') ? e.changedTouches[0].clientX : e.clientX;
            const diffX = clientX - startX;
            
            const cardInner = document.querySelector('.flashcard-inner');
            if (!cardInner) return;
            
            cardInner.style.transition = 'transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            
            if (diffX < -dragThreshold) {
                // Swipe Left -> Still learning
                if (cards[currentIndex]) {
                    cardInner.style.transform = `translateX(-1000px) rotate(-45deg)${isFlipped ? ' rotateY(180deg)' : ''}`;
                    setTimeout(() => {
                        submitReview(cards[currentIndex].id, 1);
                    }, 200);
                } else {
                    cardInner.style.transform = '';
                    cardInner.style.boxShadow = '';
                }
            } else if (diffX > dragThreshold) {
                // Swipe Right -> Know
                if (cards[currentIndex]) {
                    cardInner.style.transform = `translateX(1000px) rotate(45deg)${isFlipped ? ' rotateY(180deg)' : ''}`;
                    setTimeout(() => {
                        submitReview(cards[currentIndex].id, 5);
                    }, 200);
                } else {
                    cardInner.style.transform = '';
                    cardInner.style.boxShadow = '';
                }
            } else {
                cardInner.style.transform = '';
                cardInner.style.boxShadow = '';
            }
        }
        
        function initSwipeGestures() {
            const container = document.getElementById('flashcard-app');
            if (!container) return;
            
            // Remove existing listeners
            container.removeEventListener('mousedown', handleDragStart);
            container.removeEventListener('touchstart', handleDragStart);
            document.removeEventListener('mousemove', handleDragMove);
            document.removeEventListener('touchmove', handleDragMove);
            document.removeEventListener('mouseup', handleDragEnd);
            document.removeEventListener('touchend', handleDragEnd);
            
            // Add listeners
            container.addEventListener('mousedown', handleDragStart);
            container.addEventListener('touchstart', handleDragStart, { passive: false });
            document.addEventListener('mousemove', handleDragMove);
            document.addEventListener('touchmove', handleDragMove, { passive: false });
            document.addEventListener('mouseup', handleDragEnd);
            document.addEventListener('touchend', handleDragEnd);
        }
        @endif

        const originalRender = render;
        render = function() {
            originalRender();
            @if(auth()->user()?->learning_style === 'kinesthetic')
            initSwipeGestures();
            @endif
        };

        render();
    });
</script>

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
                topic: "{{ $flashcardSet->topic ?? 'General' }}",
                difficulty: null,
                title: title,
                content: content,
                resource_type: 'flashcard',
                resource_id: {{ $flashcardSet->id }}
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
                    When practicing flashcards today, say the terms and definitions out loud. Hearing the vocabulary spoken makes it much easier for your brain to remember.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-primary px-4 fw-bold" style="border-radius: 10px; background-color: #e5b181; border-color: #e5b181;" data-bs-dismiss="modal">Start Practice</button>
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

    let lastSpokenText = null;

    window.speakText = function(text) {
        if (synth.speaking && lastSpokenText === text) {
            synth.cancel();
            lastSpokenText = null;
            return;
        }

        synth.cancel();
        lastSpokenText = text;
        
        // Ensure browser has cleared old utterances
        setTimeout(() => {
            let plainText = text.replace(/<[^>]*>?/gm, ''); // strip html
            if (!plainText.trim()) return;

            // Split by newlines and merge non-list lines to prevent random pauses on line wraps
            let rawLines = plainText.split(/[\r\n]+/);
            let lines = [];
            let currentLine = "";

            rawLines.forEach(line => {
                line = line.trim();
                if (!line) return;

                // If line starts with a list number (e.g. "1.") or a bullet (e.g. "-", "*"), it's a new list item chunk
                if (/^(\d+\.|\-|\*)/.test(line)) {
                    if (currentLine) {
                        lines.push(currentLine);
                    }
                    currentLine = line;
                } else {
                    // Otherwise, merge with current line
                    if (currentLine) {
                        currentLine += " " + line;
                    } else {
                        currentLine = line;
                    }
                }
            });
            if (currentLine) {
                lines.push(currentLine);
            }

            let safeChunks = [];

            lines.forEach(line => {
                // Split by punctuation only if the dot is not preceded by a number
                let parts = line.split(/(?<!\b\d)[.!?]\s+/);

                parts.forEach(part => {
                    part = part.trim();
                    if (!part) return;
                    if (part.length > 200) {
                        let subParts = part.match(/.{1,180}(?:\s|$)/g) || [part];
                        safeChunks.push(...subParts);
                    } else {
                        safeChunks.push(part);
                    }
                });
            });

            safeChunks.forEach(chunkText => {
                chunkText = chunkText.trim();
                if (!chunkText) return;
                
                // Add a comma after list numbers to insert a brief pause (e.g. "1. Melepak" -> "1. , Melepak")
                let textToSpeak = chunkText.replace(/^(\d+\.)\s+/, '$1 , ');
                const u = new SpeechSynthesisUtterance(textToSpeak);
                
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
            
            if (!container.closest('.flashcard-face') && !container.closest('.flashcard-container')) {
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
            
            saveCurrentCardHighlights();
            selection.removeAllRanges();
            hideHighlighterToolbar();
        }

        function saveCurrentCardHighlights() {
            const currentCard = cards[currentIndex];
            if (!currentCard) return;
            const frontWrapper = document.querySelector('.flashcard-front .flashcard-content-wrapper');
            const backWrapper = document.querySelector('.flashcard-back .flashcard-content-wrapper');
            if (frontWrapper) {
                localStorage.setItem(`hl_flash_${currentCard.id}_front`, frontWrapper.innerHTML);
            }
            if (backWrapper) {
                localStorage.setItem(`hl_flash_${currentCard.id}_back`, backWrapper.innerHTML);
            }
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
            
            saveCurrentCardHighlights();
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
            
            if (!container.closest('.flashcard-face') && !container.closest('.flashcard-container')) {
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
@endpush
