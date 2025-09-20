{{-- resources/views/admin/dashboard/school-admin.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'School Admin Dashboard')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-school text-primary"></i> 
                        {{ $school?->name ?? 'School Dashboard' }}
                    </h1>
                    <p class="mb-0 text-muted">Welcome back, {{ Auth::user()->fname }}! Here's your school overview.</p>
                </div>
                <div class="text-muted">
                    <i class="fas fa-clock"></i> {{ now()->format('M d, Y - H:i') }}
                </div>
            </div>
        </div>
    </div>
{{-- School Information Alert --}}
@if($school)
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i>
            <strong>School Info:</strong> {{ $school->city ?? 'N/A' }}, {{ $school->country ?? 'N/A' }} •
            Operating 
            @php
                $operatingDays = [];
                if ($school->operating_days) {
                    if (is_string($school->operating_days)) {
                        $decoded = json_decode($school->operating_days, true);
                        $operatingDays = is_array($decoded) ? $decoded : ['N/A'];
                    } elseif (is_array($school->operating_days)) {
                        $operatingDays = $school->operating_days;
                    } else {
                        $operatingDays = ['N/A'];
                    }
                } else {
                    $operatingDays = ['N/A'];
                }
            @endphp
            {{ implode(', ', $operatingDays) }} •
            {{ $school->start_time ?? 'N/A' }} - {{ $school->end_time ?? 'N/A' }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
</div>
@else
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-warning" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Warning:</strong> School information not available. Please contact administrator.
        </div>
    </div>
</div>
@endif

    {{-- School Statistics Cards --}}
    <div class="row mb-4">
        {{-- Students --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Students
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_students']) }}</div>
                            <div class="text-xs text-muted">
                                <span class="text-success">{{ $stats['active_students'] }} active</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Instructors --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Instructors
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_instructors']) }}</div>
                            <div class="text-xs text-muted">Teaching staff</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Fleet --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Fleet
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_vehicles']) }}</div>
                            <div class="text-xs text-muted">
                                <span class="text-success">{{ $stats['available_vehicles'] }} available</span>
                            </div>
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
                                Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($stats['total_revenue'], 2) }}</div>
                            <div class="text-xs text-muted">
                                <span class="text-danger">{{ $stats['pending_invoices'] }} pending</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lessons Progress --}}
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Lessons Progress</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow">
                            <a class="dropdown-item" href="{{ route('admin.schedules.index') }}">View All Schedules</a>
                            <a class="dropdown-item" href="{{ route('admin.schedules.calendar') }}">Calendar View</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="lessonsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 text-center mb-3">
                            <div class="h4 font-weight-bold text-primary">{{ $stats['total_schedules'] }}</div>
                            <div class="small text-gray-500">Total Lessons</div>
                        </div>
                        <div class="col-6 text-center mb-3">
                            <div class="h4 font-weight-bold text-success">{{ $stats['completed_lessons'] }}</div>
                            <div class="small text-gray-500">Completed</div>
                        </div>
                    </div>
                    <div class="progress mb-3">
                        @php
                            $completionRate = $stats['total_schedules'] > 0 ? ($stats['completed_lessons'] / $stats['total_schedules']) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $completionRate }}%">
                            {{ round($completionRate, 1) }}%
                        </div>
                    </div>
                    <div class="text-center">
                        <small class="text-muted">Lesson Completion Rate</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Activity Tables Row --}}
    <div class="row mb-4">
        {{-- Recent Students --}}
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Students</h6>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> Add Student
                    </a>
                </div>
                <div class="card-body">
                    @forelse($recentStudents as $student)
                        <div class="d-flex align-items-center mb-3">
                            <div class="mr-3">
                                <div class="icon-circle bg-primary">
                                    <i class="fas fa-graduation-cap text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="font-weight-bold">{{ $student->full_name }}</div>
                                <div class="small text-gray-500">
                                    {{ $student->email }} •
                                    <span class="badge badge-{{ $student->status === 'active' ? 'success' : 'warning' }}">
                                        {{ ucfirst($student->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="small text-gray-500">{{ $student->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted">
                            <i class="fas fa-user-plus fa-3x mb-3"></i>
                            <p>No students yet</p>
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">Add First Student</a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Upcoming Schedules --}}
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Upcoming Lessons</h6>
                    <a href="{{ route('admin.schedules.create') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> Schedule Lesson
                    </a>
                </div>
                <div class="card-body">
                    @if($upcomingSchedules->count() > 0)
    @foreach($upcomingSchedules as $schedule)
        <div class="d-flex align-items-center mb-3">
            <div class="mr-3">
                <div class="icon-circle bg-info">
                    <i class="fas fa-calendar text-white"></i>
                </div>
            </div>
            <div class="flex-grow-1">
                <div class="font-weight-bold">
                    {{-- SAFE: Handle both relationship loading and missing data --}}
                    @if($schedule->student && is_object($schedule->student))
                        {{ ($schedule->student->fname ?? '') . ' ' . ($schedule->student->lname ?? '') }}
                    @else
                        Student #{{ $schedule->student }}
                    @endif
                </div>
                <div class="small text-gray-500">
                    with 
                    @if($schedule->instructor && is_object($schedule->instructor))
                        {{ ($schedule->instructor->fname ?? '') . ' ' . ($schedule->instructor->lname ?? '') }}
                    @else
                        Instructor #{{ $schedule->instructor }}
                    @endif
                    • {{ $schedule->start ? $schedule->start->format('M d, Y g:i A') : 'No date' }}
                </div>
            </div>
            <div>
                <span class="badge badge-{{ $schedule->status === 'scheduled' ? 'primary' : 'success' }}">
                    {{ ucfirst($schedule->status) }}
                </span>
            </div>
        </div>
    @endforeach
@else
    <div class="text-center py-4">
        <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No upcoming lessons</h5>
        <p class="text-muted">Schedule some lessons to get started.</p>
        <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Schedule Lesson
        </a>
    </div>
@endif
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions Row --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-block">
                                <i class="fas fa-user-plus"></i> Add Student
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.schedules.create') }}" class="btn btn-success btn-block">
                                <i class="fas fa-calendar-plus"></i> Schedule Lesson
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.fleet.index') }}" class="btn btn-info btn-block">
                                <i class="fas fa-car"></i> Manage Fleet
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-chart-bar"></i> View Reports
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
    // Monthly Lessons Chart
    const ctx = document.getElementById('lessonsChart').getContext('2d');
    const lessonsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Lessons',
                data: [
                    @foreach($monthlyLessons as $month)
                        {{ $month->total ?? 0 }},
                    @endforeach
                    // Fill remaining months with 0
                    @for($i = count($monthlyLessons); $i < 12; $i++)
                        0,
                    @endfor
                ],
                backgroundColor: 'rgba(54, 185, 204, 0.8)',
                borderColor: 'rgba(54, 185, 204, 1)',
                borderWidth: 1
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
                        stepSize: 1
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

    .progress {
        height: 0.5rem;
        background-color: #e3e6f0;
    }

    .progress-bar {
        border-radius: 0.35rem;
    }
</style>
@endpush
@endsection
