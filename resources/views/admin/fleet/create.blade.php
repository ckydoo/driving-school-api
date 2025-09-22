{{-- resources/views/admin/fleet/create.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Add Vehicle')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Add New Vehicle</h1>
            <p class="text-muted mb-0">Add a new vehicle to your fleet</p>
        </div>
        <div>
            <a href="{{ route('admin.fleet.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Fleet
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Vehicle Information</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.fleet.store') }}" method="POST" id="vehicleForm">
                        @csrf

                        <div class="row">
                            <!-- License Plate -->
                            <div class="col-md-6 mb-3">
                                <label for="carplate" class="form-label">License Plate <span class="text-danger">*</span></label>
                                <input type="text"
                                       id="carplate"
                                       name="carplate"
                                       class="form-control @error('carplate') is-invalid @enderror"
                                       value="{{ old('carplate') }}"
                                       placeholder="e.g., ABC-123"
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
                                    <option value="available" {{ old('status') === 'available' ? 'selected' : 'selected' }}>Available</option>
                                    <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="retired" {{ old('status') === 'retired' ? 'selected' : '' }}>Retired</option>
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
                                       value="{{ old('make') }}"
                                       placeholder="e.g., Toyota"
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
                                       value="{{ old('model') }}"
                                       placeholder="e.g., Corolla"
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
                                       value="{{ old('modelyear') }}"
                                       min="1900"
                                       max="{{ date('Y') + 1 }}"
                                       placeholder="{{ date('Y') }}"
                                       required>
                                @error('modelyear')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Instructor Assignment -->
                        <div class="mb-3">
                            <label for="instructor" class="form-label">Assign Instructor (Optional)</label>
                            <select id="instructor" name="instructor" class="form-control @error('instructor') is-invalid @enderror">
                                <option value="">Select Instructor (Optional)</option>
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->id }}" {{ old('instructor') == $instructor->id ? 'selected' : '' }}>
                                        {{ $instructor->full_name }} - {{ $instructor->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('instructor')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                You can assign an instructor now or later from the fleet list.
                            </small>
                        </div>

                        <!-- Additional Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea id="notes"
                                      name="notes"
                                      class="form-control @error('notes') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Any additional notes about this vehicle...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.fleet.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add Vehicle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar with Information -->
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Vehicle Guidelines</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Important Notes</h6>
                        <ul class="mb-0">
                            <li>License plate must be unique</li>
                            <li>Ensure vehicle registration is current</li>
                            <li>Check insurance coverage before adding</li>
                            <li>Vehicle should be roadworthy for student use</li>
                            <li>Regular maintenance schedules are recommended</li>
                        </ul>
                    </div>

                    <div class="alert alert-success">
                        <h6><i class="fas fa-check-circle"></i> Status Options</h6>
                        <ul class="mb-0">
                            <li><strong>Available:</strong> Ready for lessons</li>
                            <li><strong>Maintenance:</strong> Under repair/service</li>
                            <li><strong>Retired:</strong> No longer in active use</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Fleet Statistics -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Current Fleet</h6>
                </div>
                <div class="card-body">
                    @php
                        $fleetStats = [
                            'total' => App\Models\Fleet::count(),
                            'available' => App\Models\Fleet::where('status', 'available')->count(),
                            'maintenance' => App\Models\Fleet::where('status', 'maintenance')->count(),
                            'retired' => App\Models\Fleet::where('status', 'retired')->count(),
                        ];
                    @endphp

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-right">
                                <div class="h5 font-weight-bold text-primary">{{ $fleetStats['total'] }}</div>
                                <div class="small text-muted">Total Vehicles</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h5 font-weight-bold text-success">{{ $fleetStats['available'] }}</div>
                            <div class="small text-muted">Available</div>
                        </div>
                    </div>
                    <div class="row text-center mt-3">
                        <div class="col-6">
                            <div class="border-right">
                                <div class="h5 font-weight-bold text-warning">{{ $fleetStats['maintenance'] }}</div>
                                <div class="small text-muted">Maintenance</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h5 font-weight-bold text-danger">{{ $fleetStats['retired'] }}</div>
                            <div class="small text-muted">Retired</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.border-right {
    border-right: 1px solid #e3e6f0;
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
document.addEventListener('DOMContentLoaded', function() {
    // Auto-format license plate
    const plateInput = document.getElementById('carplate');
    plateInput.addEventListener('input', function() {
        // Convert to uppercase
        this.value = this.value.toUpperCase();
    });

    // Form validation
    const form = document.getElementById('vehicleForm');
    form.addEventListener('submit', function(e) {
        const plate = plateInput.value.trim();
        const make = document.getElementById('make').value.trim();
        const model = document.getElementById('model').value.trim();
        const year = document.getElementById('modelyear').value;

        // Basic validation
        if (!plate || !make || !model || !year) {
            e.preventDefault();
            alert('Please fill in all required fields');
            return false;
        }

        // License plate format validation
        if (plate.length < 3) {
            e.preventDefault();
            alert('License plate must be at least 3 characters long');
            return false;
        }

        // Year validation
        const currentYear = new Date().getFullYear();
        if (year < 1900 || year > (currentYear + 1)) {
            e.preventDefault();
            alert(`Year must be between 1900 and ${currentYear + 1}`);
            return false;
        }
    });

    // Auto-suggest common car makes
    const makeInput = document.getElementById('make');
    const commonMakes = [
        'Toyota', 'Honda', 'Ford', 'Chevrolet', 'Nissan', 'Hyundai',
        'Kia', 'Mazda', 'Subaru', 'Volkswagen', 'BMW', 'Mercedes-Benz',
        'Audi', 'Lexus', 'Acura', 'Infiniti', 'Volvo', 'Mitsubishi'
    ];

    makeInput.addEventListener('input', function() {
        const value = this.value;
        // Capitalize first letter
        if (value.length === 1) {
            this.value = value.toUpperCase();
        }
    });

    // Auto-suggest for models based on make
    const modelInput = document.getElementById('model');
    modelInput.addEventListener('input', function() {
        const value = this.value;
        // Capitalize first letter
        if (value.length === 1) {
            this.value = value.toUpperCase();
        }
    });
});
</script>
@endpush
