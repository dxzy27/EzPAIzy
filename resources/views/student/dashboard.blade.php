@extends('layouts.dashboard')

@section('content')
<style>
    .content-overlay {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background-color: rgba(255, 255, 255, 0.95);
        display: flex; flex-direction: row;
        align-items: center; justify-content: center;
        opacity: 0; transition: opacity 0.3s ease; z-index: 10;
    }
    .content-card:hover .content-overlay { opacity: 1; }
    .content-card:hover {
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        transform: translateY(-2px); transition: all 0.3s ease;
    }

    /* ── Diagnosis Banner ── */
    .diag-banner {
        background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
        border-radius: 18px; padding: 22px 26px; color: #fff;
        display: flex; align-items: center; gap: 20px;
        margin-bottom: 26px; position: relative; overflow: hidden;
        animation: bannerSlide .45s ease;
    }
    .diag-banner::before {
        content: ''; position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='300' height='120' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='260' cy='20' r='80' fill='rgba(255,255,255,.06)'/%3E%3Ccircle cx='20' cy='110' r='50' fill='rgba(255,255,255,.04)'/%3E%3C/svg%3E") no-repeat right top;
        pointer-events: none;
    }
    @keyframes bannerSlide {
        from { opacity: 0; transform: translateY(-12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .diag-banner-icon {
        width: 54px; height: 54px; border-radius: 14px;
        background: rgba(255,255,255,.18);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.6rem; flex-shrink: 0;
    }
    .diag-banner-actions { display: flex; gap: 10px; align-items: center; flex-shrink: 0; }
    .diag-banner-dismiss {
        background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25);
        color: #fff; font-size: .8rem; font-weight: 600;
        padding: 7px 14px; border-radius: 8px; cursor: pointer;
        transition: background .18s; text-decoration: none;
    }
    .diag-banner-dismiss:hover { background: rgba(255,255,255,.25); color: #fff; }
    .diag-banner-cta {
        background: #fff; color: #6d28d9;
        font-size: .88rem; font-weight: 700;
        padding: 9px 20px; border-radius: 9px;
        text-decoration: none; transition: opacity .18s, transform .15s;
        white-space: nowrap;
    }
    .diag-banner-cta:hover { opacity: .9; color: #5b21b6; transform: translateY(-1px); }

    /* ── Style badge ── */
    .style-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 12px; border-radius: 99px;
        font-size: .76rem; font-weight: 700; letter-spacing: .4px;
        text-transform: uppercase; text-decoration: none;
    }
    .style-badge.visual      { background: #ede9fe; color: #6d28d9; }
    .style-badge.auditory    { background: #dbeafe; color: #1d4ed8; }
    .style-badge.competitive { background: #fef3c7; color: #b45309; }

    /* ── Recommended top-stripe ── */
    .primary-card-stripe {
        height: 4px; border-radius: 4px 4px 0 0;
        margin: -1px -1px 0 -1px;
    }
</style>

@php
    $user  = auth()->user();
    $style = $user->learning_style; // null | visual | auditory | competitive

    // ── Per-style configuration ──────────────────────────────────
    $styleConfig = [
        'visual' => [
            'accent'      => '#7c3aed',
            'accentLight' => '#ede9fe',
            'accentText'  => '#4c1d95',
            'icon'        => 'bi-eye-fill',
            'label'       => 'Visual Learner',
            'tipIcon'     => '🎨',
            'tipTitle'    => 'Visual Study Tip',
            'tip'         => 'Before your next session, spend 5 minutes drawing a mini mind map of what you studied last time. Reconnecting visually with previous material dramatically improves recall.',
            'recTitle'    => '✨ Recommended: Your Flashcard Sets',
        ],
        'auditory' => [
            'accent'      => '#0891b2',
            'accentLight' => '#e0f2fe',
            'accentText'  => '#0c4a6e',
            'icon'        => 'bi-ear-fill',
            'label'       => 'Auditory Learner',
            'tipIcon'     => '🎵',
            'tipTitle'    => 'Auditory Study Tip',
            'tip'         => 'After reading any material today, close it and say aloud — in your own words — what you just learned. If you can explain it, you have truly encoded it.',
            'recTitle'    => '✨ Recommended: Reading Materials',
        ],
        'competitive' => [
            'accent'      => '#d97706',
            'accentLight' => '#fef3c7',
            'accentText'  => '#78350f',
            'icon'        => 'bi-trophy-fill',
            'label'       => 'Competitive Learner',
            'tipIcon'     => '🏆',
            'tipTitle'    => 'Competitive Study Tip',
            'tip'         => 'Challenge: Beat your last quiz score today. Check your recent results below and pick a quiz where you scored under 90% — retake it and push for a new personal best.',
            'recTitle'    => '✨ Recommended: Challenge Yourself',
        ],
    ];

    $cfg = $style ? $styleConfig[$style] : null;

    // Counts
    $flashcardCount = \App\Models\FlashcardSet::where('is_flagged', false)->count();
    $contentCount   = \App\Models\Content::where('is_flagged', false)->count();
    $quizCount      = $quizzes->count();
    $completedCount = $user->progress()->count();
    $bestScore      = $style === 'competitive' ? $progress->max('score') : null;

    // Recent material lists
    $recentContents   = \App\Models\Content::where('is_flagged', false)->latest()->take(5)->get();
    $recentFlashcards = \App\Models\FlashcardSet::where('is_flagged', false)->latest()->take(5)->get();
    $recentQuizzes    = $quizzes->sortByDesc('created_at')->take(5);

    // Style badge data
    $styleIcons  = ['visual'=>'bi-eye-fill','auditory'=>'bi-ear-fill','competitive'=>'bi-trophy-fill'];
    $styleLabels = ['visual'=>'Visual Learner','auditory'=>'Auditory Learner','competitive'=>'Competitive Learner'];
@endphp

<div class="container">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius: 12px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- ── Diagnosis Banner (undiagnosed only) ── --}}
    @if(!$style && !session('diag_banner_dismissed'))
    <div class="diag-banner">
        <div class="diag-banner-icon">🧠</div>
        <div class="flex-grow-1">
            <div style="font-weight:800;font-size:1.05rem;margin-bottom:4px;">Discover Your Learning Style</div>
            <div style="font-size:.86rem;opacity:.88;line-height:1.45;">
                Take a 10-question diagnosis to personalise your dashboard and get study recommendations tailored specifically to how <em>you</em> learn best. It only takes ~4 minutes.
            </div>
        </div>
        <div class="diag-banner-actions">
            <a href="{{ route('student.diagnosis.create') }}" class="diag-banner-cta">
                <i class="bi bi-clipboard-pulse me-1"></i> Start Diagnosis
            </a>
            <a href="{{ route('student.dashboard') }}?dismiss_diag=1" class="diag-banner-dismiss">Maybe later</a>
        </div>
    </div>
    @endif

    {{-- ── Header ── --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h4 class="fw-bold mb-0">Student Dashboard</h4>
            <div class="d-flex align-items-center gap-2 mt-1">
                <span class="text-muted" style="font-size:.875rem;">Welcome, {{ $user->name }}</span>
                @if($style)
                <a href="{{ route('student.diagnosis.show') }}"
                   class="style-badge {{ $style }}"
                   title="View your learning profile">
                    <i class="bi {{ $styleIcons[$style] }}"></i>
                    {{ $styleLabels[$style] }}
                </a>
                @endif
            </div>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('student.progress') }}" class="btn btn-warning">
                <i class="bi bi-bar-chart-fill me-2"></i>My Progress
            </a>
        </div>
    </div>

    {{-- ── Stats Cards (order + accent changes per style) ── --}}
    {{-- Define the 3 card slots --}}
    @php
        $cardFlashcard = [
            'title'     => '🗂️ Flashcards',
            'count'     => $flashcardCount,
            'sub'       => 'Card Sets Available',
            'color'     => '#7c3aed',
            'isPrimary' => $style === 'visual',
            'type'      => 'flashcard',
        ];
        $cardMaterials = [
            'title'     => '📚 Materials',
            'count'     => $contentCount,
            'sub'       => 'Reading Materials',
            'color'     => '#0891b2',
            'isPrimary' => $style === 'auditory',
            'type'      => 'materials',
        ];
        $cardQuizzes = [
            'title'     => $style === 'competitive' ? '🏆 Quizzes' : '📝 Quizzes',
            'count'     => ($style === 'competitive' && $bestScore !== null) ? $bestScore.'%' : $quizCount,
            'sub'       => ($style === 'competitive' && $bestScore !== null) ? 'Your Best Score' : 'Available Quizzes',
            'color'     => $style === 'competitive' ? '#d97706' : '#0ea5e9',
            'isPrimary' => $style === 'competitive',
            'type'      => 'quiz',
        ];
        $cardCompleted = [
            'title'     => '✅ Completed',
            'count'     => $completedCount,
            'sub'       => 'Quizzes Completed',
            'color'     => '#0891b2',
            'isPrimary' => false,
            'type'      => 'completed',
        ];

        // Card order based on style
        if ($style === 'visual') {
            $orderedCards = [$cardFlashcard, $cardMaterials, $cardCompleted];
        } elseif ($style === 'auditory') {
            $orderedCards = [$cardMaterials, $cardFlashcard, $cardCompleted];
        } elseif ($style === 'competitive') {
            $orderedCards = [$cardQuizzes, $cardFlashcard, $cardCompleted];
        } else {
            $orderedCards = [$cardQuizzes, $cardMaterials, $cardCompleted];
        }
    @endphp

    <div class="row mb-4 align-items-stretch">
        @foreach($orderedCards as $card)
        <div class="col-md-4 mb-3">
            <div class="card content-card position-relative overflow-hidden h-100"
                 style="{{ $card['isPrimary'] && $cfg ? 'border: 2px solid '.$cfg['accent'].';' : '' }}">
                {{-- Coloured top stripe for the primary card --}}
                @if($card['isPrimary'] && $cfg)
                <div class="primary-card-stripe" style="background:{{ $cfg['accent'] }};"></div>
                @endif
                <div class="card-body d-flex flex-column text-center pt-3">
                    @if($card['isPrimary'] && $cfg)
                    <span class="badge mb-2 align-self-center"
                          style="background:{{ $cfg['accentLight'] }};color:{{ $cfg['accentText'] }};font-size:.7rem;">
                        ⭐ Recommended for you
                    </span>
                    @endif
                    <h5 class="card-title">{{ $card['title'] }}</h5>
                    <h2 style="color:{{ $card['color'] }}">{{ $card['count'] }}</h2>
                    <p class="text-muted">{{ $card['sub'] }}</p>
                </div>
                {{-- Hover overlay --}}
                <div class="content-overlay">
                    <div class="d-flex w-100 justify-content-center align-items-center p-2 gap-2">
                        @if($card['type'] === 'flashcard')
                            <a href="{{ route('student.flashcards.index') }}"
                               class="btn btn-outline-success d-flex flex-column align-items-center justify-content-center p-2"
                               style="width:80%;height:80px;">
                                <i class="bi bi-collection-play fs-4 mb-1"></i>
                                <span class="small">Browse Flashcards</span>
                            </a>
                        @elseif($card['type'] === 'materials')
                            <a href="{{ route('student.flashcards.index') }}"
                               class="btn btn-outline-success d-flex flex-column align-items-center justify-content-center p-2"
                               style="width:48%;height:80px;">
                                <i class="bi bi-collection-play fs-4 mb-1"></i>
                                <span class="small">Flashcards</span>
                            </a>
                            <a href="{{ route('student.contents.index') }}"
                               class="btn btn-outline-primary d-flex flex-column align-items-center justify-content-center p-2"
                               style="width:48%;height:80px;">
                                <i class="bi bi-card-text fs-4 mb-1"></i>
                                <span class="small">Other Materials</span>
                            </a>
                        @elseif($card['type'] === 'quiz')
                            <a href="{{ route('student.quizzes') }}"
                               class="btn btn-outline-primary d-flex flex-column align-items-center justify-content-center p-2"
                               style="width:80%;height:80px;">
                                <i class="bi bi-play-circle fs-4 mb-1"></i>
                                <span class="small">Browse Quizzes</span>
                            </a>
                        @else
                            <a href="{{ route('student.progress') }}"
                               class="btn btn-outline-info d-flex flex-column align-items-center justify-content-center p-2"
                               style="width:80%;height:80px;">
                                <i class="bi bi-bar-chart fs-4 mb-1"></i>
                                <span class="small">View Progress</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Study Tip Card (diagnosed students only) ── --}}
    @if($style && $cfg)
    <div class="card mb-4 p-4"
         style="border-left: 4px solid {{ $cfg['accent'] }}; background: {{ $cfg['accentLight'] }}; border-radius: 14px; border: 1px solid {{ $cfg['accent'] }};">
        <div class="d-flex align-items-start gap-3">
            <div style="font-size:1.8rem;flex-shrink:0;">{{ $cfg['tipIcon'] }}</div>
            <div>
                <div class="fw-bold mb-1"
                     style="color:{{ $cfg['accentText'] }};font-size:.85rem;text-transform:uppercase;letter-spacing:.5px;">
                    {{ $cfg['tipTitle'] }}
                </div>
                <div style="font-size:.92rem;color:{{ $cfg['accentText'] }};line-height:1.55;">
                    {{ $cfg['tip'] }}
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Bottom Row: Recent Results + Recommended Materials ── --}}
    <div class="row">

        {{-- Recent Quiz Results --}}
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Results</h5>
                    @if($style === 'competitive' && $bestScore !== null)
                    <span class="badge"
                          style="background:{{ $cfg['accentLight'] }};color:{{ $cfg['accentText'] }};">
                        🏆 Best: {{ $bestScore }}%
                    </span>
                    @endif
                </div>
                <div class="card-body">
                    @if($progress->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Quiz</th>
                                    <th>By</th>
                                    <th>Score</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($progress->sortByDesc('created_at')->take(5) as $p)
                                <tr>
                                    <td>{{ Str::limit($p->quiz->title, 22) }}</td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="bi bi-person-circle me-1"></i>
                                            {{ $p->quiz->teacher->name ?? 'Unknown' }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $p->score >= 80 ? 'success' : ($p->score >= 50 ? 'warning' : 'danger') }}">
                                            {{ $p->score }}%
                                        </span>
                                    </td>
                                    <td>{{ $p->created_at->format('M d') }}</td>
                                    <td>
                                        <a href="{{ route('student.progress') }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center py-4">
                        No quizzes completed yet.
                        <a href="{{ route('student.quizzes') }}">Take a quiz!</a>
                    </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Adaptive Recommended Panel --}}
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header"
                     style="{{ $style && $cfg ? 'border-left: 3px solid '.$cfg['accent'].';' : '' }}">
                    <h5 class="mb-0">
                        {{ $style && $cfg ? $cfg['recTitle'] : 'New Learning Materials' }}
                    </h5>
                </div>
                <div class="card-body">

                    @if($style === 'competitive')
                        {{-- Competitive: show quizzes to retake --}}
                        @if($recentQuizzes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead><tr><th>Quiz</th><th>Questions</th><th></th></tr></thead>
                                <tbody>
                                    @foreach($recentQuizzes as $q)
                                    <tr>
                                        <td>{{ Str::limit($q->title, 26) }}</td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $q->questions->count() }} Qs
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('student.quiz.take', $q) }}"
                                               class="btn btn-sm btn-outline-warning">
                                                <i class="bi bi-play-fill"></i> Challenge
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="text-muted text-center py-4">No quizzes available yet.</p>
                        @endif

                    @elseif($style === 'visual')
                        {{-- Visual: flashcards first --}}
                        @php $visualList = $recentFlashcards->concat($recentContents)->sortByDesc('created_at')->take(5); @endphp
                        @if($visualList->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead><tr><th>Title</th><th>Type</th><th></th></tr></thead>
                                <tbody>
                                    @foreach($visualList as $item)
                                    <tr>
                                        <td>{{ Str::limit($item->title, 24) }}</td>
                                        <td>
                                            @if(class_basename($item) === 'FlashcardSet')
                                            <span class="badge" style="background:#ede9fe;color:#6d28d9;">Flashcard ⭐</span>
                                            @else
                                            <span class="badge bg-primary">Content</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(class_basename($item) === 'FlashcardSet')
                                            <a href="{{ route('student.flashcards.show', $item) }}" class="btn btn-sm btn-outline-success">Practice</a>
                                            @else
                                            <a href="{{ route('student.contents.show', $item) }}" class="btn btn-sm btn-outline-primary">View</a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="text-muted text-center py-4">No materials available.</p>
                        @endif

                    @elseif($style === 'auditory')
                        {{-- Auditory: reading materials first, with Listen buttons --}}
                        @php $auditoryList = $recentContents->concat($recentFlashcards)->sortByDesc('created_at')->take(5); @endphp
                        @if($auditoryList->count() > 0)
                        <div class="d-flex flex-column gap-2">
                            @foreach($auditoryList as $item)
                            @php
                                $isFlash = class_basename($item) === 'FlashcardSet';
                                $itemUrl = $isFlash
                                    ? route('student.flashcards.show', $item)
                                    : route('student.contents.show', $item);
                                $preview = Str::limit($item->title, 60);
                                $topicLabel = $item->topic ?? 'General';
                            @endphp
                            <div class="d-flex align-items-center gap-3 p-3 rounded-3"
                                 style="background:#f0f9ff;border:1px solid #bae6fd;transition:background .15s;"
                                 onmouseover="this.style.background='#e0f2fe'" onmouseout="this.style.background='#f0f9ff'">
                                {{-- Type badge --}}
                                <div style="flex-shrink:0;">
                                    @if($isFlash)
                                    <span class="badge" style="background:#dcfce7;color:#166534;font-size:.72rem;">🃏 Flashcard</span>
                                    @else
                                    <span class="badge" style="background:#e0f2fe;color:#0c4a6e;font-size:.72rem;">📄 Material ⭐</span>
                                    @endif
                                </div>
                                {{-- Title --}}
                                <div style="flex:1;min-width:0;">
                                    <div class="fw-semibold text-truncate" style="font-size:.88rem;color:#0f172a;">{{ $item->title }}</div>
                                    <div style="font-size:.75rem;color:#64748b;">{{ $topicLabel }}</div>
                                </div>
                                {{-- Listen button --}}
                                <button onclick="ttsPreview('{{ addslashes($item->title) }}', '{{ addslashes($topicLabel) }}')"
                                        title="Listen to preview"
                                        style="background:#0891b2;border:none;color:#fff;border-radius:9px;
                                               padding:5px 11px;font-size:.78rem;cursor:pointer;flex-shrink:0;
                                               transition:background .15s;"
                                        onmouseover="this.style.background='#0e7490'" onmouseout="this.style.background='#0891b2'">
                                    🔊
                                </button>
                                {{-- Open button --}}
                                <a href="{{ $itemUrl }}"
                                   class="btn btn-sm"
                                   style="background:#f0f9ff;border:1px solid #7dd3fc;color:#0c4a6e;font-size:.78rem;flex-shrink:0;">
                                    {{ $isFlash ? 'Practice' : 'Read' }}
                                </a>
                            </div>
                            @endforeach
                        </div>
                        <script>
                        window.ttsPreview = function(title, topic) {
                            const synth = window.speechSynthesis;
                            synth.cancel();
                            const u = new SpeechSynthesisUtterance(title + '. Topic: ' + topic);
                            u.lang = 'en-US'; u.rate = 0.95;
                            synth.speak(u);
                        };
                        </script>
                        @else
                        <p class="text-muted text-center py-4">No materials available.</p>
                        @endif

                    @else
                        {{-- Default / undiagnosed --}}
                        @php $defaultList = $recentContents->concat($recentFlashcards)->sortByDesc('created_at')->take(5); @endphp
                        @if($defaultList->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead><tr><th>Title</th><th>Type</th><th></th></tr></thead>
                                <tbody>
                                    @foreach($defaultList as $item)
                                    <tr>
                                        <td>{{ Str::limit($item->title, 24) }}</td>
                                        <td>
                                            @if(class_basename($item) === 'FlashcardSet')
                                            <span class="badge bg-success">Flashcard</span>
                                            @else
                                            <span class="badge bg-primary">Content</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(class_basename($item) === 'FlashcardSet')
                                            <a href="{{ route('student.flashcards.show', $item) }}" class="btn btn-sm btn-outline-success">Practice</a>
                                            @else
                                            <a href="{{ route('student.contents.show', $item) }}" class="btn btn-sm btn-outline-primary">View</a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="text-muted text-center py-4">No materials available.</p>
                        @endif
                    @endif

                </div>
            </div>
        </div>

    </div>{{-- /row --}}
</div>{{-- /container --}}
@endsection
