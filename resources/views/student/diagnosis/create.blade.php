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
                <div class="diag-q-text">You just learned the meaning of a new term in class. When you close the book an hour later, what is most likely to help you recall it?</div>
            </div>
        </div>
        <label class="diag-option" for="q1_A">
            <input type="radio" name="q1" id="q1_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">A clear mental image of the page layout, colour of the text, or a diagram associated with it.</span>
        </label>
        <label class="diag-option" for="q1_B">
            <input type="radio" name="q1" id="q1_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">The way your teacher's voice sounded when explaining it, or how you pronounced it to yourself.</span>
        </label>
        <label class="diag-option" for="q1_C">
            <input type="radio" name="q1" id="q1_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">Whether you got it right or wrong in a self-test — the adrenaline of being tested made it stick.</span>
        </label>
    </div>

    {{-- Q2 --}}
    <div class="diag-question-block card p-4" data-question="2">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">2</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Distraction Response</span>
                <div class="diag-q-text">You sit down to study but after 10 minutes your concentration breaks. What is the most effective thing you instinctively do next to re-focus?</div>
            </div>
        </div>
        <label class="diag-option" for="q2_A">
            <input type="radio" name="q2" id="q2_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">Re-read the key points, reorganise your notes, or draw a quick summary diagram of what you covered.</span>
        </label>
        <label class="diag-option" for="q2_B">
            <input type="radio" name="q2" id="q2_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">Put on soft background music or talk through what you studied so far — even if just to yourself.</span>
        </label>
        <label class="diag-option" for="q2_C">
            <input type="radio" name="q2" id="q2_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">Switch to a quick quiz or challenge mode — the pressure of a score timer instantly snaps you back in.</span>
        </label>
    </div>

    {{-- Q3 --}}
    <div class="diag-question-block card p-4" data-question="3">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">3</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">New Topic Approach</span>
                <div class="diag-q-text">Your teacher assigns a new chapter to prepare before the next class. What is your honest first instinct about how to approach it?</div>
            </div>
        </div>
        <label class="diag-option" for="q3_A">
            <input type="radio" name="q3" id="q3_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">Skim through looking for headings, bold terms, tables, and any visual structure before diving into the text.</span>
        </label>
        <label class="diag-option" for="q3_B">
            <input type="radio" name="q3" id="q3_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">Read the chapter carefully from the beginning, pausing to re-read difficult parts aloud or explain them to yourself.</span>
        </label>
        <label class="diag-option" for="q3_C">
            <input type="radio" name="q3" id="q3_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">Look for practice questions or past tests on the topic first, then study only the areas you get wrong.</span>
        </label>
    </div>

    {{-- Q4 --}}
    <div class="diag-question-block card p-4" data-question="4">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">4</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Exam Preparation</span>
                <div class="diag-q-text">An important exam is in two days. You have limited time. Which approach feels most natural and effective to you?</div>
            </div>
        </div>
        <label class="diag-option" for="q4_A">
            <input type="radio" name="q4" id="q4_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">Create a visual summary: mind maps, colour-coded flashcards, or a one-page visual overview of the entire topic.</span>
        </label>
        <label class="diag-option" for="q4_B">
            <input type="radio" name="q4" id="q4_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">Summarise the content verbally: record voice memos, explain concepts to a friend, or form a study group to discuss aloud.</span>
        </label>
        <label class="diag-option" for="q4_C">
            <input type="radio" name="q4" id="q4_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">Do as many practice questions as possible under timed conditions — simulate the exam pressure to get ready.</span>
        </label>
    </div>

    {{-- Q5 --}}
    <div class="diag-question-block card p-4" data-question="5">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">5</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Group Dynamics</span>
                <div class="diag-q-text">A classmate asks you to join a group study session. Which version of that session would you find most beneficial?</div>
            </div>
        </div>
        <label class="diag-option" for="q5_A">
            <input type="radio" name="q5" id="q5_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">Everyone takes turns verbally explaining one concept while others listen and ask questions — discussion-heavy.</span>
        </label>
        <label class="diag-option" for="q5_B">
            <input type="radio" name="q5" id="q5_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">The group runs a quiz tournament, competing question by question to see who scores highest by the end.</span>
        </label>
        <label class="diag-option" for="q5_C">
            <input type="radio" name="q5" id="q5_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">Everyone studies independently at the same table but compares their own written notes or diagrams at the end.</span>
        </label>
    </div>

    {{-- Q6 --}}
    <div class="diag-question-block card p-4" data-question="6">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">6</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Failure Reaction</span>
                <div class="diag-q-text">You score poorly on a quiz you thought you had prepared well for. What is your immediate, honest reaction?</div>
            </div>
        </div>
        <label class="diag-option" for="q6_A">
            <input type="radio" name="q6" id="q6_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">Review your notes and annotate the areas where your visual summary missed key points — revise your mind map.</span>
        </label>
        <label class="diag-option" for="q6_B">
            <input type="radio" name="q6" id="q6_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">Retake the quiz immediately — the failure itself motivates you to prove you can do better this time.</span>
        </label>
        <label class="diag-option" for="q6_C">
            <input type="radio" name="q6" id="q6_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">Talk it over — explain what you got wrong to a friend or to yourself out loud so you can hear where your understanding broke down.</span>
        </label>
    </div>

    {{-- Q7 --}}
    <div class="diag-question-block card p-4" data-question="7">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">7</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Content Preference</span>
                <div class="diag-q-text">Your teacher provides three types of study resources. If you could only use one, which would you choose?</div>
            </div>
        </div>
        <label class="diag-option" for="q7_A">
            <input type="radio" name="q7" id="q7_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">A set of visual flashcards with colour-coded categories, diagrams, and structured tables.</span>
        </label>
        <label class="diag-option" for="q7_B">
            <input type="radio" name="q7" id="q7_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">A detailed e-book or written notes with thorough explanations written in flowing, readable prose.</span>
        </label>
        <label class="diag-option" for="q7_C">
            <input type="radio" name="q7" id="q7_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">An interactive quiz bank with instant right/wrong feedback and a running score counter.</span>
        </label>
    </div>

    {{-- Q8 --}}
    <div class="diag-question-block card p-4" data-question="8">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">8</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Progress Motivation</span>
                <div class="diag-q-text">Which of the following would give you the strongest sense of satisfaction and push you to study more?</div>
            </div>
        </div>
        <label class="diag-option" for="q8_A">
            <input type="radio" name="q8" id="q8_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">Seeing your quiz score climb week over week on a leaderboard or personal progress graph.</span>
        </label>
        <label class="diag-option" for="q8_B">
            <input type="radio" name="q8" id="q8_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">Getting positive verbal encouragement from your teacher or a study partner who acknowledges your effort aloud.</span>
        </label>
        <label class="diag-option" for="q8_C">
            <input type="radio" name="q8" id="q8_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">Visually checking off a topic on your study plan or seeing your revision list visibly shrink.</span>
        </label>
    </div>

    {{-- Q9 --}}
    <div class="diag-question-block card p-4" data-question="9">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">9</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Retention Strategy</span>
                <div class="diag-q-text">You need to memorise a list of 15 key terms for tomorrow's test. Which strategy gives you the most confidence?</div>
            </div>
        </div>
        <label class="diag-option" for="q9_A">
            <input type="radio" name="q9" id="q9_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">Recite each term and its meaning aloud in a rhythmic pattern — creating a verbal routine or mini "chant" for each one.</span>
        </label>
        <label class="diag-option" for="q9_B">
            <input type="radio" name="q9" id="q9_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">Write each term on a flashcard with a colour or symbol, then go through the deck repeatedly until all are memorised visually.</span>
        </label>
        <label class="diag-option" for="q9_C">
            <input type="radio" name="q9" id="q9_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">Quiz yourself on all 15 terms repeatedly under pressure — keep retesting on the ones you get wrong until your score is perfect.</span>
        </label>
    </div>

    {{-- Q10 --}}
    <div class="diag-question-block card p-4" data-question="10">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">10</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Self-Assessment</span>
                <div class="diag-q-text">If you had to explain your own biggest learning strength honestly, which of these fits you best?</div>
            </div>
        </div>
        <label class="diag-option" for="q10_A">
            <input type="radio" name="q10" id="q10_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">I remember things I have seen — layouts, colours, spatial positions on a page. My memory is highly visual.</span>
        </label>
        <label class="diag-option" for="q10_B">
            <input type="radio" name="q10" id="q10_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">I perform best under pressure — deadlines and score targets push me to produce my best work.</span>
        </label>
        <label class="diag-option" for="q10_C">
            <input type="radio" name="q10" id="q10_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">I retain information best when I have talked about it — explained, discussed, or heard it multiple times.</span>
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
