@extends('layouts.dashboard')

@section('content')
@push('styles')
<style>
    :root {
        --diag-accent: #7c3aed;
        --diag-accent-light: #ede9fe;
    }

    /* ── Hero card ── */
    .result-hero {
        border-radius: 20px;
        padding: 36px 32px;
        color: #fff;
        position: relative;
        overflow: hidden;
        margin-bottom: 24px;
    }
    .result-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='400' height='200' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='350' cy='50' r='120' fill='rgba(255,255,255,.06)'/%3E%3Ccircle cx='60' cy='180' r='80' fill='rgba(255,255,255,.04)'/%3E%3C/svg%3E") no-repeat right top;
        pointer-events: none;
    }

    .result-type-badge {
        display: inline-flex; align-items: center; gap: 8px;
        background: rgba(255,255,255,.18);
        border: 1px solid rgba(255,255,255,.25);
        border-radius: 99px;
        padding: 5px 16px;
        font-size: .78rem; font-weight: 700; letter-spacing: .6px;
        text-transform: uppercase; margin-bottom: 14px;
    }
    .result-persona-title {
        font-size: 2rem; font-weight: 800;
        letter-spacing: -.5px; line-height: 1.15;
        margin-bottom: 8px;
    }
    .result-persona-sub {
        font-size: .9rem; opacity: .8; margin-bottom: 20px;
    }
    .result-confidence-pill {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,.15);
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 99px; padding: 6px 14px;
        font-size: .82rem; font-weight: 600;
    }

    /* ── Score bars ── */
    .score-bar-wrap {
        display: flex; align-items: center; gap: 12px; margin-bottom: 14px;
    }
    .score-bar-label { font-size: .82rem; font-weight: 600; width: 100px; flex-shrink: 0; color: var(--text-main, #111827); }
    .score-bar-track {
        flex: 1; height: 10px; background: #f3f4f6;
        border-radius: 99px; overflow: hidden;
    }
    .score-bar-fill { height: 100%; border-radius: 99px; transition: width 1s ease; }
    .score-bar-value { font-size: .8rem; font-weight: 700; width: 32px; text-align: right; color: var(--text-muted, #6b7280); }

    /* ── Rec cards ── */
    .rec-card {
        display: flex; align-items: flex-start; gap: 14px;
        padding: 14px 16px;
        border-radius: 12px;
        border: 1px solid var(--border, #e5e7eb);
        background: var(--card-bg, #fff);
        margin-bottom: 12px;
        transition: border-color .18s, box-shadow .18s;
    }
    .rec-card:hover {
        border-color: var(--diag-accent);
        box-shadow: 0 4px 16px -4px rgba(124,58,237,.1);
    }
    .rec-icon {
        width: 36px; height: 36px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }

    /* ── Radar chart ── */
    .radar-wrap { max-width: 280px; margin: 0 auto; }

    /* ── Gradient classes ── */
    .grad-visual      { background: linear-gradient(135deg, #6d28d9, #7c3aed); }
    .grad-auditory    { background: linear-gradient(135deg, #0891b2, #0ea5e9); }
    .grad-competitive { background: linear-gradient(135deg, #b45309, #d97706); }
</style>
@endpush

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Your Learning Profile</h4>
        <p class="text-muted mb-0" style="font-size:.875rem;">
            Diagnosed on {{ $profile->updated_at->format('d M Y') }}
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <form action="{{ route('student.diagnosis.reset') }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to reset your learning style and return to the Basic UI?');">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash me-1"></i> Reset to Basic UI
            </button>
        </form>
        <a href="{{ route('student.diagnosis.create') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-repeat me-1"></i> Retake Diagnosis
        </a>
    </div>
</div>

@php
    $style = $profile->learning_style;
    $gradClass = 'grad-' . $style;
    $icons = ['visual' => 'bi-eye-fill', 'auditory' => 'bi-ear-fill', 'competitive' => 'bi-trophy-fill'];
    $icon = $icons[$style] ?? 'bi-person-fill';
    $typeLabels = ['visual' => 'Visual Learner', 'auditory' => 'Auditory Learner', 'competitive' => 'Competitive Learner'];
    $descriptions = [
        'visual'      => 'You process and retain information most effectively through visual stimuli — colour, layout, diagrams, and spatial organisation are your strongest memory anchors.',
        'auditory'    => 'You learn best through sound and verbal processing — listening, speaking, reciting, and discussing are your strongest pathways to retaining information.',
        'competitive' => 'You are driven by challenge and performance — pressure, scoring, and the drive to beat your own record are the most powerful motivators for your learning.',
    ];
    $totalScore = max(1, $profile->score_visual + $profile->score_auditory + $profile->score_competitive);
    $recIcons = ['bi-eye', 'bi-ear', 'bi-trophy', 'bi-journals', 'bi-lightbulb', 'bi-bar-chart-line'];
    $recColors = ['#ede9fe', '#dbeafe', '#fef3c7', '#d1fae5', '#fce7f3', '#f3f4f6'];
    $recTextColors = ['#6d28d9', '#1d4ed8', '#b45309', '#065f46', '#be185d', '#374151'];
@endphp

{{-- Hero --}}
<div class="result-hero {{ $gradClass }}">
    <div class="result-type-badge">
        <i class="bi {{ $icon }}"></i> {{ strtoupper($style) }} LEARNER
    </div>
    <div class="result-persona-title">{{ $profile->persona }}</div>
    <div class="result-persona-sub">{{ $descriptions[$style] }}</div>
    <span class="result-confidence-pill">
        <i class="bi bi-graph-up-arrow"></i>
        Confidence Score: {{ number_format($profile->confidence, 1) }}%
        @if($profile->confidence >= 65)
            — Strong Match
        @elseif($profile->confidence >= 45)
            — Moderate Match
        @else
            — Emerging Tendencies
        @endif
    </span>
</div>

<div class="row g-4 mb-4">
    {{-- Score Breakdown --}}
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Evidence Score Breakdown</h6>
            <p class="text-muted small mb-4">Raw weighted evidence accumulated from your 10 answers across all learning dimensions.</p>

            <div class="score-bar-wrap">
                <span class="score-bar-label"><i class="bi bi-eye me-1" style="color:#7c3aed;"></i>Visual</span>
                <div class="score-bar-track">
                    <div class="score-bar-fill" style="width:{{ round(($profile->score_visual/$totalScore)*100) }}%;background:#7c3aed;"></div>
                </div>
                <span class="score-bar-value">{{ $profile->score_visual }}</span>
            </div>
            <div class="score-bar-wrap">
                <span class="score-bar-label"><i class="bi bi-ear me-1" style="color:#0891b2;"></i>Auditory</span>
                <div class="score-bar-track">
                    <div class="score-bar-fill" style="width:{{ round(($profile->score_auditory/$totalScore)*100) }}%;background:#0891b2;"></div>
                </div>
                <span class="score-bar-value">{{ $profile->score_auditory }}</span>
            </div>
            <div class="score-bar-wrap">
                <span class="score-bar-label"><i class="bi bi-trophy me-1" style="color:#d97706;"></i>Competitive</span>
                <div class="score-bar-track">
                    <div class="score-bar-fill" style="width:{{ round(($profile->score_competitive/$totalScore)*100) }}%;background:#d97706;"></div>
                </div>
                <span class="score-bar-value">{{ $profile->score_competitive }}</span>
            </div>
        </div>
    </div>

    {{-- Radar chart --}}
    <div class="col-md-6">
        <div class="card p-4 h-100 d-flex flex-column align-items-center justify-content-center">
            <h6 class="fw-bold mb-3 align-self-start"><i class="bi bi-broadcast me-2 text-success"></i>Learning Style Radar</h6>
            <div class="radar-wrap w-100">
                <canvas id="radarChart" style="max-height:240px;"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Recommendations --}}
<div class="card p-4 mb-4">
    <h6 class="fw-bold mb-1"><i class="bi bi-list-check me-2 text-success"></i>Personalised Recommendations</h6>
    <p class="text-muted small mb-4">Based on your specific answer patterns — not just your overall type.</p>

    @foreach($profile->recommendations as $i => $rec)
        @php
            $iconIdx = $i % count($recIcons);
        @endphp
        <div class="rec-card">
            <div class="rec-icon" style="background:{{ $recColors[$iconIdx] }};color:{{ $recTextColors[$iconIdx] }};">
                <i class="bi {{ $recIcons[$iconIdx] }}"></i>
            </div>
            <div style="font-size:.9rem;color:var(--text-main);line-height:1.5;">{{ $rec }}</div>
        </div>
    @endforeach
</div>

{{-- CTA --}}
<div class="card p-4 text-center" style="background:var(--diag-accent-light);border-color:#c4b5fd;">
    <div class="fw-bold mb-1" style="color:#4c1d95;">Ready to study your way?</div>
    <p class="text-muted small mb-3">Your dashboard is now personalised for your learning style.</p>
    <a href="{{ route('student.dashboard') }}" class="btn btn-primary btn-sm px-4">
        <i class="bi bi-house-door me-1"></i> Go to Dashboard
    </a>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('radarChart').getContext('2d');
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: ['Visual', 'Auditory', 'Competitive'],
            datasets: [{
                label: 'Your Profile',
                data: [
                    {{ $profile->score_visual }},
                    {{ $profile->score_auditory }},
                    {{ $profile->score_competitive }}
                ],
                fill: true,
                backgroundColor: 'rgba(124, 58, 237, 0.15)',
                borderColor: '#7c3aed',
                pointBackgroundColor: '#7c3aed',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#7c3aed',
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            scales: {
                r: {
                    beginAtZero: true,
                    ticks: { display: false },
                    grid: { color: 'rgba(0,0,0,.06)' },
                    pointLabels: {
                        font: { size: 12, weight: '600' },
                        color: '#374151',
                    }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
@endpush

@endsection
