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
                <div class="card-header bg-white pt-4 pb-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-graph-up text-primary me-2"></i> Quiz Progress</h5>
                </div>
                <div class="card-body p-0">
                    @if($progress->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Quiz Title</th>
                                        <th>Score</th>
                                        <th>Percentage</th>
                                        <th>Date Taken</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($progress as $p)
                                        <tr>
                                            <td class="fw-bold">{{ $p->quiz->title ?? 'Deleted Quiz' }}</td>
                                            <td>{{ $p->score }} / {{ $p->total_questions }}</td>
                                            <td>
                                                @php 
                                                    $percentage = $p->total_questions > 0 ? round(($p->score / $p->total_questions) * 100) : 0; 
                                                    $color = $percentage >= 80 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
                                                @endphp
                                                <span class="badge bg-{{ $color }}">{{ $percentage }}%</span>
                                            </td>
                                            <td class="text-muted small">{{ $p->created_at->format('M d, Y H:i') }}</td>
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
                            <h6>No Quizzes Taken Yet</h6>
                            <p class="small">This student hasn't completed any quizzes.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
