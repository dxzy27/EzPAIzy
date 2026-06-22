@extends('layouts.dashboard')

@section('content')
<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <h2>My Profile</h2>
        </div>
        <div class="col-md-12 text-end">
            <a href="{{ route('profile.edit') }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit Profile
            </a>
            @if (auth()->user()->isStudent())
                <a href="{{ route('student.dashboard') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            @else
                <a href="{{ route('teacher.dashboard') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Profile and Information combined into a single column -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-body text-center"> <!-- Center everything here -->
                    <div class="mb-3">
                        <div class="avatar-circle text-white" style="width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto; background-color: {{ $user->avatar_color }};">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    </div>
                    <h5>{{ $user->name }}</h5>
                    <p class="text-muted">
                        @if ($user->isTeacher())
                            <span class="badge bg-primary">Teacher</span>
                        @else
                            <span class="badge bg-success">Student</span>
                        @endif
                    </p>
                    <hr>
                    <p class="text-muted small">Member since {{ $user->created_at->format('M d, Y') }}</p>

                    <!-- Profile Information -->
                    <div class="mb-4">
                        <label class="form-label text-muted">Full Name</label>
                        <h6>{{ $user->name }}</h6>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted">Email Address</label>
                        <h6>{{ $user->email }}</h6>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted">Phone Number</label>
                        <h6>{{ $user->phone ?? 'Not provided' }}</h6>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted">Class</label>
                        <h6>{{ $user->class_name ?? 'Not assigned' }}</h6>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted">Address</label>
                        <h6>{{ $user->address ?? 'Not provided' }}</h6>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted">Account Type</label>
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
                        <div class="card mt-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Learning Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 text-center">
                                        <h3 class="text-primary">{{ $user->progress()->count() }}</h3>
                                        <p class="text-muted">Quizzes Taken</p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h3 class="text-success">
                                            @if ($user->progress()->count() > 0)
                                                {{ round($user->progress()->avg('score'), 2) }}%
                                            @else
                                                N/A
                                            @endif
                                        </h3>
                                        <p class="text-muted">Average Score</p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h3 class="text-info">{{ $user->widgets()->count() }}</h3>
                                        <p class="text-muted">Dashboard Widgets</p>
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
