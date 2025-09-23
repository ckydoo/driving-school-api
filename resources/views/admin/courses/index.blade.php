{{-- resources/views/admin/courses/index.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Course Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-graduation-cap"></i> Course Management
        </h1>
        <a href="{{ route('admin.courses.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus"></i> Add New Course
        </a>
    </div>

    <!-- Quick Stats Cards -->
    @if(isset($courses) && $courses->count() > 0)
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Courses
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $courses->total() ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
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
                                Active Courses
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $courses->where('status', 'active')->count() ?? 0 }}
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
                                Total Enrollments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $totalEnrollments = 0;
                                    if (isset($courses)) {
                                        foreach ($courses as $course) {
                                            if (method_exists($course, 'schedules') && $course->schedules) {
                                                $totalEnrollments += $course->schedules->count();
                                            }
                                        }
                                    }
                                @endphp
                                {{ $totalEnrollments }}
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
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Average Price
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ $courses->avg('price') ? number_format($courses->avg('price'), 2) : '0.00' }}
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
    @endif

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Search & Filters
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.courses.index') }}" class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="search" class="sr-only">Search</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <input type="text"
                                   class="form-control"
                                   id="search"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Search courses by name or description...">
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="status" class="sr-only">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                    </div>
                </div>

                @if(auth()->user()->role === 'super_admin' && isset($schools) && $schools->count() > 0)
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="school_id" class="sr-only">School</label>
                        <select class="form-control" id="school_id" name="school_id">
                            <option value="">All Schools</option>
                            @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                <div class="col-md-3">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i> Search
                        </button>
                        @if(request()->hasAny(['search', 'status', 'school_id']))
                        <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Courses Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Courses
                @if(isset($courses))
                    <span class="badge badge-secondary ml-2">{{ $courses->total() ?? 0 }}</span>
                @endif
            </h6>
        </div>
        <div class="card-body">
            @if(isset($courses) && $courses->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">Course Name</th>
                                <th width="8%">Price</th>
                                <th width="10%">Status</th>
                                @if(auth()->user()->role === 'super_admin')
                                <th width="12%">School</th>
                                @endif
                                <th width="12%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($courses as $course)
                            <tr>
                                <td>{{ $loop->iteration + ($courses->currentPage() - 1) * $courses->perPage() }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <strong class="text-primary">{{ $course->name }}</strong>
                                        @if($course->description)
                                        <small class="text-muted">
                                            {{ Str::limit($course->description, 60) }}
                                        </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-success">${{ number_format($course->price, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $course->status === 'active' ? 'success' : 'secondary' }} text-dark">
                                        {{ ucfirst($course->status) }}
                                    </span>
                                </td>
                                @if(auth()->user()->role === 'super_admin')
                                <td>
                                    @if($course->school)
                                        <a href="{{ route('admin.schools.show', $course->school) }}"
                                           class="text-decoration-none text-primary"
                                           title="{{ $course->school->name }}">
                                            {{ Str::limit($course->school->name, 15) }}
                                        </a>
                                    @else
                                        <span class="text-muted">No School</span>
                                    @endif
                                </td>
                                @endif
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.courses.show', $course) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.courses.edit', $course) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Edit Course">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('admin.courses.destroy', $course) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this course? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Delete Course">
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
                @if($courses->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $courses->firstItem() }} to {{ $courses->lastItem() }}
                        of {{ $courses->total() }} courses
                    </div>
                    <div>
                        {{ $courses->withQueryString()->links() }}
                    </div>
                </div>
                @endif

            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-graduation-cap fa-4x text-gray-300"></i>
                    </div>
                    <h5 class="text-gray-600 mb-3">No Courses Found</h5>

                    @if(request()->hasAny(['search', 'status', 'school_id']))
                        <p class="text-muted mb-4">
                            No courses match your current filters.
                            <a href="{{ route('admin.courses.index') }}" class="text-primary">
                                Clear filters
                            </a> to see all courses.
                        </p>
                    @else
                        <p class="text-muted mb-4">
                            You haven't created any courses yet. Get started by adding your first course.
                        </p>
                    @endif

                    <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Your First Course
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form when filters change
    $('#status, #school_id').on('change', function() {
        $(this).closest('form').submit();
    });

    // Show loading state when searching
    $('form').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Searching...');
        submitBtn.prop('disabled', true);

        // Re-enable after a delay (in case of fast response)
        setTimeout(function() {
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
        }, 2000);
    });

    // Tooltip initialization
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
.card {
    transition: box-shadow 0.15s ease-in-out;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.badge {
    font-size: 0.75em;
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

.text-gray-600 {
    color: #858796 !important;
}

/* Loading spinner animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.fa-spin {
    animation: spin 1s linear infinite;
}
</style>
@endpush
@endsection
