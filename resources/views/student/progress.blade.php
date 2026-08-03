@extends('layouts.dashboard')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="row mb-3 align-items-center">
        <div class="col-md-12">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ url()->previous() === url()->current() ? route('student.dashboard') : url()->previous() }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <div>
                    <h1 class="fw-extrabold text-dark mb-0" style="font-size: 2.1rem; font-weight: 800;">📈 My Progress</h1>
                    <p class="text-muted mb-0">Track your quiz attempts and study performance</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Overview Cards (Top) - Shorter Card Height -->
    @if($selectedType !== 'flashcards')
        <div class="row mb-3 g-3">
            <!-- Quiz Attempts -->
            <div class="col-md-4">
                <div class="card shadow-sm border text-center h-100" style="border-radius: 16px; background-color: #ffffff;">
                    <div class="card-body d-flex flex-column justify-content-center" style="padding: 0.9rem 1rem;">
                        <h1 class="display-6 fw-extrabold mb-1" style="font-weight: 800; color: #1565c0;">{{ $totalQuizzes }}</h1>
                        <p class="fw-bold mb-1 text-uppercase text-muted small" style="letter-spacing: 0.5px; font-size: 0.72rem;">Quizzes Taken</p>
                        <p class="mb-0 text-muted small" style="font-size: 0.75rem;">
                            {{ $totalQuizzes > 0 ? 'Keep up the practice!' : 'Start your first quiz!' }}
                        </p>
                    </div>
                </div>
            </div>
            <!-- Average Score -->
            <div class="col-md-4">
                <div class="card shadow-sm border text-center h-100" style="border-radius: 16px; background-color: #ffffff;">
                    <div class="card-body d-flex flex-column justify-content-center" style="padding: 0.9rem 1rem;">
                        <h1 class="display-6 fw-extrabold mb-1" style="font-weight: 800; color: #00a896;">{{ $averageScore }}%</h1>
                        <p class="fw-bold mb-1 text-uppercase text-muted small" style="letter-spacing: 0.5px; font-size: 0.72rem;">Average Score</p>
                        <p class="mb-0 text-muted small" style="font-size: 0.75rem;">
                            {{ $averageScore >= 70 ? 'Excellent performance!' : ($averageScore > 0 ? 'Good work, aim higher!' : 'No graded attempts yet.') }}
                        </p>
                    </div>
                </div>
            </div>
            <!-- Best Score -->
            <div class="col-md-4">
                <div class="card shadow-sm border text-center h-100" style="border-radius: 16px; background-color: #ffffff;">
                    <div class="card-body d-flex flex-column justify-content-center" style="padding: 0.9rem 1rem;">
                        <h1 class="display-6 fw-extrabold mb-1" style="font-weight: 800; color: #2e7d32;">{{ $highestScore }}%</h1>
                        <p class="fw-bold mb-1 text-uppercase text-muted small" style="letter-spacing: 0.5px; font-size: 0.72rem;">Best Quiz Score</p>
                        <p class="mb-0 text-muted small" style="font-size: 0.75rem;">
                            {{ $highestScore >= 80 ? 'Mastery achieved!' : ($highestScore > 0 ? 'Keep aiming for 100%!' : 'Take a quiz to set a record.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Mastery & Motivation Banner Row (Compact & Margins Tightened) -->
    <div class="row mb-3 g-3">
        <!-- Left: Topics Mastery Overview -->
        <div class="col-md-7">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 16px;">
                <div class="card-body p-3">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-bar-chart-line text-success"></i> Topics Mastery</h6>
                    
                    @php
                        $topicGroups = $unified->groupBy('topic');
                    @endphp

                    @if($topicGroups->count() > 0)
                        <div class="row g-2">
                            @foreach($topicGroups as $topicName => $items)
                                @php
                                    $totalItems = $items->count();
                                    $masteredItems = $items->filter(function($item) {
                                        return $item->status === 'Mastered' || $item->status === 'Excellent' || $item->status === 'graded' || (is_numeric($item->score_num) && $item->score_num >= 70);
                                    })->count();
                                    $pct = $totalItems > 0 ? round(($masteredItems / $totalItems) * 100) : 0;
                                    
                                    $barColor = 'bg-danger';
                                    if ($pct >= 85) {
                                        $barColor = 'bg-success';
                                    } elseif ($pct >= 50) {
                                        $barColor = 'bg-warning';
                                    }
                                @endphp
                                <div class="col-md-6">
                                    <div class="p-2 border rounded bg-light-style">
                                        <div class="d-flex justify-content-between mb-1 small fw-bold">
                                            <span class="text-dark text-truncate" style="max-width: 120px;">{{ $topicName }}</span>
                                            <span class="text-muted">{{ $pct }}% Mastered</span>
                                        </div>
                                        <div class="progress" style="height: 9px; border-radius: 4px; background-color: #e9ecef;">
                                            <div class="progress-bar {{ $barColor }}" style="width: {{ $pct }}%; border-radius: 4px;"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0 small py-2">No topic performance data available yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Slim Motivational Card -->
        <div class="col-md-5">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 16px; background-color: #e8f5e9;">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="bg-success text-white rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px; flex-shrink: 0;">
                        <i class="bi bi-emoji-smile fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-success mb-1">Keep learning!</h6>
                        <p class="text-dark mb-0 small" style="line-height: 1.4;">
                            @if($topicGroups->count() > 0)
                                @php
                                    $bestTopic = '';
                                    $bestPct = -1;
                                    foreach($topicGroups as $topicName => $items) {
                                        $totalItems = $items->count();
                                        $masteredItems = $items->filter(function($item) {
                                            return $item->status === 'Mastered' || (is_numeric($item->score_num) && $item->score_num >= 70);
                                        })->count();
                                        $pct = $totalItems > 0 ? round(($masteredItems / $totalItems) * 100) : 0;
                                        if ($pct > $bestPct) {
                                            $bestPct = $pct;
                                            $bestTopic = $topicName;
                                        }
                                    }
                                @endphp
                                You mastered <strong>{{ $bestPct }}%</strong> of <strong>{{ $bestTopic }}</strong>. Great job!
                            @else
                                Complete quizzes or study flashcards to track progress.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="row mb-4 align-items-end bg-white p-3 rounded shadow-sm border mx-0 g-2" style="border-radius: 12px !important;">
        <div class="col-md-3">
            <label for="type-filter" class="form-label small fw-bold text-uppercase text-muted">Filter Type</label>
            <select id="type-filter" class="form-select" onchange="applyFilters()">
                <option value="">All (Quiz & Flashcards)</option>
                <option value="quiz" {{ $selectedType === 'quiz' ? 'selected' : '' }}>Quiz</option>
                <option value="flashcards" {{ $selectedType === 'flashcards' ? 'selected' : '' }}>Flashcards</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="topic-filter" class="form-label small fw-bold text-uppercase text-muted">Filter Topic</label>
            <select id="topic-filter" class="form-select" onchange="applyFilters()">
                <option value="">All Topics</option>
                @foreach($topics as $topic)
                    <option value="{{ $topic }}" {{ $selectedTopic === $topic ? 'selected' : '' }}>{{ $topic }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="progress-search" class="form-label small fw-bold text-uppercase text-muted">Search Title / Topic</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="progress-search" class="form-control border-start-0 ps-1" placeholder="Search title or topic...">
            </div>
        </div>
        <div class="col-md-2 text-end">
            @if($selectedType || $selectedTopic)
                <a href="{{ route('student.progress') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-x-circle me-1"></i> Clear</a>
            @endif
        </div>
    </div>

    <!-- Progress Cards Grid (Replaces Table) -->
    @if($progress->count() > 0)
        <div class="row">
            @foreach($progress as $p)
                @php
                    $isQuiz = $p->type === 'Quiz';
                    $bgStyle = $isQuiz ? 'border-left: 5px solid #00a896;' : 'border-left: 5px solid #ff8f00;';
                    $badgeStyle = $isQuiz ? 'background-color: #e0f2f1; color: #00a896;' : 'background-color: #fff8e1; color: #ff8f00;';
                    $badgeIcon = $isQuiz ? 'bi-patch-question' : 'bi-card-list';
                    
                    // Status Badge Mapping
                    $statusClass = 'bg-light text-dark border';
                    if ($p->status === 'Excellent' || $p->status === 'Mastered') {
                        $statusClass = 'bg-success text-white';
                    } elseif ($p->status === 'Good' || $p->status === 'Learning') {
                        $statusClass = 'bg-warning text-dark';
                    } elseif ($p->status === 'Need Practice') {
                        $statusClass = 'bg-danger text-white';
                    } elseif ($p->status === 'Graded') {
                        $statusClass = 'bg-primary text-white';
                    }
                    
                    // Topic Badge Colors Mapping
                    $topicNorm = strtolower(trim($p->topic));
                    $topicStyle = 'background-color: #f5f5f5; color: #616161;'; // Default grey
                    
                    if (str_contains($topicNorm, 'quran') || str_contains($topicNorm, 'qur\'an')) {
                        $topicStyle = 'background-color: #f3e5f5; color: #7b1fa2;'; // Soft Purple
                    } elseif (str_contains($topicNorm, 'hadis') || str_contains($topicNorm, 'hadith')) {
                        $topicStyle = 'background-color: #e3f2fd; color: #1565c0;'; // Soft Blue
                    } elseif (str_contains($topicNorm, 'akidah') || str_contains($topicNorm, 'aqidah')) {
                        $topicStyle = 'background-color: #e0f2f1; color: #00796b;'; // Soft Teal
                    } elseif (str_contains($topicNorm, 'fiqah') || str_contains($topicNorm, 'fiqh')) {
                        $topicStyle = 'background-color: #e8f5e9; color: #2e7d32;'; // Soft Green
                    } elseif (str_contains($topicNorm, 'sirah') || str_contains($topicNorm, 'sejarah')) {
                        $topicStyle = 'background-color: #fff3e0; color: #e65100;'; // Soft Orange
                    } elseif (str_contains($topicNorm, 'akhlak') || str_contains($topicNorm, 'adab')) {
                        $topicStyle = 'background-color: #ffebee; color: #c62828;'; // Soft Rose/Red
                    }
                @endphp
                <div class="col-12 mb-3 progress-row" data-title="{{ strtolower($p->title) }}" data-topic="{{ strtolower($p->topic) }}">
                    <div class="card h-100 shadow-sm border-0" style="border-radius: 16px; {{ $bgStyle }} overflow: hidden;">
                        <div class="card-body d-flex flex-column justify-content-between" style="padding: 1.15rem;">
                            <div>
                                <!-- Badges row -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex gap-1.5 align-items-center">
                                        <span class="badge px-2 py-1 rounded-pill fw-bold small d-inline-flex align-items-center gap-1" style="{{ $badgeStyle }}">
                                            <i class="bi {{ $badgeIcon }}"></i> {{ $isQuiz ? 'Quiz' : 'Flashcard' }}
                                        </span>
                                        <span class="badge px-2 py-1 rounded-pill fw-bold small {{ $statusClass }}">
                                            {{ $p->status }}
                                        </span>
                                    </div>
                                    <!-- Dynamic colored topic badge -->
                                    <span class="badge px-2.5 py-1 rounded-pill fw-bold text-uppercase" style="{{ $topicStyle }} font-size: 0.72rem;">{{ $p->topic }}</span>
                                </div>
                                
                                <!-- Title -->
                                <h5 class="card-title fw-extrabold text-dark mb-2" style="font-size: 1.15rem; line-height: 1.4; font-weight: 800;">{{ $p->title }}</h5>
                                
                                <!-- Inline Metadata Details -->
                                <div class="text-muted mb-3" style="font-size: 0.8rem; line-height: 1.5;">
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <span>👤 By: {{ $p->teacher }}</span>
                                        <span class="text-muted-opacity">•</span>
                                        <span>📅 Attempted: {{ $p->date->format('M d, Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Divider -->
                            <hr class="my-2" style="border-top: 1px solid rgba(0,0,0,0.06); opacity: 1;">
                            
                            <!-- Score & Action Row -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    @if($isQuiz && ($p->difficulty === 'hard' || $p->difficulty === 'medium') && $p->status === 'pending')
                                        <span class="text-muted italic small">Pending Review</span>
                                    @else
                                        @if($isQuiz)
                                            <span class="text-success fw-bold d-block" style="font-size: 1.65rem; line-height: 1;">{{ $p->score }}</span>
                                        @else
                                            @php
                                                preg_match('/(\d+\/\d+)\s+Mastered\s+\((\d+)%\)/', $p->score, $matches);
                                                $fraction = $matches[1] ?? $p->score;
                                                $pct = $matches[2] ?? null;
                                            @endphp
                                            @if($pct !== null)
                                                <span class="text-success fw-bold d-block mb-0" style="font-size: 1.65rem; line-height: 1;">{{ $pct }}%</span>
                                                <span class="text-muted small" style="font-size: 0.72rem;">{{ $fraction }} Mastered</span>
                                            @else
                                                <span class="text-success fw-bold d-block" style="font-size: 1.65rem; line-height: 1;">{{ $p->score }}</span>
                                            @endif
                                        @endif
                                    @endif
                                </div>
                                <div>
                                    @if($isQuiz)
                                        @if($p->difficulty === 'hard' || $p->difficulty === 'medium' || $p->raw_progress->student_answers)
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#feedbackModal{{ $p->id }}">
                                                <i class="bi bi-eye"></i> View Results
                                            </button>
                                            
                                            <!-- Feedback Modal -->
                                            <div class="modal fade" id="feedbackModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content text-start">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Quiz Results: {{ $p->title }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            @php
                                                                $answers = $p->raw_progress->student_answers ?? [];
                                                                $questions = \App\Models\Question::where('topic', $p->raw_progress->topic)
                                                                    ->where('difficulty', $p->raw_progress->difficulty)
                                                                    ->get();
                                                                $notes = $p->raw_progress->teacher_notes ?? [];
                                                                $isReadWrite = auth()->user()?->learning_style === 'read_write';
                                                            @endphp
                                                            
                                                            <div class="@if($isReadWrite) row @endif">
                                                                <div class="@if($isReadWrite) col-md-7 @endif" style="@if($isReadWrite) max-height: 65vh; overflow-y: auto; @endif">
                                                                    @foreach($questions as $index => $q)
                                                                        <div class="mb-4 p-3 border rounded bg-white shadow-sm">
                                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                                @php
                                                                                    $studentAnsRaw = $answers[$index] ?? null;
                                                                                    $isWrong = false;
                                                                                    if (isset($notes[$index]['status'])) {
                                                                                        if ($notes[$index]['status'] === 'incorrect') {
                                                                                            $isWrong = true;
                                                                                        }
                                                                                    } else {
                                                                                        if ($p->difficulty !== 'hard' && $studentAnsRaw !== null && $q->correct_answer) {
                                                                                            if (strtolower(trim($studentAnsRaw)) !== strtolower(trim($q->correct_answer))) {
                                                                                                $isWrong = true;
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                @endphp
                                                                                <h6 class="fw-bold mb-0">
                                                                                    @if($isWrong)
                                                                                        <span class="text-danger me-1" title="Incorrect Answer">●</span>
                                                                                    @endif
                                                                                    Q{{ $index + 1 }}: {{ $q->question_text }}
                                                                                </h6>
                                                                                @if(isset($notes[$index]['status']))
                                                                                    @if($notes[$index]['status'] == 'correct')
                                                                                        <span class="badge bg-success text-white"><i class="bi bi-check-lg"></i> Approved</span>
                                                                                    @elseif($notes[$index]['status'] == 'incorrect')
                                                                                        <span class="badge bg-danger text-white"><i class="bi bi-x-lg"></i> Disapproved</span>
                                                                                    @endif
                                                                                @endif
                                                                            </div>
                                                                            
                                                                            <div class="mt-3">
                                                                                <p class="mb-1 text-primary small fw-bold">YOUR ANSWER:</p>
                                                                                <div class="p-3 border rounded bg-light text-dark" style="white-space: pre-wrap;">
                                                                                    @php
                                                                                        $studentAns = $answers[$index] ?? 'No answer provided';
                                                                                        if($q->options && isset($q->options[$studentAns])) {
                                                                                            $studentAns = strtoupper($studentAns) . ': ' . $q->options[$studentAns];
                                                                                        }
                                                                                    @endphp
                                                                                    {{ $studentAns }}
                                                                                </div>
                                                                            </div>
                                            
                                                                            @if(isset($notes[$index]['feedback']) && $notes[$index]['feedback'])
                                                                                <div class="mt-3">
                                                                                    <p class="mb-1 text-warning small fw-bold">TEACHER SUGGESTION:</p>
                                                                                    <div class="p-3 border rounded bg-light-warning shadow-sm" style="background-color: #fffcf0; border-color: #ffeeba;">
                                                                                        <i class="bi bi-chat-left-dots-fill me-1"></i> {{ $notes[$index]['feedback'] }}
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                            <div class="mt-3">
                                                                                <p class="mb-1 text-success small fw-bold">
                                                                                    @if($p->difficulty === 'easy')
                                                                                        CORRECT ANSWER:
                                                                                    @else
                                                                                        SUGGESTED ANSWER / KEY POINTS:
                                                                                    @endif
                                                                                </p>
                                                                                <div class="p-3 border rounded bg-white text-muted small">
                                                                                    @if($q->options && isset($q->options[$q->correct_answer]))
                                                                                        <span class="text-success fw-bold">{{ strtoupper($q->correct_answer) }}:</span> {{ $q->options[$q->correct_answer] }}
                                                                                    @else
                                                                                        {{ $q->correct_answer }}
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                            
                                                                    @if(isset($notes['overall_comment']) && $notes['overall_comment'])
                                                                        <div class="mt-4 p-3 border rounded shadow-sm" style="background-color: #f0f8ff; border-left: 5px solid #0d6efd !important;">
                                                                            <h6 class="fw-bold mb-2 text-primary"><i class="bi bi-chat-quote-fill me-2"></i>Teacher's Overall Comment</h6>
                                                                            <p class="mb-0 text-dark" style="white-space: pre-wrap;">{{ $notes['overall_comment'] }}</p>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                
                                                                @if($isReadWrite)
                                                                    <div class="col-md-5">
                                                                        @php
                                                                            $existingNote = \App\Models\StudentNote::where('user_id', auth()->id())
                                                                                ->where('resource_type', 'quiz')
                                                                                ->where('resource_id', $p->raw_progress->quiz->id)
                                                                                ->first();
                                                                        @endphp
                                                                        <div class="card border-success shadow-sm sticky-top" style="top: 0;">
                                                                            <div class="card-header bg-success text-white d-flex align-items-center justify-content-between py-2">
                                                                                <h6 class="mb-0 fw-bold" style="font-size: 0.85rem;"><i class="bi bi-pencil-square me-1"></i> Revision Notes</h6>
                                                                                <span id="save-status-{{ $p->id }}" class="small text-white-50" style="font-size: 0.75rem;">Auto-saved</span>
                                                                            </div>
                                                                            <div class="card-body p-2">
                                                                                <div class="mb-2">
                                                                                    <label for="note-title-{{ $p->id }}" class="form-label small fw-bold text-uppercase text-muted mb-1" style="font-size: 0.7rem;">Note Title</label>
                                                                                    <input type="text" id="note-title-{{ $p->id }}" class="form-control form-control-sm fw-bold" 
                                                                                           value="{{ $existingNote ? $existingNote->title : 'Revision: ' . $p->title }}" 
                                                                                           placeholder="Title of your note...">
                                                                                </div>
                                                                                <div class="mb-2">
                                                                                    <label for="note-content-{{ $p->id }}" class="form-label small fw-bold text-uppercase text-muted mb-1" style="font-size: 0.7rem;">Acronyms & Revision Notes</label>
                                                                                    <textarea id="note-content-{{ $p->id }}" class="form-control form-control-sm" rows="11" 
                                                                                              placeholder="Write summary notes or acronyms to review...">{{ $existingNote ? $existingNote->content : '' }}</textarea>
                                                                                </div>
                                                                                <div class="d-grid">
                                                                                    <button type="button" onclick="saveNote_{{ $p->id }}()" class="btn btn-success btn-sm fw-bold py-1">
                                                                                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Save Note
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <script>
                                                                            function saveNote_{{ $p->id }}() {
                                                                                const title = document.getElementById('note-title-{{ $p->id }}').value.trim();
                                                                                const content = document.getElementById('note-content-{{ $p->id }}').value.trim();
                                                                                const statusSpan = document.getElementById('save-status-{{ $p->id }}');
                                            
                                                                                if (!title) {
                                                                                    statusSpan.textContent = 'Title required';
                                                                                    statusSpan.style.color = '#ef4444';
                                                                                    return;
                                                                                }
                                            
                                                                                statusSpan.textContent = 'Saving...';
                                                                                statusSpan.style.color = 'rgba(255,255,255,0.7)';
                                            
                                                                                fetch("{{ route('student.notes.save') }}", {
                                                                                    method: 'POST',
                                                                                    headers: {
                                                                                        'Content-Type': 'application/json',
                                                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                                                    },
                                                                                    body: JSON.stringify({
                                                                                        topic: "{{ $p->topic }}",
                                                                                        difficulty: "{{ $p->difficulty }}",
                                                                                        title: title,
                                                                                        content: content,
                                                                                        resource_type: 'quiz',
                                                                                        resource_id: {{ $p->raw_progress->quiz->id }}
                                                                                    })
                                                                                })
                                                                                .then(res => res.json())
                                                                                .then(data => {
                                                                                    if (data.success) {
                                                                                        statusSpan.textContent = 'Auto-saved';
                                                                                        statusSpan.style.color = 'rgba(255,255,255,0.7)';
                                                                                    } else {
                                                                                        statusSpan.textContent = 'Save failed';
                                                                                        statusSpan.style.color = '#ef4444';
                                                                                    }
                                                                                })
                                                                                .catch(err => {
                                                                                    statusSpan.textContent = 'Connection error';
                                                                                    statusSpan.style.color = '#ef4444';
                                                                                });
                                                                            }
                                            
                                                                            document.addEventListener('DOMContentLoaded', function() {
                                                                                const titleInput = document.getElementById('note-title-{{ $p->id }}');
                                                                                const contentInput = document.getElementById('note-content-{{ $p->id }}');
                                                                                let saveTimeout_{{ $p->id }} = null;
                                            
                                                                                if (titleInput && contentInput) {
                                                                                    const triggerAutoSave = () => {
                                                                                        const statusSpan = document.getElementById('save-status-{{ $p->id }}');
                                                                                        statusSpan.textContent = 'Unsaved changes';
                                                                                        statusSpan.style.color = '#f59e0b';
                                                                                        
                                                                                        clearTimeout(saveTimeout_{{ $p->id }});
                                                                                        saveTimeout_{{ $p->id }} = setTimeout(saveNote_{{ $p->id }}, 1500);
                                                                                    };
                                            
                                                                                    titleInput.addEventListener('input', triggerAutoSave);
                                                                                    contentInput.addEventListener('input', triggerAutoSave);
                                                                                }
                                                                            });
                                                                        </script>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="badge bg-light text-dark border">
                                                <i class="bi bi-cpu"></i> Auto-graded
                                            </span>
                                        @endif
                                    @else
                                        <a href="{{ route('student.flashcards.show', $p->id) }}" class="btn btn-sm btn-success text-white rounded-pill px-3 fw-bold shadow-sm">
                                            Continue Study <i class="bi bi-arrow-right"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-start mt-3">
            {{ $progress->links() }}
        </div>
    @else
        <div class="alert alert-info text-start py-4" role="alert" style="border-radius: 16px;">
            <h4 class="alert-heading fw-bold">No Progress Yet</h4>
            @if($selectedType === 'flashcards')
                <p class="mb-0">You haven't completed any flashcards yet. <a href="{{ route('student.flashcards.index') }}" class="alert-link">Start studying flashcards</a> to see your progress here.</p>
            @elseif($selectedType === 'quiz')
                <p class="mb-0">You haven't completed any quizzes yet. <a href="{{ route('student.quizzes') }}" class="alert-link">Start taking quizzes</a> to see your progress here.</p>
            @else
                <p class="mb-0">You haven't completed any quizzes or flashcards yet. <a href="{{ route('student.quizzes') }}" class="alert-link">Start taking quizzes</a> or <a href="{{ route('student.flashcards.index') }}" class="alert-link">studying flashcards</a> to see your progress here.</p>
            @endif
        </div>
    @endif

    <!-- Bottom Actions (Left Aligned & Anchored) -->
    <div class="mt-4 pt-2">
        <h6 class="fw-bold text-muted text-uppercase mb-1 small" style="letter-spacing: 0.5px;">Continue Your Journey</h6>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            @if($selectedType === 'flashcards')
                <a href="{{ route('student.flashcards.index') }}" class="btn btn-primary px-4 rounded-pill fw-bold shadow-sm">
                    Study More Flashcards <i class="bi bi-arrow-right"></i>
                </a>
            @elseif($selectedType === 'quiz')
                <a href="{{ route('student.quizzes') }}" class="btn btn-success text-white px-4 rounded-pill fw-bold shadow-sm">
                    Take More Quizzes <i class="bi bi-arrow-right"></i>
                </a>
            @else
                <a href="{{ route('student.quizzes') }}" class="btn btn-success text-white px-4 rounded-pill fw-bold shadow-sm">
                    Take More Quizzes <i class="bi bi-arrow-right"></i>
                </a>
                <a href="{{ route('student.flashcards.index') }}" class="btn btn-primary px-4 rounded-pill fw-bold shadow-sm">
                    Study More Flashcards <i class="bi bi-arrow-right"></i>
                </a>
            @endif
            <a href="{{ route('student.dashboard') }}" class="btn btn-link text-secondary text-decoration-none fw-bold px-2 py-1">
                <i class="bi bi-house-door"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<script>
    function applyFilters() {
        const type = document.getElementById('type-filter').value;
        const topic = document.getElementById('topic-filter').value;
        let url = new URL(window.location.href);
        if (type) url.searchParams.set('type', type);
        else url.searchParams.delete('type');
        
        if (topic) url.searchParams.set('topic', topic);
        else url.searchParams.delete('topic');
        
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('progress-search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                const cards = document.querySelectorAll('.progress-row');
                
                cards.forEach(card => {
                    const title = card.dataset.title || '';
                    const topic = card.dataset.topic || '';
                    if (title.includes(query) || topic.includes(query)) {
                        card.style.setProperty('display', '', 'important');
                    } else {
                        card.style.setProperty('display', 'none', 'important');
                    }
                });
            });
        }
    });
</script>

<style>
.bg-light-style {
    background-color: #f8f9fa;
    border-color: #f1f3f5 !important;
}
.progress-row {
    transition: opacity 0.25s ease, transform 0.25s ease;
}
</style>
@endsection
