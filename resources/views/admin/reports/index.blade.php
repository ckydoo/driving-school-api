{{-- resources/views/admin/reports/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Reports')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-bar"></i> Reports Dashboard
            </h1>
            <p class="text-muted mb-0">View and generate comprehensive reports</p>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-download"></i> Export Reports
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('admin.reports.export', 'pdf') }}">
                    <i class="fas fa-file-pdf"></i> Export as PDF
                </a>
                <a class="dropdown-item" href="{{ route('admin.reports.export', 'excel') }}">
                    <i class="fas fa-file-excel"></i> Export as Excel
                </a>
                <a class="dropdown-item" href="{{ route('admin.reports.export', 'csv') }}">
                    <i class="fas fa-file-csv"></i> Export as CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Students
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-students">
                                Loading...
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Monthly Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="monthly-revenue">
                                Loading...
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Schedules
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="active-schedules">
                                Loading...
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Invoices
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="pending-invoices">
                                Loading...
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Categories -->
    <div class="row">
        <!-- Financial Reports -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line"></i> Financial Reports
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">Generate detailed financial reports including revenue, payments, and invoices.</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.reports.revenue') }}" class="btn btn-outline-primary">
                            <i class="fas fa-money-bill-wave"></i> Revenue Report
                        </a>
                        <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-file-invoice-dollar"></i> Invoice Report
                        </a>
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-success">
                            <i class="fas fa-credit-card"></i> Payment Report
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Reports -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-users"></i> User Reports
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">Generate reports for students, instructors, and user activities.</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.reports.students') }}" class="btn btn-outline-info">
                            <i class="fas fa-user-graduate"></i> Student Report
                        </a>
                        <a href="{{ route('admin.reports.instructors') }}" class="btn btn-outline-warning">
                            <i class="fas fa-chalkboard-teacher"></i> Instructor Report
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-users-cog"></i> User Management
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Reports -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-calendar-alt"></i> Schedule Reports
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">Analyze lesson schedules, attendance, and performance.</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.schedules.index') }}" class="btn btn-outline-success">
                            <i class="fas fa-calendar-check"></i> Schedule Overview
                        </a>
                        <a href="{{ route('admin.schedules.calendar') }}" class="btn btn-outline-primary">
                            <i class="fas fa-calendar"></i> Calendar View
                        </a>
                        <button class="btn btn-outline-secondary" onclick="generateAttendanceReport()">
                            <i class="fas fa-chart-pie"></i> Attendance Report
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicle Reports -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-car"></i> Vehicle Reports
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">Monitor vehicle usage, maintenance, and availability.</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.reports.vehicles') }}" class="btn btn-outline-warning">
                            <i class="fas fa-car-side"></i> Vehicle Usage Report
                        </a>
                        <a href="{{ route('admin.fleet.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-cogs"></i> Fleet Management
                        </a>
                        <button class="btn btn-outline-info" onclick="generateMaintenanceReport()">
                            <i class="fas fa-wrench"></i> Maintenance Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-clock"></i> Recent Report Activity
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Report Type</th>
                            <th>Generated By</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="recent-reports">
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                <i class="fas fa-spinner fa-spin"></i> Loading recent reports...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Custom Scripts -->
@section('scripts')
<script>
    // Load dashboard statistics
    function loadDashboardStats() {
        // You can implement AJAX calls to fetch real data
        // For now, showing placeholder data
        document.getElementById('total-students').textContent = '{{ \App\Models\User::where("role", "student")->count() }}';
        document.getElementById('monthly-revenue').textContent = '${{ number_format(\App\Models\Payment::whereMonth("created_at", date("m"))->sum("amount"), 2) }}';
        document.getElementById('active-schedules').textContent = '{{ \App\Models\Schedule::where("status", "scheduled")->count() }}';
        document.getElementById('pending-invoices').textContent = '{{ \App\Models\Invoice::where("status", "unpaid")->count() }}';
        
        loadRecentReports();
    }

    function loadRecentReports() {
        // Mock recent reports data
        const recentReportsTable = document.getElementById('recent-reports');
        recentReportsTable.innerHTML = `
            <tr>
                <td><i class="fas fa-chart-line text-primary"></i> Revenue Report</td>
                <td>{{ $currentUser->full_name }}</td>
                <td>{{ date('M d, Y') }}</td>
                <td><span class="badge badge-success">Completed</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="downloadReport('revenue')">
                        <i class="fas fa-download"></i> Download
                    </button>
                </td>
            </tr>
            <tr>
                <td><i class="fas fa-users text-info"></i> Student Report</td>
                <td>System</td>
                <td>{{ date('M d, Y', strtotime('-1 day')) }}</td>
                <td><span class="badge badge-success">Completed</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="downloadReport('students')">
                        <i class="fas fa-download"></i> Download
                    </button>
                </td>
            </tr>
        `;
    }

    function generateAttendanceReport() {
        // Implement attendance report generation
        alert('Attendance report generation functionality will be implemented soon.');
    }

    function generateMaintenanceReport() {
        // Implement maintenance report generation  
        alert('Maintenance report generation functionality will be implemented soon.');
    }

    function downloadReport(type) {
        // Implement report download
        window.location.href = '/admin/reports/export/' + type;
    }

    // Load stats when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadDashboardStats();
    });
</script>
@endsection
@endsection