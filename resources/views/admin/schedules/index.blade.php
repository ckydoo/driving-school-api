{{-- resources/views/admin/schedules/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Schedules')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Schedules</h1>
            <p class="text-muted mb-0">Manage lesson schedules</p>
        </div>
        <div>
            <a href="{{ route('admin.schedules.calendar') }}" class="btn btn-info mr-2">
                <i class="fas fa-calendar-alt"></i> Calendar View
            </a>
            <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Schedule Lesson
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Schedules
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $schedules->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Completed
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $schedules->where('status', 'completed')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Scheduled
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $schedules->where('status', 'scheduled')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Cancelled
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $schedules->where('status', 'cancelled')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.schedules.index') }}" class="row align-items-end">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text"
                           id="search"
                           name="search"
                           class="form-control"
                           placeholder="Search student or instructor"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="instructor_id" class="form-label">Instructor</label>
                    <select id="instructor_id" name="instructor_id" class="form-control">
                        <option value="">All Instructors</option>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->id }}" {{ request('instructor_id') == $instructor->id ? 'selected' : '' }}>
                                {{ $instructor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if(isset($currentUser) && $currentUser->isSuperAdmin())
                <div class="col-md-2">
                    <label for="school_id" class="form-label">School</label>
                    <select id="school_id" name="school_id" class="form-control">
                        <option value="">All Schools</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date"
                           id="date_from"
                           name="date_from"
                           class="form-control"
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date"
                           id="date_to"
                           name="date_to"
                           class="form-control"
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Schedules Table -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Schedules List</h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-toggle="dropdown">
                    <i class="fas fa-sort"></i> Sort
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'start', 'sort_order' => 'desc']) }}">
                        Latest First
                    </a>
                    <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'start', 'sort_order' => 'asc']) }}">
                        Oldest First
                    </a>
                    <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'status', 'sort_order' => 'asc']) }}">
                        By Status
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($schedules->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="schedulesTable">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Instructor</th>
                                <th>Date & Time</th>
                                <th>Course</th>
                                <th>Vehicle</th>
                                <th>Type</th>
                                @if(isset($currentUser) && $currentUser->isSuperAdmin())
                                <th>School</th>
                                @endif
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedules as $schedule)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm rounded-circle bg-primary text-white me-2">
                                                {{ $schedule->student ? substr($schedule->student->full_name ?? 'U', 0, 1) : 'U' }}
                                            </div>
                                            <div>
                                                <strong>{{ $schedule->student->full_name ?? 'Unknown Student' }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $schedule->student->email ?? 'No email' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm rounded-circle bg-success text-white me-2">
                                                {{ $schedule->instructor ? substr($schedule->instructor->full_name ?? 'U', 0, 1) : 'U' }}
                                            </div>
                                            <div>
                                                <strong>{{ $schedule->instructor->full_name ?? 'Unknown Instructor' }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $schedule->start ? $schedule->start->format('M d, Y') : 'No date' }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $schedule->start ? $schedule->start->format('g:i A') : 'No time' }} -
                                                {{ $schedule->end ? $schedule->end->format('g:i A') : 'No end time' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $schedule->course->name ?? 'No Course' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            {{ $schedule->car->carplate ?? 'No Vehicle' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $schedule->class_type === 'practical' ? 'primary' : ($schedule->class_type === 'theory' ? 'info' : 'warning') }}">
                                            {{ ucfirst($schedule->class_type) }}
                                        </span>
                                    </td>
                                    @if(isset($currentUser) && $currentUser->isSuperAdmin())
                                    <td>
                                        @if($schedule->school)
                                            <span class="badge badge-info">{{ $schedule->school->name }}</span>
                                        @else
                                            <span class="text-muted">No school</span>
                                        @endif
                                    </td>
                                    @endif
                                    <td>
                                        <span class="badge badge-{{
                                            $schedule->status === 'completed' ? 'success' :
                                            ($schedule->status === 'in_progress' ? 'warning' :
                                            ($schedule->status === 'cancelled' ? 'danger' : 'primary'))
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $schedule->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.schedules.show', $schedule) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.schedules.edit', $schedule) }}"
                                               class="btn btn-sm btn-outline-secondary"
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($schedule->status === 'scheduled' && !$schedule->attended)
                                                <form action="{{ route('admin.schedules.mark-attended', $schedule) }}"
                                                      method="POST"
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-success"
                                                            title="Mark Attended"
                                                            onclick="return confirm('Mark this lesson as attended?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('admin.schedules.destroy', $schedule) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Delete"
                                                        onclick="return confirm('Are you sure you want to delete this schedule?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <p class="text-muted mb-0">
                            Showing {{ $schedules->firstItem() }} to {{ $schedules->lastItem() }}
                            of {{ $schedules->total() }} results
                        </p>
                    </div>
                    <div>
                        {{ $schedules->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-calendar fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-muted">No schedules found</h5>
                    <p class="text-muted mb-4">
                        @if(request()->hasAny(['search', 'status', 'instructor_id', 'date_from', 'date_to']))
                            Try adjusting your search criteria or
                            <a href="{{ route('admin.schedules.index') }}">clear all filters</a>.
                        @else
                            Get started by scheduling your first lesson.
                        @endif
                    </p>
                    @if(!request()->hasAny(['search', 'status', 'instructor_id', 'date_from', 'date_to']))
                        <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Schedule First Lesson
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.avatar {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}

.btn-group .btn {
    border-radius: 0;
}

.btn-group .btn:first-child {
    border-top-left-radius: 0.25rem;
    border-bottom-left-radius: 0.25rem;
}

.btn-group .btn:last-child {
    border-top-right-radius: 0.25rem;
    border-bottom-right-radius: 0.25rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form on filter change
    document.querySelectorAll('#status, #instructor_id').forEach(function(select) {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });

    // Clear search functionality
    const searchInput = document.getElementById('search');
    if (searchInput && searchInput.value) {
        const clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'btn btn-outline-secondary btn-sm ms-2';
        clearBtn.innerHTML = '<i class="fas fa-times"></i>';
        clearBtn.onclick = function() {
            searchInput.value = '';
            searchInput.form.submit();
        };
        searchInput.parentNode.appendChild(clearBtn);
    }
});
</script>
@endpush
