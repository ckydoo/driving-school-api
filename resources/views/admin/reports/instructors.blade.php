{{-- resources/views/admin/reports/instructors.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Instructor Reports')

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
                        <a href="{{ route('admin.reports.index') }}" class="text-decoration-none">Reports</a>
                    </li>
                    <li class="breadcrumb-item active">Instructor Reports</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chalkboard-teacher"></i> Instructor Reports
            </h1>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-primary" onclick="exportInstructorReport()">
                <i class="fas fa-download"></i> Export Report
            </button>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Instructors</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\User::where('role', 'instructor')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Instructors</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\User::where('role', 'instructor')->where('status', 'active')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Lessons Taught</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Schedule::whereHas('instructor')->count() }}
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Avg Rating</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">4.8/5</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructor Details Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Instructor Performance
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Instructor</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Lessons</th>
                            <th>Students</th>
                            <th>Rating</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(\App\Models\User::where('role', 'instructor')->with(['instructorSchedules'])->get() as $instructor)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-placeholder bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center me-2" 
                                         style="width: 32px; height: 32px; font-size: 0.8rem;">
                                        {{ strtoupper(substr($instructor->fname, 0, 1) . substr($instructor->lname, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $instructor->full_name }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $instructor->email }}</td>
                            <td>{{ $instructor->phone ?? 'N/A' }}</td>
                            <td>
                                <span class="badge badge-{{ $instructor->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($instructor->status) }}
                                </span>
                            </td>
                            <td>{{ $instructor->instructorSchedules->count() }}</td>
                            <td>{{ $instructor->instructorSchedules->unique('student_id')->count() }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= 4 ? 'text-warning' : 'text-muted' }}"></i>
                                    @endfor
                                    <span class="ms-2">4.8</span>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.users.show', $instructor) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $instructor) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-chalkboard-teacher fa-3x mb-3"></i>
                                <p>No instructors found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    function exportInstructorReport() {
        // Implement instructor report export
        window.location.href = '{{ route("admin.reports.export", "instructors") }}';
    }
</script>
@endsection
@endsection