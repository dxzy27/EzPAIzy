@extends('layouts.dashboard')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1040px; margin: 0 auto;">
    <form action="{{ isset($flashcardSet) ? route('teacher.flashcard-sets.update', $flashcardSet->id) : route('teacher.flashcard-sets.store') }}" method="POST" id="flashcardForm">
        @csrf
        @if(isset($flashcardSet))
            @method('PUT')
        @endif
        
        <!-- Top Navigation -->
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ url()->previous() === url()->current() ? route('teacher.flashcard-sets.index') : url()->previous() }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
            <div>
                <h2 class="fw-bold text-dark mb-0" style="letter-spacing: -0.3px;">{{ isset($flashcardSet) ? 'Edit Flashcard Set' : 'Create New Flashcard Set' }}</h2>
                <p class="text-muted mb-0" style="font-size: 0.88rem;">Configure set title, topic, and edit flashcard faces</p>
            </div>
        </div>

        <!-- 1 & 2. Form Card with Integrated Action Buttons and Reduced Height -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
            <div class="card-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-1">Title</label>
                        <input type="text" name="title" class="form-control form-control-lg fw-semibold" placeholder="e.g. Chapter 4: Photosynthesis" value="{{ old('title', $flashcardSet->title ?? '') }}" required style="border-radius: 10px; font-size: 1.05rem;">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-1">Topic</label>
                        <select name="topic" class="form-select form-select-lg fw-semibold" required style="border-radius: 10px; font-size: 0.95rem;">
                            <option value="" disabled {{ !isset($flashcardSet->topic) ? 'selected' : '' }}>Select Topic</option>
                            @foreach($topics as $t)
                                <option value="{{ $t->name }}" {{ (old('topic', $flashcardSet->topic ?? request('topic')) == $t->name) ? 'selected' : '' }}>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-1">Description</label>
                        <textarea name="description" class="form-control" placeholder="Add a short summary for this set..." rows="2" style="resize: none; border-radius: 10px;">{{ old('description', $flashcardSet->description ?? '') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end align-items-center gap-2 pt-2 border-top">
                    <a href="{{ route('teacher.flashcard-sets.index') }}" class="btn btn-light fw-semibold px-4" style="border-radius: 10px;">Cancel</a>
                    <button type="submit" class="btn btn-primary fw-bold px-4" style="border-radius: 10px; box-shadow: 0 4px 12px rgba(59,130,246,0.3);">
                        <i class="bi bi-check-lg me-1"></i> {{ isset($flashcardSet) ? 'Update Set' : 'Create Set' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- 2, 3, & Header Actions: Flashcards Title with Counter, Add Flashcard, Primary CSV, & Secondary Delete All -->
        <div class="d-flex justify-content-between align-items-center mb-3 mt-4 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold text-dark mb-0">Flashcards</h4>
                <span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold px-3 py-1.5 border border-secondary border-opacity-25" id="cards-counter-badge" style="border-radius: 20px; font-size: 0.82rem; letter-spacing: 0.2px;">0 Cards</span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary btn-sm fw-bold px-3 d-inline-flex align-items-center" id="add-card-header-btn" style="border-radius: 8px; font-size: 0.88rem;">
                    <i class="bi bi-plus-lg me-1.5 fs-6"></i> Add Card
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm fw-semibold px-3 d-inline-flex align-items-center" id="csv-import-btn-header" style="border-radius: 8px; font-size: 0.88rem;">
                    <i class="bi bi-file-earmark-spreadsheet me-1.5 fs-6"></i> Import CSV
                </button>
                <button type="button" class="btn btn-light text-danger border btn-sm fw-semibold px-3 d-inline-flex align-items-center" id="delete-all-btn" style="border-radius: 8px; font-size: 0.88rem;">
                    <i class="bi bi-trash3 me-1.5 fs-6"></i> Delete All
                </button>
                <input type="file" id="csv-file-input" class="d-none" accept=".csv, .txt, .xlsx, .xls">
            </div>
        </div>

        <!-- Cards Container -->
        <div id="cards-container">
            <!-- Dynamic Flashcard Cards -->
        </div>

        <!-- Add Card Bottom Button -->
        <div class="row g-3 mb-5">
            <div class="col-12">
                <div class="card border-2 border-dashed shadow-sm text-center py-3" style="cursor: pointer; border-color: #cbd5e1 !important; border-radius: 14px; transition: all 0.2s;" id="add-card-btn">
                    <div class="card-body p-2">
                        <h6 class="fw-bold text-primary mb-0"><i class="bi bi-plus-lg me-1"></i> Add Another Card</h6>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>

@endsection

@push('styles')
<style>
    .flashcard-perspective {
        perspective: 1000px;
    }
    .flashcard-inner {
        position: relative;
        width: 100%;
        height: 100%;
        text-align: center;
        transition: transform 0.6s cubic-bezier(0.4, 0.2, 0.2, 1);
        transform-style: preserve-3d;
        cursor: pointer;
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
        border: 1px solid #cbd5e1;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        background-color: #DDDDDD !important; /* Soft Light Gray for Front */
        color: #0f172a !important;
        display: flex;
        flex-direction: column;
    }
    .flashcard-back {
        transform: rotateY(180deg);
        background-color: #EDE9E6 !important; /* Soft Warm Gray for Back */
        color: #0f172a !important;
    }
    .flashcard-input {
        background: transparent !important;
        border: none !important;
        color: #0f172a !important;
        font-size: 1.5rem;
        font-weight: 600;
        text-align: center;
        width: 100%;
        height: 100%;
        resize: none;
    }
    .flashcard-input:focus {
        box-shadow: none !important;
        outline: none !important;
    }
    .flashcard-input::placeholder {
        color: rgba(15, 23, 42, 0.45);
        font-weight: 500;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('cards-container');
        const addBtn = document.getElementById('add-card-btn');
        const addHeaderBtn = document.getElementById('add-card-header-btn');
        let cardCount = 0;

        function updateCounter() {
            const count = container.querySelectorAll('.flashcard-row').length;
            const counterBadge = document.getElementById('cards-counter-badge');
            if (counterBadge) {
                counterBadge.textContent = count + (count === 1 ? ' Card' : ' Cards');
            }
        }

        function getCardHtml(index, term = '', definition = '', createdAt = null, updatedAt = null) {
            const createdText = createdAt ? 'Created ' + createdAt : 'New Card';
            const updatedText = updatedAt ? ' • Updated ' + updatedAt : '';

            return `
            <div class="row justify-content-center mb-4 flashcard-row" data-id="${index}">
                <div class="col-12">
                    <div class="card border-0 shadow-sm overflow-hidden flashcard-item-card" style="border-radius: 16px; background: #ffffff;">
                        <!-- Card Header bar: Card # & Metadata Context -->
                        <div class="card-header bg-white border-bottom-0 pt-3 px-4 pb-0 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <span class="drag-handle text-muted" style="cursor: grab;" title="Drag to reorder"><i class="bi bi-grip-vertical fs-5"></i></span>
                                <span class="badge bg-dark bg-opacity-10 text-dark fw-bold px-2.5 py-1 card-index-badge" style="border-radius: 8px; font-size: 0.88rem;">Card #${index}</span>
                                <small class="text-muted ms-1 d-none d-sm-inline" style="font-size: 0.78rem;">${createdText}${updatedText}</small>
                            </div>
                        </div>

                        <!-- Card Body: Interactive Flashcard Face with Subtle Hover Cue -->
                        <div class="card-body p-4 pt-3">
                            <div class="flashcard-perspective" style="height: 240px;">
                                <div class="flashcard-inner">
                                    
                                    <!-- Front Face -->
                                    <div class="flashcard-face flashcard-front p-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">FRONT / QUESTION</span>
                                            <small class="text-muted fw-semibold" style="font-size: 0.8rem;"><i class="bi bi-arrow-repeat me-1 fs-6"></i> Click to flip</small>
                                        </div>
                                        <div class="flex-grow-1 d-flex align-items-center justify-content-center px-3">
                                            <textarea name="flashcards[${index-1}][term]" class="form-control flashcard-input" placeholder="Type question / term here..." required>${term}</textarea>
                                        </div>
                                    </div>

                                    <!-- Back Face -->
                                    <div class="flashcard-face flashcard-back p-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">BACK / ANSWER</span>
                                            <small class="text-muted fw-semibold" style="font-size: 0.8rem;"><i class="bi bi-arrow-repeat me-1 fs-6"></i> Click to flip</small>
                                        </div>
                                        <div class="flex-grow-1 d-flex align-items-center justify-content-center px-3">
                                            <textarea name="flashcards[${index-1}][definition]" class="form-control flashcard-input" placeholder="Type answer / definition here..." required>${definition}</textarea>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Card Footer: Right-aligned Delete Action (Far from Add Card CTA) -->
                        <div class="card-footer bg-white border-top-0 pb-3 px-4 pt-0 d-flex justify-content-end">
                            <button type="button" class="btn btn-sm btn-light text-danger border-0 px-3 py-1.5 fw-semibold delete-row d-inline-flex align-items-center gap-1.5" title="Delete Card" style="font-size: 0.85rem; border-radius: 8px;">
                                <i class="bi bi-trash3 fs-6"></i><span>Delete</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            `;
        }

        function addCard(data = null) {
            cardCount++;
            const term = data ? data.term : '';
            const definition = data ? data.definition : '';
            const createdAt = data && data.created_at ? new Date(data.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : null;
            const updatedAt = data && data.updated_at ? new Date(data.updated_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : null;
            
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = getCardHtml(cardCount, term, definition, createdAt, updatedAt);
            const newCardRow = tempDiv.firstElementChild;

            // Flip Logic
            const innerCard = newCardRow.querySelector('.flashcard-inner');
            const inputs = newCardRow.querySelectorAll('textarea');

            // Handle Flip
            innerCard.addEventListener('click', function(e) {
                if (e.target.tagName.toLowerCase() === 'textarea') return;
                this.classList.toggle('is-flipped');
            });

            inputs[0].addEventListener('focus', () => innerCard.classList.remove('is-flipped')); 
            inputs[1].addEventListener('focus', () => innerCard.classList.add('is-flipped'));

            // Delete Logic
            newCardRow.querySelector('.delete-row').addEventListener('click', function(e) {
                e.stopPropagation();
                if (document.querySelectorAll('.flashcard-row').length > 1) {
                    newCardRow.remove();
                    reindexCards();
                } else {
                    alert('You must have at least one card.');
                }
            });

            container.appendChild(newCardRow);
            updateCounter();
        }

        function reindexCards() {
            const rows = container.querySelectorAll('.flashcard-row');
            rows.forEach((row, index) => {
                const newIndex = index + 1;
                const indexBadge = row.querySelector('.card-index-badge');
                if (indexBadge) indexBadge.textContent = 'Card #' + newIndex;
                const termInput = row.querySelector('textarea[name*="[term]"]');
                const defInput = row.querySelector('textarea[name*="[definition]"]');
                if (termInput) termInput.name = `flashcards[${index}][term]`;
                if (defInput) defInput.name = `flashcards[${index}][definition]`;
            });
            cardCount = rows.length;
            updateCounter();
        }

        if(addBtn) {
            addBtn.addEventListener('click', () => addCard());
            if (addHeaderBtn) {
                addHeaderBtn.addEventListener('click', () => addCard());
            }
            
            const initialCards = @json($flashcardSet->flashcards ?? []);
            
            if (initialCards.length > 0) {
                initialCards.forEach(card => addCard(card));
            } else {
                addCard(); // Ensure at least 1 card is ready
            }
        }

        // --- File Import Logic ---
        const fileImportHeaderBtn = document.getElementById('csv-import-btn-header');
        const fileInput = document.getElementById('csv-file-input');

        if(fileImportHeaderBtn && fileInput) {
            fileImportHeaderBtn.addEventListener('click', () => fileInput.click());

            fileInput.addEventListener('change', function(e) {
                if (this.files.length === 0) return;

                const file = this.files[0];
                const originalText = fileImportHeaderBtn.innerHTML;
                fileImportHeaderBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Parsing...';
                fileImportHeaderBtn.style.pointerEvents = 'none';

                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const data = new Uint8Array(e.target.result);
                        const workbook = XLSX.read(data, {type: 'array'});
                        const firstSheetName = workbook.SheetNames[0];
                        const worksheet = workbook.Sheets[firstSheetName];
                        
                        const json = XLSX.utils.sheet_to_json(worksheet, {header: 1});
                        
                        let importedCount = 0;
                        if (json.length > 0) {
                            let termIdx = 0;
                            let defIdx = 1;
                            
                            const headerRow = json[0].map(h => String(h).toLowerCase().trim());
                            
                            const questionCol = headerRow.findIndex(h => h.includes('question') || h.includes('term'));
                            const answerCol = headerRow.findIndex(h => h.includes('answer') || h.includes('definition'));
                            
                            if (questionCol !== -1 && answerCol !== -1) {
                                termIdx = questionCol;
                                defIdx = answerCol;
                            } else if (headerRow.length >= 3 && (headerRow[0].includes('no') || headerRow[0] === '#')) {
                                termIdx = 1;
                                defIdx = 2;
                            }

                            json.forEach((row, index) => {
                                if (index === 0 && (questionCol !== -1 || row[termIdx]?.toString().toLowerCase().includes('term') || row[termIdx]?.toString().toLowerCase().includes('question'))) {
                                    return;
                                }

                                if (!row || row.length < 2) return;
                                
                                const term = row[termIdx] ? String(row[termIdx]).trim() : '';
                                const definition = row[defIdx] ? String(row[defIdx]).trim() : '';
                                
                                if (term && definition) {
                                    addCard({term: term, definition: definition});
                                    importedCount++;
                                }
                            });
                        }

                        if (importedCount > 0) {
                            const allCards = container.querySelectorAll('.flashcard-row');
                            if(allCards.length > 0) {
                                allCards[allCards.length - importedCount].scrollIntoView({ behavior: 'smooth' });
                            }
                            alert('Success! ' + importedCount + ' flashcards imported.');
                        } else {
                            alert('No valid flashcards found. Please ensure the file has at least two columns (Term, Definition).');
                        }

                    } catch (error) {
                        console.error(error);
                        alert('Error parsing file: ' + error.message);
                    } finally {
                        fileImportHeaderBtn.innerHTML = originalText;
                        fileImportHeaderBtn.style.pointerEvents = 'auto';
                        fileInput.value = '';
                    }
                };
                reader.onerror = function() {
                    alert('Error reading file.');
                    fileImportHeaderBtn.innerHTML = originalText;
                    fileImportHeaderBtn.style.pointerEvents = 'auto';
                    fileInput.value = '';
                }
                reader.readAsArrayBuffer(file);
            });
        }

        // --- Delete All Logic ---
        const deleteAllBtn = document.getElementById('delete-all-btn');
        if(deleteAllBtn) {
            deleteAllBtn.addEventListener('click', () => {
                if(confirm('Are you sure you want to delete ALL cards? This cannot be undone.')) {
                    container.innerHTML = '';
                    cardCount = 0;
                    addCard(); // Always leave 1 empty card
                }
            });
        }

        // --- Drag and Drop Logic ---
        new Sortable(container, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            onEnd: function() {
                reindexCards();
            }
        });

        // --- Prevent Double Submission ---
        const flashcardForm = document.getElementById('flashcardForm');
        if (flashcardForm) {
            flashcardForm.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    if (submitBtn.disabled) {
                        e.preventDefault();
                        return;
                    }
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
                }
            });
        }
    });
</script>
@endpush
