@extends('layouts.dashboard')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-12">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Dashboard">
                    <i class="bi bi-house-door fs-5"></i>
                </a>
                <div>
                    <h1 class="h2 fw-bold text-dark mb-0">My Progress</h1>
                    <p class="text-muted mb-0">Track your quiz scores and performance</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4 align-items-end bg-white p-3 rounded shadow-sm border mx-0 g-2">
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
                    const rows = document.querySelectorAll('.progress-row');
                    
                    rows.forEach(row => {
                        const title = row.dataset.title || '';
                        const topic = row.dataset.topic || '';
                        if (title.includes(query) || topic.includes(query)) {
                            row.style.setProperty('display', '', 'important');
                        } else {
                            row.style.setProperty('display', 'none', 'important');
                        }
                    });
                });
            }
        });
    </script>

    @if($progress->count() > 0)
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Topic</th>
                                <th>Quiz or Flashcards</th>
                                <th>Title</th>
                                <th>Teacher</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Score</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($progress as $p)
                                <tr class="progress-row" data-title="{{ strtolower($p->title) }}" data-topic="{{ strtolower($p->topic) }}">
                                    <td><span class="badge bg-light text-dark border">{{ $p->topic }}</span></td>
                                    <td>
                                        <span class="badge {{ $p->type === 'Quiz' ? 'bg-primary' : 'bg-success' }}">
                                            {{ $p->type }}
                                        </span>
                                    </td>
                                    <td class="fw-bold">{{ $p->title }}</td>
                                    <td>{{ $p->teacher }}</td>
                                    <td>{{ $p->date->format('M d, Y H:i') }}</td>
                                    <td>
                                        @if($p->type === 'Quiz')
                                            @if(($p->difficulty === 'hard' || $p->difficulty === 'medium') && $p->status === 'pending')
                                                <span class="badge bg-secondary">Not Graded Yet</span>
                                            @elseif(($p->difficulty === 'hard' || $p->difficulty === 'medium') && $p->status === 'graded')
                                                <span class="badge bg-primary">Graded</span>
                                            @elseif($p->score_num >= 70)
                                                <span class="badge bg-success">Excellent</span>
                                            @elseif($p->score_num >= 50)
                                                <span class="badge bg-warning">Good</span>
                                            @else
                                                <span class="badge bg-danger">Need Practice</span>
                                            @endif
                                        @else
                                            @if($p->status === 'Mastered')
                                                <span class="badge bg-success">Mastered</span>
                                            @elseif($p->status === 'Learning')
                                                <span class="badge bg-info">Learning</span>
                                            @else
                                                <span class="badge bg-light text-dark border">Not Started</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($p->type === 'Quiz' && ($p->difficulty === 'hard' || $p->difficulty === 'medium') && $p->status === 'pending')
                                            <span class="text-muted italic">Pending Review</span>
                                        @else
                                            @if($p->type === 'Quiz')
                                                <span class="text-success fw-bold d-block" style="font-size: 1.05rem;">{{ $p->score }}</span>
                                            @else
                                                @php
                                                    preg_match('/(\d+\/\d+)\s+Mastered\s+\((\d+)%\)/', $p->score, $matches);
                                                    $fraction = $matches[1] ?? $p->score;
                                                    $pct = $matches[2] ?? null;
                                                @endphp
                                                @if($pct !== null)
                                                    <span class="text-success fw-bold d-block mb-0" style="font-size: 1.05rem;">{{ $pct }}%</span>
                                                    <span class="text-muted small" style="font-size: 0.75rem;">{{ $fraction }} Mastered</span>
                                                @else
                                                    <strong class="text-success">{{ $p->score }}</strong>
                                                @endif
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($p->type === 'Quiz')
                                            @if($p->difficulty === 'hard' || $p->difficulty === 'medium' || $p->raw_progress->student_answers)
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#feedbackModal{{ $p->id }}">
                                                    <i class="bi bi-eye"></i> View Details
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
                                            <a href="{{ route('student.flashcards.show', $p->id) }}" class="btn btn-sm btn-outline-success">
                                                Continue Study <i class="bi bi-arrow-right"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                {{ $progress->links() }}
            </div>
        </div>

        <div class="row mt-4 g-4">
            <!-- Left Column: Performance Overview & Motivational Card -->
            <div class="col-md-8">
                <!-- Topics Mastery Overview -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
                    <div class="card-body" style="padding: 1.5rem;">
                        <h5 class="card-title fw-bold text-dark mb-4"><i class="bi bi-bar-chart-line text-success"></i> Topics Mastery Overview</h5>
                        
                        @php
                            $topicGroups = $unified->groupBy('topic');
                        @endphp

                        @if($topicGroups->count() > 0)
                            @foreach($topicGroups as $topicName => $items)
                                @php
                                    $totalItems = $items->count();
                                    $masteredItems = $items->filter(function($item) {
                                        if ($item->status === 'Mastered' || $item->status === 'Excellent' || $item->status === 'graded') {
                                            return true;
                                        }
                                        if (is_numeric($item->score_num) && $item->score_num >= 70) {
                                            return true;
                                        }
                                        return false;
                                    })->count();
                                    $pct = $totalItems > 0 ? round(($masteredItems / $totalItems) * 100) : 0;
                                    
                                    $barColor = 'bg-danger';
                                    if ($pct >= 85) {
                                        $barColor = 'bg-success';
                                    } elseif ($pct >= 50) {
                                        $barColor = 'bg-warning';
                                    }
                                @endphp
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2 small fw-bold">
                                        <span class="text-dark">{{ $topicName }}</span>
                                        <span class="text-muted">{{ $pct }}% Mastered <span class="text-black-50">({{ $masteredItems }}/{{ $totalItems }} activities)</span></span>
                                    </div>
                                    <div class="progress" style="height: 10px; border-radius: 5px; background-color: #f1f3f5;">
                                        <div class="progress-bar {{ $barColor }}" style="width: {{ $pct }}%; border-radius: 5px; transition: width 0.6s ease;"></div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted mb-0">No topic performance data available yet.</p>
                        @endif
                    </div>
                </div>

                <!-- Motivational Message Card -->
                <div class="card shadow-sm border-0" style="border-radius: 16px; background-color: #e8f5e9;">
                    <div class="card-body d-flex align-items-center gap-3" style="padding: 1.5rem;">
                        <div class="bg-success text-white rounded-circle p-3 d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
                            <i class="bi bi-emoji-smile fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-success mb-1">Keep learning!</h5>
                            <p class="text-dark mb-0" style="font-size: 0.95rem;">
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
                                    You have mastered <strong>{{ $bestPct }}%</strong> of your <strong>{{ $bestTopic }}</strong> activities. You're doing great, keep up the good work!
                                @else
                                    Start taking quizzes or studying flashcards to track your progress and master new topics!
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Statistics Stacked & Action Buttons -->
            <div class="col-md-4">
                <!-- Stat Cards -->
                <div class="d-flex flex-column gap-3">
                    @if($selectedType !== 'flashcards')
                        <!-- Quiz Attempts Card -->
                        <div class="card shadow-sm border-0" style="border-radius: 16px; background: linear-gradient(135deg, #1565c0, #1e88e5); color: white;">
                            <div class="card-body text-center" style="padding: 1.25rem;">
                                <h1 class="display-4 fw-extrabold mb-1" style="font-weight: 800;">{{ $totalQuizzes }}</h1>
                                <p class="small text-white-50 mb-0 text-uppercase fw-bold" style="letter-spacing: 0.5px; font-size: 0.75rem;">Quiz Attempts</p>
                            </div>
                        </div>

                        <!-- Average Score Card -->
                        <div class="card shadow-sm border-0" style="border-radius: 16px; background: linear-gradient(135deg, #00a896, #02c39a); color: white;">
                            <div class="card-body text-center" style="padding: 1.25rem;">
                                <h1 class="display-4 fw-extrabold mb-1" style="font-weight: 800;">{{ $averageScore }}%</h1>
                                <p class="small text-white-50 mb-0 text-uppercase fw-bold" style="letter-spacing: 0.5px; font-size: 0.75rem;">Average Score</p>
                            </div>
                        </div>

                        <!-- Best Score Card -->
                        <div class="card shadow-sm border-0" style="border-radius: 16px; background: linear-gradient(135deg, #ff8f00, #ffa000); color: white;">
                            <div class="card-body text-center" style="padding: 1.25rem;">
                                <h1 class="display-4 fw-extrabold mb-1" style="font-weight: 800;">{{ $highestScore }}%</h1>
                                <p class="small text-white-50 mb-0 text-uppercase fw-bold" style="letter-spacing: 0.5px; font-size: 0.75rem;">Best Quiz Score</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Navigation & Action Buttons -->
                <div class="card shadow-sm border-0 mt-4" style="border-radius: 16px;">
                    <div class="card-body" style="padding: 1.5rem;">
                        <h6 class="fw-bold text-muted text-uppercase mb-3 small" style="letter-spacing: 0.5px;">What's Next?</h6>
                        <div class="d-grid gap-2">
                            @if($selectedType === 'flashcards')
                                <a href="{{ route('student.flashcards.index') }}" class="btn btn-primary py-2.5 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                                    Study More Flashcards <i class="bi bi-arrow-right"></i>
                                </a>
                            @elseif($selectedType === 'quiz')
                                <a href="{{ route('student.quizzes') }}" class="btn btn-success py-2.5 rounded-pill fw-bold text-white shadow-sm d-flex align-items-center justify-content-center gap-2">
                                    Take More Quizzes <i class="bi bi-arrow-right"></i>
                                </a>
                            @else
                                <a href="{{ route('student.quizzes') }}" class="btn btn-success py-2.5 rounded-pill fw-bold text-white shadow-sm d-flex align-items-center justify-content-center gap-2">
                                    Take More Quizzes <i class="bi bi-arrow-right"></i>
                                </a>
                                <a href="{{ route('student.flashcards.index') }}" class="btn btn-outline-primary py-2.5 rounded-pill fw-bold d-flex align-items-center justify-content-center gap-2">
                                    Study More Flashcards
                                </a>
                            @endif
                            <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary py-2.5 rounded-pill fw-bold d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-house-door"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info" role="alert">
            <h4 class="alert-heading">No Progress Yet</h4>
            @if($selectedType === 'flashcards')
                <p>You haven't completed any flashcards yet. <a href="{{ route('student.flashcards.index') }}" class="alert-link">Start studying flashcards</a> to see your progress here.</p>
            @elseif($selectedType === 'quiz')
                <p>You haven't completed any quizzes yet. <a href="{{ route('student.quizzes') }}" class="alert-link">Start taking quizzes</a> to see your progress here.</p>
            @else
                <p>You haven't completed any quizzes or flashcards yet. <a href="{{ route('student.quizzes') }}" class="alert-link">Start taking quizzes</a> or <a href="{{ route('student.flashcards.index') }}" class="alert-link">studying flashcards</a> to see your progress here.</p>
            @endif
        </div>
        
        <div class="mt-4">
            <a href="{{ route('student.dashboard') }}" class="btn btn-secondary rounded-pill px-4">Back to Dashboard</a>
            @if($selectedType === 'flashcards')
                <a href="{{ route('student.flashcards.index') }}" class="btn btn-primary rounded-pill px-4 ms-2">Study More Flashcards</a>
            @elseif($selectedType === 'quiz')
                <a href="{{ route('student.quizzes') }}" class="btn btn-primary rounded-pill px-4 ms-2">Take More Quizzes</a>
            @else
                <a href="{{ route('student.quizzes') }}" class="btn btn-primary rounded-pill px-4 ms-2">Take More Quizzes</a>
                <a href="{{ route('student.flashcards.index') }}" class="btn btn-outline-primary rounded-pill px-4 ms-2">Study More Flashcards</a>
            @endif
        </div>
    @endif
</div>
@endsection
