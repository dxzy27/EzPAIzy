@extends('layouts.dashboard')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1040px; margin: 0 auto;">
    <!-- Top Navigation Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ url()->previous() === url()->current() ? (auth()->user()->isTeacher() ? route('teacher.dashboard') : route('student.dashboard')) : url()->previous() }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
            <div>
                <h2 class="fw-bold text-dark mb-0" style="letter-spacing: -0.3px;">My Profile</h2>
                <p class="text-muted mb-0" style="font-size: 0.88rem;">Manage personal details and view account statistics</p>
            </div>
        </div>
        <div>
            <a href="{{ route('profile.edit') }}" class="btn btn-primary fw-bold px-3 py-2 d-inline-flex align-items-center gap-1.5" style="border-radius: 10px; font-size: 0.88rem;">
                <i class="bi bi-pencil-square"></i> Edit Profile
            </a>
        </div>
    </div>

    <!-- Main Profile Card -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; background: #ffffff;">
        <div class="card-body p-4 text-center">
            <!-- Avatar Circle -->
            <div class="mb-3">
                <div class="avatar-circle text-white fw-bold shadow-sm" style="width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto; background-color: {{ $user->avatar_color }}; border: 4px solid #ffffff;">
                    {{ substr($user->name, 0, 1) }}
                </div>
            </div>
            
            <h3 class="fw-bold mb-1 text-dark" style="font-size: 1.6rem; letter-spacing: -0.3px;">{{ $user->name }}</h3>
            <div class="d-flex align-items-center justify-content-center gap-2 mb-4">
                @if ($user->isTeacher())
                    <span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-25 fw-bold px-3 py-1" style="border-radius: 20px;">Teacher</span>
                @else
                    <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 fw-bold px-3 py-1" style="border-radius: 20px;">Student</span>
                @endif
                <span class="text-muted" style="font-size: 0.85rem;">• Member since {{ $user->created_at->format('M d, Y') }}</span>
            </div>

            <hr class="my-4 text-muted opacity-25" style="max-width: 600px; margin-left: auto; margin-right: auto;">

            <!-- Information Details Grid -->
            <div class="row g-3 justify-content-center text-center mb-4" style="max-width: 650px; margin: 0 auto;">
                <div class="col-12 col-md-6 mb-2">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Full Name</span>
                    <h6 class="fw-semibold text-dark mb-0" style="font-size: 1rem;">{{ $user->name }}</h6>
                </div>

                <div class="col-12 col-md-6 mb-2">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Email Address</span>
                    <h6 class="fw-semibold text-dark mb-0" style="font-size: 1rem;">{{ $user->email }}</h6>
                </div>

                <div class="col-12 col-md-6 mb-2">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Phone Number</span>
                    <h6 class="fw-semibold text-dark mb-0" style="font-size: 1rem;">{{ $user->phone_number ?? 'Not provided' }}</h6>
                </div>

                <div class="col-12 col-md-6 mb-2">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Class</span>
                    <h6 class="fw-semibold text-dark mb-0" style="font-size: 1rem;">{{ $user->class_name ?? 'Not assigned' }}</h6>
                </div>

                <div class="col-12 mb-2">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Address</span>
                    <h6 class="fw-semibold text-dark mb-0" style="font-size: 0.98rem; word-wrap: break-word;">{{ $user->address ?? 'Not provided' }}</h6>
                </div>

                <div class="col-12 mb-2">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Account Type</span>
                    <div>
                        @if ($user->isTeacher())
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 fw-semibold px-2.5 py-1" style="border-radius: 8px;">Teacher</span>
                        @else
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 fw-semibold px-2.5 py-1" style="border-radius: 8px;">Student</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            @if ($user->isStudent())
                <div class="card border-0 bg-light mt-4" style="border-radius: 14px;">
                    <div class="card-body py-3.5 px-4">
                        <h6 class="fw-bold text-muted text-uppercase mb-3" style="font-size: 0.8rem; letter-spacing: 0.5px;">Learning Statistics</h6>
                        <div class="row align-items-center g-3">
                            <div class="col-md-4 text-center">
                                <h3 class="text-primary fw-bold mb-0" style="font-size: 2.2rem;">{{ $user->progress()->count() }}</h3>
                                <p class="text-muted mb-0 small fw-semibold">📝 Quizzes Taken</p>
                            </div>
                            <div class="col-md-4 text-center">
                                <h3 class="text-success fw-bold mb-0" style="font-size: 2.2rem;">
                                    @if ($user->progress()->count() > 0)
                                        {{ round($user->progress()->avg('score'), 1) }}%
                                    @else
                                        N/A
                                    @endif
                                </h3>
                                <p class="text-muted mb-0 small fw-semibold">📊 Average Score</p>
                            </div>
                            <div class="col-md-4 text-center">
                                <h3 class="text-info fw-bold mb-0" style="font-size: 2.2rem;">{{ $user->favorites()->count() }}</h3>
                                <p class="text-muted mb-0 small fw-semibold">📚 Saved Materials</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($user->isTeacher())
                <div class="card border-0 bg-light mt-4" style="border-radius: 14px;">
                    <div class="card-body py-3.5 px-4">
                        <h6 class="fw-bold text-muted text-uppercase mb-3" style="font-size: 0.8rem; letter-spacing: 0.5px;">Teaching Statistics</h6>
                        <div class="row align-items-center g-3">
                            <div class="col-md-4 text-center">
                                <h3 class="text-primary fw-bold mb-0" style="font-size: 2.2rem;">{{ $user->quizzes()->count() }}</h3>
                                <p class="text-muted mb-0 small fw-semibold">Quizzes Created</p>
                            </div>
                            <div class="col-md-4 text-center">
                                <h3 class="text-success fw-bold mb-0" style="font-size: 2.2rem;">{{ $user->contents()->count() }}</h3>
                                <p class="text-muted mb-0 small fw-semibold">Lessons Uploaded</p>
                            </div>
                            <div class="col-md-4 text-center">
                                <h3 class="text-info fw-bold mb-0" style="font-size: 2.2rem;">{{ \App\Models\User::where('role', 'student')->count() }}</h3>
                                <p class="text-muted mb-0 small fw-semibold">Total Students</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
@endsection
