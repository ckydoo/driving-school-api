{{-- resources/views/admin/fleet/edit.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Edit Vehicle')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit Vehicle</h1>
            <p class="text-muted mb-0">Update vehicle information</p>
        </div>
        <div>
            <a href="{{ route('admin.fleet.show', $vehicle) }}" class="btn btn-secondary mr-2">
                <i class="fas fa-arrow-left"></i> Back to Details
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Vehicle Information</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.fleet.update', $vehicle) }}" method="POST" id="vehicleEditForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- License Plate -->
                            <div class="col-md-6 mb-3">
                                <label for="carplate" class="form-label">License Plate <span class="text-danger">*</span></label>
                                <input type="text"
                                       id="carplate"
                                       name="carplate"
                                       class="form-control @error('carplate') is-invalid @enderror"
                                       value="{{ old('carplate', $vehicle->carplate) }}"
                                       required>
                                @error('carplate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                                    <option value="">Select Status</option>
                                    <option value="available" {{ old('status', $vehicle->status) === 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="maintenance" {{ old('status', $vehicle->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="retired" {{ old('status', $vehicle->status) === 'retired' ? 'selected' : '' }}>Retired</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Make -->
                            <div class="col-md-4 mb-3">
                                <label for="make" class="form-label">Make <span class="text-danger">*</span></label>
                                <input type="text"
                                       id="make"
                                       name="make"
                                       class="form-control @error('make') is-invalid @enderror"
                                       value="{{ old('make', $vehicle->make) }}"
                                       required>
                                @error('make')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Model -->
                            <div class="col-md-4 mb-3">
                                <label for="model" class="form-label">Model <span class="text-danger">*</span></label>
                                <input type="text"
                                       id="model"
                                       name="model"
                                       class="form-control @error('model') is-invalid @enderror"
                                       value="{{ old('model', $vehicle->model) }}"
                                       required>
                                @error('model')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Year -->
                            <div class="col-md-4 mb-3">
                                <label for="modelyear" class="form-label">Year <span class="text-danger">*</span></label>
                                <input type="number"
                                       id="modelyear"
                                       name="modelyear"
                                       class="form-control @error('modelyear') is-invalid @enderror"
                                       value="{{ old('modelyear', $vehicle->modelyear) }}"
                                       min="1900"
                                       max="{{ date('Y') + 1 }}"
                                       required>
                                @error('modelyear')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Instructor Assignment -->
                        <div class="mb-3">
                            <label for="instructor" class="form-label">Assigned Instructor</label>
                            <select id="instructor" name="instructor" class="form-control @error('instructor') is-invalid @enderror">
                                <option value="">No instructor assigned</option>
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->id }}"
                                            {{ old('instructor', $vehicle->instructor) == $instructor->id ? 'selected' : '' }}>
                                        {{ $instructor->full_name }} - {{ $instructor->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('instructor')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Select an instructor to assign to this vehicle, or leave blank for no assignment.
                            </small>
                        </div>

                        <!-- Additional Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea id="notes"
                                      name="notes"
                                      class="form-control @error('notes') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Any additional notes about this vehicle...">{{ old('notes', $vehicle->notes ?? '') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('admin.fleet.show', $vehicle) }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="button" class="btn btn-danger ml-2" onclick="confirmDelete()">
                                    <i class="fas fa-trash"></i> Delete Vehicle
                                </button>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Vehicle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar with Current Details -->
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Current Details</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Current Information</h6>
                        <ul class="mb-0 small">
                            <li><strong>License Plate:</strong> {{ $vehicle->carplate }}</li>
                            <li><strong>Make:</strong> {{ $vehicle->make }}</li>
                            <li><strong>Model:</strong> {{ $vehicle->model }}</li>
                            <li><strong>Year:</strong> {{ $vehicle->modelyear }}</li>
                            <li><strong>Status:</strong> {{ ucfirst($vehicle->status) }}</li>
                            <li><strong>Instructor:</strong> {{ $vehicle->assignedInstructor->full_name ?? 'Not assigned' }}</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Important</h6>
                        <p class="mb-0 small">Changes to this vehicle will affect all future schedules. Existing schedules will remain unchanged.</p>
                    </div>
                </div>
            </div>

            <!-- Usage Statistics -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Usage History</h6>
                </div>
                <div class="card-body">
                    @php
                        $usageStats = [
                            'total_schedules' => $vehicle->schedules()->count(),
                            'completed_lessons' => $vehicle->schedules()->where('status', 'completed')->count(),
                            'upcoming_schedules' => $vehicle->schedules()->where('start', '>', now())->where('status', 'scheduled')->count(),
                            'last_used' => $vehicle->schedules()->orderBy('start', 'desc')->first(),
                        ];
                    @endphp

                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <div class="small text-muted">{{ $vehicle->created_at->format('M d, Y g:i A') }}</div>
                                <div class="small">Vehicle added to fleet</div>
                            </div>
                        </div>
                        @if($vehicle->updated_at != $vehicle->created_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <div class="small text-muted">{{ $vehicle->updated_at->format('M d, Y g:i A') }}</div>
                                    <div class="small">Vehicle information updated</div>
                                </div>
                            </div>
                        @endif
                        @if($usageStats['last_used'])
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <div class="small text-muted">{{ $usageStats['last_used']->start->format('M d, Y g:i A') }}</div>
                                    <div class="small">Last lesson scheduled</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="border-top mt-3 pt-3">
                        <div class="row text-center small">
                            <div class="col-4">
                                <div class="h6 font-weight-bold text-primary">{{ $usageStats['total_schedules'] }}</div>
                                <div class="text-muted">Total</div>
                            </div>
                            <div class="col-4">
                                <div class="h6 font-weight-bold text-success">{{ $usageStats['completed_lessons'] }}</div>
                                <div class="text-muted">Completed</div>
                            </div>
                            <div class="col-4">
                                <div class="h6 font-weight-bold text-warning">{{ $usageStats['upcoming_schedules'] }}</div>
                                <div class="text-muted">Upcoming</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change History -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.fleet.show', $vehicle) }}"
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-eye text-primary"></i>
                            <span class="ml-2">View Details</span>
                        </a>
                        <a href="{{ route('admin.schedules.create') }}?car={{ $vehicle->id }}"
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-calendar-plus text-success"></i>
                            <span class="ml-2">Schedule Lesson</span>
                        </a>
                        <a href="{{ route('admin.fleet.schedules', $vehicle) }}"
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-calendar text-info"></i>
                            <span class="ml-2">View All Schedules</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Vehicle</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this vehicle?</p>
                <div class="alert alert-warning">
                    <strong>Warning:</strong> This will permanently remove the vehicle from your fleet.
                    @if($usageStats['total_schedules'] > 0)
                        This vehicle has {{ $usageStats['total_schedules'] }} schedule(s) associated with it.
                    @endif
                </div>
                <div class="form-group">
                    <label>Type <strong>{{ $vehicle->carplate }}</strong> to confirm:</label>
                    <input type="text" id="confirmPlate" class="form-control" placeholder="Enter license plate">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.fleet.destroy', $vehicle) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" id="deleteBtn" class="btn btn-danger" disabled>Delete Vehicle</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -25px;
    top: 5px;
    width: 2px;
    height: 100%;
    background-color: #e3e6f0;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.timeline-content {
    margin-left: 10px;
}

.form-label {
    font-weight: 600;
    color: #5a5c69;
}

.alert ul {
    padding-left: 20px;
}

.alert li {
    margin-bottom: 5px;
}
</style>
@endpush

@push('scripts')
<script>
function confirmDelete() {
    $('#deleteModal').modal('show');
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-format license plate
    const plateInput = document.getElementById('carplate');
    plateInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Delete confirmation
    const confirmInput = document.getElementById('confirmPlate');
    const deleteBtn = document.getElementById('deleteBtn');
    const targetPlate = '{{ $vehicle->carplate }}';

    confirmInput.addEventListener('input', function() {
        if (this.value === targetPlate) {
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Delete Vehicle';
        } else {
            deleteBtn.disabled = true;
            deleteBtn.textContent = 'Delete Vehicle';
        }
    });

    // Form validation
    const form = document.getElementById('vehicleEditForm');
    form.addEventListener('submit', function(e) {
        const plate = plateInput.value.trim();
        const make = document.getElementById('make').value.trim();
        const model = document.getElementById('model').value.trim();
        const year = document.getElementById('modelyear').value;

        if (!plate || !make || !model || !year) {
            e.preventDefault();
            alert('Please fill in all required fields');
            return false;
        }

        if (plate.length < 3) {
            e.preventDefault();
            alert('License plate must be at least 3 characters long');
            return false;
        }

        const currentYear = new Date().getFullYear();
        if (year < 1900 || year > (currentYear + 1)) {
            e.preventDefault();
            alert(`Year must be between 1900 and ${currentYear + 1}`);
            return false;
        }
    });

    // Auto-suggest for make and model
    const makeInput = document.getElementById('make');
    const modelInput = document.getElementById('model');

    makeInput.addEventListener('input', function() {
        const value = this.value;
        if (value.length === 1) {
            this.value = value.toUpperCase();
        }
    });

    modelInput.addEventListener('input', function() {
        const value = this.value;
        if (value.length === 1) {
            this.value = value.toUpperCase();
        }
    });
});
</script>
@endpush
