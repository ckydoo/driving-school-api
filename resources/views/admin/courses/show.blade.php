{{-- resources/views/admin/courses/show.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Course Details - ' . $course->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.courses.index') }}" class="text-decoration-none">Courses</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $course->name }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-graduation-cap"></i> {{ $course->name }}
            </h1>
        </div>
        <div class="btn-group" role="group">
            <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-edit"></i> Edit Course
            </a>
            <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="{{ route('admin.courses.index') }}">
                    <i class="fas fa-list"></i> All Courses
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('admin.courses.destroy', $course) }}"
                      onsubmit="return confirm('Are you sure you want to delete this course? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-trash"></i> Delete Course
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Enrollments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_enrollments'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                Completed Lessons
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['completed_lessons'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                Active Students
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['active_students'] ?? 0 }}
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
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($stats['total_revenue'] ?? 0, 2) }}
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

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Course Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Course Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Course Name:</label>
                                <p class="text-gray-900 mb-0">{{ $course->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark">Status:</label>
                                <p class="mb-0">
                                    <span class="badge badge-{{ $course->status === 'active' ? 'success' : 'secondary' }} badge-lg text-dark">
                                        {{ ucfirst($course->status) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Price:</label>
                                <p class="text-success h5 mb-0">${{ number_format($course->price, 2) }}</p>
                            </div>
                        </div>
                        @if(auth()->user()->role === 'super_admin' && $course->school)
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">School:</label>
                                <p class="mb-0">
                                    <a href="{{ route('admin.schools.show', $course->school) }}"
                                       class="text-primary text-decoration-none">
                                        {{ $course->school->name }}
                                    </a>
                                </p>
                            </div>
                        </div>
                        @endif
                        @if($course->description)
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Description:</label>
                                <p class="text-gray-900 mb-0">{{ $course->description }}</p>
                            </div>
                        </div>
                        @endif
                        @if($course->requirements)
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Requirements:</label>
                                <p class="text-gray-900 mb-0">{{ $course->requirements }}</p>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Created:</label>
                                <p class="text-muted mb-0">{{ $course->created_at->format('M d, Y g:i A') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Last Updated:</label>
                                <p class="text-muted mb-0">{{ $course->updated_at->format('M d, Y g:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assigned Instructors -->
            @if(isset($course->instructors) && is_countable($course->instructors) && $course->instructors->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chalkboard-teacher"></i> Assigned Instructors
                        <span class="badge badge-secondary ml-2">{{ $course->instructors->count() }}</span>
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-plus"></i> Assign Instructor
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($course->instructors as $instructor)
                            @if(is_object($instructor) && isset($instructor->fname))
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="avatar-circle bg-primary text-white mr-3">
                                        {{ strtoupper(substr($instructor->fname ?? 'U', 0, 1)) }}{{ strtoupper(substr($instructor->lname ?? '', 0, 1)) }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $instructor->fname ?? 'Unknown' }} {{ $instructor->lname ?? '' }}</h6>
                                        <p class="text-muted mb-1">{{ $instructor->email ?? 'No email' }}</p>
                                        <p class="text-muted mb-0">{{ $instructor->phone ?? 'No phone' }}</p>
                                    </div>
                                    <div>
                                        @if(isset($instructor->id))
                                        <a href="{{ route('admin.users.show', $instructor->id) }}"
                                           class="btn btn-sm btn-outline-info" title="View Profile">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Recent Enrollments -->
            @if(isset($course->schedules) && is_countable($course->schedules) && $course->schedules->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-alt"></i> Recent Enrollments
                        <span class="badge badge-secondary ml-2">{{ $course->schedules->count() }}</span>
                    </h6>
                    <a href="{{ route('admin.schedules.create') }}?course_id={{ $course->id }}"
                       class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-plus"></i> Add Schedule
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Instructor</th>
                                    <th>Schedule Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($course->schedules->take(10) as $schedule)
                                <tr>
                                    <td>
                                        @if(is_object($schedule->student) && isset($schedule->student->fname))
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-info text-white mr-2 small">
                                                    {{ strtoupper(substr($schedule->student->fname ?? 'U', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="font-weight-bold">
                                                        {{ $schedule->student->fname ?? 'Unknown' }} {{ $schedule->student->lname ?? '' }}
                                                    </div>
                                                    <small class="text-muted">{{ $schedule->student->email ?? 'No email' }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(is_object($schedule->instructor) && isset($schedule->instructor->fname))
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-success text-white mr-2 small">
                                                    {{ strtoupper(substr($schedule->instructor->fname ?? 'U', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="font-weight-bold">
                                                        {{ $schedule->instructor->fname ?? 'Unknown' }} {{ $schedule->instructor->lname ?? '' }}
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(isset($schedule->scheduled_date))
                                        <div>
                                            <div class="font-weight-bold">
                                                {{ \Carbon\Carbon::parse($schedule->scheduled_date)->format('M d, Y') }}
                                            </div>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($schedule->scheduled_date)->format('g:i A') }}
                                            </small>
                                        </div>
                                        @else
                                            <span class="text-muted">No date</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{
                                            ($schedule->status ?? '') === 'completed' ? 'success' :
                                            (($schedule->status ?? '') === 'scheduled' ? 'primary' :
                                            (($schedule->status ?? '') === 'cancelled' ? 'danger' : 'warning'))
                                        }}">
                                            {{ ucfirst($schedule->status ?? 'Unknown') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if(isset($schedule->id))
                                        <a href="{{ route('admin.schedules.show', $schedule->id) }}"
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($course->schedules->count() > 10)
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.schedules.index') }}?course_id={{ $course->id }}"
                           class="btn btn-outline-primary">
                            View All Schedules ({{ $course->schedules->count() }})
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Course Performance -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line"></i> Performance Metrics
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Completion Rate -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="font-weight-bold">Completion Rate</span>
                            @php
                                $completionRate = ($stats['total_enrollments'] ?? 0) > 0
                                    ? round((($stats['completed_lessons'] ?? 0) / ($stats['total_enrollments'] ?? 1)) * 100)
                                    : 0;
                            @endphp
                            <span class="text-success">{{ $completionRate }}%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: {{ $completionRate }}%"
                                 aria-valuenow="{{ $completionRate }}"
                                 aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Per Student -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <span class="font-weight-bold">Revenue per Student:</span>
                            <span class="text-primary">
                                ${{ ($stats['active_students'] ?? 0) > 0
                                    ? number_format(($stats['total_revenue'] ?? 0) / ($stats['active_students'] ?? 1), 2)
                                    : '0.00' }}
                            </span>
                        </div>
                    </div>

                    <!-- Average Lesson Cost -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <span class="font-weight-bold">Cost per Lesson:</span>
                            <span class="text-info">
                                ${{ $course->lessons > 0
                                    ? number_format($course->price / $course->lessons, 2)
                                    : '0.00' }}
                            </span>
                        </div>
                    </div>

                    <!-- Course Popularity -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <span class="font-weight-bold">Popularity Rank:</span>
                            <span class="badge badge-warning">
                                @php
                                    // This would need to be calculated in the controller
                                    $rank = 'N/A';
                                @endphp
                                {{ $rank }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.schedules.create') }}?course_id={{ $course->id }}"
                           class="btn btn-primary btn-sm mb-2">
                            <i class="fas fa-calendar-plus"></i> Schedule New Lesson
                        </a>
                        <a href="{{ route('admin.courses.edit', $course) }}"
                           class="btn btn-outline-secondary btn-sm mb-2">
                            <i class="fas fa-edit"></i> Edit Course Details
                        </a>
                        <button type="button" class="btn btn-outline-info btn-sm mb-2"
                                onclick="toggleStatus({{ $course->id }})">
                            <i class="fas fa-toggle-{{ $course->status === 'active' ? 'on' : 'off' }}"></i>
                            {{ $course->status === 'active' ? 'Deactivate' : 'Activate' }} Course
                        </button>
                        <a href="{{ route('admin.courses.index') }}"
                           class="btn btn-outline-dark btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Courses
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Recent Activity
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($course->schedules) && is_countable($course->schedules) && $course->schedules->count() > 0)
                        @foreach($course->schedules->sortByDesc('created_at')->take(5) as $activity)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-circle bg-light text-dark mr-3 small">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="font-weight-bold small">New enrollment</div>
                                <div class="text-muted small">
                                    @if(is_object($activity->student) && isset($activity->student->fname))
                                        {{ $activity->student->fname ?? 'Student' }} {{ $activity->student->lname ?? '' }}
                                    @else
                                        Student
                                    @endif
                                </div>
                                <div class="text-muted small">
                                    @if(isset($activity->created_at))
                                        {{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}
                                    @else
                                        Recently
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted small">No recent activity</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleStatus(courseId) {
    if (confirm('Are you sure you want to change the course status?')) {
        // This would need to be implemented as a route in your controller
        fetch(`/admin/courses/${courseId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating course status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating course status');
        });
    }
}

// Initialize tooltips
$(document).ready(function() {
    $('[title]').tooltip();
});

// Flash message auto-hide
@if(session()->has('success') || session()->has('error') || session()->has('warning'))
setTimeout(function() {
    $('.alert').fadeOut('slow');
}, 5000);
@endif
</script>
@endpush

@push('styles')
<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}

.avatar-circle.small {
    width: 30px;
    height: 30px;
    font-size: 12px;
}

.badge-lg {
    font-size: 0.875em;
    padding: 0.5em 0.75em;
}

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

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.text-gray-900 {
    color: #3a3b45 !important;
}

.card {
    transition: box-shadow 0.15s ease-in-out;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.progress {
    background-color: #f8f9fc;
}

.btn-group .dropdown-toggle::after {
    margin-left: 0.5em;
}

.breadcrumb {
    background-color: transparent;
    padding: 0;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: ">";
    color: #858796;
}

.d-grid {
    display: grid;
}

.gap-2 {
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
        width: 100%;
    }

    .btn-group .btn {
        border-radius: 0.35rem !important;
        margin-bottom: 0.5rem;
    }

    .dropdown-menu {
        position: relative !important;
        transform: none !important;
        float: none;
        display: block;
        margin-top: 0.5rem;
    }
}
</style>
@endpush
@endsection
