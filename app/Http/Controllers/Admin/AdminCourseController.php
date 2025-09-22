<?php
// app/Http/Controllers/Admin/AdminCourseController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Course, User, School};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AdminCourseController extends Controller
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
            $query->where(function($q) use ($currentUser) {
                $q->where('school_id', $currentUser->school_id)
                  ->orWhereNull('school_id'); // Include courses without school assignment for now
            });
        }
        
        return $query;
    }

    /**
     * Check if user can access this course
     */
    protected function canAccessCourse($currentUser, $course)
    {
        // Super admins can access all courses
        if ($currentUser->isSuperAdmin()) {
            return true;
        }
        
        // School admins can access courses from their school OR courses without school assignment
        if ($currentUser->school_id) {
            return $course->school_id === $currentUser->school_id || $course->school_id === null;
        }
        
        return false;
    }

    /**
     * Display a listing of courses
     */
    public function index(Request $request)
    {
        $currentUser = $this->ensureAdminAccess();
        
        $query = Course::query();
        
        // Apply school filtering first
        $query = $this->applySchoolFilter($query, $currentUser);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $courses = $query->paginate(15);

        return view('admin.courses.index', compact('courses', 'currentUser'));
    }

    /**
     * Show the form for creating a new course
     */
    public function create()
    {
        $currentUser = $this->ensureAdminAccess();
        
        return view('admin.courses.create', compact('currentUser'));
    }

    /**
     * Store a newly created course in storage
     */
    public function store(Request $request)
    {
        $currentUser = $this->ensureAdminAccess();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0|max:999999.99',
            'description' => 'nullable|string|max:1000',
            'duration_hours' => 'nullable|integer|min:1|max:1000',
            'lessons_included' => 'nullable|integer|min:1|max:200',
            'status' => 'required|in:active,inactive',
            'type' => 'nullable|in:practical,theory,combined',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $courseData = $request->all();
        $courseData['school_id'] = $currentUser->isSuperAdmin() 
            ? ($request->school_id ?? $currentUser->school_id) 
            : $currentUser->school_id;

        Course::create($courseData);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course created successfully.');
    }

    /**
     * Display the specified course
     */
    public function show(Course $course)
    {
        $currentUser = $this->ensureAdminAccess();
        
        // Check if user can access this course
        if (!$this->canAccessCourse($currentUser, $course)) {
            abort(403, 'Access denied. You can only view courses from your school.');
        }
        
        // Auto-assign school_id if it's missing and user is school admin
        if (!$course->school_id && $currentUser->school_id) {
            $course->update(['school_id' => $currentUser->school_id]);
            $course->refresh();
        }
        
        // Load relationships for statistics
        $course->load(['schedules', 'invoices']);
        
        return view('admin.courses.show', compact('course', 'currentUser'));
    }

    /**
     * Show the form for editing the specified course
     */
    public function edit(Course $course)
    {
        $currentUser = $this->ensureAdminAccess();
        
        // Check if user can edit this course
        if (!$this->canAccessCourse($currentUser, $course)) {
            abort(403, 'Access denied. You can only edit courses from your school.');
        }
        
        return view('admin.courses.edit', compact('course', 'currentUser'));
    }

    /**
     * Update the specified course in storage
     */
    public function update(Request $request, Course $course)
    {
        $currentUser = $this->ensureAdminAccess();
        
        // Check if user can update this course
        if (!$this->canAccessCourse($currentUser, $course)) {
            abort(403, 'Access denied. You can only edit courses from your school.');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0|max:999999.99',
            'description' => 'nullable|string|max:1000',
            'duration_hours' => 'nullable|integer|min:1|max:1000',
            'lessons_included' => 'nullable|integer|min:1|max:200',
            'status' => 'required|in:active,inactive',
            'type' => 'nullable|in:practical,theory,combined',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $updateData = $request->all();
        
        // Ensure school_id is set correctly
        if (!$currentUser->isSuperAdmin()) {
            $updateData['school_id'] = $currentUser->school_id;
        } elseif (!isset($updateData['school_id'])) {
            $updateData['school_id'] = $course->school_id ?: $currentUser->school_id;
        }

        $course->update($updateData);

        return redirect()->route('admin.courses.show', $course)
            ->with('success', 'Course updated successfully.');
    }

    /**
     * Remove the specified course from storage
     */
    public function destroy(Course $course)
    {
        $currentUser = $this->ensureAdminAccess();
        
        // Check if user can delete this course
        if (!$this->canAccessCourse($currentUser, $course)) {
            abort(403, 'Access denied. You can only delete courses from your school.');
        }
        
        // Check if course is being used in schedules or invoices
        if ($course->schedules()->count() > 0) {
            return back()->with('error', 'Cannot delete course that is being used in schedules.');
        }
        
        if ($course->invoices()->count() > 0) {
            return back()->with('error', 'Cannot delete course that is being used in invoices.');
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
        $currentUser = $this->ensureAdminAccess();
        
        // Check if user can modify this course
        if (!$this->canAccessCourse($currentUser, $course)) {
            abort(403, 'Access denied. You can only modify courses from your school.');
        }
        
        $newStatus = $course->status === 'active' ? 'inactive' : 'active';
        $course->update(['status' => $newStatus]);

        $message = $newStatus === 'active' 
            ? 'Course activated successfully.' 
            : 'Course deactivated successfully.';

        return back()->with('success', $message);
    }
}