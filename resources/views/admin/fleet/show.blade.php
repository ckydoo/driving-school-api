{{-- resources/views/admin/fleet/show.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Vehicle Details - ' . $fleet->make . ' ' . $fleet->model)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-car"></i> Vehicle Details
        </h1>
        <div>
            <a href="{{ route('admin.fleet.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Fleet
            </a>
            <a href="{{ route('admin.fleet.edit', $fleet) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form method="POST" action="{{ route('admin.fleet.destroy', $fleet) }}" style="display: inline;"
                  onsubmit="return confirm('Are you sure you want to delete this vehicle?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Vehicle Information Card -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Vehicle Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Car Plate:</label>
                                <p class="text-gray-900">{{ $fleet->carplate }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Make:</label>
                                <p class="text-gray-900">{{ $fleet->make }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Model:</label>
                                <p class="text-gray-900">{{ $fleet->model }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Model Year:</label>
                                <p class="text-gray-900">{{ $fleet->modelyear }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Status:</label>
                                <p>
                                    @if($fleet->status === 'available')
                                        <span class="badge badge-success">Available</span>
                                    @elseif($fleet->status === 'maintenance')
                                        <span class="badge badge-warning">Maintenance</span>
                                    @else
                                        <span class="badge badge-danger">Retired</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Assigned Instructor:</label>
                                <p class="text-gray-900">
                                    @if($fleet->assignedInstructor)
                                        {{ $fleet->assignedInstructor->fname }} {{ $fleet->assignedInstructor->lname }}
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Schedules -->
            @if($fleet->schedules->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-alt"></i> Recent Schedules
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Student</th>
                                    <th>Instructor</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fleet->schedules->take(10) as $schedule)
                                <tr>
                                    <td>{{ $schedule->scheduled_date->format('M d, Y') }}</td>
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
                    @if($fleet->schedules->count() > 10)
                    <div class="text-center">
                        <a href="{{ route('admin.fleet.schedules', $fleet) }}" class="btn btn-primary btn-sm">
                            View All Schedules
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Quick Stats -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar"></i> Quick Stats
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="text-center">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Schedules
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $fleet->schedules->count() }}
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="text-center">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Completed Lessons
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $fleet->schedules->where('status', 'completed')->count() }}
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="text-center">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Vehicle Age
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ date('Y') - $fleet->modelyear }} years
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assign Instructor (if not assigned) -->
            @if(!$fleet->assignedInstructor)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-plus"></i> Assign Instructor
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.fleet.assign-instructor', $fleet) }}">
                        @csrf
                        <div class="form-group">
                            <select name="instructor_id" class="form-control" required>
                                <option value="">Select an instructor...</option>
                                @foreach(\App\Models\User::where('role', 'instructor')->where('status', 'active')->get() as $instructor)
                                <option value="{{ $instructor->id }}">
                                    {{ $instructor->fname }} {{ $instructor->lname }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-check"></i> Assign Instructor
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
