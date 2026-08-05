@extends('layouts.dashboard')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1040px; margin: 0 auto;">
    <div class="row mb-4 align-items-center">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Dashboard">
                        <i class="bi bi-arrow-left fs-5"></i>
                    </a>
                    <div>
                        <h1 class="h2 fw-bold text-dark mb-0">Manage Students</h1>
                        <p class="text-muted mb-0" style="font-size: 0.9rem;">Overview of registered students and their learning progress</p>
                    </div>
                </div>
                <a href="{{ route('teacher.students.create') }}" class="btn btn-primary fw-semibold px-3" style="border-radius: 10px; font-size: 0.9rem;">
                    <i class="bi bi-plus-lg me-1"></i> Add New Student
                </a>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 16px;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Name</th>
                        <th style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Email</th>
                        <th style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Phone</th>
                        <th style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Class</th>
                        <th style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Address</th>
                        <th style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Learning Style</th>
                        <th style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Joined</th>
                        <th style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Quizzes Taken</th>
                        <th class="pe-4 text-end" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold text-dark">{{ $student->name }}</span>
                            </td>
                            <td class="text-muted">{{ $student->email }}</td>
                            <td class="text-muted">{{ $student->phone_number ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary fw-semibold px-2 py-1" style="border-radius: 6px;">{{ $student->class_name ?? 'N/A' }}</span>
                            </td>
                            <td class="text-muted text-truncate" style="max-width: 150px;" title="{{ $student->address }}">{{ $student->address ?? 'N/A' }}</td>
                            <td>
                                @if($student->learning_style)
                                    <span class="badge bg-primary bg-opacity-10 text-primary text-capitalize fw-semibold px-2 py-1" style="border-radius: 6px;">{{ $student->learning_style }}</span>
                                @else
                                    <span class="text-muted fst-italic" style="font-size: 0.85rem;">Pending</span>
                                @endif
                            </td>
                            <td class="text-muted" style="font-size: 0.85rem;">{{ $student->created_at->format('M d, Y') }}</td>
                            <td>
                                <span class="badge fw-bold px-2 py-1" style="border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; min-width: 24px; min-height: 24px; background-color: #3b82f6 !important; color: #ffffff !important;">{{ $student->progress()->count() }}</span>
                            </td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('teacher.students.show', $student) }}" class="btn btn-sm btn-outline-info me-1" title="View" style="border-radius: 8px;">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('teacher.students.edit', $student) }}" class="btn btn-sm btn-outline-warning me-1" title="Edit" style="border-radius: 8px;">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('teacher.students.destroy', $student) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this student?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" style="border-radius: 8px;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-people fs-2 d-block mb-2 opacity-50"></i>
                                No students found. <a href="{{ route('teacher.students.create') }}" class="fw-semibold text-primary">Create one now</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $students->links() }}
    </div>

</div>
@endsection
