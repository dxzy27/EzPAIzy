@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Edit Quiz</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('teacher.quizzes.update', $quiz) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="title" class="form-label">Quiz Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $quiz->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="topic" class="form-label">Topic</label>
                            <select name="topic" id="topic" class="form-select @error('topic') is-invalid @enderror" required>
                                <option value="" disabled>Select a Topic</option>
                                @foreach($topics as $t)
                                    <option value="{{ $t->name }}" {{ (old('topic', $quiz->topic) == $t->name) ? 'selected' : '' }}>{{ $t->name }}</option>
                                @endforeach
                            </select>
                            @error('topic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(true)
                        <div class="mb-4">
                            <h4 class="mb-3">Questions {{ $quiz->difficulty == 'medium' || $quiz->difficulty == 'hard' ? '(Short Answer/KBAT)' : '(MCQ)' }}</h4>
                            <div id="questions-container"></div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-primary w-100 h-100" id="add-question-btn">
                                        <i class="bi bi-plus-lg"></i> Add Blank Question
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-success w-100" id="import-db-btn" data-bs-toggle="modal" data-bs-target="#dbModal">
                                        <i class="bi bi-database-fill"></i> Import from Database
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Update Quiz</button>
                            <a href="javascript:history.back()" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import from Database Modal -->
<div class="modal fade" id="dbModal" tabindex="-1" aria-labelledby="dbModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="dbModalLabel">
                    <i class="bi bi-database-fill text-success me-2"></i>Import from Database
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small mb-3">
                    <i class="bi bi-info-circle-fill me-1"></i> Showing questions matching topic: <strong id="modal-topic-label">...</strong> and difficulty: <strong id="modal-diff-label">...</strong>.
                </div>
                
                <!-- Search Filter -->
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="modal-search-input" class="form-control" placeholder="Search questions in pool...">
                </div>

                <div id="qbank-loading" class="text-center py-4">
                    <div class="spinner-border text-success" role="status"></div>
                    <p class="text-muted mt-2 small">Loading questions from database...</p>
                </div>

                <div id="qbank-empty" class="text-center py-4 d-none">
                    <i class="bi bi-folder-x fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No matching questions found in the database.</p>
                </div>

                <div id="qbank-list" class="list-group d-none">
                    <!-- Dynamic Questions Injection -->
                </div>
            </div>
            <div class="modal-footer">
                <span class="text-muted me-auto small" id="modal-selected-count">0 selected</span>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="modal-import-btn" disabled>Import Selected</button>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('questions-container');
        const addBtn = document.getElementById('add-question-btn');
        const difficulty = "{{ $quiz->difficulty }}"; // 'easy', 'medium', 'hard'
        let questionCount = 0;

        function addQuestion(data = null) {
            questionCount++;
            const current = questionCount;
            
            const text = data ? (data.question_text || data.text || '') : '';
            const correct = data ? (data.correct_answer || '') : '';
            const type = data ? (data.type || 'mcq') : ((difficulty === 'medium' || difficulty === 'hard') ? 'short_answer' : 'mcq');

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
                        <label class="form-label small text-muted">${difficulty === 'medium' ? 'Correct Answer / Alternatives (separated by |)' : 'Suggested Key Points'}</label>
                        <input type="text" name="questions[${current}][correct]" class="form-control" placeholder="${difficulty === 'medium' ? 'e.g. Fiqah|Feqah|Fikah' : 'Enter suggested key points (Hard difficulty is manually graded)'}" value="${correct}" required>
                        ${difficulty === 'medium' ? '<div class="form-text text-muted small mt-1">Tip: Separatkan jawapan alternatif dengan tanda "|" untuk memudahkan pelajar (cth: Fiqah|Feqah).</div>' : ''}
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
        
        // Load existing questions
        const existingQuestions = @json($quiz->questions);
        if (existingQuestions && existingQuestions.length > 0) {
            existingQuestions.forEach(q => addQuestion(q));
        } else {
            addQuestion();
        }

        // ── Import from Database Modal Logic ──
        const dbModal = document.getElementById('dbModal');
        const qbankList = document.getElementById('qbank-list');
        const qbankLoading = document.getElementById('qbank-loading');
        const qbankEmpty = document.getElementById('qbank-empty');
        const modalTopicLabel = document.getElementById('modal-topic-label');
        const modalDiffLabel = document.getElementById('modal-diff-label');
        const modalSearchInput = document.getElementById('modal-search-input');
        const modalSelectedCount = document.getElementById('modal-selected-count');
        const modalImportBtn = document.getElementById('modal-import-btn');
        
        let qbankQuestions = [];
        let selectedIndices = new Set();

        dbModal.addEventListener('show.bs.modal', function () {
            const topic = document.getElementById('topic').value;
            modalTopicLabel.textContent = topic || '(None)';
            modalDiffLabel.textContent = difficulty.charAt(0).toUpperCase() + difficulty.slice(1);
            
            qbankList.classList.add('d-none');
            qbankEmpty.classList.add('d-none');
            qbankLoading.classList.remove('d-none');
            modalSearchInput.value = '';
            selectedIndices.clear();
            updateModalFooter();

            if (!topic) {
                qbankLoading.classList.add('d-none');
                qbankEmpty.classList.remove('d-none');
                qbankEmpty.querySelector('p').textContent = 'Please select a Topic in the form first.';
                return;
            }

            fetch(`{{ route('teacher.quizzes.import_search') }}?topic=${encodeURIComponent(topic)}&difficulty=${difficulty}`)
                .then(res => res.json())
                .then(data => {
                    qbankQuestions = data;
                    qbankLoading.classList.add('d-none');
                    renderQbankQuestions(data);
                })
                .catch(err => {
                    console.error(err);
                    qbankLoading.classList.add('d-none');
                    qbankEmpty.classList.remove('d-none');
                    qbankEmpty.querySelector('p').textContent = 'Error fetching questions from database.';
                });
        });

        function renderQbankQuestions(questions) {
            qbankList.innerHTML = '';
            if (questions.length === 0) {
                qbankEmpty.classList.remove('d-none');
                qbankEmpty.querySelector('p').textContent = 'No matching questions found in the database.';
                return;
            }

            qbankEmpty.classList.add('d-none');
            qbankList.classList.remove('d-none');

            questions.forEach((q, index) => {
                let html = `
                    <label class="list-group-item d-flex gap-3 align-items-start py-3 qbank-item" style="cursor: pointer;" data-index="${index}">
                        <input class="form-check-input flex-shrink-0 qbank-check" type="checkbox" value="${index}" style="width: 1.25em; height: 1.25em;">
                        <div class="flex-grow-1">
                            <div class="fw-semibold text-dark">${q.question_text}</div>
                            <div class="small text-muted mt-1">Type: ${q.type === 'mcq' ? 'MCQ' : 'Short Answer/KBAT'}</div>
                `;

                if (q.type === 'mcq' && q.options) {
                    let parsedOptions = q.options;
                    if (typeof q.options === 'string') {
                        try { parsedOptions = JSON.parse(q.options); } catch(e) {}
                    }
                    html += `<div class="row g-1 mt-1">`;
                    for (const key in parsedOptions) {
                        const isCorrect = String(q.correct_answer).toLowerCase() === key.toLowerCase();
                        html += `
                            <div class="col-md-6 small">
                                <span class="${isCorrect ? 'text-success fw-bold' : 'text-muted'}">${key.toUpperCase()}. ${parsedOptions[key]}</span>
                            </div>
                        `;
                    }
                    html += `</div>`;
                } else {
                    html += `<div class="small text-success mt-1">Suggested Answer: <strong>${q.correct_answer}</strong></div>`;
                }

                html += `
                        </div>
                    </label>
                `;
                qbankList.insertAdjacentHTML('beforeend', html);
            });

            // Bind checkbox events
            document.querySelectorAll('.qbank-check').forEach(chk => {
                chk.addEventListener('change', function () {
                    const index = parseInt(this.value);
                    if (this.checked) {
                        selectedIndices.add(index);
                    } else {
                        selectedIndices.delete(index);
                    }
                    updateModalFooter();
                });
            });
        }

        // Live Search Filter inside Modal
        modalSearchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            const items = qbankList.querySelectorAll('.qbank-item');
            let visibleCount = 0;

            items.forEach(item => {
                const text = item.querySelector('.fw-semibold').textContent.toLowerCase();
                if (text.includes(term)) {
                    item.classList.remove('d-none');
                    visibleCount++;
                } else {
                    item.classList.add('d-none');
                }
            });

            if (visibleCount === 0 && qbankQuestions.length > 0) {
                qbankEmpty.classList.remove('d-none');
                qbankEmpty.querySelector('p').textContent = 'No questions match your search.';
            } else if (qbankQuestions.length > 0) {
                qbankEmpty.classList.add('d-none');
            }
        });

        function updateModalFooter() {
            const count = selectedIndices.size;
            modalSelectedCount.textContent = `${count} selected`;
            modalImportBtn.disabled = count === 0;
        }

        modalImportBtn.addEventListener('click', function () {
            selectedIndices.forEach(index => {
                const q = qbankQuestions[index];
                
                let parsedOptions = q.options;
                if (typeof q.options === 'string') {
                    try { parsedOptions = JSON.parse(q.options); } catch(e) {}
                }

                addQuestion({
                    question_text: q.question_text,
                    options: parsedOptions,
                    correct_answer: q.correct_answer,
                    type: q.type
                });
            });

            // Close modal
            const modalInstance = bootstrap.Modal.getInstance(dbModal);
            modalInstance.hide();
        });
    });
</script>

@endsection
