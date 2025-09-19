<?php
// app/Http/Controllers/Admin/AdminScheduleController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Course;
use App\Models\Fleet;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AdminScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = Schedule::with(['student', 'instructor', 'course', 'car']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                  ->orWhere('lname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('instructor', function($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                  ->orWhere('lname', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('start', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('end', '<=', $request->date_to . ' 23:59:59');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by instructor
        if ($request->filled('instructor_id')) {
            $query->where('instructor', $request->instructor_id);
        }

        // Filter by school
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'start');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $schedules = $query->paginate(20);
        $instructors = User::instructors()->active()->get();
        $schools = School::active()->get();

        return view('admin.schedules.index', compact('schedules', 'instructors', 'schools'));
    }

    public function create()
    {
        $students = User::students()->active()->get();
        $instructors = User::instructors()->active()->get();
        $courses = Course::active()->get();
        $vehicles = Fleet::where('status', 'available')->get();
        $schools = School::active()->get();

        return view('admin.schedules.create', compact('students', 'instructors', 'courses', 'vehicles', 'schools'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student' => 'required|exists:users,id',
            'instructor' => 'required|exists:users,id',
            'course' => 'required|exists:courses,id',
            'car' => 'required|exists:fleets,id',
            'start' => 'required|date|after:now',
            'end' => 'required|date|after:start',
            'class_type' => 'required|in:practical,theory,test',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'school_id' => 'required|exists:schools,id',
            'notes' => 'nullable|string|max:1000',
            'is_recurring' => 'boolean',
            'recurring_pattern' => 'nullable|in:daily,weekly,monthly',
            'recurring_end_date' => 'nullable|date|after:start',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check for schedule conflicts
        $conflicts = $this->checkScheduleConflicts($request);
        if ($conflicts->isNotEmpty()) {
            return back()->with('error', 'Schedule conflict detected! Please choose a different time.')
                        ->withInput();
        }

        $scheduleData = $request->all();
        $scheduleData['attended'] = false;
        $scheduleData['lessons_deducted'] = 0;

        $schedule = Schedule::create($scheduleData);

        // Handle recurring schedules
        if ($request->is_recurring && $request->recurring_pattern && $request->recurring_end_date) {
            $this->createRecurringSchedules($schedule, $request);
        }

        return redirect()->route('admin.schedules.show', $schedule)
            ->with('success', 'Schedule created successfully!');
    }

    public function show(Schedule $schedule)
    {
        $schedule->load(['student', 'instructor', 'course', 'car']);
        return view('admin.schedules.show', compact('schedule'));
    }

    public function edit(Schedule $schedule)
    {
        $students = User::students()->active()->get();
        $instructors = User::instructors()->active()->get();
        $courses = Course::active()->get();
        $vehicles = Fleet::where('status', 'available')->get();
        $schools = School::active()->get();

        return view('admin.schedules.edit', compact('schedule', 'students', 'instructors', 'courses', 'vehicles', 'schools'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validator = Validator::make($request->all(), [
            'student' => 'required|exists:users,id',
            'instructor' => 'required|exists:users,id',
            'course' => 'required|exists:courses,id',
            'car' => 'required|exists:fleets,id',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'class_type' => 'required|in:practical,theory,test',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'school_id' => 'required|exists:schools,id',
            'notes' => 'nullable|string|max:1000',
            'instructor_notes' => 'nullable|string|max:1000',
            'lessons_completed' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check for schedule conflicts (excluding current schedule)
        $conflicts = $this->checkScheduleConflicts($request, $schedule->id);
        if ($conflicts->isNotEmpty()) {
            return back()->with('error', 'Schedule conflict detected! Please choose a different time.')
                        ->withInput();
        }

        $schedule->update($request->all());

        return redirect()->route('admin.schedules.show', $schedule)
            ->with('success', 'Schedule updated successfully!');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule deleted successfully!');
    }

    public function markAttended(Schedule $schedule)
    {
        $schedule->update([
            'attended' => !$schedule->attended,
            'status' => $schedule->attended ? 'scheduled' : 'completed',
            'lessons_completed' => $schedule->attended ? 0 : 1,
            'lessons_deducted' => $schedule->attended ? 0 : 1,
        ]);

        $status = $schedule->attended ? 'marked as attended' : 'marked as not attended';
        return back()->with('success', "Schedule {$status} successfully!");
    }

    public function calendar(Request $request)
    {
        $start = $request->get('start', now()->startOfMonth());
        $end = $request->get('end', now()->endOfMonth());

        $schedules = Schedule::with(['student', 'instructor', 'course', 'car'])
            ->whereBetween('start', [$start, $end])
            ->get()
            ->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'title' => $schedule->student->full_name . ' - ' . $schedule->course->name,
                    'start' => $schedule->start->toISOString(),
                    'end' => $schedule->end->toISOString(),
                    'backgroundColor' => $this->getScheduleColor($schedule->status),
                    'borderColor' => $this->getScheduleColor($schedule->status),
                    'url' => route('admin.schedules.show', $schedule),
                    'extendedProps' => [
                        'student' => $schedule->student->full_name,
                        'instructor' => $schedule->instructor->full_name,
                        'status' => $schedule->status,
                        'class_type' => $schedule->class_type,
                    ]
                ];
            });

        if ($request->ajax()) {
            return response()->json($schedules);
        }

        return view('admin.schedules.calendar', compact('schedules'));
    }

    private function checkScheduleConflicts(Request $request, $excludeId = null)
    {
        $query = Schedule::where(function($q) use ($request) {
            $q->where('instructor', $request->instructor)
              ->orWhere('car', $request->car)
              ->orWhere('student', $request->student);
        })
        ->where(function($q) use ($request) {
            $q->whereBetween('start', [$request->start, $request->end])
              ->orWhereBetween('end', [$request->start, $request->end])
              ->orWhere(function($q2) use ($request) {
                  $q2->where('start', '<=', $request->start)
                     ->where('end', '>=', $request->end);
              });
        })
        ->where('status', '!=', 'cancelled');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get();
    }

    private function createRecurringSchedules(Schedule $originalSchedule, Request $request)
    {
        $current = Carbon::parse($request->start);
        $endDate = Carbon::parse($request->recurring_end_date);
        $pattern = $request->recurring_pattern;

        while ($current->lt($endDate)) {
            switch ($pattern) {
                case 'daily':
                    $current->addDay();
                    break;
                case 'weekly':
                    $current->addWeek();
                    break;
                case 'monthly':
                    $current->addMonth();
                    break;
            }

            if ($current->lte($endDate)) {
                $duration = Carbon::parse($request->end)->diffInMinutes(Carbon::parse($request->start));

                Schedule::create([
                    'student' => $originalSchedule->student,
                    'instructor' => $originalSchedule->instructor,
                    'course' => $originalSchedule->course,
                    'car' => $originalSchedule->car,
                    'start' => $current->copy(),
                    'end' => $current->copy()->addMinutes($duration),
                    'class_type' => $originalSchedule->class_type,
                    'status' => 'scheduled',
                    'school_id' => $originalSchedule->school_id,
                    'notes' => $originalSchedule->notes,
                    'is_recurring' => true,
                    'recurring_pattern' => $pattern,
                    'attended' => false,
                    'lessons_deducted' => 0,
                ]);
            }
        }
    }

    private function getScheduleColor($status)
    {
        switch ($status) {
            case 'scheduled':
                return '#007bff'; // Blue
            case 'in_progress':
                return '#ffc107'; // Yellow
            case 'completed':
                return '#28a745'; // Green
            case 'cancelled':
                return '#dc3545'; // Red
            default:
                return '#6c757d'; // Gray
        }
    }
}
