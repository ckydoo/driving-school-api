{{-- resources/views/admin/fleet/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Fleet Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Fleet Management</h1>
            <p class="text-muted mb-0">Manage your driving school vehicles</p>
        </div>
        <div>
            <a href="{{ route('admin.fleet.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Vehicle
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
                                Total Vehicles
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $vehicles->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-car fa-2x text-gray-300"></i>
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
                                Available
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $vehicles->where('status', 'available')->count() }}
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
                                In Maintenance
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $vehicles->where('status', 'maintenance')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wrench fa-2x text-gray-300"></i>
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
                                Retired
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $vehicles->where('status', 'retired')->count() }}
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
            <form method="GET" action="{{ route('admin.fleet.index') }}" class="row align-items-end">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text"
                           id="search"
                           name="search"
                           class="form-control"
                           placeholder="Search by plate, make, or model"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                        <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="retired" {{ request('status') === 'retired' ? 'selected' : '' }}>Retired</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="make" class="form-label">Make</label>
                    <select id="make" name="make" class="form-control">
                        <option value="">All Makes</option>
                        @php
                            $makes = $vehicles->pluck('make')->unique()->sort();
                        @endphp
                        @foreach($makes as $make)
                            <option value="{{ $make }}" {{ request('make') === $make ? 'selected' : '' }}>
                                {{ $make }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Fleet Table -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Vehicle Fleet</h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-toggle="dropdown">
                    <i class="fas fa-sort"></i> Sort
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => 'desc']) }}">
                        Newest First
                    </a>
                    <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'carplate', 'sort_order' => 'asc']) }}">
                        By License Plate
                    </a>
                    <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'make', 'sort_order' => 'asc']) }}">
                        By Make
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($vehicles->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="fleetTable">
                        <thead>
                            <tr>
                                <th>License Plate</th>
                                <th>Vehicle</th>
                                <th>Year</th>
                                <th>Status</th>
                                <th>Assigned Instructor</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehicles as $vehicle)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="vehicle-icon mr-3">
                                                <i class="fas fa-car fa-2x text-{{ $vehicle->status === 'available' ? 'success' : ($vehicle->status === 'maintenance' ? 'warning' : 'danger') }}"></i>
                                            </div>
                                            <div>
                                                <strong class="text-primary">{{ $vehicle->carplate }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $vehicle->make }} {{ $vehicle->model }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $vehicle->modelyear }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{
                                            $vehicle->status === 'available' ? 'success' :
                                            ($vehicle->status === 'maintenance' ? 'warning' : 'danger')
                                        }}">
                                            {{ ucfirst($vehicle->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($vehicle->assignedInstructor)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm rounded-circle bg-success text-white mr-2">
                                                    {{ substr($vehicle->assignedInstructor->full_name ?? 'I', 0, 1) }}
                                                </div>
                                                <div>
                                                    <strong>{{ $vehicle->assignedInstructor->full_name ?? 'Unknown Instructor' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $vehicle->assignedInstructor->email ?? '' }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">
                                                <i class="fas fa-user-slash"></i> Not Assigned
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.fleet.show', $vehicle) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.fleet.edit', $vehicle) }}"
                                               class="btn btn-sm btn-outline-secondary"
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if(!$vehicle->assignedInstructor)
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-success"
                                                        title="Assign Instructor"
                                                        onclick="showAssignModal({{ $vehicle->id }}, '{{ $vehicle->carplate }}')">
                                                    <i class="fas fa-user-plus"></i>
                                                </button>
                                            @else
                                                <form action="{{ route('admin.fleet.assign-instructor', $vehicle) }}"
                                                      method="POST"
                                                      class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="instructor" value="">
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-warning"
                                                            title="Unassign Instructor"
                                                            onclick="return confirm('Unassign instructor from this vehicle?')">
                                                        <i class="fas fa-user-minus"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('admin.fleet.destroy', $vehicle) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Delete"
                                                        onclick="return confirm('Are you sure you want to delete this vehicle?')">
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
                            Showing {{ $vehicles->firstItem() }} to {{ $vehicles->lastItem() }}
                            of {{ $vehicles->total() }} results
                        </p>
                    </div>
                    <div>
                        {{ $vehicles->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-car fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-muted">No vehicles found</h5>
                    <p class="text-muted mb-4">
                        @if(request()->hasAny(['search', 'status', 'make']))
                            Try adjusting your search criteria or
                            <a href="{{ route('admin.fleet.index') }}">clear all filters</a>.
                        @else
                            Get started by adding your first vehicle to the fleet.
                        @endif
                    </p>
                    @if(!request()->hasAny(['search', 'status', 'make']))
                        <a href="{{ route('admin.fleet.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add First Vehicle
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Assign Instructor Modal -->
<div class="modal fade" id="assignInstructorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Instructor</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="assignInstructorForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Assign an instructor to vehicle: <strong id="vehiclePlate"></strong></p>
                    <div class="form-group">
                        <label for="instructor">Select Instructor:</label>
                        <select id="instructor" name="instructor" class="form-control" required>
                            <option value="">Choose an instructor...</option>
                            {{-- Will be populated via AJAX --}}
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

.vehicle-icon {
    opacity: 0.8;
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
function showAssignModal(vehicleId, vehiclePlate) {
    document.getElementById('vehiclePlate').textContent = vehiclePlate;
    document.getElementById('assignInstructorForm').action = `/admin/fleet/${vehicleId}/assign-instructor`;

    // Load available instructors
    fetch('/api/admin/instructors/available')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('instructor');
            select.innerHTML = '<option value="">Choose an instructor...</option>';

            if (data.instructors) {
                data.instructors.forEach(instructor => {
                    const option = document.createElement('option');
                    option.value = instructor.id;
                    option.textContent = `${instructor.full_name} (${instructor.email})`;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading instructors:', error);
        });

    $('#assignInstructorModal').modal('show');
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form on filter change
    document.querySelectorAll('#status, #make').forEach(function(select) {
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
