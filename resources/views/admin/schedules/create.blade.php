{{-- resources/views/admin/schedules/create.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Schedule New Lesson')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Schedule New Lesson</h1>
            <p class="text-muted mb-0">Create a new lesson schedule</p>
        </div>
        <div>
            <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Schedules
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Schedule Details</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.schedules.store') }}" method="POST" id="scheduleForm">
                        @csrf

                        <div class="row">
                            <!-- Student Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="student" class="form-label">Student <span class="text-danger">*</span></label>
                                <select id="student" name="student" class="form-control @error('student') is-invalid @enderror" required>
                                    <option value="">Select Student</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ old('student') == $student->id ? 'selected' : '' }}>
                                            {{ $student->full_name }} - {{ $student->email }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('student')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Instructor Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="instructor" class="form-label">Instructor <span class="text-danger">*</span></label>
                                <select id="instructor" name="instructor" class="form-control @error('instructor') is-invalid @enderror" required>
                                    <option value="">Select Instructor</option>
                                    @foreach($instructors as $instructor)
                                        <option value="{{ $instructor->id }}" {{ old('instructor') == $instructor->id ? 'selected' : '' }}>
                                            {{ $instructor->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('instructor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Course Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="course" class="form-label">Course <span class="text-danger">*</span></label>
                                <select id="course" name="course" class="form-control @error('course') is-invalid @enderror" required>
                                    <option value="">Select Course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course') == $course->id ? 'selected' : '' }}>
                                            {{ $course->name }} - ${{ $course->price }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('course')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Vehicle Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="car" class="form-label">Vehicle <span class="text-danger">*</span></label>
                                <select id="car" name="car" class="form-control @error('car') is-invalid @enderror" required>
                                    <option value="">Select Vehicle</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ old('car') == $vehicle->id ? 'selected' : '' }}>
                                            {{ $vehicle->license_plate }} - {{ $vehicle->make }} {{ $vehicle->model }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('car')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Start Date & Time -->
                            <div class="col-md-6 mb-3">
                                <label for="start" class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local"
                                       id="start"
                                       name="start"
                                       class="form-control @error('start') is-invalid @enderror"
                                       value="{{ old('start') }}"
                                       required>
                                @error('start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- End Date & Time -->
                            <div class="col-md-6 mb-3">
                                <label for="end" class="form-label">End Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local"
                                       id="end"
                                       name="end"
                                       class="form-control @error('end') is-invalid @enderror"
                                       value="{{ old('end') }}"
                                       required>
                                @error('end')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Class Type -->
                            <div class="col-md-4 mb-3">
                                <label for="class_type" class="form-label">Class Type <span class="text-danger">*</span></label>
                                <select id="class_type" name="class_type" class="form-control @error('class_type') is-invalid @enderror" required>
                                    <option value="">Select Type</option>
                                    <option value="practical" {{ old('class_type') === 'practical' ? 'selected' : '' }}>Practical</option>
                                    <option value="theory" {{ old('class_type') === 'theory' ? 'selected' : '' }}>Theory</option>
                                    <option value="test" {{ old('class_type') === 'test' ? 'selected' : '' }}>Test</option>
                                </select>
                                @error('class_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                                    <option value="scheduled" {{ old('status') === 'scheduled' ? 'selected' : 'selected' }}>Scheduled</option>
                                    <option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- School -->
                            <div class="col-md-4 mb-3">
                                <label for="school_id" class="form-label">School <span class="text-danger">*</span></label>
                                <select id="school_id" name="school_id" class="form-control @error('school_id') is-invalid @enderror" required>
                                    @if(isset($currentUser) && $currentUser->isSuperAdmin())
                                        <option value="">Select School</option>
                                        @foreach($schools as $school)
                                            <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                                                {{ $school->name }}
                                            </option>
                                        @endforeach
                                    @else
                                        @if(Auth::user()->school)
                                            <option value="{{ Auth::user()->school_id }}" selected>
                                                {{ Auth::user()->school->name }}
                                            </option>
                                        @else
                                            <option value="">No school assigned</option>
                                        @endif
                                    @endif
                                </select>
                                @error('school_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea id="notes"
                                      name="notes"
                                      class="form-control @error('notes') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Any additional notes for this schedule...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Recurring Options -->
                        <div class="card border-left-info mb-3">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-info mb-3">Recurring Schedule (Optional)</h6>

                                <div class="form-check mb-3">
                                    <input type="checkbox"
                                           id="is_recurring"
                                           name="is_recurring"
                                           class="form-check-input"
                                           value="1"
                                           {{ old('is_recurring') ? 'checked' : '' }}>
                                    <label for="is_recurring" class="form-check-label">
                                        Create recurring schedule
                                    </label>
                                </div>

                                <div id="recurring_options" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="recurring_pattern" class="form-label">Repeat Pattern</label>
                                            <select id="recurring_pattern" name="recurring_pattern" class="form-control">
                                                <option value="">Select Pattern</option>
                                                <option value="daily" {{ old('recurring_pattern') === 'daily' ? 'selected' : '' }}>Daily</option>
                                                <option value="weekly" {{ old('recurring_pattern') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                                <option value="monthly" {{ old('recurring_pattern') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="recurring_end_date" class="form-label">End Recurring On</label>
                                            <input type="date"
                                                   id="recurring_end_date"
                                                   name="recurring_end_date"
                                                   class="form-control"
                                                   value="{{ old('recurring_end_date') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Schedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar with Quick Info -->
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Schedule Guidelines</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Important Notes</h6>
                        <ul class="mb-0">
                            <li>Check for instructor availability before scheduling</li>
                            <li>Ensure vehicle is available for the selected time</li>
                            <li>Allow sufficient time between lessons</li>
                            <li>Practical lessons typically last 1-2 hours</li>
                            <li>Theory lessons usually last 1 hour</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Conflict Check</h6>
                        <p class="mb-0">The system will automatically check for scheduling conflicts with the selected instructor, vehicle, and student before saving.</p>
                    </div>
                </div>
            </div>

            <!-- Today's Schedule Preview -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Today's Schedule</h6>
                </div>
                <div class="card-body">
                    <div id="todaySchedule">
                        <p class="text-muted">Select an instructor to see their today's schedule...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.form-check-input:checked {
    background-color: #4e73df;
    border-color: #4e73df;
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
    // Recurring schedule toggle
    const recurringCheckbox = document.getElementById('is_recurring');
    const recurringOptions = document.getElementById('recurring_options');

    if (recurringCheckbox) {
        recurringCheckbox.addEventListener('change', function() {
            recurringOptions.style.display = this.checked ? 'block' : 'none';
        });

        // Show options if already checked (from old input)
        if (recurringCheckbox.checked) {
            recurringOptions.style.display = 'block';
        }
    }

    // Auto-calculate end time based on class type
    const classTypeSelect = document.getElementById('class_type');
    const startTimeInput = document.getElementById('start');
    const endTimeInput = document.getElementById('end');

    function updateEndTime() {
        if (startTimeInput.value && classTypeSelect.value) {
            const startTime = new Date(startTimeInput.value);
            let hours = 1; // default

            switch(classTypeSelect.value) {
                case 'practical':
                    hours = 2;
                    break;
                case 'theory':
                    hours = 1;
                    break;
                case 'test':
                    hours = 1;
                    break;
            }

            const endTime = new Date(startTime.getTime() + (hours * 60 * 60 * 1000));
            endTimeInput.value = endTime.toISOString().slice(0, 16);
        }
    }

    if (classTypeSelect && startTimeInput) {
        classTypeSelect.addEventListener('change', updateEndTime);
        startTimeInput.addEventListener('change', updateEndTime);
    }

    // Load instructor's today schedule
    const instructorSelect = document.getElementById('instructor');
    const todayScheduleDiv = document.getElementById('todaySchedule');

    instructorSelect.addEventListener('change', function() {
        const instructorId = this.value;
        if (instructorId) {
            // You can implement an AJAX call here to fetch instructor's today schedule
            todayScheduleDiv.innerHTML = '<p class="text-muted">Loading schedule...</p>';

            // Simulated loading - replace with actual AJAX call
            setTimeout(() => {
                todayScheduleDiv.innerHTML = `
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span>9:00 AM - 11:00 AM</span>
                            <span class="badge badge-success">Booked</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>2:00 PM - 4:00 PM</span>
                            <span class="badge badge-primary">Available</span>
                        </div>
                    </div>
                `;
            }, 1000);
        } else {
            todayScheduleDiv.innerHTML = '<p class="text-muted">Select an instructor to see their today\'s schedule...</p>';
        }
    });

    // Form validation
    const form = document.getElementById('scheduleForm');
    form.addEventListener('submit', function(e) {
        const startTime = new Date(startTimeInput.value);
        const endTime = new Date(endTimeInput.value);

        if (endTime <= startTime) {
            e.preventDefault();
            alert('End time must be after start time!');
            return false;
        }

        // Check if start time is in the past
        if (startTime <= new Date()) {
            e.preventDefault();
            alert('Schedule time cannot be in the past!');
            return false;
        }
    });

    // Set minimum date to today
    const today = new Date().toISOString().slice(0, 16);
    startTimeInput.setAttribute('min', today);
    endTimeInput.setAttribute('min', today);
});
</script>
@endpush
