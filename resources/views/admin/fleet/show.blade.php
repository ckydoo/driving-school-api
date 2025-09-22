{{-- resources/views/admin/fleet/show.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Vehicle Details')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Vehicle Details</h1>
            <p class="text-muted mb-0">{{ $vehicle->make }} {{ $vehicle->model }} - {{ $vehicle->carplate }}</p>
        </div>
        <div>
            <a href="{{ route('admin.fleet.index') }}" class="btn btn-secondary mr-2">
                <i class="fas fa-arrow-left"></i> Back to Fleet
            </a>
            <a href="{{ route('admin.fleet.edit', $vehicle) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Vehicle
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Vehicle Details -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Vehicle Information</h6>
                    <span class="badge badge-{{
                        $vehicle->status === 'available' ? 'success' :
                        ($vehicle->status === 'maintenance' ? 'warning' : 'danger')
                    }} badge-lg">
                        {{ ucfirst($vehicle->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- License Plate & Status -->
                        <div class="col-md-6 mb-4">
                            <div class="border-left-primary pl-3">
                                <h6 class="font-weight-bold text-primary mb-1">
                                    <i class="fas fa-id-card mr-2"></i>License Plate
                                </h6>
                                <p class="h4 mb-1 text-primary">{{ $vehicle->carplate }}</p>
                                <p class="text-muted mb-0">
                                    Status:
                                    <span class="badge badge-{{
                                        $vehicle->status === 'available' ? 'success' :
                                        ($vehicle->status === 'maintenance' ? 'warning' : 'danger')
                                    }}">
                                        {{ ucfirst($vehicle->status) }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Vehicle Details -->
                        <div class="col-md-6 mb-4">
                            <div class="border-left-info pl-3">
                                <h6 class="font-weight-bold text-info mb-1">
                                    <i class="fas fa-car mr-2"></i>Vehicle Specifications
                                </h6>
                                <p class="h5 mb-1">{{ $vehicle->make }} {{ $vehicle->model }}</p>
                                <p class="text-muted mb-0">Year: {{ $vehicle->modelyear }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Instructor -->
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <div class="border-left-success pl-3">
                                <h6 class="font-weight-bold text-success mb-2">
                                    <i class="fas fa-chalkboard-teacher mr-2"></i>Assigned Instructor
                                </h6>
                                @if($vehicle->assignedInstructor)
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-md rounded-circle bg-success text-white mr-3">
                                            {{ substr($vehicle->assignedInstructor->full_name ?? 'I', 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="h6 mb-1">{{ $vehicle->assignedInstructor->full_name ?? 'Unknown Instructor' }}</p>
                                            <p class="text-muted mb-1">{{ $vehicle->assignedInstructor->email ?? 'No email' }}</p>
                                            <p class="text-muted mb-0">{{ $vehicle->assignedInstructor->phone ?? 'No phone' }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <a href="{{ route('admin.users.show', $vehicle->assignedInstructor) }}" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-eye"></i> View Instructor
                                        </a>
                                        <form action="{{ route('admin.fleet.assign-instructor', $vehicle) }}"
                                              method="POST"
                                              class="d-inline ml-2">
                                            @csrf
                                            <input type="hidden" name="instructor" value="">
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-warning"
                                                    onclick="return confirm('Unassign this instructor from the vehicle?')">
                                                <i class="fas fa-user-minus"></i> Unassign
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div class="text-center py-3">
                                        <i class="fas fa-user-slash fa-2x text-gray-300 mb-2"></i>
                                        <p class="text-muted mb-2">No instructor assigned to this vehicle</p>
                                        <button type="button"
                                                class="btn btn-sm btn-success"
                                                onclick="showAssignModal()">
                                            <i class="fas fa-user-plus"></i> Assign Instructor
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    @if($vehicle->notes ?? false)
                    <div class="border-top pt-4">
                        <h6 class="font-weight-bold mb-2">
                            <i class="fas fa-sticky-note mr-2"></i>Notes
                        </h6>
                        <div class="bg-light p-3 rounded">
                            <p class="mb-0">{{ $vehicle->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Recent Schedules -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Schedules</h6>
                </div>
                <div class="card-body">
                    @php
                        $recentSchedules = $vehicle->schedules()->with(['student', 'instructor'])
                                                  ->orderBy('start', 'desc')
                                                  ->take(10)
                                                  ->get();
                    @endphp

                    @if($recentSchedules->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Student</th>
                                        <th>Instructor</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentSchedules as $schedule)
                                        <tr>
                                            <td>{{ $schedule->start ? $schedule->start->format('M d, Y') : 'No date' }}</td>
                                            <td>
                                                {{ $schedule->start && $schedule->end ?
                                                   $schedule->start->format('g:i A') . ' - ' . $schedule->end->format('g:i A') :
                                                   'No time' }}
                                            </td>
                                            <td>{{ $schedule->student->full_name ?? 'Unknown Student' }}</td>
                                            <td>{{ $schedule->instructor->full_name ?? 'Unknown Instructor' }}</td>
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
                                                <a href="{{ route('admin.schedules.show', $schedule) }}"
                                                   class="btn btn-xs btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.fleet.schedules', $vehicle) }}" class="btn btn-sm btn-outline-primary">
                                View All Schedules
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No schedules found for this vehicle</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.fleet.edit', $vehicle) }}" class="btn btn-outline-primary btn-block mb-2">
                            <i class="fas fa-edit"></i> Edit Vehicle Details
                        </a>

                        @if($vehicle->status === 'available')
                            <form action="{{ route('admin.fleet.update', $vehicle) }}" method="POST" class="mb-2">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="maintenance">
                                <button type="submit" class="btn btn-outline-warning btn-block"
                                        onclick="return confirm('Mark this vehicle as under maintenance?')">
                                    <i class="fas fa-wrench"></i> Mark for Maintenance
                                </button>
                            </form>
                        @elseif($vehicle->status === 'maintenance')
                            <form action="{{ route('admin.fleet.update', $vehicle) }}" method="POST" class="mb-2">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="available">
                                <button type="submit" class="btn btn-outline-success btn-block"
                                        onclick="return confirm('Mark this vehicle as available?')">
                                    <i class="fas fa-check"></i> Mark Available
                                </button>
                            </form>
                        @endif

                        @if(!$vehicle->assignedInstructor)
                            <button type="button"
                                    class="btn btn-outline-success btn-block mb-2"
                                    onclick="showAssignModal()">
                                <i class="fas fa-user-plus"></i> Assign Instructor
                            </button>
                        @endif

                        <a href="{{ route('admin.schedules.create') }}?car={{ $vehicle->id }}"
                           class="btn btn-outline-info btn-block mb-2">
                            <i class="fas fa-calendar-plus"></i> Schedule Lesson
                        </a>

                        <form action="{{ route('admin.fleet.destroy', $vehicle) }}"
                              method="POST"
                              class="mb-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="btn btn-outline-danger btn-block"
                                    onclick="return confirm('Are you sure you want to delete this vehicle? This action cannot be undone.')">
                                <i class="fas fa-trash"></i> Delete Vehicle
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Vehicle Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Vehicle Statistics</h6>
                </div>
                <div class="card-body">
                    @php
                        $stats = [
                            'total_schedules' => $vehicle->schedules()->count(),
                            'completed_lessons' => $vehicle->schedules()->where('status', 'completed')->count(),
                            'upcoming_schedules' => $vehicle->schedules()->where('start', '>', now())->where('status', 'scheduled')->count(),
                            'this_month' => $vehicle->schedules()->whereMonth('start', now()->month)->whereYear('start', now()->year)->count(),
                        ];
                    @endphp

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-right">
                                <div class="h5 font-weight-bold text-primary">{{ $stats['total_schedules'] }}</div>
                                <div class="small text-muted">Total Lessons</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h5 font-weight-bold text-success">{{ $stats['completed_lessons'] }}</div>
                            <div class="small text-muted">Completed</div>
                        </div>
                    </div>
                    <div class="row text-center mt-3">
                        <div class="col-6">
                            <div class="border-right">
                                <div class="h5 font-weight-bold text-warning">{{ $stats['upcoming_schedules'] }}</div>
                                <div class="small text-muted">Upcoming</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h5 font-weight-bold text-info">{{ $stats['this_month'] }}</div>
                            <div class="small text-muted">This Month</div>
                        </div>
                    </div>

                    <div class="border-top mt-3 pt-3">
                        <div class="small">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Added:</span>
                                <span>{{ $vehicle->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Updated:</span>
                                <span>{{ $vehicle->updated_at->format('M d, Y') }}</span>
                            </div>
                            @if($vehicle->assignedInstructor)
                                <div class="d-flex justify-content-between">
                                    <span>Instructor:</span>
                                    <span>{{ $vehicle->assignedInstructor->full_name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
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
            <form action="{{ route('admin.fleet.assign-instructor', $vehicle) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Assign an instructor to: <strong>{{ $vehicle->carplate }}</strong></p>
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
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }

.avatar {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
}

.badge-lg {
    font-size: 0.9em;
    padding: 0.5em 0.75em;
}

.border-right {
    border-right: 1px solid #e3e6f0;
}

.btn-xs {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}
</style>
@endpush

@push('scripts')
<script>
function showAssignModal() {
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
            alert('Error loading instructors. Please try again.');
        });

    $('#assignInstructorModal').modal('show');
}
</script>
@endpush
