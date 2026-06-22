@extends('layouts.dashboard')

@section('content')
<div class="card p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-0 fw-bold">User Account Management</h4>
            <p class="text-muted mb-0" style="font-size: .875rem;">Manage Teacher and Student accounts, passwords, and permissions.</p>
        </div>
        <div>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus-fill me-2"></i>Create New User
            </a>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filters --}}
    <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3 mb-4 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label small fw-bold text-muted">Search User</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by name or email..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small fw-bold text-muted">Role Filter</label>
            <select name="role" class="form-select">
                <option value="">All Roles</option>
                <option value="teacher" {{ request('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
                <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Student</option>
            </select>
        </div>
        <div class="col-6 col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-outline-secondary w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-light w-100">Clear</a>
        </div>
    </form>

    {{-- Users Table --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Contact Info</th>
                    <th>Class / Details</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-circle" style="width: 40px; height: 40px; border-radius: 50%; background: {{ $user->avatar_color }}; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $user->name }}</div>
                                    <div class="text-muted small">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="badge bg-purple-soft text-purple" style="color: #6d28d9; background: #faf5ff; border: 1px solid #e9d5ff; padding: 5px 10px; border-radius: 6px; font-weight: 500;">Admin</span>
                            @elseif($user->role === 'teacher')
                                <span class="badge" style="color: #1d4ed8; background: #eff6ff; border: 1px solid #bfdbfe; padding: 5px 10px; border-radius: 6px; font-weight: 500;">Teacher</span>
                            @else
                                <span class="badge" style="color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 5px 10px; border-radius: 6px; font-weight: 500;">Student</span>
                            @endif
                        </td>
                        <td>
                            <div class="small"><i class="bi bi-telephone me-1 text-muted"></i>{{ $user->phone ?? 'N/A' }}</div>
                            <div class="text-muted small"><i class="bi bi-geo-alt me-1 text-muted"></i>{{ Str::limit($user->address ?? 'No address', 20) }}</div>
                        </td>
                        <td>
                            <span class="text-muted small">{{ $user->role === 'student' ? ($user->class_name ?? 'Unassigned Class') : 'PAI Instructor' }}</span>
                        </td>
                        <td>
                            @if($user->is_suspended)
                                <span class="badge bg-danger-soft text-danger" style="background:#fee2e2; color:#b91c1c; padding: 5px 10px; border-radius:6px; font-weight:500;">Suspended</span>
                            @else
                                <span class="badge bg-success-soft text-success" style="background:#ecfdf5; color:#047857; padding: 5px 10px; border-radius:6px; font-weight:500;">Active</span>
                            @endif

                            @if($user->role === 'teacher')
                                @if($user->is_approved)
                                    <span class="badge bg-success text-white ms-1" style="padding: 5px 10px; border-radius:6px; font-weight:500; font-size: .75rem;"><i class="bi bi-shield-check me-1"></i>Verified</span>
                                @else
                                    <span class="badge bg-warning text-dark ms-1" style="padding: 5px 10px; border-radius:6px; font-weight:500; font-size: .75rem;"><i class="bi bi-hourglass-split me-1"></i>Pending Admin</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                {{-- Teacher Verify Toggle --}}
                                @if($user->role === 'teacher')
                                <form action="{{ route('admin.users.toggle-approval', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $user->is_approved ? 'btn-outline-danger' : 'btn-outline-success' }}" title="{{ $user->is_approved ? 'Revoke Approval' : 'Approve/Verify Teacher' }}">
                                        @if($user->is_approved)
                                            <i class="bi bi-x-circle"></i>
                                        @else
                                            <i class="bi bi-check-circle"></i>
                                        @endif
                                    </button>
                                </form>
                                @endif

                                {{-- Edit Button --}}
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary" title="Edit Profile">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                {{-- Reset Password Trigger --}}
                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#resetPasswordModal-{{ $user->id }}" title="Reset Password">
                                    <i class="bi bi-key"></i>
                                </button>

                                {{-- Suspend Toggle --}}
                                <form action="{{ route('admin.users.toggle-suspension', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $user->is_suspended ? 'btn-outline-success' : 'btn-outline-danger' }}" title="{{ $user->is_suspended ? 'Unsuspend Account' : 'Suspend Account' }}">
                                        @if($user->is_suspended)
                                            <i class="bi bi-play-circle"></i>
                                        @else
                                            <i class="bi bi-slash-circle"></i>
                                        @endif
                                    </button>
                                </form>

                                {{-- Delete Button --}}
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete User">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>

                            {{-- Reset Password Modal --}}
                            <div class="modal fade" id="resetPasswordModal-{{ $user->id }}" tabindex="-1" aria-labelledby="resetPasswordModalLabel-{{ $user->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold" id="resetPasswordModalLabel-{{ $user->id }}"><i class="bi bi-key-fill text-warning me-2"></i>Reset Password</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('admin.users.reset-password', $user) }}" method="POST">
                                            @csrf
                                            <div class="modal-body text-start">
                                                <p>Resetting password for: <strong>{{ $user->name }}</strong> ({{ $user->email }})</p>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">New Password</label>
                                                    <input type="password" name="password" class="form-control" placeholder="At least 8 characters" required minlength="8">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Confirm New Password</label>
                                                    <input type="password" name="password_confirmation" class="form-control" placeholder="Re-type password" required minlength="8">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-warning">Reset Password</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-people-fill display-6 d-block mb-3 text-muted"></i>
                            No users found matching your filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection
