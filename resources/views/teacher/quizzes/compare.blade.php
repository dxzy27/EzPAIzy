@extends('layouts.dashboard')

@section('content')
@push('styles')
<style>
    .model-card { border-radius: 16px; border: none; box-shadow: 0 8px 24px rgba(0,0,0,.08); overflow: hidden; }
    .model-header-gpt    { background: linear-gradient(135deg, #10a37f, #065f46) !important; color: #fff !important; }
    .model-header-gemini { background: linear-gradient(135deg, #4285F4, #1a56db) !important; color: #fff !important; }

    .question-item {
        background: #ffffff;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        border: 2px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        cursor: pointer;
        transition: all .2s ease;
    }
    .question-item:hover  { border-color: #cbd5e1; box-shadow: 0 6px 16px rgba(0,0,0,0.08); transform: translateY(-2px); }
    .question-item.selected { border-color: #3b82f6; background: #eff6ff; }
    
    .q-num { font-weight: 800; color: #475569; font-size: .85rem; text-transform: uppercase; background: #f1f5f9; padding: 0.2rem 0.6rem; border-radius: 6px; }
    
    .option-row  { display: flex; align-items: flex-start; gap: .5rem; font-size: .9rem; margin-top: .4rem; padding: 0.3rem 0.5rem; border-radius: 6px; }
    .option-row:hover { background: #f8fafc; }
    .option-key  { font-weight: 700; min-width: 22px; color: #64748b; }
    .correct-option { color: #059669; font-weight: 600; background: #d1fae5 !important; }
    .correct-option .option-key { color: #059669; }
    
    .error-box   { background:#fff1f2; border:1px solid #fecdd3; border-radius:12px; padding:1.5rem; text-align:center; }
    .sticky-save {
        position: sticky; bottom: 0;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border-top: 1px solid #e2e8f0;
        padding: 1.25rem 1.5rem;
        z-index: 100;
        box-shadow: 0 -8px 24px rgba(0,0,0,.06);
    }
    .source-badge-gpt    { background:#d1fae5; color:#065f46; font-size:.7rem; padding:.2em .6em; border-radius:50px; font-weight:700; }
    .source-badge-gemini { background:#dbeafe; color:#1e40af; font-size:.7rem; padding:.2em .6em; border-radius:50px; font-weight:700; }
    
    /* Make the checkbox slightly larger and aligned */
    .form-check-input.large-checkbox { width: 1.4em; height: 1.4em; cursor: pointer; border-color: #94a3b8; }
    .form-check-input.large-checkbox:checked { background-color: #3b82f6; border-color: #3b82f6; }
</style>
@endpush

{{-- Inject PHP data safely into JS BEFORE any HTML that references it --}}
<script>
    // All question data stored in JS — no HTML attribute JSON parsing needed
    const allData = {
        gpt:    @json($gpt['questions'] ?? []),
        gemini: @json($gemini['questions'] ?? [])
    };
    const selectedQuestions = {}; // key: "gpt-0", "gemini-2", etc.
</script>

<div class="container-fluid py-4 px-4" style="padding-bottom: 130px;">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1">⚖️ AI Comparison Results</h1>
            <p class="text-muted mb-0">
                Topic: <strong>{{ $topic }}</strong> &nbsp;|&nbsp;
                Difficulty: <strong>{{ ucfirst($difficulty) }}</strong>
                &nbsp;|&nbsp;
                <span class="text-primary fw-semibold" id="selected-count">0 questions selected</span>
            </p>
        </div>
        <a href="{{ route('teacher.quizzes.generate') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Generate Again
        </a>
    </div>

    <p class="text-muted mb-4">
        <i class="bi bi-hand-index me-1"></i>
        <strong>Click</strong> any question to select it. Mix and match from both AIs — only selected questions will be saved.
    </p>

    <div class="row g-4">

        {{-- GPT-5.2 Column --}}
        <div class="col-lg-6">
            <div class="model-card card h-100">
                <div class="model-header-gpt p-3 d-flex align-items-center gap-2">
                    <span style="font-size:1.4rem;">🤖</span>
                    <div>
                        <div class="fw-bold">GPT</div>
                        <div style="font-size:.8rem;opacity:.8;">{{ count($gpt['questions'] ?? []) }} questions generated</div>
                    </div>
                    <div class="ms-auto d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-light text-white" onclick="selectAll('gpt')">Select All</button>
                        <button type="button" class="btn btn-sm btn-outline-light text-white"  onclick="deselectAll('gpt')">Clear</button>
                    </div>
                </div>
                <div class="card-body p-3">
                    @if(isset($gpt['error']))
                        <div class="error-box">
                            <i class="bi bi-exclamation-triangle-fill text-danger fs-3 d-block mb-2"></i>
                            <strong>GPT failed</strong>
                            <p class="text-muted small mt-1 mb-0">{{ $gpt['error'] }}</p>
                        </div>
                    @else
                        @foreach(($gpt['questions'] ?? []) as $i => $q)
                            <label class="question-item d-block" id="gpt-q-{{ $i }}" for="gpt-check-{{ $i }}" style="cursor: pointer;">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input large-checkbox" type="checkbox" id="gpt-check-{{ $i }}" onchange="toggleQuestion('gpt', {{ $i }}, this.checked)">
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="q-num">Q{{ $i + 1 }}</span>
                                            <span class="source-badge-gpt">GPT</span>
                                        </div>
                                        <div class="fw-semibold text-dark" style="font-size:1rem;">{{ $q['text'] }}</div>
                                @if(!empty($q['options']))
                                    <div class="mt-2">
                                    @foreach($q['options'] as $key => $opt)
                                        <div class="option-row {{ strtolower($q['correct_answer'] ?? '') === $key ? 'correct-option' : '' }}">
                                            <span class="option-key">{{ strtoupper($key) }}.</span>
                                            <span>{{ $opt }}
                                                @if(strtolower($q['correct_answer'] ?? '') === $key)
                                                    <i class="bi bi-check-circle-fill ms-1" style="font-size:.75rem;"></i>
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                    </div>
                                @else
                                    <div class="text-muted small mt-1">
                                        <i class="bi bi-pencil me-1"></i><em>{{ $q['correct_answer'] ?? 'Open-ended' }}</em>
                                    </div>
                                @endif
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        {{-- Gemini Column --}}
        <div class="col-lg-6">
            <div class="model-card card h-100">
                <div class="model-header-gemini p-3 d-flex align-items-center gap-2">
                    <span style="font-size:1.4rem;">✨</span>
                    <div>
                        <div class="fw-bold">Gemini</div>
                        <div style="font-size:.8rem;opacity:.8;">{{ count($gemini['questions'] ?? []) }} questions generated</div>
                    </div>
                    <div class="ms-auto d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-light text-white" onclick="selectAll('gemini')">Select All</button>
                        <button type="button" class="btn btn-sm btn-outline-light text-white"  onclick="deselectAll('gemini')">Clear</button>
                    </div>
                </div>
                <div class="card-body p-3">
                    @if(isset($gemini['error']))
                        <div class="error-box">
                            <i class="bi bi-exclamation-triangle-fill text-danger fs-3 d-block mb-2"></i>
                            <strong>Gemini failed</strong>
                            <p class="text-muted small mt-1 mb-0">{{ $gemini['error'] }}</p>
                        </div>
                    @else
                        @foreach(($gemini['questions'] ?? []) as $i => $q)
                            <label class="question-item d-block" id="gemini-q-{{ $i }}" for="gemini-check-{{ $i }}" style="cursor: pointer;">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input large-checkbox" type="checkbox" id="gemini-check-{{ $i }}" onchange="toggleQuestion('gemini', {{ $i }}, this.checked)">
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="q-num">Q{{ $i + 1 }}</span>
                                            <span class="source-badge-gemini">Gemini</span>
                                        </div>
                                        <div class="fw-semibold text-dark" style="font-size:1rem;">{{ $q['text'] }}</div>
                                @if(!empty($q['options']))
                                    <div class="mt-2">
                                    @foreach($q['options'] as $key => $opt)
                                        <div class="option-row {{ strtolower($q['correct_answer'] ?? '') === $key ? 'correct-option' : '' }}">
                                            <span class="option-key">{{ strtoupper($key) }}.</span>
                                            <span>{{ $opt }}
                                                @if(strtolower($q['correct_answer'] ?? '') === $key)
                                                    <i class="bi bi-check-circle-fill ms-1" style="font-size:.75rem;"></i>
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                    </div>
                                @else
                                    <div class="text-muted small mt-1">
                                        <i class="bi bi-pencil me-1"></i><em>{{ $q['correct_answer'] ?? 'Open-ended' }}</em>
                                    </div>
                                @endif
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- end row --}}
</div>

{{-- Sticky Save Bar --}}
<div class="sticky-save">
    <form id="save-form" action="{{ route('teacher.quizzes.save_selected') }}" method="POST">
        @csrf
        <input type="hidden" name="topic"      value="{{ $topic }}">
        <input type="hidden" name="difficulty" value="{{ $difficulty }}">
        <input type="hidden" name="questions"  id="questions-input" value="[]">

        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="flex-grow-1">
                <input type="text" name="title" class="form-control"
                    placeholder="Quiz title (e.g. Al-Quran Easy Quiz)" required
                    style="max-width: 400px;">
            </div>
            <div class="text-muted small" id="save-summary">No questions selected</div>
            <button type="submit" class="btn btn-success px-4 fw-semibold" id="save-btn" disabled>
                <i class="bi bi-floppy me-2"></i> Save Selected to Database
            </button>
        </div>
    </form>
</div>

<script>
    function toggleQuestion(model, index, isChecked) {
        const key = model + '-' + index;
        const el  = document.getElementById(model + '-q-' + index);
        const checkbox = document.getElementById(model + '-check-' + index);

        if (!isChecked) {
            delete selectedQuestions[key];
            if (el) el.classList.remove('selected');
        } else {
            const q = allData[model][index];
            selectedQuestions[key] = Object.assign({}, q, {
                _source: model === 'gpt' ? 'GPT' : 'Gemini'
            });
            if (el) el.classList.add('selected');
        }
        updateUI();
    }

    function selectAll(model) {
        (allData[model] || []).forEach(function(q, index) {
            const key = model + '-' + index;
            const el  = document.getElementById(model + '-q-' + index);
            const checkbox = document.getElementById(model + '-check-' + index);
            
            selectedQuestions[key] = Object.assign({}, q, {
                _source: model === 'gpt' ? 'GPT' : 'Gemini'
            });
            if (el) el.classList.add('selected');
            if (checkbox) checkbox.checked = true;
        });
        updateUI();
    }

    function deselectAll(model) {
        (allData[model] || []).forEach(function(q, index) {
            const key = model + '-' + index;
            const el  = document.getElementById(model + '-q-' + index);
            const checkbox = document.getElementById(model + '-check-' + index);
            
            delete selectedQuestions[key];
            if (el) el.classList.remove('selected');
            if (checkbox) checkbox.checked = false;
        });
        updateUI();
    }

    function updateUI() {
        const count       = Object.keys(selectedQuestions).length;
        const gptCount    = Object.keys(selectedQuestions).filter(k => k.startsWith('gpt-')).length;
        const geminiCount = Object.keys(selectedQuestions).filter(k => k.startsWith('gemini-')).length;

        document.getElementById('selected-count').textContent =
            count + ' question' + (count !== 1 ? 's' : '') + ' selected';
        document.getElementById('save-btn').disabled = count === 0;

        if (count === 0) {
            document.getElementById('save-summary').textContent = 'No questions selected';
        } else {
            let parts = [];
            if (gptCount)    parts.push(gptCount + ' from GPT');
            if (geminiCount) parts.push(geminiCount + ' from Gemini');
            document.getElementById('save-summary').textContent = parts.join(' + ');
        }

        document.getElementById('questions-input').value =
            JSON.stringify(Object.values(selectedQuestions));
    }

    document.getElementById('save-form').addEventListener('submit', function(e) {
        if (Object.keys(selectedQuestions).length === 0) {
            e.preventDefault();
            alert('Please select at least one question.');
            return;
        }
        const btn = document.getElementById('save-btn');
        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
    });
</script>
@endsection
