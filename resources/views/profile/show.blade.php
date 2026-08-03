@extends('layouts.dashboard')

@section('content')
<div class="container mt-3">
    <div class="row mb-3">
        <div class="col-md-12 text-center">
            <h2>My Profile</h2>
        </div>
        <div class="col-md-12 text-end">
            <a href="{{ route('profile.edit') }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit Profile
            </a>
            @if (auth()->user()->isStudent())
                <a href="{{ route('student.dashboard') }}" class="btn text-white" style="background-color: #475569;">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            @else
                <a href="{{ route('teacher.dashboard') }}" class="btn text-white" style="background-color: #475569;">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Profile and Information combined into a single column -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-body text-center py-3"> <!-- Center everything here and reduce padding -->
                    <div class="mb-2">
                        <div class="avatar-circle text-white" style="width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto; background-color: {{ $user->avatar_color }};">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1" style="font-size: 1.6rem; color: #0f172a;">{{ $user->name }}</h3>
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                        @if ($user->isTeacher())
                            <span class="badge bg-primary">Teacher</span>
                        @else
                            <span class="badge bg-success">Student</span>
                        @endif
                        <span style="font-size: 12px; color: #94a3b8;">• Member since {{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                    <hr class="my-2">

                    <!-- Profile Information - Reduced margin to mb-2 for a compact layout -->
                    <div class="mb-2">
                        <label class="form-label text-muted mb-0">Full Name</label>
                        <h6>{{ $user->name }}</h6>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-muted mb-0">Email Address</label>
                        <h6>{{ $user->email }}</h6>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-muted mb-0">Phone Number</label>
                        <h6>{{ $user->phone_number ?? 'Not provided' }}</h6>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-muted mb-0">Class</label>
                        <h6>{{ $user->class_name ?? 'Not assigned' }}</h6>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-muted mb-0">Address</label>
                        <h6 style="word-wrap: break-word; white-space: normal; overflow-wrap: break-word; max-width: 600px; margin: 0 auto;">{{ $user->address ?? 'Not provided' }}</h6>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-muted mb-0">Account Type</label>
                        <h6>
                            @if ($user->isTeacher())
                                <span class="badge bg-primary">Teacher</span>
                            @else
                                <span class="badge bg-success">Student</span>
                            @endif
                        </h6>
                    </div>

                    <!-- Learning Statistics (for students) -->
                    @if ($user->isStudent())
                        <div class="card mt-3">
                            <div class="card-header bg-light py-2">
                                <h5 class="mb-0">Learning Statistics</h5>
                            </div>
                            <div class="card-body py-3">
                                <div class="row">
                                    <div class="col-md-4 text-center">
                                        <h3 class="text-primary fw-bold" style="font-size: 38px;">{{ $user->progress()->count() }}</h3>
                                        <p class="text-muted" style="font-size: 16px;">📝 Quizzes Taken</p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h3 class="text-success fw-bold" style="font-size: 38px;">
                                            @if ($user->progress()->count() > 0)
                                                {{ round($user->progress()->avg('score'), 2) }}%
                                            @else
                                                N/A
                                            @endif
                                        </h3>
                                        <p class="text-muted" style="font-size: 16px;">📊 Average Score</p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h3 class="text-info fw-bold" style="font-size: 38px;">{{ $user->favorites()->count() }}</h3>
                                        <p class="text-muted" style="font-size: 16px;">📚 Saved Materials</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Teaching Statistics (for teachers) -->
                    @if ($user->isTeacher())
                        <div class="card mt-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Teaching Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 text-center">
                                        <h3 class="text-primary">{{ $user->quizzes()->count() }}</h3>
                                        <p class="text-muted">Quizzes Created</p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h3 class="text-success">{{ $user->contents()->count() }}</h3>
                                        <p class="text-muted">Lessons Uploaded</p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h3 class="text-info">{{ \App\Models\User::where('role', 'student')->count() }}</h3>
                                        <p class="text-muted">Total Students</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
