{{-- resources/views/admin/courses/show.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Course Details - ' . $course->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-book"></i> Course Details
        </h1>
        <div>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Courses
            </a>
            <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" style="display: inline;"
                  onsubmit="return confirm('Are you sure you want to delete this course?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Course Information Card -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Course Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Course Name:</label>
                                <p class="text-gray-900">{{ $course->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Status:</label>
                                <p>
                                    @if($course->status === 'active')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Course Type:</label>
                                <p>
                                    <span class="badge badge-{{ $course->type_badge_color }}">
                                        {{ ucfirst($course->type) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Price:</label>
                                <p class="text-gray-900 font-weight-bold text-success">
                                    {{ $course->formatted_price }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Number of Lessons:</label>
                                <p class="text-gray-900">{{ $course->lessons }} lessons</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Duration per Lesson:</label>
                                <p class="text-gray-900">{{ $course->formatted_duration }}</p>
                            </div>
                        </div>
                        @if($course->description)
                        <div class="col-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Description:</label>
                                <p class="text-gray-900">{{ $course->description }}</p>
                            </div>
                        </div>
                        @endif
                        @if($course->requirements)
                        <div class="col-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Requirements:</label>
                                <p class="text-gray-900">{{ $course->requirements }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Assigned Instructors -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chalkboard-teacher"></i> Assigned Instructors ({{ $course->instructors->count() }})
                    </h6>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#assignInstructorModal">
                        <i class="fas fa-plus"></i> Assign Instructor
                    </button>
                </div>
                <div class="card-body">
                    @if($course->instructors->count() > 0)
                        <div class="row">
                            @foreach($course->instructors as $instructor)
                            <div class="col-md-6 mb-3">
                                <div class="card border-left-info">
                                    <div class="card-body py-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">{{ $instructor->fname }} {{ $instructor->lname }}</h6>
                                                <p class="text-muted mb-0">{{ $instructor->email }}</p>
                                                @if($instructor->phone)
                                                    <small class="text-muted">{{ $instructor->phone }}</small>
                                                @endif
                                            </div>
                                            <form method="POST" action="{{ route('admin.courses.remove-instructor', $course) }}"
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Remove this instructor from the course?')">
                                                @csrf
                                                <input type="hidden" name="instructor_id" value="{{ $instructor->id }}">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chalkboard-teacher fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No Instructors Assigned</h6>
                            <p class="text-muted">Assign instructors to teach this course.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Enrollments -->
            @if($course->schedules->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-alt"></i> Recent Enrollments
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Instructor</th>
                                    <th>Schedule Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($course->schedules->take(10) as $schedule)
                                <tr>
                                    <td>
                                        @if($schedule->student)
                                            {{ $schedule->student->fname }} {{ $schedule->student->lname }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($schedule->instructor)
                                            {{ $schedule->instructor->fname }} {{ $schedule->instructor->lname }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $schedule->scheduled_date->format('M d, Y g:i A') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $schedule->status === 'completed' ? 'success' : 'warning' }}">
                                            {{ ucfirst($schedule->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Stats Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar"></i> Course Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12 mb-3">
                            <div class="border-bottom pb-2 mb-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Enrollments
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['total_enrollments'] }}
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="border-bottom pb-2 mb-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Completed Lessons
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['completed_lessons'] }}
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="border-bottom pb-2 mb-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Active Students
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['active_students'] }}
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($stats['total_revenue'], 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course Status Toggle -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-toggle-on"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body text-center">
                    <form method="POST" action="{{ route('admin.courses.toggle-status', $course) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-{{ $course->status === 'active' ? 'warning' : 'success' }} btn-block">
                            <i class="fas fa-{{ $course->status === 'active' ? 'pause' : 'play' }}"></i>
                            {{ $course->status === 'active' ? 'Deactivate' : 'Activate' }} Course
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Instructor Modal -->
<div class="modal fade" id="assignInstructorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.courses.assign-instructor', $course) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign Instructor</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="instructor_id">Select Instructor</label>
                        <select name="instructor_id" id="instructor_id" class="form-control" required>
                            <option value="">Choose an instructor...</option>
                            @foreach($course->getAvailableInstructors() as $instructor)
                                @if(!$course->instructors->contains($instructor->id))
                                <option value="{{ $instructor->id }}">
                                    {{ $instructor->fname }} {{ $instructor->lname }} ({{ $instructor->email }})
                                </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Instructor</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
