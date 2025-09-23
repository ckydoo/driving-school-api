{{-- resources/views/admin/reports/vehicles.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Vehicle Reports')

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
                    <li class="breadcrumb-item active">Vehicle Reports</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-car"></i> Vehicle Reports
            </h1>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-primary" onclick="exportVehicleReport()">
                <i class="fas fa-download"></i> Export Report
            </button>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>

    <!-- Vehicle Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Vehicles</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Fleet::count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-car fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Available</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Fleet::where('status', 'available')->count() }}
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
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">In Use</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Fleet::where('status', 'in_use')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-car-side fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Maintenance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Fleet::where('status', 'maintenance')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wrench fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehicle Details Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Vehicle Fleet Details
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>License Plate</th>
                            <th>Year</th>
                            <th>Status</th>
                            <th>Instructor</th>
                            <th>Lessons</th>
                            <th>Last Service</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(\App\Models\Fleet::with(['assignedInstructor', 'schedules'])->get() as $vehicle)
                        <tr>
                            <td>
                                <div>
                                    <strong>{{ $vehicle->make }} {{ $vehicle->model }}</strong>
                                    @if($vehicle->color)
                                        <br><small class="text-muted">{{ $vehicle->color }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <code>{{ $vehicle->license_plate ?? 'N/A' }}</code>
                            </td>
                            <td>{{ $vehicle->year ?? 'N/A' }}</td>
                            <td>
                                <span class="badge badge-{{ 
                                    $vehicle->status === 'available' ? 'success' : 
                                    ($vehicle->status === 'in_use' ? 'warning' : 
                                    ($vehicle->status === 'maintenance' ? 'danger' : 'secondary')) 
                                }}">
                                    {{ ucfirst(str_replace('_', ' ', $vehicle->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($vehicle->assignedInstructor)
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-placeholder bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center me-2" 
                                             style="width: 28px; height: 28px; font-size: 0.7rem;">
                                            {{ strtoupper(substr($vehicle->assignedInstructor->fname, 0, 1) . substr($vehicle->assignedInstructor->lname, 0, 1)) }}
                                        </div>
                                        <div>
                                            <small>{{ $vehicle->assignedInstructor->full_name }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                            <td>{{ $vehicle->schedules->count() }}</td>
                            <td>
                                @if($vehicle->last_service_date)
                                    {{ \Carbon\Carbon::parse($vehicle->last_service_date)->format('M d, Y') }}
                                @else
                                    <span class="text-muted">No record</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.fleet.show', $vehicle) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.fleet.edit', $vehicle) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($vehicle->schedules->count() > 0)
                                    <a href="{{ route('admin.fleet.schedules', $vehicle) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-calendar"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-car fa-3x mb-3"></i>
                                <p>No vehicles found.</p>
                                <a href="{{ route('admin.fleet.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add First Vehicle
                                </a>
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
    function exportVehicleReport() {
        // Implement vehicle report export
        window.location.href = '{{ route("admin.reports.export", "vehicles") }}';
    }
</script>
@endsection
@endsection