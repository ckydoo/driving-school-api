<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Course, School};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminCourseController extends Controller
{
    private function getSchoolScope()
    {
        $user = auth()->user();

        if ($user->role === 'super_admin') {
            return null; // No restriction
        }

        return $user->school_id;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope();

        $query = Course::with('school')
                      ->when($schoolId, fn($q) => $q->where('school_id', $schoolId));

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by school (super admin only)
        if ($request->filled('school_id') && !$schoolId) {
            $query->where('school_id', $request->school_id);
        }

        $courses = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get schools for filter (super admin only)
        $schools = $user->role === 'super_admin' ? School::orderBy('name')->get() : collect();

        return view('admin.courses.index', compact('courses', 'schools'));
    }

    public function create()
    {
        return view('admin.courses.create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope() ?: $user->school_id;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0|max:999999.99',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        Course::create([
            'name' => $request->name,
            'price' => $request->price,
            'status' => $request->status,
            'school_id' => $schoolId,
        ]);

        return redirect()->route('admin.courses.index')
                        ->with('success', 'Course created successfully.');
    }

    public function show(Course $course)
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope();

        // Check permissions
        if ($schoolId && $course->school_id !== $schoolId) {
            abort(403, 'Access denied.');
        }

        // Load relationships
        $course->load([
            'schedules.student',
            'schedules.instructor',
            'invoices.student'
        ]);

        // Calculate stats
        $stats = [
            'total_enrollments' => $course->schedules->count(),
            'completed_lessons' => $course->schedules->where('status', 'completed')->count(),
            'total_revenue' => $course->invoices->sum('total_amount'),
            'active_students' => $course->schedules->where('status', '!=', 'completed')->unique('student')->count(),
        ];

        return view('admin.courses.show', compact('course', 'stats'));
    }

    public function edit(Course $course)
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope();

        // Check permissions
        if ($schoolId && $course->school_id !== $schoolId) {
            abort(403, 'Access denied.');
        }

        return view('admin.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope();

        // Check permissions
        if ($schoolId && $course->school_id !== $schoolId) {
            abort(403, 'Access denied.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0|max:999999.99',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $course->update([
            'name' => $request->name,
            'price' => $request->price,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.courses.show', $course)
                        ->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope();

        // Check permissions
        if ($schoolId && $course->school_id !== $schoolId) {
            abort(403, 'Access denied.');
        }

        // Check if course is being used
        if ($course->schedules()->count() > 0) {
            return redirect()->route('admin.courses.index')
                           ->with('error', 'Cannot delete course that has existing schedules.');
        }

        if ($course->invoices()->count() > 0) {
            return redirect()->route('admin.courses.index')
                           ->with('error', 'Cannot delete course that has existing invoices.');
        }

        $course->delete();

        return redirect()->route('admin.courses.index')
                        ->with('success', 'Course deleted successfully.');
    }

    /**
     * Toggle course status
     */
    public function toggleStatus(Course $course)
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope();

        // Check permissions
        if ($schoolId && $course->school_id !== $schoolId) {
            abort(403, 'Access denied.');
        }

        $newStatus = $course->status === 'active' ? 'inactive' : 'active';
        $course->update(['status' => $newStatus]);

        return redirect()->back()
                        ->with('success', "Course status updated to {$newStatus}.");
    }
}
