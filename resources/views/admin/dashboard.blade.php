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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_schools']) }}</div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_users']) }}</div>
                            <div class="text-xs text-muted">{{ $stats['total_students'] }} students, {{ $stats['total_instructors'] }} instructors</div>
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
                                Fleet
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
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                            <span class="dropdown-item-text">View Full Report</span>
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
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Today's Lessons</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['todays_schedules'] }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Active Users</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['active_users'] }}</div>
                        </div>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $stats['total_vehicles'] > 0 ? ($stats['available_vehicles'] / $stats['total_vehicles']) * 100 : 0 }}%" aria-valuenow="{{ $stats['available_vehicles'] }}" aria-valuemin="0" aria-valuemax="{{ $stats['total_vehicles'] }}">
                            Fleet Availability
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity Row --}}
    <div class="row">
        {{-- Recent Schedules --}}
        <div class="col-xl-8 col-lg-12">
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
                                    <th>Instructor</th>
                                    <th>Course</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSchedules as $schedule)
                                <tr>
                                    <td>
                                        @php
                                            // Safe way to get student info
                                            $studentName = 'N/A';
                                            if (isset($schedule->student) && is_object($schedule->student)) {
                                                $studentName = ($schedule->student->fname ?? '') . ' ' . ($schedule->student->lname ?? '');
                                            } elseif (is_numeric($schedule->student)) {
                                                // If student is just an ID, fetch the user
                                                $student = \App\Models\User::find($schedule->student);
                                                $studentName = $student ? ($student->fname . ' ' . $student->lname) : "Student ID: {$schedule->student}";
                                            }
                                        @endphp
                                        <a href="{{ route('admin.schedules.show', $schedule) }}" class="text-decoration-none">
                                            {{ trim($studentName) ?: 'N/A' }}
                                        </a>
                                    </td>
                                    <td>
                                        @php
                                            // Safe way to get instructor info
                                            $instructorName = 'N/A';
                                            if (isset($schedule->instructor) && is_object($schedule->instructor)) {
                                                $instructorName = ($schedule->instructor->fname ?? '') . ' ' . ($schedule->instructor->lname ?? '');
                                            } elseif (is_numeric($schedule->instructor)) {
                                                // If instructor is just an ID, fetch the user
                                                $instructor = \App\Models\User::find($schedule->instructor);
                                                $instructorName = $instructor ? ($instructor->fname . ' ' . $instructor->lname) : "Instructor ID: {$schedule->instructor}";
                                            }
                                        @endphp
                                        {{ trim($instructorName) ?: 'N/A' }}
                                    </td>
                                    <td>
                                        @php
                                            // Safe way to get course info
                                            $courseName = 'N/A';
                                            if (isset($schedule->course) && is_object($schedule->course)) {
                                                $courseName = $schedule->course->name ?? 'N/A';
                                            } elseif (is_numeric($schedule->course)) {
                                                // If course is just an ID, fetch the course
                                                $course = \App\Models\Course::find($schedule->course);
                                                $courseName = $course ? $course->name : "Course ID: {$schedule->course}";
                                            }
                                        @endphp
                                        {{ $courseName }}
                                    </td>
                                    <td>
                                        @if($schedule->start)
                                            {{ \Carbon\Carbon::parse($schedule->start)->format('M d, Y H:i') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            // Safe status handling with proper fallback
                                            $status = $schedule->status ?? 'scheduled';
                                            $status = strtolower(trim($status)) ?: 'scheduled';

                                            // Map status to badge colors - using darker variants for better visibility
                                            $badgeClass = match($status) {
                                                'completed' => 'success',
                                                'cancelled', 'canceled' => 'danger',
                                                'missed' => 'danger',
                                                'in_progress', 'ongoing' => 'info',
                                                'scheduled', 'pending' => 'primary', // Changed from 'warning' to 'primary' for better visibility
                                                default => 'dark' // Changed from 'secondary' to 'dark'
                                            };

                                            // Display status
                                            $displayStatus = match($status) {
                                                'in_progress' => 'In Progress',
                                                'cancelled' => 'Cancelled',
                                                'canceled' => 'Cancelled',
                                                default => ucfirst($status)
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $badgeClass }}">
                                            {{ $displayStatus }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No recent schedules</td>
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
                                        @php
                                            // Safe way to get payment student info
                                            $paymentStudentName = 'N/A';
                                            if (isset($payment->invoice) && is_object($payment->invoice) && isset($payment->invoice->student) && is_object($payment->invoice->student)) {
                                                $paymentStudentName = ($payment->invoice->student->fname ?? '') . ' ' . ($payment->invoice->student->lname ?? '');
                                            } elseif (isset($payment->user) && is_object($payment->user)) {
                                                $paymentStudentName = ($payment->user->fname ?? '') . ' ' . ($payment->user->lname ?? '');
                                            } elseif (isset($payment->userId) && is_numeric($payment->userId)) {
                                                $user = \App\Models\User::find($payment->userId);
                                                $paymentStudentName = $user ? ($user->fname . ' ' . $user->lname) : "User ID: {$payment->userId}";
                                            }
                                        @endphp
                                        <a href="{{ route('admin.payments.show', $payment) }}" class="text-decoration-none">
                                            {{ trim($paymentStudentName) ?: 'N/A' }}
                                        </a>
                                    </td>
                                    <td>${{ number_format($payment->amount, 2) }}</td>
                                    <td>
                                        @php
                                            // Safe payment status handling
                                            $paymentStatus = strtolower(trim($payment->status ?? 'pending'));

                                            $paymentBadgeClass = match($paymentStatus) {
                                                'completed', 'paid' => 'success',
                                                'failed', 'cancelled' => 'danger',
                                                'pending', 'processing' => 'primary',
                                                'refunded' => 'info',
                                                default => 'dark'
                                            };

                                            $paymentDisplayStatus = ucfirst($paymentStatus);
                                        @endphp
                                        <span class="badge badge-{{ $paymentBadgeClass }}">
                                            {{ $paymentDisplayStatus }}
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
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
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
                    data: revenueData.map(item => parseFloat(item.total || 0)),
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
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                },
                elements: {
                    point: {
                        radius: 3,
                        hoverRadius: 5
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
