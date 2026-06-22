@extends('layouts.dashboard')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
@section('content')
<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Manage Students</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('teacher.students.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Student
            </a>
        </div>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Class</th>
                        <th>Quizzes Taken</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr>
                            <td>
                                <strong>{{ $student->name }}</strong>
                            </td>
                            <td>{{ $student->email }}</td>
                            <td>{{ $student->phone ?? 'N/A' }}</td>
                            <td>{{ $student->class_name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $student->progress()->count() }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('teacher.students.show', $student) }}" class="btn btn-sm btn-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('teacher.students.edit', $student) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('teacher.students.destroy', $student) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this student?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No students found. <a href="{{ route('teacher.students.create') }}">Create one now</a>
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
        <div class="row mt-4">
        <div class="col-md-12">
            <a href="{{ route('teacher.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</div>
@endsection
