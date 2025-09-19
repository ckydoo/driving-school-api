{{-- resources/views/admin/dashboard.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                    <p class="mb-0 text-muted">Welcome back, {{ Auth::user()->fname }}!</p>
                </div>
                <div class="text-muted">
                    <i class="fas fa-clock"></i> {{ now()->format('M d, Y - H:i') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        {{-- Schools --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Schools
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_schools'] }}</div>
                            <div class="text-xs text-muted">{{ $stats['active_schools'] }} active</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-school fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Users --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_users'] }}</div>
                            <div class="text-xs text-muted">
                                <span class="text-success">{{ $stats['total_students'] }}</span> students, 
                                <span class="text-info">{{ $stats['total_instructors'] }}</span> instructors
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Vehicles --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Fleet Vehicles
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_vehicles'] }}</div>
                            <div class="text-xs text-muted">{{ $stats['available_vehicles'] }} available</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-car fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Revenue --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($stats['total_revenue'], 2) }}</div>
                            <div class="text-xs text-muted">{{ $stats['pending_invoices'] }} pending invoices</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row mb-4">
        {{-- Revenue Chart --}}
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Revenue</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                            <a class="dropdown-item" href="{{ route('admin.reports.revenue') }}">View Full Report</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Today's Overview</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <div class="text-center mb-3">
                                <div class="text-2xl font-weight-bold text-primary">{{ $stats['todays_schedules'] }}</div>
                                <div class="text-xs text-muted">Today's Lessons</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="text-lg font-weight-bold text-success">{{ $stats['active_users'] }}</div>
                            <div class="text-xs text-muted">Active Users</div>
                        </div>
                        <div class="col-6">
                            <div class="text-lg font-weight-bold text-info">{{ $stats['available_vehicles'] }}</div>
                            <div class="text-xs text-muted">Available Cars</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm btn-block mb-2">
                        <i class="fas fa-user-plus"></i> Add New User
                    </a>
                    <a href="{{ route('admin.schools.create') }}" class="btn btn-success btn-sm btn-block mb-2">
                        <i class="fas fa-school"></i> Add New School
                    </a>
                    <a href="{{ route('admin.fleet.create') }}" class="btn btn-info btn-sm btn-block mb-2">
                        <i class="fas fa-car"></i> Add New Vehicle
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-warning btn-sm btn-block">
                        <i class="fas fa-chart-bar"></i> View Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity Tables --}}
    <div class="row">
        {{-- Recent Users --}}
        <div class="col-xl-4 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Users</h6>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentUsers as $user)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.users.show', $user) }}" class="text-decoration-none">
                                            {{ $user->full_name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'instructor' ? 'info' : 'success') }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No recent users</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Schedules --}}
        <div class="col-xl-4 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Schedules</h6>
                    <a href="{{ route('admin.schedules.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSchedules as $schedule)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.schedules.show', $schedule) }}" class="text-decoration-none">
                                            {{ $schedule->student->full_name ?? 'N/A' }}
                                        </a>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($schedule->start)->format('M d') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $schedule->status === 'completed' ? 'success' : ($schedule->status === 'cancelled' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($schedule->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No recent schedules</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Payments --}}
        <div class="col-xl-4 col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Payments</h6>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayments as $payment)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.payments.show', $payment) }}" class="text-decoration-none">
                                            {{ $payment->student->full_name ?? 'N/A' }}
                                        </a>
                                    </td>
                                    <td>${{ number_format($payment->amount, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'failed' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No recent payments</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueData = @json($monthlyRevenue);
    
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: revenueData.map(item => {
                const date = new Date(item.month + '-01');
                return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            }),
            datasets: [{
                label: 'Revenue ($)',
                data: revenueData.map(item => item.total),
                borderColor: 'rgb(78, 115, 223)',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
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
                            return ' + value.toLocaleString();'
                        }
                    }
                }
            },
            elements: {
                point: {
                    radius: 3,
                    hoverRadius: 3
                }
            }
        }
    });
});
</script>
@endpush
@endsection