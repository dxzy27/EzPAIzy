@extends('layouts.dashboard')

@section('title', 'Student Details')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 fw-bold text-dark">Student Details</h1>
        <div>
            <a href="{{ route('teacher.students.edit', $student) }}" class="btn btn-warning me-2"><i class="bi bi-pencil me-1"></i> Edit</a>
            <a href="{{ route('teacher.students.index') }}" class="btn btn-secondary">Back to Students</a>
        </div>
    </div>

    <div class="row">
        <!-- Student Info -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 text-center">
                    <div class="profile-avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        {{ strtoupper(substr($student->name, 0, 1)) }}
                    </div>
                    <h4 class="fw-bold mb-1">{{ $student->name }}</h4>
                    <p class="text-muted mb-0">{{ $student->email }}</p>
                </div>
                <div class="card-body">
                    <hr>
                    <div class="mb-3">
                        <small class="text-muted d-block fw-bold text-uppercase">Phone Number</small>
                        <span>{{ $student->phone_number ?? $student->phone ?? 'Not provided' }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block fw-bold text-uppercase">Class</small>
                        <span class="badge bg-info">{{ $student->class_name ?? 'Unassigned' }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block fw-bold text-uppercase">Learning Style</small>
                        @if($student->learning_style)
                            <span class="badge bg-primary text-capitalize">{{ $student->learning_style }}</span>
                        @else
                            <span class="text-muted fst-italic">Pending assessment</span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block fw-bold text-uppercase">Joined</small>
                        <span>{{ $student->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress/Quizzes -->
        <div class="col-md-8 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white pt-4 pb-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-graph-up text-primary me-2"></i> Student Progress</h5>
                </div>
                <!-- Filters -->
                <div class="row g-2 px-4 py-3 border-bottom align-items-end mx-0 bg-light">
                    <div class="col-md-5">
                        <label for="type-filter" class="form-label small fw-bold text-uppercase text-muted mb-1" style="font-size: 0.75rem;">Filter Type</label>
                        <select id="type-filter" class="form-select form-select-sm" onchange="applyFilters()">
                            <option value="">All (Quiz & Flashcards)</option>
                            <option value="quiz" {{ $selectedType === 'quiz' ? 'selected' : '' }}>Quiz</option>
                            <option value="flashcards" {{ $selectedType === 'flashcards' ? 'selected' : '' }}>Flashcards</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="topic-filter" class="form-label small fw-bold text-uppercase text-muted mb-1" style="font-size: 0.75rem;">Filter Topic</label>
                        <select id="topic-filter" class="form-select form-select-sm" onchange="applyFilters()">
                            <option value="">All Topics</option>
                            @foreach($topics as $topic)
                                <option value="{{ $topic }}" {{ $selectedTopic === $topic ? 'selected' : '' }}>{{ $topic }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 text-end">
                        @if($selectedType || $selectedTopic)
                            <a href="{{ route('teacher.students.show', $student) }}" class="btn btn-outline-secondary btn-sm w-100"><i class="bi bi-x-circle me-1"></i>Clear</a>
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
                </script>

                <div class="card-body p-0">
                    @if($progress->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
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
                                        <tr>
                                            <td><span class="badge bg-light text-dark border">{{ $p->topic }}</span></td>
                                            <td>
                                                <span class="badge {{ $p->type === 'Quiz' ? 'bg-primary' : 'bg-success' }}">
                                                    {{ $p->type }}
                                                </span>
                                            </td>
                                            <td class="fw-bold">{{ $p->title }}</td>
                                            <td>{{ $p->teacher }}</td>
                                            <td class="text-muted small">{{ $p->date->format('M d, Y H:i') }}</td>
                                            <td>
                                                @if($p->type === 'Quiz')
                                                    @if($p->difficulty === 'hard' && $p->status === 'pending')
                                                        <span class="badge bg-secondary">Not Graded Yet</span>
                                                    @elseif($p->difficulty === 'hard' && $p->status === 'graded')
                                                        <span class="badge bg-primary">Graded</span>
                                                    @elseif($p->score_num >= 70)
                                                        <span class="badge bg-success">Passed</span>
                                                    @elseif($p->score_num >= 50)
                                                        <span class="badge bg-warning">Average</span>
                                                    @else
                                                        <span class="badge bg-danger">Failed</span>
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
                                                @if($p->type === 'Quiz' && $p->difficulty === 'hard' && $p->status === 'pending')
                                                    <span class="text-muted italic">Pending Review</span>
                                                @else
                                                    <strong>{{ $p->score }}</strong>
                                                @endif
                                            </td>
                                            <td>
                                                @if($p->type === 'Quiz')
                                                    @if($p->difficulty === 'hard' || $p->raw_progress->student_answers)
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#feedbackModal{{ $p->id }}">
                                                            <i class="bi bi-eye"></i> Details
                                                        </button>

                                                        <!-- Feedback Modal -->
                                                        <div class="modal fade" id="feedbackModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                                            <div class="modal-dialog modal-lg text-start">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Quiz Results: {{ $p->title }}</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                                                                        @php
                                                                            $answers = $p->raw_progress->student_answers ?? [];
                                                                            $questions = $p->raw_progress->quiz->questions;
                                                                            $notes = $p->raw_progress->teacher_notes ?? [];
                                                                        @endphp
                                                                        
                                                                        @foreach($questions as $index => $q)
                                                                            <div class="mb-4 p-3 border rounded bg-white shadow-sm">
                                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                                    <h6 class="fw-bold mb-0">Q{{ $index + 1 }}: {{ $q->question_text }}</h6>
                                                                                    @if(isset($notes[$index]['status']))
                                                                                        @if($notes[$index]['status'] == 'correct')
                                                                                            <span class="badge bg-success text-white"><i class="bi bi-check-lg"></i> Approved</span>
                                                                                        @elseif($notes[$index]['status'] == 'incorrect')
                                                                                            <span class="badge bg-danger text-white"><i class="bi bi-x-lg"></i> Disapproved</span>
                                                                                        @endif
                                                                                    @endif
                                                                                </div>
                                                                                
                                                                                <div class="mt-3">
                                                                                    <p class="mb-1 text-primary small fw-bold">STUDENT ANSWER:</p>
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
                                                                                        <p class="mb-1 text-warning small fw-bold">FEEDBACK/SUGGESTION:</p>
                                                                                        <div class="p-3 border rounded bg-light-warning shadow-sm" style="background-color: #fffcf0; border-color: #ffeeba;">
                                                                                            <i class="bi bi-chat-left-dots-fill me-1"></i> {{ $notes[$index]['feedback'] }}
                                                                                        </div>
                                                                                    </div>
                                                                                @endif
                                                                                <div class="mt-3">
                                                                                    <p class="mb-1 text-success small fw-bold">SUGGESTED ANSWER / KEY POINTS:</p>
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
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="badge bg-light text-dark border">Auto-graded</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3 border-top">
                            {{ $progress->links('pagination::bootstrap-5') }}
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x display-4 mb-3 d-block text-secondary opacity-50"></i>
                            <h6>No Progress Data Found</h6>
                            <p class="small">There is no progress logged matching the filters.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
