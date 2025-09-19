{{-- resources/views/admin/users/super-admins.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Super Administrators')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.super.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">System Users</li>
        <li class="breadcrumb-item active">Super Administrators</li>
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
                        <i class="fas fa-crown text-warning"></i> Super Administrators
                    </h1>
                    <p class="mb-0 text-muted">Manage system super administrators</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.users.all') }}" class="btn btn-info">
                        <i class="fas fa-users"></i> All Users
                    </a>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Add Super Admin
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Super Admins</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_super_admins']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-crown fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['active_super_admins']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Inactive</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['inactive_super_admins']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Recent Logins</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['recent_logins']) }}</div>
                            <div class="text-xs text-muted">Last 30 days</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sign-in-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.users.super-admins') }}" class="row g-3">
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search Super Administrators</label>
                            <input type="text" class="form-control" id="search" name="search"
                                   value="{{ request('search') }}" placeholder="Name, email, phone...">
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.users.super-admins') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Super Admins Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-crown"></i> Super Administrators ({{ $superAdmins->total() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($superAdmins->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Administrator</th>
                                        <th>Contact Information</th>
                                        <th>Status</th>
                                        <th>Last Login</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($superAdmins as $admin)
                                        <tr class="{{ $admin->status !== 'active' ? 'table-warning' : '' }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-lg bg-gradient-warning rounded-circle d-flex align-items-center justify-content-center me-3">
                                                        <i class="fas fa-crown fa-lg text-white"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">
                                                            <a href="{{ route('admin.users.show', $admin) }}" class="text-decoration-none">
                                                                {{ $admin->fname }} {{ $admin->lname }}
                                                            </a>
                                                            @if($admin->id === Auth::id())
                                                                <span class="badge badge-info ms-2">You</span>
                                                            @endif
                                                        </h6>
                                                        @if($admin->idnumber)
                                                            <small class="text-muted">ID: {{ $admin->idnumber }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <a href="mailto:{{ $admin->email }}" class="text-decoration-none d-block">
                                                        <i class="fas fa-envelope text-muted me-1"></i>
                                                        {{ $admin->email }}
                                                    </a>
                                                    @if($admin->phone)
                                                        <a href="tel:{{ $admin->phone }}" class="text-decoration-none">
                                                            <i class="fas fa-phone text-muted me-1"></i>
                                                            {{ $admin->phone }}
                                                        </a>
                                                    @endif
                                                    @if($admin->address)
                                                        <small class="d-block text-muted mt-1">
                                                            <i class="fas fa-map-marker-alt text-muted me-1"></i>
                                                            {{ Str::limit($admin->address, 30) }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($admin->id !== Auth::id())
                                                    <form action="{{ route('admin.users.toggle-status', $admin) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit"
                                                                class="btn btn-sm btn-outline-{{ $admin->status === 'active' ? 'success' : 'secondary' }} border-0"
                                                                onclick="return confirm('Toggle status for {{ $admin->fname }} {{ $admin->lname }}?')"
                                                                title="Click to toggle status">
                                                            <span class="badge badge-{{ $admin->status === 'active' ? 'success' : ($admin->status === 'suspended' ? 'danger' : 'warning') }}">
                                                                {{ ucfirst($admin->status) }}
                                                            </span>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="badge badge-{{ $admin->status === 'active' ? 'success' : ($admin->status === 'suspended' ? 'danger' : 'warning') }}">
                                                        {{ ucfirst($admin->status) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($admin->last_login)
                                                    <div>
                                                        <div>{{ $admin->last_login->format('M d, Y') }}</div>
                                                        <small class="text-muted">{{ $admin->last_login->diffForHumans() }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Never</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <div>{{ $admin->created_at->format('M d, Y') }}</div>
                                                    <small class="text-muted">{{ $admin->created_at->diffForHumans() }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.users.show', $admin) }}"
                                                       class="btn btn-sm btn-outline-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.users.edit', $admin) }}"
                                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($admin->id !== Auth::id())
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-warning"
                                                                title="Reset Password"
                                                                onclick="resetPassword({{ $admin->id }}, '{{ $admin->fname }} {{ $admin->lname }}')">
                                                            <i class="fas fa-key"></i>
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
                                    Showing {{ $superAdmins->firstItem() }} to {{ $superAdmins->lastItem() }} of {{ $superAdmins->total() }} super administrators
                                </small>
                            </div>
                            <div>
                                {{ $superAdmins->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        {{-- Empty State --}}
                        <div class="text-center py-5">
                            <i class="fas fa-crown fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Super Administrators Found</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['search', 'status']))
                                    No super administrators match your current filters.
                                    <a href="{{ route('admin.users.super-admins') }}" class="text-decoration-none">Clear filters</a>
                                @else
                                    No super administrators found in the system.
                                @endif
                            </p>
                            <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                                <i class="fas fa-user-plus"></i> Add Super Administrator
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Security Alert --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="fas fa-shield-alt"></i>
                <strong>Security Notice:</strong> Super administrators have unlimited system access.
                Only grant this role to trusted individuals. Regular security audits are recommended.
            </div>
        </div>
    </div>
</div>

{{-- Reset Password Modal --}}
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetPasswordModalLabel">
                    <i class="fas fa-key text-warning"></i> Reset Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Reset password for <strong id="adminName"></strong>?</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    A temporary password will be generated and sent to the administrator's email address.
                </div>
                <div class="mb-3">
                    <label for="newPassword" class="form-label">New Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="newPassword" placeholder="Enter new password" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="generatePassword()">
                            <i class="fas fa-random"></i> Generate
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm new password" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmReset">
                    <i class="fas fa-key"></i> Reset Password
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
    .border-left-success { border-left: 0.25rem solid #1cc88a !important; }
    .border-left-danger { border-left: 0.25rem solid #e74a3b !important; }
    .border-left-info { border-left: 0.25rem solid #36b9cc !important; }
    .text-gray-300 { color: #dddfeb !important; }
    .text-gray-800 { color: #5a5c69 !important; }
    .avatar-lg { width: 50px; height: 50px; }
    .bg-gradient-warning {
        background: linear-gradient(180deg, #f6c23e 10%, #dda20a 100%) !important;
    }
    .badge-success { background-color: #1cc88a !important; color: #fff !important; }
    .badge-warning { background-color: #f39c12 !important; color: #fff !important; }
    .badge-danger { background-color: #e74a3b !important; color: #fff !important; }
    .badge-info { background-color: #36b9cc !important; color: #fff !important; }
</style>
@endpush

@push('scripts')
<script>
    let currentAdminId = null;

    function resetPassword(adminId, adminName) {
        currentAdminId = adminId;
        document.getElementById('adminName').textContent = adminName;
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';
        new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
    }

    function generatePassword() {
        const length = 12;
        const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
        let password = "";
        for (let i = 0; i < length; i++) {
            password += charset.charAt(Math.floor(Math.random() * charset.length));
        }
        document.getElementById('newPassword').value = password;
        document.getElementById('confirmPassword').value = password;
    }

    document.getElementById('confirmReset').addEventListener('click', function() {
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (!newPassword || !confirmPassword) {
            alert('Please fill in both password fields.');
            return;
        }

        if (newPassword !== confirmPassword) {
            alert('Passwords do not match.');
            return;
        }

        if (newPassword.length < 8) {
            alert('Password must be at least 8 characters long.');
            return;
        }

        // Create and submit form to reset password
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${currentAdminId}/reset-password`;
        form.innerHTML = `
            @csrf
            <input type="hidden" name="password" value="${newPassword}">
            <input type="hidden" name="password_confirmation" value="${confirmPassword}">
        `;
        document.body.appendChild(form);
        form.submit();
    });

    // Auto-refresh every 5 minutes to show live updates
    setInterval(function() {
        if (!document.querySelector('.modal.show')) {
            location.reload();
        }
    }, 300000);
</script>
@endpush
@endsection
