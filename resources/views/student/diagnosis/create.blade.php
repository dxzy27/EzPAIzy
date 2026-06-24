@extends('layouts.dashboard')

@section('content')
@push('styles')
<style>
    :root {
        --diag-accent: #7c3aed;
        --diag-accent-light: #ede9fe;
        --diag-accent-mid: #a78bfa;
        --card-radius: 18px;
    }

    /* ── Progress Bar ── */
    .diag-progress-wrap {
        background: var(--card-bg, #f8fafc);
        border: 1px solid var(--border, #e5e7eb);
        border-radius: 99px;
        height: 8px;
        overflow: hidden;
        margin-bottom: 6px;
    }
    .diag-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #7c3aed, #a78bfa);
        border-radius: 99px;
        transition: width .4s ease;
    }
    .diag-step-label {
        font-size: .78rem;
        color: var(--text-muted, #6b7280);
        font-weight: 600;
        letter-spacing: .3px;
    }

    /* ── Question Slides ── */
    .diag-question-block {
        display: none;
        animation: fadeSlide .35s ease;
    }
    .diag-question-block.active {
        display: block;
    }
    @keyframes fadeSlide {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .diag-q-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px; height: 36px;
        background: var(--diag-accent);
        color: #fff;
        font-weight: 700;
        font-size: .9rem;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .diag-q-text {
        font-size: 1.08rem;
        font-weight: 600;
        color: var(--text-main, #111827);
        line-height: 1.45;
    }
    .diag-dimension-badge {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
        padding: 3px 10px;
        border-radius: 20px;
        background: var(--diag-accent-light);
        color: var(--diag-accent);
    }

    /* ── Option Cards ── */
    .diag-option {
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 14px 16px;
        border: 1.5px solid var(--border, #e5e7eb);
        border-radius: 12px;
        cursor: pointer;
        background: var(--card-bg, #fff);
        transition: border-color .18s, box-shadow .18s, background .18s;
        margin-bottom: 10px;
    }
    .diag-option:hover {
        border-color: var(--diag-accent-mid);
        background: var(--diag-accent-light);
    }
    .diag-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip: rect(0 0 0 0);
        white-space: nowrap;
        pointer-events: none;
    }
    .diag-option input[type="radio"]:checked ~ .diag-option-inner {
        /* handled by JS class on label */
    }
    .diag-option.selected {
        border-color: var(--diag-accent);
        background: var(--diag-accent-light);
        box-shadow: 0 0 0 3px rgba(124,58,237,.12);
    }
    .diag-option-letter {
        width: 30px; height: 30px;
        border-radius: 8px;
        background: #f3f4f6;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: .85rem;
        color: #374151;
        flex-shrink: 0;
        transition: background .18s, color .18s;
    }
    .diag-option.selected .diag-option-letter {
        background: var(--diag-accent);
        color: #fff;
    }
    .diag-option-text {
        font-size: .93rem;
        color: var(--text-main, #111827);
        line-height: 1.45;
        padding-top: 4px;
    }

    /* ── Nav Buttons ── */
    .diag-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 24px;
        gap: 12px;
    }
    .diag-btn-prev {
        background: none;
        border: 1.5px solid var(--border, #e5e7eb);
        color: var(--text-muted, #6b7280);
        font-weight: 600; font-size: .88rem;
        padding: 10px 22px; border-radius: 10px;
        cursor: pointer; transition: all .18s;
    }
    .diag-btn-prev:hover { border-color: #9ca3af; color: #374151; }
    .diag-btn-next {
        background: var(--diag-accent);
        border: none;
        color: #fff;
        font-weight: 700; font-size: .92rem;
        padding: 11px 28px; border-radius: 10px;
        cursor: pointer; transition: background .18s, transform .15s;
        display: flex; align-items: center; gap: 8px;
    }
    .diag-btn-next:hover { background: #6d28d9; transform: translateY(-1px); }
    .diag-btn-next:disabled { background: #c4b5fd; cursor: not-allowed; transform: none; }
    .diag-btn-submit {
        background: linear-gradient(135deg, #7c3aed, #4f46e5);
        border: none; color: #fff;
        font-weight: 700; font-size: .92rem;
        padding: 12px 32px; border-radius: 10px;
        cursor: pointer; transition: opacity .18s, transform .15s;
        display: none; align-items: center; gap: 8px;
    }
    .diag-btn-submit:hover { opacity: .9; transform: translateY(-1px); }

    /* ── Info banner ── */
    .diag-intro-banner {
        background: linear-gradient(135deg, #ede9fe 0%, #e0e7ff 100%);
        border: 1px solid #c4b5fd;
        border-radius: var(--card-radius);
        padding: 18px 22px;
        margin-bottom: 28px;
        display: flex; gap: 14px; align-items: flex-start;
    }
    .diag-intro-icon {
        width: 42px; height: 42px;
        background: #7c3aed;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; color: #fff; flex-shrink: 0;
    }
</style>
@endpush

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Learning Style Diagnosis</h4>
        <p class="text-muted mb-0" style="font-size:.875rem;">10 scenario-based questions · ~4 minutes</p>
    </div>
    <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-x-lg me-1"></i> Skip for now
    </a>
</div>

{{-- Intro Banner --}}
<div class="diag-intro-banner">
    <div class="diag-intro-icon"><i class="bi bi-clipboard-pulse"></i></div>
    <div>
        <div class="fw-bold" style="color:#4c1d95;font-size:.95rem;">How this works</div>
        <div class="text-muted" style="font-size:.84rem;margin-top:3px;">
            Answer honestly based on how you <em>actually</em> behave — not what you wish you did. Each scenario is designed to reveal patterns across multiple dimensions of your learning behaviour. There are no wrong answers.
        </div>
    </div>
</div>

{{-- Progress --}}
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="diag-step-label">Question <span id="currentStep">1</span> of 10</span>
        <span class="diag-step-label"><span id="progressPct">10</span>% complete</span>
    </div>
    <div class="diag-progress-wrap">
        <div class="diag-progress-bar" id="progressBar" style="width:10%;"></div>
    </div>
</div>

{{-- Form --}}
<form action="{{ route('student.diagnosis.store') }}" method="POST" id="diagForm">
    @csrf

    {{-- Q1 --}}
    <div class="diag-question-block card p-4 active" data-question="1">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">1</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Memory Encoding</span>
                <div class="diag-q-text">You just learned a new term. What helps you recall it best an hour later?</div>
            </div>
        </div>
        <label class="diag-option" for="q1_A">
            <input type="radio" name="q1" id="q1_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">Writing it down, summarizing it, or making a quick acronym.</span>
        </label>
        <label class="diag-option" for="q1_B">
            <input type="radio" name="q1" id="q1_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">Remembering how it sounded, or saying it out loud to yourself.</span>
        </label>
        <label class="diag-option" for="q1_C">
            <input type="radio" name="q1" id="q1_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">Recalling whether you got it right or wrong in a self-test.</span>
        </label>
    </div>

    {{-- Q2 --}}
    <div class="diag-question-block card p-4" data-question="2">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">2</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Distraction Response</span>
                <div class="diag-q-text">You lose focus while studying. What helps you get back on track?</div>
            </div>
        </div>
        <label class="diag-option" for="q2_A">
            <input type="radio" name="q2" id="q2_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">Writing a quick summary list or making a flashcard.</span>
        </label>
        <label class="diag-option" for="q2_B">
            <input type="radio" name="q2" id="q2_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">Listening to background music or speaking thoughts aloud.</span>
        </label>
        <label class="diag-option" for="q2_C">
            <input type="radio" name="q2" id="q2_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">Starting a timed quiz or score-based challenge.</span>
        </label>
    </div>

    {{-- Q3 --}}
    <div class="diag-question-block card p-4" data-question="3">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">3</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">New Topic Approach</span>
                <div class="diag-q-text">How do you prefer to start learning a newly assigned topic?</div>
            </div>
        </div>
        <label class="diag-option" for="q3_A">
            <input type="radio" name="q3" id="q3_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">Reading the text carefully and taking written summary notes.</span>
        </label>
        <label class="diag-option" for="q3_B">
            <input type="radio" name="q3" id="q3_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">Reading the text and explaining difficult parts out loud.</span>
        </label>
        <label class="diag-option" for="q3_C">
            <input type="radio" name="q3" id="q3_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">Jumping straight into practice questions or self-tests.</span>
        </label>
    </div>

    {{-- Q4 --}}
    <div class="diag-question-block card p-4" data-question="4">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">4</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Exam Preparation</span>
                <div class="diag-q-text">You have an exam in two days. What is your preparation strategy?</div>
            </div>
        </div>
        <label class="diag-option" for="q4_A">
            <input type="radio" name="q4" id="q4_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">Writing summary sheets, acronyms, or re-writing notes.</span>
        </label>
        <label class="diag-option" for="q4_B">
            <input type="radio" name="q4" id="q4_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">Recording voice notes, discussing with friends, or explaining concepts out loud.</span>
        </label>
        <label class="diag-option" for="q4_C">
            <input type="radio" name="q4" id="q4_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">Practicing as many timed exam questions as possible.</span>
        </label>
    </div>

    {{-- Q5 --}}
    <div class="diag-question-block card p-4" data-question="5">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">5</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Group Dynamics</span>
                <div class="diag-q-text">What type of group study session is most beneficial for you?</div>
            </div>
        </div>
        <label class="diag-option" for="q5_A">
            <input type="radio" name="q5" id="q5_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">Discussion-focused sessions explaining concepts verbally.</span>
        </label>
        <label class="diag-option" for="q5_B">
            <input type="radio" name="q5" id="q5_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">Competitive quiz tournaments to see who gets the highest score.</span>
        </label>
        <label class="diag-option" for="q5_C">
            <input type="radio" name="q5" id="q5_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">Studying quietly together and comparing written notes at the end.</span>
        </label>
    </div>

    {{-- Q6 --}}
    <div class="diag-question-block card p-4" data-question="6">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">6</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Failure Reaction</span>
                <div class="diag-q-text">You score poorly on a quiz you prepared for. What is your reaction?</div>
            </div>
        </div>
        <label class="diag-option" for="q6_A">
            <input type="radio" name="q6" id="q6_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">Reviewing and re-writing corrected facts in your notes.</span>
        </label>
        <label class="diag-option" for="q6_B">
            <input type="radio" name="q6" id="q6_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">Retaking the quiz immediately to get a higher score.</span>
        </label>
        <label class="diag-option" for="q6_C">
            <input type="radio" name="q6" id="q6_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">Talking it over and explaining the mistakes out loud.</span>
        </label>
    </div>

    {{-- Q7 --}}
    <div class="diag-question-block card p-4" data-question="7">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">7</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Content Preference</span>
                <div class="diag-q-text">Which type of study material do you prefer most?</div>
            </div>
        </div>
        <label class="diag-option" for="q7_A">
            <input type="radio" name="q7" id="q7_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">A printed text where you can write notes and marginal definitions.</span>
        </label>
        <label class="diag-option" for="q7_B">
            <input type="radio" name="q7" id="q7_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">An audio lecture or podcast explaining the concepts.</span>
        </label>
        <label class="diag-option" for="q7_C">
            <input type="radio" name="q7" id="q7_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">An interactive quiz bank with instant score feedback.</span>
        </label>
    </div>

    {{-- Q8 --}}
    <div class="diag-question-block card p-4" data-question="8">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">8</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Progress Motivation</span>
                <div class="diag-q-text">What motivates you most to keep studying?</div>
            </div>
        </div>
        <label class="diag-option" for="q8_A">
            <input type="radio" name="q8" id="q8_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">Seeing your scores improve on a leaderboard or progress graph.</span>
        </label>
        <label class="diag-option" for="q8_B">
            <input type="radio" name="q8" id="q8_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">Hearing verbal praise or encouragement from a teacher or peer.</span>
        </label>
        <label class="diag-option" for="q8_C">
            <input type="radio" name="q8" id="q8_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">Looking through folders of your completed written summaries.</span>
        </label>
    </div>

    {{-- Q9 --}}
    <div class="diag-question-block card p-4" data-question="9">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">9</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Retention Strategy</span>
                <div class="diag-q-text">You need to memorize 15 terms for a test. What is your strategy?</div>
            </div>
        </div>
        <label class="diag-option" for="q9_A">
            <input type="radio" name="q9" id="q9_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">Saying the terms and definitions out loud repeatedly.</span>
        </label>
        <label class="diag-option" for="q9_B">
            <input type="radio" name="q9" id="q9_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">Writing down the terms and definitions multiple times.</span>
        </label>
        <label class="diag-option" for="q9_C">
            <input type="radio" name="q9" id="q9_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">Practicing active recall via rapid self-quizzing.</span>
        </label>
    </div>

    {{-- Q10 --}}
    <div class="diag-question-block card p-4" data-question="10">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">10</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Self-Assessment</span>
                <div class="diag-q-text">What is your greatest learning strength?</div>
            </div>
        </div>
        <label class="diag-option" for="q10_A">
            <input type="radio" name="q10" id="q10_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">Learning by writing summaries, lists, and notes.</span>
        </label>
        <label class="diag-option" for="q10_B">
            <input type="radio" name="q10" id="q10_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">Performing well under pressure, deadlines, or test scores.</span>
        </label>
        <label class="diag-option" for="q10_C">
            <input type="radio" name="q10" id="q10_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">Retaining information by explaining, discussing, or hearing it.</span>
        </label>
    </div>

    {{-- Navigation --}}
    <div class="diag-nav">
        <button type="button" class="diag-btn-prev" id="btnPrev" style="visibility:hidden;">
            <i class="bi bi-arrow-left me-1"></i> Previous
        </button>
        <button type="button" class="diag-btn-next" id="btnNext" disabled>
            Next <i class="bi bi-arrow-right ms-1"></i>
        </button>
        <button type="submit" class="diag-btn-submit" id="btnSubmit">
            <i class="bi bi-check-circle me-1"></i> Get My Results
        </button>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mt-3">Please answer all questions before submitting.</div>
    @endif
</form>

@push('scripts')
<script>
(function () {
    const TOTAL = 10;
    let current = 1;
    const answers = {};

    function getBlock(n) {
        return document.querySelector(`.diag-question-block[data-question="${n}"]`);
    }

    function isAnswered(n) {
        const block = getBlock(n);
        return block && block.querySelector('input[type=radio]:checked') !== null;
    }

    function updateUI() {
        const blocks = document.querySelectorAll('.diag-question-block');
        const stepLabel = document.getElementById('currentStep');
        const pctLabel = document.getElementById('progressPct');
        const bar = document.getElementById('progressBar');
        const btnPrev = document.getElementById('btnPrev');
        const btnNext = document.getElementById('btnNext');
        const btnSubmit = document.getElementById('btnSubmit');

        if (!blocks.length || !stepLabel || !pctLabel || !bar || !btnPrev || !btnNext || !btnSubmit) return;

        blocks.forEach(b => b.classList.remove('active'));
        const activeBlock = getBlock(current);
        if (activeBlock) activeBlock.classList.add('active');

        stepLabel.textContent = current;
        const pct = Math.round((current / TOTAL) * 100);
        pctLabel.textContent  = pct;
        bar.style.width       = pct + '%';

        btnPrev.style.visibility = current === 1 ? 'hidden' : 'visible';

        const answered = isAnswered(current);
        btnNext.disabled = !answered;

        if (current === TOTAL) {
            btnNext.style.display   = 'none';
            btnSubmit.style.display = answered ? 'flex' : 'none';
        } else {
            btnNext.style.display   = 'flex';
            btnSubmit.style.display = 'none';
        }
    }

    // 1. Listen for changes on radio buttons using event delegation on document
    document.addEventListener('change', function (event) {
        const target = event.target;
        if (target && target.type === 'radio' && target.name.startsWith('q')) {
            const name = target.name;
            
            // Remove 'selected' class from all sibling options for this question
            document.querySelectorAll(`input[name="${name}"]`).forEach(i => {
                const diagOption = i.closest('.diag-option');
                if (diagOption) diagOption.classList.remove('selected');
            });
            
            // Add 'selected' class to the checked option's label
            if (target.checked) {
                const diagOption = target.closest('.diag-option');
                if (diagOption) diagOption.classList.add('selected');
                
                answers[name] = target.value;
                
                // Re-enable Next / Submit button dynamically
                const btnNext = document.getElementById('btnNext');
                const btnSubmit = document.getElementById('btnSubmit');
                if (btnNext) btnNext.disabled = false;
                if (current === TOTAL && btnSubmit) {
                    btnSubmit.style.display = 'flex';
                }
            }
        }
    });

    // 2. Listen for clicks on Next / Prev buttons using event delegation on document
    document.addEventListener('click', function (event) {
        const nextBtn = event.target.closest('#btnNext');
        const prevBtn = event.target.closest('#btnPrev');

        if (nextBtn) {
            if (!isAnswered(current)) return;
            if (current < TOTAL) {
                current++;
                updateUI();
            }
        } else if (prevBtn) {
            if (current > 1) {
                current--;
                updateUI();
            }
        }
    });

    // 3. Initialize visual state for pre-selected options (if any) and set initial UI
    function init() {
        document.querySelectorAll('input[type="radio"]').forEach(inp => {
            const diagOption = inp.closest('.diag-option');
            if (inp.checked && diagOption) {
                diagOption.classList.add('selected');
                answers[inp.name] = inp.value;
            }
        });
        updateUI();
    }

    // Run init on multiple events to guarantee execution after Vue mounts
    if (document.readyState === 'complete') {
        init();
    } else {
        window.addEventListener('load', init);
    }
    
    // Also run init after a tiny delay as a foolproof safeguard
    setTimeout(init, 100);
})();
</script>
@endpush

@endsection
