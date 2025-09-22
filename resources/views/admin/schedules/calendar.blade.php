{{-- resources/views/admin/schedules/calendar.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Schedule Calendar')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Schedule Calendar</h1>
            <p class="text-muted mb-0">View and manage schedules in calendar format</p>
        </div>
        <div>
            <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary mr-2">
                <i class="fas fa-list"></i> List View
            </a>
            <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Schedule Lesson
            </a>
        </div>
    </div>

    <!-- Calendar Controls -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        </div>
                        <input type="month" id="monthPicker" class="form-control" value="{{ date('Y-m') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select id="viewType" class="form-control">
                        <option value="month">Month View</option>
                        <option value="week">Week View</option>
                        <option value="day">Day View</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-primary mr-2" onclick="goToToday()">
                            <i class="fas fa-calendar-day"></i> Today
                        </button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary" onclick="navigateCalendar('prev')">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="navigateCalendar('next')">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="card shadow">
        <div class="card-body p-0">
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Legend -->
    <div class="card shadow mt-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <h6 class="font-weight-bold mb-3">Legend</h6>
                    <div class="d-flex flex-wrap">
                        <div class="mr-4 mb-2">
                            <span class="badge badge-primary mr-1">■</span> Scheduled
                        </div>
                        <div class="mr-4 mb-2">
                            <span class="badge badge-warning mr-1">■</span> In Progress
                        </div>
                        <div class="mr-4 mb-2">
                            <span class="badge badge-success mr-1">■</span> Completed
                        </div>
                        <div class="mr-4 mb-2">
                            <span class="badge badge-danger mr-1">■</span> Cancelled
                        </div>
                        <div class="mr-4 mb-2">
                            <span class="badge badge-info mr-1">■</span> Theory
                        </div>
                        <div class="mr-4 mb-2">
                            <span class="badge badge-secondary mr-1">■</span> Test
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Details Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="scheduleModalBody">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2">Loading schedule details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a href="#" id="editScheduleBtn" class="btn btn-primary">Edit Schedule</a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<style>
.fc-event {
    cursor: pointer;
    border-radius: 4px;
    padding: 2px 4px;
    font-size: 0.875rem;
}

.fc-event-title {
    font-weight: 600;
}

.fc-daygrid-event {
    white-space: normal;
}

.badge {
    font-size: 16px;
    line-height: 1;
}

/* Custom calendar styling */
.fc-toolbar {
    padding: 1rem;
}

.fc-toolbar-title {
    font-size: 1.5rem !important;
    font-weight: 600;
    color: #5a5c69;
}

.fc-button {
    background-color: #4e73df;
    border-color: #4e73df;
    color: white;
}

.fc-button:hover {
    background-color: #2e59d9;
    border-color: #2e59d9;
}

.fc-button:focus {
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

.fc-day-today {
    background-color: rgba(78, 115, 223, 0.1) !important;
}

.schedule-practical {
    background-color: #4e73df;
    border-color: #4e73df;
}

.schedule-theory {
    background-color: #36b9cc;
    border-color: #36b9cc;
}

.schedule-test {
    background-color: #f6c23e;
    border-color: #f6c23e;
    color: #000 !important;
}

.schedule-cancelled {
    background-color: #e74a3b;
    border-color: #e74a3b;
    opacity: 0.7;
}

.schedule-completed {
    background-color: #1cc88a;
    border-color: #1cc88a;
}

.schedule-in-progress {
    background-color: #f6c23e;
    border-color: #f6c23e;
    color: #000 !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    let calendar;

    // Initialize calendar
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'title',
            center: '',
            right: 'prev,next today'
        },
        events: function(info, successCallback, failureCallback) {
            // Fetch events from server
            fetch(`{{ route('admin.schedules.calendar') }}?start=${info.startStr}&end=${info.endStr}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                // Process events and add custom styling
                const events = data.map(event => ({
                    ...event,
                    classNames: [
                        `schedule-${event.extendedProps.class_type || 'practical'}`,
                        `schedule-${event.extendedProps.status || 'scheduled'}`
                    ]
                }));
                successCallback(events);
            })
            .catch(error => {
                console.error('Error fetching events:', error);
                failureCallback(error);
            });
        },
        eventClick: function(info) {
            showScheduleDetails(info.event.id);
            info.jsEvent.preventDefault();
        },
        eventDidMount: function(info) {
            // Add tooltip
            info.el.title = `${info.event.extendedProps.student} with ${info.event.extendedProps.instructor}`;
        },
        height: 'auto',
        dayMaxEvents: 3,
        moreLinkClick: 'popover',
        eventDisplay: 'block'
    });

    calendar.render();

    // Month picker
    document.getElementById('monthPicker').addEventListener('change', function() {
        const [year, month] = this.value.split('-');
        calendar.gotoDate(new Date(year, month - 1, 1));
    });

    // View type selector
    document.getElementById('viewType').addEventListener('change', function() {
        calendar.changeView(this.value === 'month' ? 'dayGridMonth' :
                           this.value === 'week' ? 'timeGridWeek' : 'timeGridDay');
    });

    // Navigation functions
    window.goToToday = function() {
        calendar.today();
        document.getElementById('monthPicker').value = new Date().toISOString().slice(0, 7);
    };

    window.navigateCalendar = function(direction) {
        if (direction === 'prev') {
            calendar.prev();
        } else {
            calendar.next();
        }

        // Update month picker
        const currentDate = calendar.getDate();
        document.getElementById('monthPicker').value = currentDate.toISOString().slice(0, 7);
    };

    // Show schedule details in modal
    window.showScheduleDetails = function(scheduleId) {
        const modal = document.getElementById('scheduleModal');
        const modalBody = document.getElementById('scheduleModalBody');
        const editBtn = document.getElementById('editScheduleBtn');

        // Show modal
        $(modal).modal('show');

        // Update edit button
        editBtn.href = `/admin/schedules/${scheduleId}/edit`;

        // Load schedule details
        fetch(`/admin/schedules/${scheduleId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(schedule => {
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-user-graduate mr-2"></i>Student Details
                        </h6>
                        <p><strong>Name:</strong> ${schedule.student_name}</p>
                        <p><strong>Email:</strong> ${schedule.student_email}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-success mb-3">
                            <i class="fas fa-chalkboard-teacher mr-2"></i>Instructor Details
                        </h6>
                        <p><strong>Name:</strong> ${schedule.instructor_name}</p>
                        <p><strong>Email:</strong> ${schedule.instructor_email}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-info mb-3">
                            <i class="fas fa-calendar-alt mr-2"></i>Schedule Details
                        </h6>
                        <p><strong>Date:</strong> ${new Date(schedule.start).toLocaleDateString()}</p>
                        <p><strong>Time:</strong> ${new Date(schedule.start).toLocaleTimeString()} - ${new Date(schedule.end).toLocaleTimeString()}</p>
                        <p><strong>Type:</strong> <span class="badge badge-primary">${schedule.class_type}</span></p>
                        <p><strong>Status:</strong> <span class="badge badge-${getStatusColor(schedule.status)}">${schedule.status}</span></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-warning mb-3">
                            <i class="fas fa-info-circle mr-2"></i>Additional Info
                        </h6>
                        <p><strong>Course:</strong> ${schedule.course_name || 'N/A'}</p>
                        <p><strong>Vehicle:</strong> ${schedule.vehicle_plate || 'N/A'}</p>
                        <p><strong>Attended:</strong> ${schedule.attended ? 'Yes' : 'No'}</p>
                    </div>
                </div>
                ${schedule.notes ? `
                    <div class="border-top pt-3 mt-3">
                        <h6 class="mb-2"><i class="fas fa-sticky-note mr-2"></i>Notes</h6>
                        <div class="bg-light p-3 rounded">
                            <p class="mb-0">${schedule.notes}</p>
                        </div>
                    </div>
                ` : ''}
            `;
        })
        .catch(error => {
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Error loading schedule details. Please try again.
                </div>
            `;
        });
    };

    function getStatusColor(status) {
        const colors = {
            'scheduled': 'primary',
            'in_progress': 'warning',
            'completed': 'success',
            'cancelled': 'danger'
        };
        return colors[status] || 'secondary';
    }
});
</script>
@endpush
