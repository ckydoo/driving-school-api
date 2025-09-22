{{-- resources/views/admin/schedules/edit.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Edit Schedule')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit Schedule</h1>
            <p class="text-muted mb-0">Modify lesson schedule details</p>
        </div>
        <div>
            <a href="{{ route('admin.schedules.show', $schedule) }}" class="btn btn-secondary mr-2">
                <i class="fas fa-arrow-left"></i> Back to Details
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Schedule Details</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.schedules.update', $schedule) }}" method="POST" id="scheduleEditForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Student Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="student" class="form-label">Student <span class="text-danger">*</span></label>
                                <select id="student" name="student" class="form-control @error('student') is-invalid @enderror" required>
                                    <option value="">Select Student</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ (old('student', $schedule->student) == $student->id) ? 'selected' : '' }}>
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
                                        <option value="{{ $instructor->id }}" {{ (old('instructor', $schedule->instructor) == $instructor->id) ? 'selected' : '' }}>
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
                                        <option value="{{ $course->id }}" {{ (old('course', $schedule->course) == $course->id) ? 'selected' : '' }}>
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
                                        <option value="{{ $vehicle->id }}" {{ (old('car', $schedule->car) == $vehicle->id) ? 'selected' : '' }}>
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
                                       value="{{ old('start', \Carbon\Carbon::parse($schedule->start)->format('Y-m-d\TH:i')) }}"
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
                                       value="{{ old('end', \Carbon\Carbon::parse($schedule->end)->format('Y-m-d\TH:i')) }}"
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
                                    <option value="practical" {{ old('class_type', $schedule->class_type) === 'practical' ? 'selected' : '' }}>Practical</option>
                                    <option value="theory" {{ old('class_type', $schedule->class_type) === 'theory' ? 'selected' : '' }}>Theory</option>
                                    <option value="test" {{ old('class_type', $schedule->class_type) === 'test' ? 'selected' : '' }}>Test</option>
                                </select>
                                @error('class_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                                    <option value="scheduled" {{ old('status', $schedule->status) === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                    <option value="in_progress" {{ old('status', $schedule->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ old('status', $schedule->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ old('status', $schedule->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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
                                            <option value="{{ $school->id }}" {{ old('school_id', $schedule->school_id) == $school->id ? 'selected' : '' }}>
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

                        <!-- Lesson Tracking -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="lessons_completed" class="form-label">Lessons Completed</label>
                                <input type="number"
                                       id="lessons_completed"
                                       name="lessons_completed"
                                       class="form-control @error('lessons_completed') is-invalid @enderror"
                                       value="{{ old('lessons_completed', $schedule->lessons_completed ?? 0) }}"
                                       min="0">
                                @error('lessons_completed')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4">
                                    <input type="checkbox"
                                           id="attended"
                                           name="attended"
                                           class="form-check-input"
                                           value="1"
                                           {{ old('attended', $schedule->attended) ? 'checked' : '' }}>
                                    <label for="attended" class="form-check-label">
                                        Student attended this lesson
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea id="notes"
                                      name="notes"
                                      class="form-control @error('notes') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Any additional notes for this schedule...">{{ old('notes', $schedule->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Instructor Notes -->
                        <div class="mb-3">
                            <label for="instructor_notes" class="form-label">Instructor Notes</label>
                            <textarea id="instructor_notes"
                                      name="instructor_notes"
                                      class="form-control @error('instructor_notes') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Notes from the instructor...">{{ old('instructor_notes', $schedule->instructor_notes) }}</textarea>
                            @error('instructor_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('admin.schedules.show', $schedule) }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="button" class="btn btn-danger ml-2" onclick="confirmDelete()">
                                    <i class="fas fa-trash"></i> Delete Schedule
                                </button>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Schedule
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
                    <h6 class="m-0 font-weight-bold text-primary">Current Schedule</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Current Details</h6>
                        <ul class="mb-0 small">
                            <li><strong>Student:</strong> {{ $schedule->student->full_name ?? 'Unknown' }}</li>
                            <li><strong>Instructor:</strong> {{ $schedule->instructor->full_name ?? 'Unknown' }}</li>
                            <li><strong>Date:</strong> {{ $schedule->start ? $schedule->start->format('M d, Y') : 'No date' }}</li>
                            <li><strong>Time:</strong> {{ $schedule->start && $schedule->end ? $schedule->start->format('g:i A') . ' - ' . $schedule->end->format('g:i A') : 'No time' }}</li>
                            <li><strong>Type:</strong> {{ ucfirst($schedule->class_type) }}</li>
                            <li><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $schedule->status)) }}</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Important</h6>
                        <p class="mb-0 small">Changes to date, time, instructor, or vehicle will be checked for conflicts before saving.</p>
                    </div>
                </div>
            </div>

            <!-- Schedule History -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Change History</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <div class="small text-muted">{{ $schedule->created_at->format('M d, Y g:i A') }}</div>
                                <div class="small">Schedule created</div>
                            </div>
                        </div>
                        @if($schedule->updated_at != $schedule->created_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <div class="small text-muted">{{ $schedule->updated_at->format('M d, Y g:i A') }}</div>
                                    <div class="small">Schedule updated</div>
                                </div>
                            </div>
                        @endif
                        @if($schedule->attended)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <div class="small text-muted">{{ $schedule->updated_at->format('M d, Y g:i A') }}</div>
                                    <div class="small">Marked as attended</div>
                                </div>
                            </div>
                        @endif
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
                <h5 class="modal-title">Delete Schedule</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this schedule? This action cannot be undone.</p>
                <div class="alert alert-warning">
                    <strong>Warning:</strong> This will permanently remove the schedule from the system.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Schedule</button>
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
</style>
@endpush

@push('scripts')
<script>
function confirmDelete() {
    $('#deleteModal').modal('show');
}

document.addEventListener('DOMContentLoaded', function() {
    const startTimeInput = document.getElementById('start');
    const endTimeInput = document.getElementById('end');
    const form = document.getElementById('scheduleEditForm');

    // Form validation
    form.addEventListener('submit', function(e) {
        const startTime = new Date(startTimeInput.value);
        const endTime = new Date(endTimeInput.value);

        if (endTime <= startTime) {
            e.preventDefault();
            alert('End time must be after start time!');
            return false;
        }
    });

    // Auto-update lessons completed when attended is checked
    const attendedCheckbox = document.getElementById('attended');
    const lessonsCompletedInput = document.getElementById('lessons_completed');

    attendedCheckbox.addEventListener('change', function() {
        if (this.checked && lessonsCompletedInput.value == 0) {
            lessonsCompletedInput.value = 1;
        } else if (!this.checked) {
            lessonsCompletedInput.value = 0;
        }
    });

    // Status change handling
    const statusSelect = document.getElementById('status');
    statusSelect.addEventListener('change', function() {
        if (this.value === 'completed') {
            attendedCheckbox.checked = true;
            if (lessonsCompletedInput.value == 0) {
                lessonsCompletedInput.value = 1;
            }
        } else if (this.value === 'cancelled') {
            attendedCheckbox.checked = false;
            lessonsCompletedInput.value = 0;
        }
    });
});
</script>
@endpush
