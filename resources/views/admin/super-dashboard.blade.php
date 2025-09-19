{{-- resources/views/admin/super-dashboard.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="container-fluid">
    {{-- Impersonation Alert --}}
    @if(session('super_admin_id'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-user-secret me-2"></i>
            <strong>Impersonation Mode:</strong> You are currently logged in as a school admin.
            <a href="{{ route('admin.schools.return-super-admin') }}" class="btn btn-sm btn-outline-dark ms-2">
                <i class="fas fa-crown"></i> Return to Super Admin
            </a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-crown text-warning"></i> Super Administrator Dashboard
                    </h1>
                    <p class="mb-0 text-muted">System-wide overview and management</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.schools.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Add School
                    </a>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Add User
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Error Alert --}}
    @if(isset($error))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ $error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- System Statistics Cards --}}
    <div class="row mb-4">
        {{-- Total Schools --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Schools
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_schools']) }}</div>
                            <div class="text-xs text-muted">
                                <span class="text-success">{{ $stats['active_schools'] ?? 0 }} active</span>
                                @if(isset($stats['trial_schools']))
                                    • <span class="text-warning">{{ $stats['trial_schools'] }} trial</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-school fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Users --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_users']) }}</div>
                            <div class="text-xs text-muted">
                                <span class="text-info">{{ $stats['total_students'] ?? 0 }} students</span>
                                • <span class="text-warning">{{ $stats['total_instructors'] ?? 0 }} instructors</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Revenue --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</div>
                            <div class="text-xs text-muted">All time earnings</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Super Admins --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Super Admins
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['super_admins'] ?? 0 }}</div>
                            <div class="text-xs text-muted">{{ $stats['school_admins'] ?? 0 }} school admins</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-crown fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- School Management Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-school"></i> School Management
                    </h6>
                    <div>
                        <a href="{{ route('admin.schools.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                        <a href="{{ route('admin.schools.create') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-plus"></i> Add School
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($recentSchools) && $recentSchools->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>School</th>
                                        <th>Contact</th>
                                        <th>Users</th>
                                        <th>Status</th>
                                        <th>Subscription</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentSchools->take(5) as $school)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-soft-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        <i class="fas fa-school text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $school->name }}</h6>
                                                        <small class="text-muted">{{ $school->city }}, {{ $school->state }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <small class="d-block">{{ $school->email }}</small>
                                                    <small class="text-muted">{{ $school->phone }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="badge badge-info">{{ $school->users_count ?? 0 }} total</span>
                                                    @if(isset($school->students_count))
                                                        <small class="d-block text-muted">{{ $school->students_count }} students</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <form action="{{ route('admin.schools.toggle-status', $school) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-{{ $school->status === 'active' ? 'success' : 'secondary' }} border-0"
                                                            onclick="return confirm('Are you sure you want to {{ $school->status === 'active' ? 'deactivate' : 'activate' }} this school?')">
                                                        <span class="badge badge-{{ $school->status === 'active' ? 'success' : 'warning' }}">
                                                            {{ ucfirst($school->status) }}
                                                        </span>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ ($school->subscription_status ?? 'trial') === 'active' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($school->subscription_status ?? 'trial') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.schools.show', $school) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.schools.edit', $school) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($school->status === 'active')
                                                        <a href="{{ route('admin.schools.login-as', $school) }}" class="btn btn-sm btn-outline-warning" title="Login as School Admin">
                                                            <i class="fas fa-sign-in-alt"></i>
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
                            <i class="fas fa-school fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No schools found</h5>
                            <p class="text-muted">Get started by adding your first school.</p>
                            <a href="{{ route('admin.schools.create') }}" class="btn btn-success">
                                <i class="fas fa-plus"></i> Add First School
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Recent Activity --}}
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-clock"></i> Recent Activity
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($recentUsers) && $recentUsers->count() > 0)
                        <div class="timeline">
                            @foreach($recentUsers->take(8) as $user)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-soft-{{ $user->role === 'admin' ? 'warning' : 'info' }} rounded-circle d-flex align-items-center justify-content-center me-3">
                                            <i class="fas fa-{{ $user->role === 'admin' ? 'user-shield' : ($user->role === 'instructor' ? 'chalkboard-teacher' : 'user-graduate') }} text-{{ $user->role === 'admin' ? 'warning' : 'info' }}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">{{ $user->fname }} {{ $user->lname }}</h6>
                                            <small class="text-muted">
                                                New {{ $user->role }} joined {{ $user->school->name ?? 'No School' }}
                                                • {{ $user->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                        <div>
                                            <span class="badge badge-{{ $user->role === 'admin' ? 'warning' : 'info' }}">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No recent activity found.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-chart-pie"></i> System Overview
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">Active Schools</small>
                            <small class="font-weight-bold">{{ $stats['active_schools'] ?? 0 }}/{{ $stats['total_schools'] ?? 0 }}</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: {{ $stats['total_schools'] > 0 ? (($stats['active_schools'] ?? 0) / $stats['total_schools']) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">Active Users</small>
                            <small class="font-weight-bold">{{ $stats['active_users'] ?? 0 }}/{{ $stats['total_users'] ?? 0 }}</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: {{ $stats['total_users'] > 0 ? (($stats['active_users'] ?? 0) / $stats['total_users']) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">Paid Schools</small>
                            <small class="font-weight-bold">{{ $stats['paid_schools'] ?? 0 }}/{{ $stats['total_schools'] ?? 0 }}</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: {{ $stats['total_schools'] > 0 ? (($stats['paid_schools'] ?? 0) / $stats['total_schools']) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <hr>

                    <div class="text-center">
                        <a href="{{ route('admin.reports.system') }}" class="btn btn-sm btn-outline-info btn-block">
                            <i class="fas fa-chart-bar"></i> View Detailed Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Performing Schools --}}
    @if(isset($topSchools) && $topSchools->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-trophy"></i> Top Performing Schools
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($topSchools->take(5) as $index => $school)
                            <div class="col-md-{{ $index < 3 ? '4' : '6' }} mb-3">
                                <div class="card border-0 bg-light">
                                    <div class="card-body text-center py-3">
                                        <div class="mb-2">
                                            @if($index === 0)
                                                <i class="fas fa-crown fa-2x text-warning"></i>
                                            @elseif($index === 1)
                                                <i class="fas fa-medal fa-2x text-secondary"></i>
                                            @elseif($index === 2)
                                                <i class="fas fa-award fa-2x text-warning"></i>
                                            @else
                                                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <span class="text-white font-weight-bold">{{ $index + 1 }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <h6 class="card-title mb-1">{{ $school->name }}</h6>
                                        <small class="text-muted">{{ $school->users_count ?? 0 }} total users</small>
                                        <div class="mt-2">
                                            <a href="{{ route('admin.schools.show', $school) }}" class="btn btn-sm btn-outline-primary">
                                                View Details
                                            </a>
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
</div>

@push('styles')
<style>
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }
    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }
    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }
    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }
    .text-gray-300 {
        color: #dddfeb !important;
    }
    .text-gray-800 {
        color: #5a5c69 !important;
    }
    .badge-danger {
        background-color: #e74a3b !important;
        color: #fff !important;
    }
    .badge-warning {
        background-color: #f39c12 !important;
        color: #fff !important;
    }
    .badge-info {
        background-color: #36b9cc !important;
        color: #fff !important;
    }
    .badge-success {
        background-color: #1cc88a !important;
        color: #fff !important;
    }
    .avatar-sm {
        width: 40px;
        height: 40px;
    }
    .bg-soft-primary {
        background-color: rgba(78, 115, 223, 0.1) !important;
    }
    .bg-soft-warning {
        background-color: rgba(246, 194, 62, 0.1) !important;
    }
    .bg-soft-info {
        background-color: rgba(54, 185, 204, 0.1) !important;
    }
    .timeline-item {
        padding-left: 0;
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-refresh dashboard every 5 minutes
    setTimeout(() => {
        window.location.reload();
    }, 300000);

    // Confirmation for destructive actions
    document.querySelectorAll('[data-confirm]').forEach(function(element) {
        element.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush
@endsection
