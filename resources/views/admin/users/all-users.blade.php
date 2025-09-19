{{-- resources/views/admin/users/all-users.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'All System Users')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.super.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">System Users</li>
        <li class="breadcrumb-item active">All Users</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-users text-primary"></i> All System Users
                    </h1>
                    <p class="mb-0 text-muted">Manage users across all schools in the system</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.users.super-admins') }}" class="btn btn-warning">
                        <i class="fas fa-crown"></i> Super Admins
                    </a>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Add User
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_users']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['active_users']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Super Admins</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['super_admins']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-crown fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">School Admins</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['school_admins']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Instructors</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['instructors']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Students</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['students']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- School Selector for Super Admin --}}
    @include('components.school-selector')

    {{-- Filters and Search --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.users.all') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search Users</label>
                            <input type="text" class="form-control" id="search" name="search"
                                   value="{{ request('search') }}" placeholder="Name, email, phone...">
                        </div>
                        <div class="col-md-2">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role">
                                <option value="">All Roles</option>
                                <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>School Admin</option>
                                <option value="instructor" {{ request('role') === 'instructor' ? 'selected' : '' }}>Instructor</option>
                                <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Student</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="school_id" class="form-label">School</label>
                            <select class="form-select" id="school_id" name="school_id">
                                <option value="">All Schools</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                                        {{ $school->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.users.all') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list"></i> All Users ({{ $users->total() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Contact</th>
                                        <th>Role</th>
                                        <th>School</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr class="{{ $user->status !== 'active' ? 'table-warning' : '' }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-soft-{{ $user->role === 'super_admin' ? 'danger' : ($user->role === 'admin' ? 'warning' : ($user->role === 'instructor' ? 'info' : 'success')) }} rounded-circle d-flex align-items-center justify-content-center me-3">
                                                        <i class="fas fa-{{ $user->role === 'super_admin' ? 'crown' : ($user->role === 'admin' ? 'user-shield' : ($user->role === 'instructor' ? 'chalkboard-teacher' : 'user-graduate')) }} text-{{ $user->role === 'super_admin' ? 'danger' : ($user->role === 'admin' ? 'warning' : ($user->role === 'instructor' ? 'info' : 'success')) }}"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">
                                                            <a href="{{ route('admin.users.show', $user) }}" class="text-decoration-none">
                                                                {{ $user->fname }} {{ $user->lname }}
                                                            </a>
                                                        </h6>
                                                        @if($user->idnumber)
                                                            <small class="text-muted">ID: {{ $user->idnumber }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <a href="mailto:{{ $user->email }}" class="text-decoration-none d-block">
                                                        <i class="fas fa-envelope text-muted me-1"></i>
                                                        {{ $user->email }}
                                                    </a>
                                                    @if($user->phone)
                                                        <a href="tel:{{ $user->phone }}" class="text-decoration-none">
                                                            <i class="fas fa-phone text-muted me-1"></i>
                                                            {{ $user->phone }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $user->role === 'super_admin' ? 'danger' : ($user->role === 'admin' ? 'warning' : ($user->role === 'instructor' ? 'info' : 'success')) }}">
                                                    {{ $user->role_display }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($user->school)
                                                    <a href="{{ route('admin.schools.show', $user->school) }}" class="text-decoration-none">
                                                        {{ $user->school->name }}
                                                    </a>
                                                    <small class="d-block text-muted">{{ $user->school->city }}, {{ $user->school->state }}</small>
                                                @else
                                                    <span class="text-muted">{{ $user->role === 'super_admin' ? 'System-wide' : 'No School' }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-{{ $user->status === 'active' ? 'success' : 'secondary' }} border-0"
                                                            onclick="return confirm('Toggle status for {{ $user->fname }} {{ $user->lname }}?')"
                                                            title="Click to toggle status">
                                                        <span class="badge badge-{{ $user->status === 'active' ? 'success' : ($user->status === 'suspended' ? 'danger' : 'warning') }}">
                                                            {{ ucfirst($user->status) }}
                                                        </span>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <div>
                                                    <div>{{ $user->created_at->format('M d, Y') }}</div>
                                                    <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.users.show', $user) }}"
                                                       class="btn btn-sm btn-outline-info" title="View User">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.users.edit', $user) }}"
                                                       class="btn btn-sm btn-outline-primary" title="Edit User">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($user->id !== Auth::id() && !($user->role === 'super_admin' && $user->id !== Auth::id()))
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-danger"
                                                                title="Delete User"
                                                                onclick="deleteUser({{ $user->id }}, '{{ $user->fname }} {{ $user->lname }}')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <small class="text-muted">
                                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users
                                </small>
                            </div>
                            <div>
                                {{ $users->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        {{-- Empty State --}}
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Users Found</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['search', 'role', 'status', 'school_id']))
                                    No users match your current filters.
                                    <a href="{{ route('admin.users.all') }}" class="text-decoration-none">Clear filters</a>
                                @else
                                    No users found in the system.
                                @endif
                            </p>
                            <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                                <i class="fas fa-user-plus"></i> Add First User
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .border-left-primary { border-left: 0.25rem solid #4e73df !important; }
    .border-left-success { border-left: 0.25rem solid #1cc88a !important; }
    .border-left-info { border-left: 0.25rem solid #36b9cc !important; }
    .border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
    .text-gray-300 { color: #dddfeb !important; }
    .text-gray-800 { color: #5a5c69 !important; }
    .avatar-sm { width: 40px; height: 40px; }
    .bg-soft-danger { background-color: rgba(231, 74, 59, 0.1) !important; }
    .bg-soft-warning { background-color: rgba(246, 194, 62, 0.1) !important; }
    .bg-soft-info { background-color: rgba(54, 185, 204, 0.1) !important; }
    .bg-soft-success { background-color: rgba(28, 200, 138, 0.1) !important; }
    .badge-danger { background-color: #e74a3b !important; color: #fff !important; }
    .badge-warning { background-color: #f39c12 !important; color: #fff !important; }
    .badge-info { background-color: #36b9cc !important; color: #fff !important; }
    .badge-success { background-color: #1cc88a !important; color: #fff !important; }
</style>
@endpush

@push('scripts')
<script>
    function deleteUser(userId, userName) {
        if (confirm(`Are you sure you want to delete ${userName}? This action cannot be undone.`)) {
            // Create and submit delete form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/users/${userId}`;
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Auto-refresh every 2 minutes to show live updates
    setInterval(function() {
        // Only refresh if no modals are open and no forms are being edited
        if (!document.querySelector('.modal.show') && !document.activeElement.matches('input, select, textarea')) {
            location.reload();
        }
    }, 120000);
</script>
@endpush
@endsection
