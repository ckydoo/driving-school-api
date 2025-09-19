{{-- resources/views/admin/dashboard/super-admin.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="container-fluid">
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
                <div class="text-muted">
                    <i class="fas fa-clock"></i> {{ now()->format('M d, Y - H:i') }}
                </div>
            </div>
        </div>
    </div>

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
                                <span class="text-success">{{ $stats['active_schools'] }} active</span>
                                • <span class="text-warning">{{ $stats['trial_schools'] }} trial</span>
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
                                {{ $stats['total_students'] }} students • {{ $stats['total_instructors'] }} instructors
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Admin Users --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Administrators
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['super_admins'] + $stats['school_admins']) }}</div>
                            <div class="text-xs text-muted">
                                {{ $stats['super_admins'] }} super • {{ $stats['school_admins'] }} school
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Users --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Active Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['active_users']) }}</div>
                            <div class="text-xs text-muted">
                                {{ round(($stats['active_users'] / max($stats['total_users'], 1)) * 100, 1) }}% of total
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts and Analytics Row --}}
    <div class="row mb-4">
        {{-- Monthly Revenue Chart --}}
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Revenue Trends</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow">
                            <a class="dropdown-item" href="{{ route('admin.reports.revenue') }}">View Full Report</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Schools --}}
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Top Performing Schools</h6>
                    <a href="{{ route('admin.schools.index') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> View All
                    </a>
                </div>
                <div class="card-body">
                    @forelse($topSchools as $school)
                        <div class="d-flex align-items-center mb-3">
                            <div class="mr-3">
                                <div class="icon-circle bg-primary">
                                    <i class="fas fa-school text-white"></i>
                                </div>
                            </div>
                            <div class="font-weight-bold">
                                <div class="text-truncate">{{ $school->name }}</div>
                                <div class="small text-gray-500">
                                    {{ $school->users_count }} users • {{ $school->students_count }} students
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted">
                            <i class="fas fa-school fa-3x mb-3"></i>
                            <p>No schools found</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity Row --}}
    <div class="row mb-4">
        {{-- Recent Schools --}}
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recently Added Schools</h6>
                    <a href="{{ route('admin.schools.create') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> Add School
                    </a>
                </div>
                <div class="card-body">
                    @forelse($recentSchools as $school)
                        <div class="d-flex align-items-center mb-3">
                            <div class="mr-3">
                                <div class="icon-circle bg-{{ $school->status === 'active' ? 'success' : 'warning' }}">
                                    <i class="fas fa-school text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="font-weight-bold">{{ $school->name }}</div>
                                <div class="small text-gray-500">
                                    {{ $school->city }}, {{ $school->country }} •
                                    <span class="badge badge-{{ $school->status === 'active' ? 'success' : 'warning' }}">
                                        {{ ucfirst($school->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="small text-gray-500">{{ $school->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted">
                            <i class="fas fa-plus-circle fa-3x mb-3"></i>
                            <p>No recent schools</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent Users --}}
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recently Added Users</h6>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> View All
                    </a>
                </div>
                <div class="card-body">
                    @forelse($recentUsers as $user)
                        <div class="d-flex align-items-center mb-3">
                            <div class="mr-3">
                                <div class="icon-circle bg-{{ $user->role === 'admin' ? 'primary' : ($user->role === 'instructor' ? 'info' : 'secondary') }}">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="font-weight-bold">{{ $user->full_name }}</div>
                                <div class="small text-gray-500">
                                    {{ $user->school?->name ?? 'No School' }} •
                                    <span class="badge badge-{{ $user->role === 'admin' ? 'primary' : ($user->role === 'instructor' ? 'info' : 'secondary') }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="small text-gray-500">{{ $user->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted">
                            <i class="fas fa-user-plus fa-3x mb-3"></i>
                            <p>No recent users</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- System Actions Row --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.schools.create') }}" class="btn btn-success btn-block">
                                <i class="fas fa-plus"></i> Add School
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-block">
                                <i class="fas fa-user-plus"></i> Add User
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.reports.system') }}" class="btn btn-info btn-block">
                                <i class="fas fa-chart-bar"></i> System Reports
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.settings') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Chart
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Revenue',
                data: [
                    @foreach($monthlyRevenue as $month)
                        {{ $month->total ?? 0 }},
                    @endforeach
                ],
                borderColor: 'rgba(78, 115, 223, 1)',
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>
@endpush

@push('styles')
<style>
    .icon-circle {
        height: 2.5rem;
        width: 2.5rem;
        border-radius: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .chart-area {
        position: relative;
        height: 10rem;
        width: 100%;
    }
</style>
@endpush
@endsection
