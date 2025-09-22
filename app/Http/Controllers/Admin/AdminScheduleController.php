<?php
// app/Http/Controllers/Admin/AdminScheduleController.php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Fleet;
use App\Models\Course;
use App\Models\School;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminScheduleController extends Controller
{

    /**
     * Ensure admin access and get current user
     */
    protected function ensureAdminAccess()
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }
        return $user;
    }

    /**
     * Apply school filtering to query based on user role
     */
    protected function applySchoolFilter($query, $currentUser)
    {
        // If school admin, restrict to their school only
        if (!$currentUser->isSuperAdmin() && $currentUser->school_id) {
            $query->where('school_id', $currentUser->school_id);
        }

        return $query;
    }

    /**
     * Get available schools for dropdowns based on user role
     */
    protected function getAvailableSchools($currentUser)
    {
        if ($currentUser->isSuperAdmin()) {
            return School::active()->orderBy('name')->get();
        } else {
            return $currentUser->school ? collect([$currentUser->school]) : collect();
        }
    }

    /**
     * Get available students based on user's school
     */
    protected function getAvailableStudents($currentUser)
    {
        $query = User::where('role', 'student')->where('status', 'active');

        if (!$currentUser->isSuperAdmin() && $currentUser->school_id) {
            $query->where('school_id', $currentUser->school_id);
        }

        return $query->orderBy('fname')->orderBy('lname')->get();
    }

    /**
     * Get available instructors based on user's school
     */
    protected function getAvailableInstructors($currentUser)
    {

        $query = User::where('role', 'instructor')->where('status', 'active');

        if (!$currentUser->isSuperAdmin() && $currentUser->school_id) {
            $query->where('school_id', $currentUser->school_id);
        }

        return $query->orderBy('fname')->orderBy('lname')->get();
    }

    /**
 * FIXED INDEX METHOD - Display schedules with proper school filtering
 */
public function index(Request $request)
{
    $currentUser = $this->ensureAdminAccess();

    // Eager load all relationships to prevent N+1 queries
    $query = Schedule::with(['student', 'instructor', 'course', 'car']);

    // Apply school filtering first - this is the key fix!
    $query = $this->applySchoolFilter($query, $currentUser);

    // Search functionality - fixed to work with actual field names
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->whereHas('student', function($subQuery) use ($search) {
                $subQuery->where('fname', 'like', "%{$search}%")
                         ->orWhere('lname', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('instructor', function($subQuery) use ($search) {
                $subQuery->where('fname', 'like', "%{$search}%")
                         ->orWhere('lname', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
            });
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

    // Filter by instructor - fixed field name
    if ($request->filled('instructor_id')) {
        $query->where('instructor', $request->instructor_id);
    }

    // Super admins can filter by school, school admins are already filtered
    if ($currentUser->isSuperAdmin() && $request->filled('school_id')) {
        $query->where('school_id', $request->school_id);
    }

    // Sort
    $sortBy = $request->get('sort_by', 'start');
    $sortOrder = $request->get('sort_order', 'desc');
    $query->orderBy($sortBy, $sortOrder);

    $schedules = $query->paginate(20);

    // Get filtered data for dropdowns
    $instructors = $this->getAvailableInstructors($currentUser);
    $schools = $this->getAvailableSchools($currentUser);

    return view('admin.schedules.index', compact('schedules', 'instructors', 'schools', 'currentUser'));
}

   /**
 * FIXED CREATE METHOD - Show form with school-specific data
 */
public function create()
{
    $currentUser = $this->ensureAdminAccess();

    $students = $this->getAvailableStudents($currentUser);
    $instructors = $this->getAvailableInstructors($currentUser);

    // Get courses and vehicles based on school
    $coursesQuery = Course::where('status', 'active');
    $vehiclesQuery = Fleet::where('status', 'available');

    if (!$currentUser->isSuperAdmin() && $currentUser->school_id) {
        $coursesQuery->where('school_id', $currentUser->school_id);
        $vehiclesQuery->where('school_id', $currentUser->school_id);
    }

    $courses = $coursesQuery->get();
    $vehicles = $vehiclesQuery->get();
    $schools = $this->getAvailableSchools($currentUser);

    return view('admin.schedules.create', compact('students', 'instructors', 'courses', 'vehicles', 'schools', 'currentUser'));
}


    /**
 * FIXED STORE METHOD - Create schedule with proper validation
 */
public function store(Request $request)
{
    $currentUser = $this->ensureAdminAccess();

    $validator = Validator::make($request->all(), [
        'student' => 'required|exists:users,id',
        'instructor' => 'required|exists:users,id',
        'course' => 'required|exists:courses,id',
        'car' => 'required|exists:fleet,id', // Fixed: 'fleet' table not 'fleets'
        'start' => 'required|date|after:now',
        'end' => 'required|date|after:start',
        'class_type' => 'required|in:practical,theory,test',
        'status' => 'required|in:scheduled,in_progress,completed,cancelled',
        'school_id' => $currentUser->isSuperAdmin() ? 'required|exists:schools,id' : 'nullable',
        'notes' => 'nullable|string|max:1000',
        'is_recurring' => 'boolean',
        'recurring_pattern' => 'nullable|in:daily,weekly,monthly',
        'recurring_end_date' => 'nullable|date|after:start',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // Additional validation: ensure student and instructor belong to the correct school
    $schoolId = $currentUser->isSuperAdmin() ? $request->school_id : $currentUser->school_id;

    $student = User::find($request->student);
    $instructor = User::find($request->instructor);

    if (!$currentUser->isSuperAdmin()) {
        if ($student && $student->school_id !== $currentUser->school_id) {
            return back()->with('error', 'Selected student does not belong to your school.');
        }
        if ($instructor && $instructor->school_id !== $currentUser->school_id) {
            return back()->with('error', 'Selected instructor does not belong to your school.');
        }
    }

    // Check for schedule conflicts
    $conflicts = $this->checkScheduleConflicts($request);
    if ($conflicts && $conflicts->isNotEmpty()) {
        return back()->with('error', 'Schedule conflict detected! Please choose a different time.')
                    ->withInput();
    }

    $scheduleData = $request->all();
    $scheduleData['school_id'] = $schoolId;
    $scheduleData['attended'] = false;
    $scheduleData['lessons_deducted'] = 0;
    $scheduleData['lessons_completed'] = 0;

    $schedule = Schedule::create($scheduleData);

    // Handle recurring schedules
    if ($request->is_recurring && $request->recurring_pattern && $request->recurring_end_date) {
        $this->createRecurringSchedules($schedule, $request);
    }

    return redirect()->route('admin.schedules.show', $schedule)
        ->with('success', 'Schedule created successfully!');
}

    /**
     * FIXED SHOW METHOD - Display schedule with access control
     */
    public function show(Schedule $schedule)
    {
        $currentUser = $this->ensureAdminAccess();

        // Check if user can access this schedule
        if (!$currentUser->isSuperAdmin() && $schedule->school_id !== $currentUser->school_id) {
            abort(403, 'Access denied. You can only view schedules from your school.');
        }

        $schedule->load(['student', 'instructor', 'course', 'car', 'school']);
        return view('admin.schedules.show', compact('schedule', 'currentUser'));
    }

    /**
     * FIXED EDIT METHOD - Edit form with access control
     */
    public function edit(Schedule $schedule)
    {
        $currentUser = $this->ensureAdminAccess();

        // Check if user can edit this schedule
        if (!$currentUser->isSuperAdmin() && $schedule->school_id !== $currentUser->school_id) {
            abort(403, 'Access denied. You can only edit schedules from your school.');
        }

        $students = $this->getAvailableStudents($currentUser);
        $instructors = $this->getAvailableInstructors($currentUser);

        // Get courses and vehicles based on school
        $coursesQuery = Course::where('status', 'active');
        $vehiclesQuery = Fleet::where('status', 'available');

        if (!$currentUser->isSuperAdmin() && $currentUser->school_id) {
            $coursesQuery->where('school_id', $currentUser->school_id);
            $vehiclesQuery->where('school_id', $currentUser->school_id);
        }

        $courses = $coursesQuery->get();
        $vehicles = $vehiclesQuery->get();
        $schools = $this->getAvailableSchools($currentUser);

        return view('admin.schedules.edit', compact('schedule', 'students', 'instructors', 'courses', 'vehicles', 'schools', 'currentUser'));
    }

    /**
     * FIXED UPDATE METHOD - Update with access control
     */
    public function update(Request $request, Schedule $schedule)
    {
        $currentUser = $this->ensureAdminAccess();

        // Check if user can update this schedule
        if (!$currentUser->isSuperAdmin() && $schedule->school_id !== $currentUser->school_id) {
            abort(403, 'Access denied. You can only edit schedules from your school.');
        }

        $validator = Validator::make($request->all(), [
            'student' => 'required|exists:users,id',
            'instructor' => 'required|exists:users,id',
            'course' => 'required|exists:courses,id',
            'car' => 'required|exists:fleets,id',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'class_type' => 'required|in:practical,theory,test',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'school_id' => $currentUser->isSuperAdmin() ? 'required|exists:schools,id' : 'nullable',
            'notes' => 'nullable|string|max:1000',
            'instructor_notes' => 'nullable|string|max:1000',
            'lessons_completed' => 'nullable|integer|min:0',
            'attended' => 'boolean',
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

        $updateData = $request->all();

        // School admins cannot change school_id
        if (!$currentUser->isSuperAdmin()) {
            $updateData['school_id'] = $currentUser->school_id;
        }

        $schedule->update($updateData);

        return redirect()->route('admin.schedules.show', $schedule)
            ->with('success', 'Schedule updated successfully!');
    }

    /**
     * FIXED DESTROY METHOD - Delete with access control
     */
    public function destroy(Schedule $schedule)
    {
        $currentUser = $this->ensureAdminAccess();

        // Check if user can delete this schedule
        if (!$currentUser->isSuperAdmin() && $schedule->school_id !== $currentUser->school_id) {
            abort(403, 'Access denied. You can only delete schedules from your school.');
        }

        $schedule->delete();

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule deleted successfully!');
    }

    /**
     * FIXED CALENDAR METHOD - Calendar view with school filtering
     */
    public function calendar(Request $request)
    {
        $currentUser = $this->ensureAdminAccess();

        $start = $request->get('start', now()->startOfMonth());
        $end = $request->get('end', now()->endOfMonth());

        $query = Schedule::with(['student', 'instructor', 'course', 'car'])
            ->whereBetween('start', [$start, $end]);

        // Apply school filtering
        $query = $this->applySchoolFilter($query, $currentUser);

        $schedules = $query->get()->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'title' => ($schedule->student->full_name ?? 'Student') . ' - ' . ($schedule->course->name ?? 'Course'),
                'start' => $schedule->start,
                'end' => $schedule->end,
                'backgroundColor' => $this->getScheduleColor($schedule->status),
                'borderColor' => $this->getScheduleColor($schedule->status),
                'url' => route('admin.schedules.show', $schedule),
                'extendedProps' => [
                    'student' => $schedule->student->full_name ?? 'Unknown',
                    'instructor' => $schedule->instructor->full_name ?? 'Unknown',
                    'status' => $schedule->status,
                    'class_type' => $schedule->class_type,
                ]
            ];
        });

        if ($request->ajax()) {
            return response()->json($schedules);
        }

        return view('admin.schedules.calendar', compact('schedules', 'currentUser'));
    }

    /**
     * FIXED MARK ATTENDED METHOD - With access control
     */
    public function markAttended(Schedule $schedule)
    {
        $currentUser = $this->ensureAdminAccess();

        // Check if user can modify this schedule
        if (!$currentUser->isSuperAdmin() && $schedule->school_id !== $currentUser->school_id) {
            abort(403, 'Access denied. You can only modify schedules from your school.');
        }

        $schedule->update([
            'attended' => !$schedule->attended,
            'status' => $schedule->attended ? 'scheduled' : 'completed',
            'lessons_completed' => $schedule->attended ? 0 : 1,
            'lessons_deducted' => $schedule->attended ? 0 : 1,
        ]);

        $status = $schedule->attended ? 'marked as attended' : 'marked as not attended';
        return back()->with('success', "Schedule {$status} successfully!");
    }

    /**
     * Get instructor schedules for instructor dashboard
     */
    public function instructorSchedules(Request $request)
    {
        $currentUser = Auth::user();

        if (!$currentUser || !in_array($currentUser->role, ['super_admin', 'admin', 'instructor'])) {
            abort(403, 'Access denied. Instructor privileges required.');
        }

        $query = Schedule::with(['student', 'course', 'car'])
            ->where('instructor', $currentUser->id);

        // Apply filters
        if ($request->filled('date_from')) {
            $query->where('start', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('end', '<=', $request->date_to . ' 23:59:59');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schedules = $query->orderBy('start', 'desc')->paginate(15);

        return view('instructor.schedules', compact('schedules', 'currentUser'));
    }

    /**
     * Get color for schedule status
     */
    private function getScheduleColor($status)
    {
        return match($status) {
            'scheduled' => '#4e73df',
            'in_progress' => '#f6c23e',
            'completed' => '#1cc88a',
            'cancelled' => '#e74a3b',
            default => '#6c757d'
        };
    }
}
