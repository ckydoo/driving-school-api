{{-- resources/views/admin/schedules/show.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Schedule Details')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Schedule Details</h1>
            <p class="text-muted mb-0">View and manage lesson schedule</p>
        </div>
        <div>
            <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary mr-2">
                <i class="fas fa-arrow-left"></i> Back to Schedules
            </a>
            <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Schedule
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Schedule Details -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Schedule Information</h6>
                    <span class="badge badge-{{
                        $schedule->status === 'completed' ? 'success' :
                        ($schedule->status === 'in_progress' ? 'warning' :
                        ($schedule->status === 'cancelled' ? 'danger' : 'primary'))
                    }} badge-lg">
                        {{ ucfirst(str_replace('_', ' ', $schedule->status)) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Date & Time -->
                        <div class="col-md-6 mb-4">
                            <div class="border-left-primary pl-3">
                                <h6 class="font-weight-bold text-primary mb-1">
                                    <i class="fas fa-calendar-alt mr-2"></i>Date & Time
                                </h6>
                                <p class="h5 mb-1">{{ \Carbon\Carbon::parse($schedule->start)->format('M d, Y') }}</p>
                                <p class="text-muted mb-0">
                                    {{ \Carbon\Carbon::parse($schedule->start)->format('g:i A') }} -
                                    {{ \Carbon\Carbon::parse($schedule->end)->format('g:i A') }}
                                    ({{ \Carbon\Carbon::parse($schedule->start)->diffInHours(\Carbon\Carbon::parse($schedule->end)) }} hour{{ \Carbon\Carbon::parse($schedule->start)->diffInHours(\Carbon\Carbon::parse($schedule->end)) > 1 ? 's' : '' }})
                                </p>
                            </div>
                        </div>

                        <!-- Class Type -->
                        <div class="col-md-6 mb-4">
                            <div class="border-left-info pl-3">
                                <h6 class="font-weight-bold text-info mb-1">
                                    <i class="fas fa-tag mr-2"></i>Class Type
                                </h6>
                                <p class="h5 mb-0">
                                    <span class="badge badge-{{ $schedule->class_type === 'practical' ? 'primary' : ($schedule->class_type === 'theory' ? 'info' : 'warning') }} badge-lg">
                                        {{ ucfirst($schedule->class_type) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Student Information -->
                        <div class="col-md-6 mb-4">
                            <div class="border-left-success pl-3">
                                <h6 class="font-weight-bold text-success mb-2">
                                    <i class="fas fa-user-graduate mr-2"></i>Student
                                </h6>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-md rounded-circle bg-primary text-white mr-3">
                                        {{ $schedule->student ? substr($schedule->student->full_name ?? 'U', 0, 1) : 'U' }}
                                    </div>
                                    <div>
                                        <p class="h6 mb-1">{{ $schedule->student->full_name ?? 'Unknown Student' }}</p>
                                        <p class="text-muted mb-1">{{ $schedule->student->email ?? 'No email' }}</p>
                                        <p class="text-muted mb-0">{{ $schedule->student->phone ?? 'No phone' }}</p>
                                    </div>
                                </div>
                                @if($schedule->student)
                                <div class="mt-2">
                                    <a href="{{ route('admin.users.show', $schedule->student) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View Profile
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Instructor Information -->
                        <div class="col-md-6 mb-4">
                            <div class="border-left-warning pl-3">
                                <h6 class="font-weight-bold text-warning mb-2">
                                    <i class="fas fa-chalkboard-teacher mr-2"></i>Instructor
                                </h6>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-md rounded-circle bg-success text-white mr-3">
                                        {{ $schedule->instructor ? substr($schedule->instructor->full_name ?? 'U', 0, 1) : 'U' }}
                                    </div>
                                    <div>
                                        <p class="h6 mb-1">{{ $schedule->instructor->full_name ?? 'Unknown Instructor' }}</p>
                                        <p class="text-muted mb-1">{{ $schedule->instructor->email ?? 'No email' }}</p>
                                        <p class="text-muted mb-0">{{ $schedule->instructor->phone ?? 'No phone' }}</p>
                                    </div>
                                </div>
                                @if($schedule->instructor)
                                <div class="mt-2">
                                    <a href="{{ route('admin.users.show', $schedule->instructor) }}" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-eye"></i> View Profile
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Course Information -->
                        <div class="col-md-6 mb-4">
                            <div class="border-left-info pl-3">
                                <h6 class="font-weight-bold text-info mb-1">
                                    <i class="fas fa-book mr-2"></i>Course
                                </h6>
                                <p class="h6 mb-1">{{ $schedule->course->name ?? 'No course assigned' }}</p>
                                <p class="text-muted mb-0">
                                    @if($schedule->course)
                                        Price: ${{ number_format($schedule->course->price, 2) }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Vehicle Information -->
                        <div class="col-md-6 mb-4">
                            <div class="border-left-danger pl-3">
                                <h6 class="font-weight-bold text-danger mb-1">
                                    <i class="fas fa-car mr-2"></i>Vehicle
                                </h6>
                                @if($schedule->car)
                                    <p class="h6 mb-1">{{ $schedule->car->carplate ?? 'No plate' }}</p>
                                    <p class="text-muted mb-0">
                                        {{ $schedule->car->make ?? 'Unknown' }} {{ $schedule->car->model ?? '' }}
                                        @if($schedule->car->modelyear) ({{ $schedule->car->modelyear }}) @endif
                                    </p>
                                @else
                                    <p class="text-muted mb-0">No vehicle assigned</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    @if($schedule->notes)
                    <div class="border-top pt-4">
                        <h6 class="font-weight-bold mb-2">
                            <i class="fas fa-sticky-note mr-2"></i>Notes
                        </h6>
                        <div class="bg-light p-3 rounded">
                            <p class="mb-0">{{ $schedule->notes }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Instructor Notes Section -->
                    @if($schedule->instructor_notes)
                    <div class="border-top pt-4 mt-4">
                        <h6 class="font-weight-bold mb-2">
                            <i class="fas fa-user-edit mr-2"></i>Instructor Notes
                        </h6>
                        <div class="bg-warning-light p-3 rounded">
                            <p class="mb-0">{{ $schedule->instructor_notes }}</p>
                        </div>
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
                        @if($schedule->status === 'scheduled' && !$schedule->attended)
                            <form action="{{ route('admin.schedules.mark-attended', $schedule) }}" method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Mark this lesson as attended?')">
                                    <i class="fas fa-check"></i> Mark as Attended
                                </button>
                            </form>
                        @elseif($schedule->attended)
                            <form action="{{ route('admin.schedules.mark-attended', $schedule) }}" method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-block" onclick="return confirm('Mark this lesson as not attended?')">
                                    <i class="fas fa-undo"></i> Mark as Not Attended
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-outline-primary btn-block mb-2">
                            <i class="fas fa-edit"></i> Edit Schedule
                        </a>

                        @if($schedule->status !== 'cancelled')
                            <button type="button" class="btn btn-outline-danger btn-block" onclick="cancelSchedule()">
                                <i class="fas fa-times"></i> Cancel Schedule
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Schedule Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Schedule Details</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-right">
                                <div class="h5 font-weight-bold">{{ $schedule->attended ? 'Yes' : 'No' }}</div>
                                <div class="small text-muted">Attended</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h5 font-weight-bold">{{ $schedule->lessons_completed ?? 0 }}</div>
                            <div class="small text-muted">Lessons Done</div>
                        </div>
                    </div>
                    <div class="border-top mt-3 pt-3">
                        <div class="small">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Created:</span>
                                <span>{{ $schedule->created_at->format('M d, Y g:i A') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Updated:</span>
                                <span>{{ $schedule->updated_at->format('M d, Y g:i A') }}</span>
                            </div>
                            @if($schedule->school)
                                <div class="d-flex justify-content-between">
                                    <span>School:</span>
                                    <span class="badge badge-info">{{ $schedule->school->name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Schedules -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Schedules</h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <p class="text-muted mb-2">Other recent schedules for this student:</p>
                        <div id="recentSchedules">
                            <p class="text-muted">Loading...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Schedule Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Schedule</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.schedules.update', $schedule) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p>Are you sure you want to cancel this schedule?</p>
                    <div class="form-group">
                        <label for="cancellation_reason">Reason for cancellation:</label>
                        <textarea id="cancellation_reason" name="instructor_notes" class="form-control" rows="3" placeholder="Enter reason..."></textarea>
                    </div>
                    <input type="hidden" name="status" value="cancelled">
                    <!-- Keep other fields -->
                    <input type="hidden" name="student" value="{{ $schedule->student }}">
                    <input type="hidden" name="instructor" value="{{ $schedule->instructor }}">
                    <input type="hidden" name="course" value="{{ $schedule->course }}">
                    <input type="hidden" name="car" value="{{ $schedule->car }}">
                    <input type="hidden" name="start" value="{{ $schedule->start }}">
                    <input type="hidden" name="end" value="{{ $schedule->end }}">
                    <input type="hidden" name="class_type" value="{{ $schedule->class_type }}">
                    <input type="hidden" name="school_id" value="{{ $schedule->school_id }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Schedule</button>
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
.border-left-danger { border-left: 0.25rem solid #e74a3b !important; }

.avatar {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
}

.avatar-md {
    width: 48px;
    height: 48px;
}

.badge-lg {
    font-size: 0.9em;
    padding: 0.5em 0.75em;
}

.bg-warning-light {
    background-color: #fff3cd;
}

.border-right {
    border-right: 1px solid #e3e6f0;
}
</style>
@endpush

@push('scripts')
<script>
function cancelSchedule() {
    $('#cancelModal').modal('show');
}

// Load recent schedules for this student
document.addEventListener('DOMContentLoaded', function() {
    const studentId = {{ $schedule->student }};
    const currentScheduleId = {{ $schedule->id }};

    // Simulate loading recent schedules - replace with actual AJAX call
    setTimeout(() => {
        const recentSchedulesDiv = document.getElementById('recentSchedules');
        recentSchedulesDiv.innerHTML = `
            <div class="mb-2">
                <div class="d-flex justify-content-between">
                    <small class="text-muted">Jan 15, 2024</small>
                    <span class="badge badge-success badge-sm">Completed</span>
                </div>
                <div class="font-weight-bold small">Practical Lesson</div>
            </div>
            <div class="mb-2">
                <div class="d-flex justify-content-between">
                    <small class="text-muted">Jan 10, 2024</small>
                    <span class="badge badge-success badge-sm">Completed</span>
                </div>
                <div class="font-weight-bold small">Theory Lesson</div>
            </div>
        `;
    }, 1000);
});
</script>
@endpush
