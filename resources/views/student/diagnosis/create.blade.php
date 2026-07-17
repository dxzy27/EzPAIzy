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
        align-items: center;
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
    .diag-option input[type="checkbox"] {
        margin: 0;
        cursor: pointer;
        width: 16px;
        height: 16px;
        flex-shrink: 0;
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
        padding-top: 0;
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
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Dashboard">
            <i class="bi bi-house-door fs-5"></i>
        </a>
        <div>
            <h4 class="mb-0 fw-bold">The VARK Questionnaire for Younger People</h4>
            <p class="text-muted mb-0" style="font-size:.875rem;">16 scenario-based questions · ~6 minutes</p>
        </div>
    </div>
    <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-x-lg me-1"></i> Skip for now
    </a>
</div>

{{-- Intro Banner --}}
<div class="diag-intro-banner">
    <div class="diag-intro-icon"><i class="bi bi-clipboard-pulse"></i></div>
    <div>
        <div class="fw-bold" style="color:#4c1d95;font-size:.95rem;">How Do I Learn Best?</div>
        <div class="text-muted" style="font-size:.84rem;margin-top:3px;">
            Choose the answers which best explain your preference. Please click more than one if a single answer does not match your perception. Leave blank any question that does not apply.
        </div>
    </div>
</div>

{{-- Progress --}}
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="diag-step-label">Question <span id="currentStep">1</span> of 16</span>
        <span class="diag-step-label"><span id="progressPct">6</span>% complete</span>
    </div>
    <div class="diag-progress-wrap">
        <div class="diag-progress-bar" id="progressBar" style="width:6.25%;"></div>
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
                <span class="diag-dimension-badge mb-2 d-inline-block">Younger VARK</span>
                <div class="diag-q-text">I need to find the way to a shop that a friend has recommended. I would:</div>
            </div>
        </div>
        <label class="diag-option" for="q1_A">
            <input type="checkbox" name="q1[]" id="q1_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">find out where the shop is in relation to somewhere I know.</span>
        </label>
        <label class="diag-option" for="q1_B">
            <input type="checkbox" name="q1[]" id="q1_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">ask my friend to tell me the directions.</span>
        </label>
        <label class="diag-option" for="q1_C">
            <input type="checkbox" name="q1[]" id="q1_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">write down the street directions I need to remember.</span>
        </label>
        <label class="diag-option" for="q1_D">
            <input type="checkbox" name="q1[]" id="q1_D" value="D">
            <span class="diag-option-letter">D</span>
            <span class="diag-option-text">use a map.</span>
        </label>
    </div>

    {{-- Q2 --}}
    <div class="diag-question-block card p-4" data-question="2">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">2</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Younger VARK</span>
                <div class="diag-q-text">A website has a video showing how to make a special graph or chart. There is a person speaking, some lists and words describing what to do and some diagrams. I would learn most from:</div>
            </div>
        </div>
        <label class="diag-option" for="q2_A">
            <input type="checkbox" name="q2[]" id="q2_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">seeing the diagrams.</span>
        </label>
        <label class="diag-option" for="q2_B">
            <input type="checkbox" name="q2[]" id="q2_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">listening.</span>
        </label>
        <label class="diag-option" for="q2_C">
            <input type="checkbox" name="q2[]" id="q2_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">reading the words.</span>
        </label>
        <label class="diag-option" for="q2_D">
            <input type="checkbox" name="q2[]" id="q2_D" value="D">
            <span class="diag-option-letter">D</span>
            <span class="diag-option-text">watching the actions.</span>
        </label>
    </div>

    {{-- Q3 --}}
    <div class="diag-question-block card p-4" data-question="3">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">3</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Younger VARK</span>
                <div class="diag-q-text">I want to find out more about a tour that I am going on. I would:</div>
            </div>
        </div>
        <label class="diag-option" for="q3_A">
            <input type="checkbox" name="q3[]" id="q3_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">watch videos to see if there are things I like.</span>
        </label>
        <label class="diag-option" for="q3_B">
            <input type="checkbox" name="q3[]" id="q3_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">use a map and see where the places are.</span>
        </label>
        <label class="diag-option" for="q3_C">
            <input type="checkbox" name="q3[]" id="q3_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">read about the tour on the itinerary.</span>
        </label>
        <label class="diag-option" for="q3_D">
            <input type="checkbox" name="q3[]" id="q3_D" value="D">
            <span class="diag-option-letter">D</span>
            <span class="diag-option-text">talk with the person who planned the tour or others who are going on the tour.</span>
        </label>
    </div>

    {{-- Q4 --}}
    <div class="diag-question-block card p-4" data-question="4">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">4</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Younger VARK</span>
                <div class="diag-q-text">When choosing my subjects to study, these are important for me:</div>
            </div>
        </div>
        <label class="diag-option" for="q4_A">
            <input type="checkbox" name="q4[]" id="q4_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">Applying my knowledge in real situations.</span>
        </label>
        <label class="diag-option" for="q4_B">
            <input type="checkbox" name="q4[]" id="q4_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">Communicating with others through discussion.</span>
        </label>
        <label class="diag-option" for="q4_C">
            <input type="checkbox" name="q4[]" id="q4_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">Working with designs, maps or charts.</span>
        </label>
        <label class="diag-option" for="q4_D">
            <input type="checkbox" name="q4[]" id="q4_D" value="D">
            <span class="diag-option-letter">D</span>
            <span class="diag-option-text">Using words well in written communications.</span>
        </label>
    </div>

    {{-- Q5 --}}
    <div class="diag-question-block card p-4" data-question="5">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">5</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Younger VARK</span>
                <div class="diag-q-text">When I am learning I:</div>
            </div>
        </div>
        <label class="diag-option" for="q5_A">
            <input type="checkbox" name="q5[]" id="q5_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">like to talk things through.</span>
        </label>
        <label class="diag-option" for="q5_B">
            <input type="checkbox" name="q5[]" id="q5_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">see patterns in things.</span>
        </label>
        <label class="diag-option" for="q5_C">
            <input type="checkbox" name="q5[]" id="q5_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">use examples and applications.</span>
        </label>
        <label class="diag-option" for="q5_D">
            <input type="checkbox" name="q5[]" id="q5_D" value="D">
            <span class="diag-option-letter">D</span>
            <span class="diag-option-text">read books, articles and handouts.</span>
        </label>
    </div>

    {{-- Q6 --}}
    <div class="diag-question-block card p-4" data-question="6">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">6</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Younger VARK</span>
                <div class="diag-q-text">I want to suggest fund-raising options for a sports team. I would:</div>
            </div>
        </div>
        <label class="diag-option" for="q6_A">
            <input type="checkbox" name="q6[]" id="q6_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">focus on fund-raising options that I know will work.</span>
        </label>
        <label class="diag-option" for="q6_B">
            <input type="checkbox" name="q6[]" id="q6_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">list details about different options.</span>
        </label>
        <label class="diag-option" for="q6_C">
            <input type="checkbox" name="q6[]" id="q6_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">compare graphs of different fund-raising options.</span>
        </label>
        <label class="diag-option" for="q6_D">
            <input type="checkbox" name="q6[]" id="q6_D" value="D">
            <span class="diag-option-letter">D</span>
            <span class="diag-option-text">question others who have been involved with fundraising.</span>
        </label>
    </div>

    {{-- Q7 --}}
    <div class="diag-question-block card p-4" data-question="7">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">7</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Younger VARK</span>
                <div class="diag-q-text">I want to learn how to play a new board game or card game. I would:</div>
            </div>
        </div>
        <label class="diag-option" for="q7_A">
            <input type="checkbox" name="q7[]" id="q7_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">watch others play the game before joining in.</span>
        </label>
        <label class="diag-option" for="q7_B">
            <input type="checkbox" name="q7[]" id="q7_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">listen to somebody explaining it and ask questions.</span>
        </label>
        <label class="diag-option" for="q7_C">
            <input type="checkbox" name="q7[]" id="q7_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">use the diagrams that explain the various stages, moves and strategies in the game.</span>
        </label>
        <label class="diag-option" for="q7_D">
            <input type="checkbox" name="q7[]" id="q7_D" value="D">
            <span class="diag-option-letter">D</span>
            <span class="diag-option-text">read the instructions.</span>
        </label>
    </div>

    {{-- Q8 --}}
    <div class="diag-question-block card p-4" data-question="8">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">8</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Younger VARK</span>
                <div class="diag-q-text">I have problem with my knee. I would prefer that the doctor:</div>
            </div>
        </div>
        <label class="diag-option" for="q8_A">
            <input type="checkbox" name="q8[]" id="q8_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">gave me something to read to explain what was wrong.</span>
        </label>
        <label class="diag-option" for="q8_B">
            <input type="checkbox" name="q8[]" id="q8_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">used a plastic model to show me what was wrong.</span>
        </label>
        <label class="diag-option" for="q8_C">
            <input type="checkbox" name="q8[]" id="q8_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">described what was wrong.</span>
        </label>
        <label class="diag-option" for="q8_D">
            <input type="checkbox" name="q8[]" id="q8_D" value="D">
            <span class="diag-option-letter">D</span>
            <span class="diag-option-text">showed me a diagram of what was wrong.</span>
        </label>
    </div>

    {{-- Q9 --}}
    <div class="diag-question-block card p-4" data-question="9">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">9</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Younger VARK</span>
                <div class="diag-q-text">I want to learn to do something new on a computer. I would:</div>
            </div>
        </div>
        <label class="diag-option" for="q9_A">
            <input type="checkbox" name="q9[]" id="q9_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">read the written instructions that came with the program.</span>
        </label>
        <label class="diag-option" for="q9_B">
            <input type="checkbox" name="q9[]" id="q9_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">talk with people who know about the program.</span>
        </label>
        <label class="diag-option" for="q9_C">
            <input type="checkbox" name="q9[]" id="q9_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">start using it and learn by trial and error.</span>
        </label>
        <label class="diag-option" for="q9_D">
            <input type="checkbox" name="q9[]" id="q9_D" value="D">
            <span class="diag-option-letter">D</span>
            <span class="diag-option-text">follow the diagrams in a manual or online.</span>
        </label>
    </div>

    {{-- Q10 --}}
    <div class="diag-question-block card p-4" data-question="10">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">10</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Younger VARK</span>
                <div class="diag-q-text">When learning from the Internet I like:</div>
            </div>
        </div>
        <label class="diag-option" for="q10_A">
            <input type="checkbox" name="q10[]" id="q10_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">videos showing how to do or make things.</span>
        </label>
        <label class="diag-option" for="q10_B">
            <input type="checkbox" name="q10[]" id="q10_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">interesting design and visual features.</span>
        </label>
        <label class="diag-option" for="q10_C">
            <input type="checkbox" name="q10[]" id="q10_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">interesting written descriptions, lists and explanations.</span>
        </label>
        <label class="diag-option" for="q10_D">
            <input type="checkbox" name="q10[]" id="q10_D" value="D">
            <span class="diag-option-letter">D</span>
            <span class="diag-option-text">audio channels where I can listen to podcasts or interviews.</span>
        </label>
    </div>

    {{-- Q11 --}}
    <div class="diag-question-block card p-4" data-question="11">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">11</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Younger VARK</span>
                <div class="diag-q-text">After reading a play, I need to do a project. I would prefer to:</div>
            </div>
        </div>
        <label class="diag-option" for="q11_A">
            <input type="checkbox" name="q11[]" id="q11_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">draw or sketch a scene from the play.</span>
        </label>
        <label class="diag-option" for="q11_B">
            <input type="checkbox" name="q11[]" id="q11_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">write about the play.</span>
        </label>
        <label class="diag-option" for="q11_C">
            <input type="checkbox" name="q11[]" id="q11_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">read a speech from the play.</span>
        </label>
        <label class="diag-option" for="q11_D">
            <input type="checkbox" name="q11[]" id="q11_D" value="D">
            <span class="diag-option-letter">D</span>
            <span class="diag-option-text">act out a scene from the play.</span>
        </label>
    </div>

    {{-- Q12 --}}
    <div class="diag-question-block card p-4" data-question="12">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">12</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Younger VARK</span>
                <div class="diag-q-text">I want to learn how to take better photos. I would:</div>
            </div>
        </div>
        <label class="diag-option" for="q12_A">
            <input type="checkbox" name="q12[]" id="q12_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">ask questions and talk about how to achieve interesting effects.</span>
        </label>
        <label class="diag-option" for="q12_B">
            <input type="checkbox" name="q12[]" id="q12_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">use the written instructions about what to do.</span>
        </label>
        <label class="diag-option" for="q12_C">
            <input type="checkbox" name="q12[]" id="q12_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">use diagrams showing how different camera settings work.</span>
        </label>
        <label class="diag-option" for="q12_D">
            <input type="checkbox" name="q12[]" id="q12_D" value="D">
            <span class="diag-option-letter">D</span>
            <span class="diag-option-text">use examples of good and poor photos showing how to improve them.</span>
        </label>
    </div>

    {{-- Q13 --}}
    <div class="diag-question-block card p-4" data-question="13">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">13</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Younger VARK</span>
                <div class="diag-q-text">I prefer a presenter or a teacher who uses:</div>
            </div>
        </div>
        <label class="diag-option" for="q13_A">
            <input type="checkbox" name="q13[]" id="q13_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">demonstrations, models or practical sessions.</span>
        </label>
        <label class="diag-option" for="q13_B">
            <input type="checkbox" name="q13[]" id="q13_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">question and answer, talk, group discussion, or guest speakers.</span>
        </label>
        <label class="diag-option" for="q13_C">
            <input type="checkbox" name="q13[]" id="q13_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">handouts, books, or readings.</span>
        </label>
        <label class="diag-option" for="q13_D">
            <input type="checkbox" name="q13[]" id="q13_D" value="D">
            <span class="diag-option-letter">D</span>
            <span class="diag-option-text">diagrams, charts, maps or graphs.</span>
        </label>
    </div>

    {{-- Q14 --}}
    <div class="diag-question-block card p-4" data-question="14">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">14</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Younger VARK</span>
                <div class="diag-q-text">I have finished a competition or test and I would like some feedback. I would like to have feedback:</div>
            </div>
        </div>
        <label class="diag-option" for="q14_A">
            <input type="checkbox" name="q14[]" id="q14_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">using examples from what I have done.</span>
        </label>
        <label class="diag-option" for="q14_B">
            <input type="checkbox" name="q14[]" id="q14_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">using a written description of my results.</span>
        </label>
        <label class="diag-option" for="q14_C">
            <input type="checkbox" name="q14[]" id="q14_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">from somebody who talks it through with me.</span>
        </label>
        <label class="diag-option" for="q14_D">
            <input type="checkbox" name="q14[]" id="q14_D" value="D">
            <span class="diag-option-letter">D</span>
            <span class="diag-option-text">using graphs of my results.</span>
        </label>
    </div>

    {{-- Q15 --}}
    <div class="diag-question-block card p-4" data-question="15">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">15</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Younger VARK</span>
                <div class="diag-q-text">I want to find out about a house or an apartment. Before visiting it, I would want:</div>
            </div>
        </div>
        <label class="diag-option" for="q15_A">
            <input type="checkbox" name="q15[]" id="q15_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">to view a video of the property.</span>
        </label>
        <label class="diag-option" for="q15_B">
            <input type="checkbox" name="q15[]" id="q15_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">a discussion with the owner.</span>
        </label>
        <label class="diag-option" for="q15_C">
            <input type="checkbox" name="q15[]" id="q15_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">a printed description of the rooms and features.</span>
        </label>
        <label class="diag-option" for="q15_D">
            <input type="checkbox" name="q15[]" id="q15_D" value="D">
            <span class="diag-option-letter">D</span>
            <span class="diag-option-text">a plan showing the rooms and a map of the area.</span>
        </label>
    </div>

    {{-- Q16 --}}
    <div class="diag-question-block card p-4" data-question="16">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="diag-q-number">16</div>
            <div>
                <span class="diag-dimension-badge mb-2 d-inline-block">Younger VARK</span>
                <div class="diag-q-text">I want to assemble a wooden table that came in parts (kitset). I would learn best from:</div>
            </div>
        </div>
        <label class="diag-option" for="q16_A">
            <input type="checkbox" name="q16[]" id="q16_A" value="A">
            <span class="diag-option-letter">A</span>
            <span class="diag-option-text">diagrams showing each stage of the assembly.</span>
        </label>
        <label class="diag-option" for="q16_B">
            <input type="checkbox" name="q16[]" id="q16_B" value="B">
            <span class="diag-option-letter">B</span>
            <span class="diag-option-text">advice from someone who has done it before.</span>
        </label>
        <label class="diag-option" for="q16_C">
            <input type="checkbox" name="q16[]" id="q16_C" value="C">
            <span class="diag-option-letter">C</span>
            <span class="diag-option-text">written instructions that came with the parts for the table.</span>
        </label>
        <label class="diag-option" for="q16_D">
            <input type="checkbox" name="q16[]" id="q16_D" value="D">
            <span class="diag-option-letter">D</span>
            <span class="diag-option-text">watching a video of a person assembling a similar table.</span>
        </label>
    </div>

    {{-- Navigation --}}
    <div class="diag-nav">
        <button type="button" class="diag-btn-prev" id="btnPrev" style="visibility:hidden;">
            <i class="bi bi-arrow-left me-1"></i> Previous
        </button>
        <button type="button" class="diag-btn-next" id="btnNext">
            Next <i class="bi bi-arrow-right ms-1"></i>
        </button>
        <button type="submit" class="diag-btn-submit" id="btnSubmit">
            <i class="bi bi-check-circle me-1"></i> Get My Results
        </button>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mt-3">Please answer at least one question before submitting.</div>
    @endif
</form>

@push('scripts')
<script>
(function () {
    const TOTAL = 16;
    let current = 1;

    function getBlock(n) {
        return document.querySelector(`.diag-question-block[data-question="${n}"]`);
    }

    function isAnswered(n) {
        return true;
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

        btnNext.disabled = false;

        if (current === TOTAL) {
            btnNext.style.display   = 'none';
            btnSubmit.style.display = 'flex';
        } else {
            btnNext.style.display   = 'flex';
            btnSubmit.style.display = 'none';
        }
    }

    document.addEventListener('change', function (event) {
        const target = event.target;
        if (target && target.type === 'checkbox' && target.name.startsWith('q')) {
            const diagOption = target.closest('.diag-option');
            if (diagOption) {
                if (target.checked) {
                    diagOption.classList.add('selected');
                } else {
                    diagOption.classList.remove('selected');
                }
            }
        }
    });

    document.addEventListener('click', function (event) {
        const nextBtn = event.target.closest('#btnNext');
        const prevBtn = event.target.closest('#btnPrev');

        if (nextBtn) {
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

    function init() {
        document.querySelectorAll('input[type="checkbox"]').forEach(inp => {
            const diagOption = inp.closest('.diag-option');
            if (inp.checked && diagOption) {
                diagOption.classList.add('selected');
            }
        });
        updateUI();
    }

    if (document.readyState === 'complete') {
        init();
    } else {
        window.addEventListener('load', init);
    }
    
    setTimeout(init, 100);
})();
</script>
@endpush

@endsection
