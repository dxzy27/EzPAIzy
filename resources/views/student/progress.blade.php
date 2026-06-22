@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>My Progress</h1>
            <p class="text-muted">Track your quiz scores and performance</p>
        </div>
    </div>

    @if($progress->count() > 0)
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Quiz Title</th>
                                <th>Teacher</th>
                                <th>Score</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($progress as $p)
                                <tr>
                                    <td>{{ $p->quiz->title }}</td>
                                    <td>{{ $p->quiz->teacher->name }}</td>
                                    <td>
                                        @if($p->quiz->difficulty === 'hard' && $p->status === 'pending')
                                            <span class="text-muted italic">Pending Review</span>
                                        @else
                                            <strong>{{ $p->score }}%</strong>
                                        @endif
                                    </td>
                                    <td>{{ $p->updated_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        @if($p->quiz->difficulty === 'hard' && $p->status === 'pending')
                                            <span class="badge bg-secondary">Not Graded Yet</span>
                                        @elseif($p->quiz->difficulty === 'hard' && $p->status === 'graded')
                                            <span class="badge bg-primary">Graded</span>
                                        @elseif($p->score >= 70)
                                            <span class="badge bg-success">Passed</span>
                                        @elseif($p->score >= 50)
                                            <span class="badge bg-warning">Average</span>
                                        @else
                                            <span class="badge bg-danger">Failed</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($p->quiz->difficulty === 'hard' || $p->student_answers)
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#feedbackModal{{ $p->id }}">
                                                <i class="bi bi-eye"></i> View Details
                                            </button>

                                            <!-- Feedback Modal -->
                                            <div class="modal fade" id="feedbackModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content text-start">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Quiz Results: {{ $p->quiz->title }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            @php
                                                                $answers = $p->student_answers ?? [];
                                                                $questions = $p->quiz->questions;
                                                                $notes = $p->teacher_notes ?? [];
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
                                            <span class="badge bg-light text-dark border">
                                                <i class="bi bi-cpu"></i> Auto-graded
                                            </span>
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

        <!-- Statistics -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Quizzes</h5>
                        <h2 class="text-primary">{{ $progress->count() }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Average Score</h5>
                        <h2 class="text-info">
                            @php
                                $gradedQuizzes = $progress->filter(function($p) {
                                    return $p->quiz->difficulty !== 'hard';
                                });
                            @endphp
                            {{ $gradedQuizzes->count() > 0 ? round($gradedQuizzes->avg('score'), 1) : 0 }}%
                        </h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Highest Score</h5>
                        <h2 class="text-success">
                            {{ $gradedQuizzes->count() > 0 ? $gradedQuizzes->max('score') : 0 }}%
                        </h2>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info" role="alert">
            <h4 class="alert-heading">No Progress Yet</h4>
            <p>You haven't completed any quizzes yet. <a href="{{ route('student.quizzes') }}" class="alert-link">Start taking quizzes</a> to see your progress here.</p>
        </div>
    @endif

    <div class="row mt-4">
        <div class="col-md-12">
            <a href="{{ route('student.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
            <a href="{{ route('student.quizzes') }}" class="btn btn-primary">Take More Quizzes</a>
        </div>
    </div>
</div>
@endsection
