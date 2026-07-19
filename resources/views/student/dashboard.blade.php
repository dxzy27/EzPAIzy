@extends('layouts.dashboard')

@section('content')
<style>
    /* ── Blended Card Styling (Quizlet Style) ── */
    .card {
        background: rgba(255, 255, 255, 0.58) !important;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.5) !important;
        box-shadow: 0 8px 32px 0 rgba(31, 110, 104, 0.03) !important;
        border-radius: 16px !important;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .card:hover {
        box-shadow: 0 15px 35px rgba(31, 110, 104, 0.08) !important;
    }
    .content-card {
        background: rgba(255, 255, 255, 0.58) !important;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.5) !important;
        box-shadow: 0 8px 32px 0 rgba(31, 110, 104, 0.03) !important;
        border-radius: 16px !important;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .content-card:hover {
        box-shadow: 0 15px 35px rgba(31, 110, 104, 0.08) !important;
        transform: translateY(-4px);
    }
    .hover-recent-item {
        border-radius: 12px !important;
        transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1), background 0.22s ease !important;
    }
    .hover-recent-item:hover {
        transform: translateY(-2px) !important;
        background: rgba(255, 255, 255, 0.35) !important;
    }

    /* ── Redesigned Filled Buttons ── */
    .quizlet-btn {
        background-color: #4255ff !important;
        border: none !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        font-size: 0.88rem !important;
        border-radius: 99px !important;
        padding: 10px 24px !important;
        box-shadow: 0 4px 14px rgba(66, 85, 255, 0.25) !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        text-decoration: none !important;
        cursor: pointer;
    }
    .quizlet-btn:hover {
        background-color: #2b3eff !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 20px rgba(66, 85, 255, 0.35) !important;
        color: #ffffff !important;
    }
    .quizlet-btn-secondary {
        background-color: rgba(0, 0, 0, 0.05) !important;
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
        color: #475569 !important;
        font-weight: 600 !important;
        font-size: 0.88rem !important;
        border-radius: 99px !important;
        padding: 9px 24px !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        text-decoration: none !important;
        cursor: pointer;
    }
    .quizlet-btn-secondary:hover {
        background-color: rgba(0, 0, 0, 0.12) !important;
        color: #1e293b !important;
        transform: translateY(-2px) !important;
    }

    /* ── Teal Table Headers ── */
    .table-teal-header th {
        background-color: #1F6E68 !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        font-size: 0.76rem !important;
        letter-spacing: 0.5px;
        border-bottom: none !important;
        padding: 14px 16px !important;
    }
    .table-teal-header th:first-child {
        border-top-left-radius: 12px;
    }
    .table-teal-header th:last-child {
        border-top-right-radius: 12px;
    }

    /* ── Carousel Responsive Controls ── */
    .custom-carousel-control {
        width: 40px !important;
        height: 40px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        background: rgba(31, 110, 104, 0.18) !important;
        border-radius: 50% !important;
        border: none !important;
        opacity: 1 !important;
        transition: all 0.25s ease !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .custom-carousel-control:hover {
        background: rgba(31, 110, 104, 0.35) !important;
        transform: translateY(-50%) scale(1.08) !important;
    }
    .carousel-control-prev.custom-carousel-control {
        left: -60px !important;
    }
    .carousel-control-next.custom-carousel-control {
        right: -60px !important;
    }
    @media (max-width: 991px) {
        .carousel-control-prev.custom-carousel-control {
            left: -40px !important;
        }
        .carousel-control-next.custom-carousel-control {
            right: -40px !important;
        }
    }
    @media (max-width: 767px) {
        .carousel-control-prev.custom-carousel-control {
            left: 10px !important;
            background: rgba(31, 110, 104, 0.35) !important;
        }
        .carousel-control-next.custom-carousel-control {
            right: 10px !important;
            background: rgba(31, 110, 104, 0.35) !important;
        }
    }

    /* ── Teal Card Headers ── */
    .card-header-teal {
        background-color: rgba(31, 110, 104, 0.88) !important;
        color: #ffffff !important;
        border-bottom: none !important;
        padding: 16px 24px !important;
        border-top-left-radius: 15px !important;
        border-top-right-radius: 15px !important;
    }
    .card-header-teal h5 {
        color: #ffffff !important;
        font-weight: 600 !important;
        font-size: 1.02rem !important;
        margin: 0;
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
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
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
    .style-badge.read_write  { background: #faf6f6; color: #453938; border: 1px solid #7d6867; }
    .style-badge.auditory    { background: #fff7ed; color: #7c2d12; border: 1px solid #e5b181; }
    .style-badge.visual      { background: #ecfeff; color: #083344; border: 1px solid #06b6d4; }
    .style-badge.kinesthetic { background: #fdf4ff; color: #701a75; border: 1px solid #d946ef; }

    /* ── Recommended top-stripe ── */
    .primary-card-stripe {
        height: 4px; border-radius: 4px 4px 0 0;
        margin: -1px -1px 0 -1px;
    }
</style>

@php
    $user  = auth()->user();
    $style = $user->learning_style; // null | read_write | auditory | visual | kinesthetic | competitive

    // ── Per-style configuration ──────────────────────────────────
    $styleConfig = [
        'read_write' => [
            'accent'      => '#7d6867',
            'accentLight' => '#f6f4f4',
            'accentText'  => '#453938',
            'icon'        => 'bi-journal-text',
            'label'       => 'Read/Write Learner',
            'tipIcon'     => '✍️',
            'tipTitle'    => 'Read/Write Study Tip',
            'tip'         => 'Use the Notepad next to your materials and quizzes to jot down summaries and acronyms. You can access all your saved notes from the "My Folders" sidebar section.',
            'recTitle'    => '✨ Recommended:',
        ],
        'auditory' => [
            'accent'      => '#e5b181',
            'accentLight' => '#fff7ed',
            'accentText'  => '#7c2d12',
            'icon'        => 'bi-ear-fill',
            'label'       => 'Auditory Learner',
            'tipIcon'     => '🎵',
            'tipTitle'    => 'Auditory Study Tip',
            'tip'         => 'After reading any material today, close it and say aloud — in your own words — what you just learned. If you can explain it, you have truly encoded it.',
            'recTitle'    => '✨ Recommended:',
        ],
        'visual' => [
            'accent'      => '#06b6d4',
            'accentLight' => '#ecfeff',
            'accentText'  => '#083344',
            'icon'        => 'bi-eye-fill',
            'label'       => 'Visual Learner',
            'tipIcon'     => '👁️',
            'tipTitle'    => 'Visual Study Tip',
            'tip'         => 'You can highlight or underline the text that you read in flashcards, quizzes and other materials.',
            'recTitle'    => '✨ Recommended:',
        ],
        'kinesthetic' => [
            'accent'      => '#d946ef',
            'accentLight' => '#fdf4ff',
            'accentText'  => '#701a75',
            'icon'        => 'bi-activity',
            'label'       => 'Kinaesthetic Learner',
            'tipIcon'     => '🤸',
            'tipTitle'    => 'Kinaesthetic Study Tip',
            'tip'         => 'Interact directly with your study tools! Use the Swipe Flashcards in Review Mode, and complete interactive exercises to lock in the material.',
            'recTitle'    => '✨ Recommended:',
        ],
    ];

    $cfg = $style ? $styleConfig[$style] : null;

    // Counts
    $flashcardCount = \App\Models\FlashcardSet::where('is_flagged', false)->whereIn('user_id', $teacherIds)->count();
    $contentCount   = \App\Models\Content::where('is_flagged', false)->whereIn('teacher_id', $teacherIds)->count();
    $quizCount      = $quizzes->count();
    $completedCount = $user->progress()->count();
    $bestScore      = $style === 'competitive' ? $progress->max('score') : null;

    // Recent material lists
    $recentContents   = \App\Models\Content::where('is_flagged', false)->whereIn('teacher_id', $teacherIds)->latest()->take(5)->get();
    $recentFlashcards = \App\Models\FlashcardSet::where('is_flagged', false)->whereIn('user_id', $teacherIds)->latest()->take(5)->get();
    $recentQuizzes    = $quizzes->sortByDesc('created_at')->take(5);

    // Style badge data
    $styleIcons  = [
        'read_write'  => 'bi-journal-text',
        'auditory'    => 'bi-ear-fill',
        'visual'      => 'bi-eye-fill',
        'kinesthetic' => 'bi-activity',
    ];
    $styleLabels = [
        'read_write'  => 'Read/Write Learner',
        'auditory'    => 'Auditory Learner',
        'visual'      => 'Visual Learner',
        'kinesthetic' => 'Kinaesthetic Learner',
    ];
@endphp

<div class="container-fluid px-4" style="max-width: 1040px; margin: 0 auto;">

    {{-- ── Diagnosis Modal (undiagnosed only) ── --}}
    @if(!$style && !session('diag_banner_dismissed'))
    <div class="modal fade" id="diagnosisModal" tabindex="-1" aria-labelledby="diagnosisModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%); color: white;">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3" style="font-size: 3.5rem; display: inline-block; animation: pulse 2s infinite ease-in-out;">🧠</div>
                    <h3 class="fw-bold mb-2" id="diagnosisModalLabel">Discover Your Learning Style</h3>
                    <p class="text-white-50 mb-4" style="line-height: 1.5; font-size: 0.95rem; opacity: 0.85;">
                        Take a 16-question diagnosis to determine your learning styles to study how <em>you</em> learn best.
                    </p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('student.diagnosis.create') }}" class="btn btn-light fw-bold py-2" style="border-radius: 10px; font-size: 1rem; color: #5b21b6 !important;">
                            <i class="bi bi-clipboard-pulse me-1"></i> Start Diagnosis
                        </a>
                        <a href="{{ route('student.dashboard') }}?dismiss_diag=1" class="btn btn-link text-white-50 fw-semibold text-decoration-none py-1" style="font-size: 0.9rem;">
                            Maybe later
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

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

    {{-- ── Stats Cards (order + accent changes per style) ── --}}
    {{-- Define the 3 card slots --}}
    @php
        $cardMaterials = [
            'title'     => '📚 Materials',
            'count'     => $contentCount + $flashcardCount,
            'sub'       => 'Available Materials',
            'color'     => ($style === 'read_write') ? '#7d6867' : (($style === 'auditory') ? '#e5b181' : (($style === 'visual') ? '#06b6d4' : (($style === 'kinesthetic') ? '#d946ef' : '#14b8a6'))),
            'isPrimary' => ($style === 'auditory' || $style === 'read_write' || $style === 'visual' || $style === 'kinesthetic'),
            'type'      => 'materials',
        ];
        $cardQuizzes = [
            'title'     => '📝 Quizzes',
            'count'     => $quizCount,
            'sub'       => 'Available Quizzes',
            'color'     => '#14b8a6',
            'isPrimary' => false,
            'type'      => 'quiz',
        ];
        $cardCompleted = [
            'title'     => '✅ Completed',
            'count'     => $completedCount,
            'sub'       => 'Quizzes Completed',
            'color'     => '#14b8a6',
            'isPrimary' => false,
            'type'      => 'completed',
        ];

        $orderedCards = [$cardMaterials, $cardQuizzes, $cardCompleted];
    @endphp

    <div class="row mb-4 justify-content-center">
        <div class="col-md-9 col-lg-8">
            <div id="statsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6000">
                
                <!-- Indicators/Dots (Centered at bottom) -->
                <div class="carousel-indicators" style="bottom: 15px; margin-bottom: 0;">
                    <button type="button" data-bs-target="#statsCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Materials" style="background-color: #1F6E68; width: 10px; height: 10px; border-radius: 50%; border: none;"></button>
                    <button type="button" data-bs-target="#statsCarousel" data-bs-slide-to="1" aria-label="Quizzes" style="background-color: #1F6E68; width: 10px; height: 10px; border-radius: 50%; border: none;"></button>
                    <button type="button" data-bs-target="#statsCarousel" data-bs-slide-to="2" aria-label="Completed" style="background-color: #1F6E68; width: 10px; height: 10px; border-radius: 50%; border: none;"></button>
                </div>

                <!-- Carousel Items -->
                <div class="carousel-inner" style="border-radius: 16px;">
                    @foreach($orderedCards as $index => $card)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <div class="card content-card position-relative overflow-hidden"
                             style="{{ $card['isPrimary'] && $cfg ? 'border: 2px solid '.$cfg['accent'].' !important;' : '' }}">
                            {{-- Coloured top stripe for the primary card --}}
                            @if($card['isPrimary'] && $cfg)
                            <div class="primary-card-stripe" style="background:{{ $cfg['accent'] }};"></div>
                            @endif
                            <div class="card-body pt-4 px-4 pb-5">
                                <div class="row align-items-center justify-content-center">
                                    <!-- Left Side: Stats and Action Buttons -->
                                    <div class="col-6 d-flex flex-column justify-content-center align-items-center text-center">
                                        <h1 class="display-3 fw-bold mb-0" style="color:{{ $card['color'] }}; line-height: 1;">{{ $card['count'] }}</h1>
                                        <p class="text-dark fw-bold mb-3 mt-1" style="font-size: 1.15rem; line-height: 1.25;">
                                            @if($card['type'] === 'materials') Available Content @elseif($card['type'] === 'quiz') Available Quizzes @else Quizzes Completed @endif
                                        </p>
                                        
                                        <!-- Actions -->
                                        <div class="d-flex gap-2 w-100 justify-content-center">
                                            @if($card['type'] === 'materials')
                                                <a href="{{ route('student.flashcards.index') }}" class="quizlet-btn">
                                                    Flashcards
                                                </a>
                                                <a href="{{ route('student.contents.index') }}" class="quizlet-btn-secondary">
                                                    Materials
                                                </a>
                                            @elseif($card['type'] === 'quiz')
                                                <a href="{{ route('student.quizzes') }}" class="quizlet-btn">
                                                    Browse Quizzes
                                                </a>
                                            @else
                                                <a href="{{ route('student.progress') }}" class="quizlet-btn">
                                                    View Progress
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Vertical Dashed Divider -->
                                    <div class="col-auto px-0 align-self-stretch d-flex align-items-center">
                                        <div style="border-left: 2px dashed rgba(0, 0, 0, 0.15); height: 90px; margin: auto 0;"></div>
                                    </div>

                                    <!-- Right Side: Graphic illustration -->
                                    <div class="col-5 d-flex align-items-center justify-content-center">
                                        <img src="{{ asset('images/' . ($card['type'] === 'materials' ? 'slideshow 1.png' : ($card['type'] === 'quiz' ? 'slideshow 2.png' : 'slideshow 3.png'))) }}"
                                             alt="Illustration"
                                             style="max-height: 140px; width: auto; object-fit: contain; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.06));">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Controls/Arrows -->
                <button class="carousel-control-prev custom-carousel-control" type="button" data-bs-target="#statsCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true" style="filter: invert(34%) sepia(25%) saturate(1048%) hue-rotate(124deg) brightness(91%) contrast(85%);"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next custom-carousel-control" type="button" data-bs-target="#statsCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true" style="filter: invert(34%) sepia(25%) saturate(1048%) hue-rotate(124deg) brightness(91%) contrast(85%);"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Bottom Row: Combined Recents (Quizlet Style) ── --}}
    @php
        $combinedRecents = collect();
        
        // Add Flashcards
        foreach($recentFlashcards as $fc) {
            $item = new \stdClass();
            $item->title = $fc->title;
            $item->type = 'flashcard';
            $item->subtitle = count($fc->flashcards ?? []) . ' cards • by ' . ($fc->user?->name ?? 'Teacher');
            $item->url = route('student.flashcards.show', $fc);
            $item->created_at = $fc->created_at;
            $combinedRecents->push($item);
        }
        
        // Add Quizzes
        foreach($recentQuizzes as $qz) {
            $item = new \stdClass();
            $item->title = $qz->title;
            $item->type = 'quiz';
            $item->subtitle = 'Practice quiz • by ' . ($qz->teacher?->name ?? 'Teacher');
            $item->url = route('student.quiz.take', ['topic' => $qz->topic, 'difficulty' => $qz->difficulty]);
            $item->created_at = $qz->created_at;
            $combinedRecents->push($item);
        }

        // Add Learning Materials
        foreach($recentContents as $c) {
            $item = new \stdClass();
            $item->title = $c->title;
            $item->type = 'material';
            $item->subtitle = 'Material • by ' . ($c->teacher?->name ?? 'Teacher');
            $item->url = route('student.contents.show', $c);
            $item->created_at = $c->created_at;
            $combinedRecents->push($item);
        }

        // Sort by created_at descending and take 4
        $recents = $combinedRecents->sortByDesc('created_at')->take(4);
    @endphp

    <div class="row justify-content-center mb-5">
        <div class="col-md-9 col-lg-8">
            <div class="row mb-3">
                <div class="col-12">
                    <h4 class="fw-bold text-dark mb-0">Recents</h4>
                </div>
            </div>
            
            <div class="row">
                @forelse($recents as $r)
                    <div class="col-md-6 mb-3">
                        <a href="{{ $r->url }}" class="d-flex align-items-center gap-3 p-3 hover-recent-item flex-row text-decoration-none" style="background: transparent !important; border: none !important; box-shadow: none !important;">
                            {{-- Icon Container --}}
                            <div class="recent-icon-box" style="flex-shrink:0; width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: {{ $r->type === 'flashcard' ? 'rgba(66, 85, 255, 0.32)' : ($r->type === 'quiz' ? 'rgba(245, 158, 11, 0.32)' : 'rgba(16, 185, 129, 0.32)') }};">
                                @if($r->type === 'flashcard')
                                    <i class="bi bi-card-text" style="font-size: 1.3rem; color: #4255ff;"></i>
                                @elseif($r->type === 'quiz')
                                    <i class="bi bi-pencil-square" style="font-size: 1.3rem; color: #f59e0b;"></i>
                                @else
                                    <i class="bi bi-file-earmark-text" style="font-size: 1.3rem; color: #10b981;"></i>
                                @endif
                            </div>
                            {{-- Text Info --}}
                            <div style="flex: 1; min-width: 0;">
                                <h6 class="fw-bold mb-0 text-dark text-truncate" style="font-size: 0.92rem; letter-spacing: -0.1px;">{{ $r->title }}</h6>
                                <span class="text-muted" style="font-size: 0.78rem;">{{ $r->subtitle }}</span>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-muted text-center py-4 card">No recent activities available.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Personalize Your Content (Quizlet Style) ── --}}
    <div class="row justify-content-center mb-5">
        <div class="col-md-9 col-lg-8">
            <span class="text-muted fw-bold d-block mb-2" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Personalize your content</span>
            
            <div class="card p-4 position-relative overflow-hidden" style="border: 1px solid rgba(255, 255, 255, 0.5) !important;">
                <div class="d-flex align-items-center gap-4">
                    {{-- Logo / Icon --}}
                    <div style="flex-shrink: 0; width: 64px; height: 64px; border-radius: 16px; background: rgba(66, 85, 255, 0.1); display: flex; align-items: center; justify-content: center;">
                        <img src="{{ asset('images/logo.png') }}" alt="EzPAIzy Logo" style="width: 38px; height: 38px; object-fit: contain;">
                    </div>
                    
                    {{-- Text and Button --}}
                    <div style="flex: 1;">
                        <h5 class="fw-bold text-dark mb-3" style="font-size: 1.15rem; letter-spacing: -0.2px;">Explore content based on your learning style</h5>
                        <a href="{{ route('student.diagnosis.create') }}" class="quizlet-btn py-2 px-4" style="font-size: 0.85rem;">
                            Take VARK Questionnaire
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>{{-- /container --}}
@endsection

@push('scripts')
@if(!$style && !session('diag_banner_dismissed'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function showModal() {
            if (window.bootstrap && window.bootstrap.Modal) {
                const modalEl = document.getElementById('diagnosisModal');
                if (modalEl) {
                    const myModal = new window.bootstrap.Modal(modalEl);
                    myModal.show();
                }
            } else {
                setTimeout(showModal, 50); // Polling wait for Vite bundle to load Bootstrap
            }
        }
        showModal();
    });
</script>
@endif
@endpush
