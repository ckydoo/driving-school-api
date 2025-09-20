@extends('admin.layouts.app')

@section('title', 'System Reports')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">System Reports</h1>
                
            </div>
        </div>
    </div>

    <!-- System Statistics Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Schools</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($systemStats['total_schools']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-school fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($systemStats['total_users']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($revenueStats['total_revenue'], 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Schedules</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($systemStats['total_schedules']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Breakdown Row -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Breakdown</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 font-weight-bold text-info">{{ number_format($systemStats['total_students']) }}</div>
                                <div class="text-gray-600">Students</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 font-weight-bold text-warning">{{ number_format($systemStats['total_instructors']) }}</div>
                                <div class="text-gray-600">Instructors</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 font-weight-bold text-success">{{ number_format($systemStats['total_admins']) }}</div>
                                <div class="text-gray-600">Administrators</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 font-weight-bold text-primary">{{ number_format($systemStats['total_vehicles']) }}</div>
                                <div class="text-gray-600">Vehicles</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Financial Overview</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Monthly Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($revenueStats['monthly_revenue'], 2) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Invoices</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($revenueStats['pending_invoices'], 2) }}</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Invoices</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($systemStats['total_invoices']) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Payments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($systemStats['total_payments']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- School Performance Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">School Performance</h6>
                </div>
                <div class="card-body">
                    @if($schoolPerformance->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>School Name</th>
                                        <th>Total Users</th>
                                        <th>Students</th>
                                        <th>Instructors</th>
                                        <th>Vehicles</th>
                                        <th>Monthly Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schoolPerformance as $school)
                                    <tr>
                                        <td>{{ $school['name'] }}</td>
                                        <td>{{ number_format($school['total_users']) }}</td>
                                        <td>{{ number_format($school['students']) }}</td>
                                        <td>{{ number_format($school['instructors']) }}</td>
                                        <td>{{ number_format($school['total_vehicles']) }}</td>
                                        <td>${{ number_format($school['monthly_revenue'], 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No schools found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- System Health -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity (Last 7 Days)</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="h4 font-weight-bold text-primary">{{ $recentActivity['new_users'] }}</div>
                            <div class="text-gray-600">New Users</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 font-weight-bold text-success">{{ $recentActivity['new_schedules'] }}</div>
                            <div class="text-gray-600">New Schedules</div>
                        </div>
                        <div class="col-6">
                            <div class="h4 font-weight-bold text-info">{{ $recentActivity['new_payments'] }}</div>
                            <div class="text-gray-600">New Payments</div>
                        </div>
                        <div class="col-6">
                            <div class="h4 font-weight-bold text-warning">{{ $recentActivity['new_invoices'] }}</div>
                            <div class="text-gray-600">New Invoices</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Health</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Database Status:</span>
                            <span class="badge badge-{{ $systemHealth['database_status'] === 'healthy' ? 'success' : 'danger' }}">
                                {{ ucfirst($systemHealth['database_status']) }}
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Cache Status:</span>
                            <span class="badge badge-{{ $systemHealth['cache_status'] === 'healthy' ? 'success' : 'danger' }}">
                                {{ ucfirst($systemHealth['cache_status']) }}
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Storage:</span>
                            <span class="text-gray-600">{{ $systemHealth['storage_usage'] }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>PHP Version:</span>
                            <span class="text-gray-600">{{ $systemHealth['php_version'] }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between">
                            <span>Laravel Version:</span>
                            <span class="text-gray-600">{{ $systemHealth['laravel_version'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
