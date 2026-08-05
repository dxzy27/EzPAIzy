@extends('layouts.dashboard')

@push('styles')
<style>
    /* ── Blended Card Styling (Quizlet Style) ── */
    .card {
        background: linear-gradient(180deg, #FFFFFF 0%, #F8FBFD 100%) !important;
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
        background: linear-gradient(180deg, #FFFFFF 0%, #F8FBFD 100%) !important;
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

    /* ── Carousel Responsive Controls ── */
    .custom-carousel-control {
        width: 36px !important;
        height: 36px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        background: transparent !important;
        border: none !important;
        opacity: 0.85 !important;
        transition: all 0.25s ease !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .custom-carousel-control i {
        color: #1e293b !important;
        font-size: 1.6rem !important;
        font-weight: 800 !important;
        transition: transform 0.22s ease !important;
    }
    .custom-carousel-control:hover {
        background: transparent !important;
        opacity: 1 !important;
    }
    .custom-carousel-control:hover i {
        transform: scale(1.2) !important;
    }
    .carousel-control-prev.custom-carousel-control {
        left: -50px !important;
    }
    .carousel-control-next.custom-carousel-control {
        right: -50px !important;
    }
    @media (max-width: 991px) {
        .carousel-control-prev.custom-carousel-control { left: -40px !important; }
        .carousel-control-next.custom-carousel-control { right: -40px !important; }
    }
    @media (max-width: 767px) {
        .carousel-control-prev.custom-carousel-control {
            left: 10px !important;
            background: rgba(255, 255, 255, 0.4) !important;
            border-radius: 50% !important;
        }
        .carousel-control-next.custom-carousel-control {
            right: 10px !important;
            background: rgba(255, 255, 255, 0.4) !important;
            border-radius: 50% !important;
        }
    }

    /* ── Quick Action Cards ── */
    .action-card-item {
        background: #ffffff !important;
        border-radius: 14px !important;
        border: 1px solid rgba(0, 0, 0, 0.05) !important;
        padding: 14px 18px !important;
        display: flex !important;
        align-items: center !important;
        gap: 14px !important;
        text-decoration: none !important;
        color: #1e293b !important;
        font-weight: 600 !important;
        font-size: 0.95rem !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02) !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .action-card-item:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06) !important;
        color: #4255ff !important;
    }
    .action-icon-box {
        width: 44px; height: 44px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        font-size: 1.25rem;
    }
</style>
@endpush

@section('content')
@php
    $flashcardSetsCount = \App\Models\FlashcardSet::where('user_id', auth()->id())->count();
    
    $cardQuizzes = [
        'title' => 'Quizzes',
        'count' => $quizzes->count(),
        'sub'   => 'Quizzes Created',
        'color' => '#4255ff',
        'type'  => 'quizzes',
    ];
    $cardMaterials = [
        'title' => 'Materials',
        'count' => $totalContentsCount,
        'sub'   => 'Uploaded Materials',
        'color' => '#10b981',
        'type'  => 'materials',
    ];
    $cardStudents = [
        'title' => 'Students',
        'count' => $studentsCount,
        'sub'   => 'Registered Students',
        'color' => '#f59e0b',
        'type'  => 'students',
    ];

    $orderedCards = [$cardQuizzes, $cardMaterials, $cardStudents];
@endphp

<div class="container-fluid px-4" style="max-width: 1040px; margin: 0 auto;">

    {{-- ── Welcoming Greeting Row ── --}}
    <div class="row justify-content-center mt-4 mb-4">
        <div class="col-md-9 col-lg-8">
            <div class="d-flex align-items-start gap-3">
                <span style="font-size: 2.2rem; line-height: 1.2;">👋</span>
                <div>
                    <h3 class="fw-extrabold text-dark mb-1" style="letter-spacing: -0.3px; font-size: 1.6rem; font-weight: 800;">
                        Welcome back, {{ explode(' ', trim(auth()->user()->name))[0] }}
                    </h3>
                    <p class="text-muted mb-0" style="font-size: 0.95rem; font-weight: 500;">
                        Here's an overview of your class activity.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Stats Carousel ── --}}
    <div class="row mb-4 justify-content-center">
        <div class="col-md-9 col-lg-8">
            <div id="teacherStatsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6000">
                
                <!-- Indicators/Dots -->
                <div class="carousel-indicators" style="bottom: 15px; margin-bottom: 0;">
                    <button type="button" data-bs-target="#teacherStatsCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Quizzes" style="background-color: #1F6E68; width: 10px; height: 10px; border-radius: 50%; border: none;"></button>
                    <button type="button" data-bs-target="#teacherStatsCarousel" data-bs-slide-to="1" aria-label="Materials" style="background-color: #1F6E68; width: 10px; height: 10px; border-radius: 50%; border: none;"></button>
                    <button type="button" data-bs-target="#teacherStatsCarousel" data-bs-slide-to="2" aria-label="Students" style="background-color: #1F6E68; width: 10px; height: 10px; border-radius: 50%; border: none;"></button>
                </div>

                <!-- Carousel Items -->
                <div class="carousel-inner" style="border-radius: 16px;">
                    @foreach($orderedCards as $index => $card)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <div class="card content-card position-relative overflow-hidden">
                            <div class="card-body pt-4 px-4 pb-5">
                                <div class="row align-items-center justify-content-center">
                                    <!-- Left Side: Stats and Action Buttons -->
                                    <div class="col-6 d-flex flex-column justify-content-center align-items-center text-center">
                                        <h1 class="display-3 fw-bold mb-0" style="color:{{ $card['color'] }}; line-height: 1;">{{ $card['count'] }}</h1>
                                        <p class="text-dark fw-bold mb-3 mt-1" style="font-size: 1.15rem; line-height: 1.25;">
                                            {{ $card['sub'] }}
                                        </p>
                                        
                                        <!-- Actions -->
                                        <div class="d-flex gap-2 w-100 justify-content-center">
                                            @if($card['type'] === 'quizzes')
                                                <a href="{{ route('teacher.quizzes.generate') }}" class="quizlet-btn">
                                                    <i class="bi bi-stars me-1"></i> Generate Quiz
                                                </a>
                                                <a href="{{ route('teacher.quizzes.index') }}" class="quizlet-btn-secondary">
                                                    Manage
                                                </a>
                                            @elseif($card['type'] === 'materials')
                                                <a href="{{ route('teacher.contents.create') }}" class="quizlet-btn" style="background-color: #10b981 !important; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25) !important;">
                                                    <i class="bi bi-plus me-1"></i> Add Material
                                                </a>
                                                <a href="{{ route('teacher.contents.index') }}" class="quizlet-btn-secondary">
                                                    Browse
                                                </a>
                                            @else
                                                <a href="{{ route('teacher.students.index') }}" class="quizlet-btn" style="background-color: #f59e0b !important; box-shadow: 0 4px 14px rgba(245, 158, 11, 0.25) !important;">
                                                    <i class="bi bi-people me-1"></i> View Students
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
                                        <img src="{{ asset('images/' . ($card['type'] === 'quizzes' ? 'slideshow 2.png' : ($card['type'] === 'materials' ? 'slideshow 1.png' : 'slideshow 3.png'))) }}"
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
                <button class="carousel-control-prev custom-carousel-control" type="button" data-bs-target="#teacherStatsCarousel" data-bs-slide="prev">
                    <i class="bi bi-chevron-left"></i>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next custom-carousel-control" type="button" data-bs-target="#teacherStatsCarousel" data-bs-slide="next">
                    <i class="bi bi-chevron-right"></i>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Recents Section ── --}}
    @php
        $teacherRecents = collect();

        foreach($quizzes->take(4) as $qz) {
            $item = new \stdClass();
            $item->title = $qz->title;
            $item->type = 'quiz';
            $item->subtitle = 'Practice quiz • ' . ($qz->questions_count ?? 0) . ' questions';
            $item->url = route('teacher.quizzes.show', ['topic' => $qz->topic, 'difficulty' => $qz->difficulty]);
            $item->created_at = $qz->created_at;
            $teacherRecents->push($item);
        }

        foreach($recentContents->take(4) as $c) {
            $isFC = class_basename($c) === 'FlashcardSet';
            $item = new \stdClass();
            $item->title = $c->title;
            $item->type = $isFC ? 'flashcard' : 'material';
            $item->subtitle = ($isFC ? 'Flashcard set' : 'Material') . ' • ' . $c->created_at->diffForHumans();
            $item->url = $isFC ? route('teacher.flashcard-sets.edit', $c) : route('teacher.contents.edit', $c);
            $item->created_at = $c->created_at;
            $teacherRecents->push($item);
        }

        $sortedRecents = $teacherRecents->sortByDesc('created_at')->take(4);
    @endphp

    <div class="row justify-content-center mb-5">
        <div class="col-md-9 col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h4 class="fw-bold text-dark mb-0">Recents</h4>
                <a href="{{ route('teacher.quizzes.index') }}" class="text-decoration-none fw-semibold" style="font-size: 0.88rem; color: #4255ff;">View all</a>
            </div>
            
            <div class="row">
                @forelse($sortedRecents as $r)
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

    {{-- ── Quick Actions Section ── --}}
    <div class="row justify-content-center mb-5">
        <div class="col-md-9 col-lg-8">
            <span class="text-muted fw-bold d-block mb-3" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">⚡ Quick Actions</span>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <a href="{{ route('teacher.quizzes.generate') }}" class="action-card-item">
                        <div class="action-icon-box" style="background: rgba(66, 85, 255, 0.12); color: #4255ff;">
                            <i class="bi bi-stars"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div>Generate Quiz with AI</div>
                            <small class="text-muted fw-normal d-block" style="font-size: 0.78rem;">Auto-create quizzes instantly</small>
                        </div>
                        <i class="bi bi-chevron-right text-muted" style="font-size: 0.85rem;"></i>
                    </a>
                </div>

                <div class="col-md-6">
                    <a href="{{ route('teacher.quizzes.create') }}" class="action-card-item">
                        <div class="action-icon-box" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div>Create Quiz Manually</div>
                            <small class="text-muted fw-normal d-block" style="font-size: 0.78rem;">Build custom question sets</small>
                        </div>
                        <i class="bi bi-chevron-right text-muted" style="font-size: 0.85rem;"></i>
                    </a>
                </div>

                <div class="col-md-6">
                    <a href="{{ route('teacher.flashcard-sets.create') }}" class="action-card-item">
                        <div class="action-icon-box" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                            <i class="bi bi-card-text"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div>New Flashcard Set</div>
                            <small class="text-muted fw-normal d-block" style="font-size: 0.78rem;">Create flashcard study sets</small>
                        </div>
                        <i class="bi bi-chevron-right text-muted" style="font-size: 0.85rem;"></i>
                    </a>
                </div>

                <div class="col-md-6">
                    <a href="{{ route('teacher.contents.create') }}" class="action-card-item">
                        <div class="action-icon-box" style="background: rgba(2, 132, 199, 0.12); color: #0284c7;">
                            <i class="bi bi-file-earmark-plus"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div>Upload Material</div>
                            <small class="text-muted fw-normal d-block" style="font-size: 0.78rem;">Share PDFs, notes, or docs</small>
                        </div>
                        <i class="bi bi-chevron-right text-muted" style="font-size: 0.85rem;"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

