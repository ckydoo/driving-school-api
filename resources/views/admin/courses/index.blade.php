{{-- resources/views/admin/courses/index.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Course Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-book"></i> Course Management
        </h1>
        <a href="{{ route('admin.courses.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add New Course
        </a>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filters & Search
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.courses.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text"
                                   class="form-control"
                                   id="search"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Search by name or description...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    @if(auth()->user()->role === 'super_admin' && $schools->count() > 0)
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="school_id">School</label>
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
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="d-flex">
                                <button type="submit" class="btn btn-primary btn-sm mr-2">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Courses Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Courses ({{ $courses->total() }})
            </h6>
        </div>
        <div class="card-body">
            @if($courses->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Lessons</th>
                                <th>Duration</th>
                                @if(auth()->user()->role === 'super_admin')
                                <th>School</th>
                                @endif
                                <th>Instructors</th>
                                <th>Enrollments</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($courses as $course)
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ $course->name }}</div>
                                    @if($course->description)
                                        <small class="text-muted">{{ Str::limit($course->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $course->type_badge_color }}">
                                        {{ ucfirst($course->type) }}
                                    </span>
                                </td>
                                <td class="font-weight-bold text-success">
                                    {{ $course->formatted_price }}
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $course->lessons }} lessons</span>
                                </td>
                                <td>{{ $course->formatted_duration }}</td>
                                @if(auth()->user()->role === 'super_admin')
                                <td>
                                    @if($course->school)
                                        <a href="{{ route('admin.schools.show', $course->school) }}"
                                           class="text-decoration-none">
                                            {{ $course->school->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">No School</span>
                                    @endif
                                </td>
                                @endif
                                <td>
                                    @if($course->instructors->count() > 0)
                                        <div class="d-flex flex-wrap">
                                            @foreach($course->instructors->take(2) as $instructor)
                                                <span class="badge badge-secondary mr-1 mb-1">
                                                    {{ $instructor->fname }} {{ $instructor->lname }}
                                                </span>
                                            @endforeach
                                            @if($course->instructors->count() > 2)
                                                <span class="badge badge-light">+{{ $course->instructors->count() - 2 }} more</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">No instructors</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-center">
                                        <span class="font-weight-bold">{{ $course->total_enrollments }}</span>
                                        <br>
                                        <small class="text-muted">students</small>
                                    </div>
                                </td>
                                <td>
                                    @if($course->status === 'active')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.courses.show', $course) }}"
                                           class="btn btn-info btn-sm"
                                           title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.courses.edit', $course) }}"
                                           class="btn btn-warning btn-sm"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('admin.courses.destroy', $course) }}"
                                              style="display: inline;"
                                              onsubmit="return confirm('Are you sure you want to delete this course?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-danger btn-sm"
                                                    title="Delete">
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
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $courses->firstItem() }} to {{ $courses->lastItem() }} of {{ $courses->total() }} courses
                    </div>
                    {{ $courses->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Courses Found</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'status', 'school_id']))
                            No courses match your current filters.
                            <a href="{{ route('admin.courses.index') }}">Clear filters</a> to see all courses.
                        @else
                            You haven't created any courses yet.
                        @endif
                    </p>
                    <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Your First Course
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Stats Cards -->
    @if($courses->count() > 0)
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Courses</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $courses->total() }}</div>
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
                                Active Courses</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $courses->where('status', 'active')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check fa-2x text-gray-300"></i>
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
                                Total Enrollments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $courses->sum('total_enrollments') }}
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
                                Average Price</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($courses->avg('price'), 2) }}
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
</div>
@endsection
