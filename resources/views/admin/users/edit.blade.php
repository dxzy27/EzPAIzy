@extends('layouts.dashboard')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="{{ route('admin.users.index') }}" class="btn btn-icon btn-light" style="border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h4 class="mb-0 fw-bold">Edit User Account</h4>
                    <p class="text-muted mb-0" style="font-size: .875rem;">Modify credentials, role, class, or contact details for: <strong>{{ $user->name }}</strong>.</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.users.update', $user) }}" method="POST" id="editUserForm">
                @csrf

                <div class="row g-3">
                    {{-- Full Name --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter full name" value="{{ old('name', $user->name) }}" required>
                    </div>

                    {{-- Email Address --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="user@example.com" value="{{ old('email', $user->email) }}" required>
                    </div>

                    {{-- Account Role --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Role / Permission</label>
                        <select name="role" id="roleSelect" class="form-select" required>
                            <option value="student" {{ old('role', $user->role) === 'student' ? 'selected' : '' }}>Student</option>
                            <option value="teacher" {{ old('role', $user->role) === 'teacher' ? 'selected' : '' }}>Teacher</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrator</option>
                        </select>
                    </div>

                    {{-- Class Name (For Students only) --}}
                    <div class="col-md-6" id="classInputGroup">
                        <label class="form-label small fw-bold">Class Name</label>
                        <input type="text" name="class_name" class="form-control" placeholder="e.g. 5 Al-Ghazali" value="{{ old('class_name', $user->class_name) }}">
                    </div>

                    {{-- Phone Number --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="e.g. +60123456789" value="{{ old('phone', $user->phone) }}">
                    </div>

                    {{-- Address --}}
                    <div class="col-12">
                        <label class="form-label small fw-bold">Residential Address</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Enter home address...">{{ old('address', $user->address) }}</textarea>
                    </div>

                    <div class="col-12 text-end mt-4">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('roleSelect');
        const classInputGroup = document.getElementById('classInputGroup');

        function toggleClassInput() {
            if (roleSelect.value === 'student') {
                classInputGroup.style.display = 'block';
            } else {
                classInputGroup.style.display = 'none';
            }
        }

        roleSelect.addEventListener('change', toggleClassInput);
        toggleClassInput(); // Run initially
    });
</script>
@endsection
