{{-- resources/views/admin/schools/show.blade.php --}}

@extends('admin.layouts.app')

@section('title', $school->name . ' - School Details')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ Auth::user()->isSuperAdmin() ? route('admin.super.dashboard') : route('admin.dashboard') }}">
                Dashboard
            </a>
        </li>
        @if(Auth::user()->isSuperAdmin())
            <li class="breadcrumb-item"><a href="{{ route('admin.schools.index') }}">Schools</a></li>
        @endif
        <li class="breadcrumb-item active">{{ $school->name }}</li>
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
                        <i class="fas fa-school text-primary"></i> {{ $school->name }}
                    </h1>
                    <p class="mb-0 text-muted">{{ $school->city }}, {{ $school->state }}</p>
                </div>
                <div class="d-flex gap-2">
                    @if(Auth::user()->isSuperAdmin())
                        <a href="{{ route('admin.schools.edit', $school) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit School
                        </a>
                        @if($school->status === 'active' && $schoolAdmins->count() > 0)
                            <a href="{{ route('admin.schools.login-as', $school) }}"
                               class="btn btn-warning"
                               onclick="return confirm('Login as {{ $school->name }} admin?')">
                                <i class="fas fa-sign-in-alt"></i> Login As Admin
                            </a>
                        @endif
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-cog"></i> Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <form action="{{ route('admin.schools.toggle-status', $school) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item"
                                                onclick="return confirm('{{ $school->status === 'active' ? 'Deactivate' : 'Activate' }} this school?')">
                                            <i class="fas fa-{{ $school->status === 'active' ? 'pause' : 'play' }}"></i>
                                            {{ $school->status === 'active' ? 'Deactivate' : 'Activate' }} School
                                        </button>
                                    </form>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button type="button" class="dropdown-item text-danger" onclick="deleteSchool()">
                                        <i class="fas fa-trash"></i> Delete School
                                    </button>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('admin.schools.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Schools
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- School Status Alert --}}
    @if($school->status !== 'active')
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>School Status:</strong> This school is currently {{ $school->status }}.
            @if(Auth::user()->isSuperAdmin())
                <a href="#" onclick="event.preventDefault(); document.getElementById('toggle-status-form').submit();" class="alert-link">
                    Click here to activate it.
                </a>
                <form id="toggle-status-form" action="{{ route('admin.schools.toggle-status', $school) }}" method="POST" style="display: none;">
                    @csrf
                </form>
            @endif
        </div>
    @endif

    <div class="row">
        {{-- School Information --}}
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> School Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold text-muted">School Name:</td>
                                    <td>{{ $school->name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Email:</td>
                                    <td>
                                        <a href="mailto:{{ $school->email }}" class="text-decoration-none">
                                            {{ $school->email }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Phone:</td>
                                    <td>
                                        <a href="tel:{{ $school->phone }}" class="text-decoration-none">
                                            {{ $school->phone }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">License Number:</td>
                                    <td>{{ $school->license_number ?: 'Not provided' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold text-muted">Address:</td>
                                    <td>{{ $school->address }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">City, State:</td>
                                    <td>{{ $school->city }}, {{ $school->state }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">ZIP Code:</td>
                                    <td>{{ $school->zip_code }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Status:</td>
                                    <td>
                                        <span class="badge badge-{{ $school->status === 'active' ? 'success' : ($school->status === 'suspended' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($school->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Subscription:</td>
                                    <td>
                                        <span class="badge badge-{{ ($school->subscription_status ?? 'trial') === 'active' ? 'success' : 'warning' }}">
                                            {{ ucfirst($school->subscription_status ?? 'trial') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Created:</td>
                                    <td>{{ $school->created_at->format('M d, Y') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics --}}
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-chart-pie"></i> School Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="text-primary mb-0">{{ $stats['total_users'] }}</h4>
                                <small class="text-muted">Total Users</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="text-success mb-0">{{ $stats['active_users'] }}</h4>
                                <small class="text-muted">Active Users</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="text-info mb-0">{{ $stats['students'] }}</h4>
                                <small class="text-muted">Students</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="text-warning mb-0">{{ $stats['instructors'] }}</h4>
                                <small class="text-muted">Instructors</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="border rounded p-3">
                                <h4 class="text-danger mb-0">{{ $stats['admins'] }}</h4>
                                <small class="text-muted">Administrators</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- School Administrators --}}
    @if($schoolAdmins->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-user-shield"></i> School Administrators
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($schoolAdmins as $admin)
                            <div class="col-md-6 mb-3">
                                <div class="card border-left-warning">
                                    <div class="card-body py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-soft-warning rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="fas fa-user-shield text-warning"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">{{ $admin->fname }} {{ $admin->lname }}</h6>
                                                <small class="text-muted">
                                                    <a href="mailto:{{ $admin->email }}" class="text-decoration-none">
                                                        {{ $admin->email }}
                                                    </a>
                                                </small>
                                                @if($admin->phone)
                                                    <small class="d-block text-muted">
                                                        <a href="tel:{{ $admin->phone }}" class="text-decoration-none">
                                                            {{ $admin->phone }}
                                                        </a>
                                                    </small>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="badge badge-{{ $admin->status === 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($admin->status) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Recent Users --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-users"></i> Recent Users
                    </h6>
                    <div>
                        <a href="{{ route('admin.users.index') }}{{ Auth::user()->isSuperAdmin() ? '?school_filter=' . $school->id : '' }}"
                           class="btn btn-sm btn-outline-info">
                            View All Users
                        </a>
                        @if(Auth::user()->isSuperAdmin() || Auth::user()->school_id === $school->id)
                            <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-success">
                                <i class="fas fa-plus"></i> Add User
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($recentUsers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentUsers as $user)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-soft-{{ $user->role === 'admin' ? 'warning' : ($user->role === 'instructor' ? 'info' : 'success') }} rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        <i class="fas fa-{{ $user->role === 'admin' ? 'user-shield' : ($user->role === 'instructor' ? 'chalkboard-teacher' : 'user-graduate') }} text-{{ $user->role === 'admin' ? 'warning' : ($user->role === 'instructor' ? 'info' : 'success') }}"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $user->fname }} {{ $user->lname }}</h6>
                                                        @if($user->phone)
                                                            <small class="text-muted">{{ $user->phone }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="mailto:{{ $user->email }}" class="text-decoration-none">
                                                    {{ $user->email }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $user->role === 'admin' ? 'warning' : ($user->role === 'instructor' ? 'info' : 'success') }}">
                                                    {{ ucfirst($user->role) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $user->status === 'active' ? 'success' : ($user->status === 'suspended' ? 'danger' : 'secondary') }}">
                                                    {{ ucfirst($user->status) }}
                                                </span>
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
                                                    @if(Auth::user()->isSuperAdmin() || Auth::user()->school_id === $school->id)
                                                        <a href="{{ route('admin.users.edit', $user) }}"
                                                           class="btn btn-sm btn-outline-primary" title="Edit User">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Users Found</h5>
                            <p class="text-muted">This school doesn't have any users yet.</p>
                            @if(Auth::user()->isSuperAdmin() || Auth::user()->school_id === $school->id)
                                <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Add First User
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
@if(Auth::user()->isSuperAdmin())
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger"></i> Confirm School Deletion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong>{{ $school->name }}</strong>?</p>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> This action cannot be undone. All school data, users, and related information will be permanently deleted.
                </div>
                <p class="text-muted">To confirm, type the school name below:</p>
                <input type="text" class="form-control" id="confirmName" placeholder="School name">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.schools.destroy', $school) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" id="confirmDelete" disabled>
                        <i class="fas fa-trash"></i> Delete School
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@push('styles')
<style>
    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }
    .avatar-sm {
        width: 40px;
        height: 40px;
    }
    .bg-soft-warning {
        background-color: rgba(246, 194, 62, 0.1) !important;
    }
    .bg-soft-info {
        background-color: rgba(54, 185, 204, 0.1) !important;
    }
    .bg-soft-success {
        background-color: rgba(28, 200, 138, 0.1) !important;
    }
    .badge-success {
        background-color: #1cc88a !important;
        color: #fff !important;
    }
    .badge-warning {
        background-color: #f6c23e !important;
        color: #fff !important;
    }
    .badge-danger {
        background-color: #e74a3b !important;
        color: #fff !important;
    }
    .badge-info {
        background-color: #36b9cc !important;
        color: #fff !important;
    }
    .badge-secondary {
        background-color: #858796 !important;
        color: #fff !important;
    }
</style>
@endpush

@push('scripts')
<script>
    function deleteSchool() {
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    // Enable delete button only when school name is correctly typed
    document.getElementById('confirmName').addEventListener('input', function() {
        const schoolName = '{{ $school->name }}';
        const confirmName = this.value;
        const deleteBtn = document.getElementById('confirmDelete');

        if (confirmName === schoolName) {
            deleteBtn.disabled = false;
            deleteBtn.classList.remove('btn-secondary');
            deleteBtn.classList.add('btn-danger');
        } else {
            deleteBtn.disabled = true;
            deleteBtn.classList.remove('btn-danger');
            deleteBtn.classList.add('btn-secondary');
        }
    });
</script>
@endpush
@endsection
