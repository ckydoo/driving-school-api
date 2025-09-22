<?php
// app/Http/Controllers/Admin/AdminUserController.php (Updated)

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\School;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $query = User::with('school');

        // Scope to current user's permissions
        if ($currentUser->isSchoolAdmin()) {
            $query->where('school_id', $currentUser->school_id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                  ->orWhere('lname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by school (only for super admins)
        if ($request->filled('school_id') && $currentUser->isSuperAdmin()) {
            $query->where('school_id', $request->school_id);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get schools based on user permissions
        $schools = $currentUser->isSuperAdmin()
            ? School::orderBy('name')->get()
            : collect([$currentUser->school]);

        return view('admin.users.index', compact('users', 'schools', 'currentUser'));
    }

    public function create()
    {
        $currentUser = Auth::user();

        // Get available schools based on permissions
        $schools = $currentUser->isSuperAdmin()
            ? School::orderBy('name')->get()
            : collect([$currentUser->school]);

        // Available roles based on user permissions
        $availableRoles = $this->getAvailableRoles($currentUser);

        return view('admin.users.create', compact('schools', 'availableRoles', 'currentUser'));
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();

        $validator = Validator::make($request->all(), [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'required|date',
            'role' => ['required', Rule::in($this->getAvailableRoles($currentUser))],
            'status' => 'required|in:active,inactive,suspended',
            'gender' => 'required|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'idnumber' => 'nullable|string|unique:users,idnumber',
            'school_id' => $this->getSchoolValidationRule($currentUser),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $userData = $request->all();
        $userData['password'] = Hash::make($request->password);

        // Set school_id based on user permissions
        if ($currentUser->isSchoolAdmin()) {
            $userData['school_id'] = $currentUser->school_id;
        } elseif (!$currentUser->isSuperAdmin() || $request->role === 'super_admin') {
            $userData['school_id'] = null;
        }

        User::create($userData);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully!');
    }

    public function show(User $user)
    {
        $currentUser = Auth::user();

        // Check if user can view this user
        if (!$this->canAccessUser($currentUser, $user)) {
            abort(403, 'Access denied.');
        }

        $user->load(['school', 'studentSchedules.instructor', 'instructorSchedules.student', 'invoices', 'payments']);

        $stats = [
            'total_schedules' => $user->studentSchedules->count() + $user->instructorSchedules->count(),
            'completed_lessons' => $user->studentSchedules->where('status', 'completed')->count(),
            'total_invoices' => $user->invoices->count(),
            'paid_amount' => $user->payments->where('status', 'completed')->sum('amount'),
        ];

        return view('admin.users.show', compact('user', 'stats', 'currentUser'));
    }

    public function edit(User $user)
    {
        $currentUser = Auth::user();

        // Check if user can edit this user
        if (!$this->canAccessUser($currentUser, $user)) {
            abort(403, 'Access denied.');
        }

        $schools = $currentUser->isSuperAdmin()
            ? School::orderBy('name')->get()
            : collect([$currentUser->school]);

        $availableRoles = $this->getAvailableRoles($currentUser);

        return view('admin.users.edit', compact('user', 'schools', 'availableRoles', 'currentUser'));
    }

    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();

        // Check if user can edit this user
        if (!$this->canAccessUser($currentUser, $user)) {
            abort(403, 'Access denied.');
        }

        $validator = Validator::make($request->all(), [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'date_of_birth' => 'required|date',
            'role' => ['required', Rule::in($this->getAvailableRoles($currentUser))],
            'status' => 'required|in:active,inactive,suspended',
            'gender' => 'required|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'idnumber' => ['nullable', 'string', Rule::unique('users')->ignore($user->id)],
            'school_id' => $this->getSchoolValidationRule($currentUser),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $userData = $request->except(['password', 'password_confirmation']);

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        // Restrict school changes for school admins
        if ($currentUser->isSchoolAdmin()) {
            $userData['school_id'] = $currentUser->school_id;
        }

        $user->update($userData);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        $currentUser = Auth::user();

        // Check if user can delete this user
        if (!$this->canAccessUser($currentUser, $user)) {
            abort(403, 'Access denied.');
        }

        // Prevent deletion of users with schedules
        if ($user->studentSchedules->count() > 0 || $user->instructorSchedules->count() > 0) {
            return back()->with('error', 'Cannot delete user with existing schedules.');
        }

        // Prevent school admins from deleting super admins
        if ($currentUser->isSchoolAdmin() && $user->isSuperAdmin()) {
            abort(403, 'Cannot delete super administrator.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    public function toggleStatus(User $user)
    {
        $currentUser = Auth::user();

        if (!$this->canAccessUser($currentUser, $user)) {
            abort(403, 'Access denied.');
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        return back()->with('success', "User status updated to {$newStatus}!");
    }

    // === HELPER METHODS ===

    private function canAccessUser($currentUser, $targetUser): bool
    {
        // Super admins can access all users
        if ($currentUser->isSuperAdmin()) {
            return true;
        }

        // School admins can only access users from their school
        if ($currentUser->isSchoolAdmin()) {
            return $targetUser->school_id === $currentUser->school_id;
        }

        return false;
    }

    private function getAvailableRoles($currentUser): array
    {
        if ($currentUser->isSuperAdmin()) {
            return ['super_admin', 'admin', 'instructor', 'student'];
        }

        if ($currentUser->isSchoolAdmin()) {
            return ['instructor', 'student']; // School admins can't create other admins
        }

        return ['student']; // Fallback
    }

    private function getSchoolValidationRule($currentUser): string
    {
        if ($currentUser->isSuperAdmin()) {
            return 'nullable|exists:schools,id';
        }

        return 'required|exists:schools,id|in:' . $currentUser->school_id;
    }

    public function allUsers(Request $request)
{
    $currentUser = Auth::user();

    // Only super admins can access this
    if (!$currentUser->isSuperAdmin()) {
        abort(403, 'Access denied. Super Administrator privileges required.');
    }

    $query = User::with('school');

    // Search functionality
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('fname', 'like', "%{$search}%")
              ->orWhere('lname', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // Filter by role
    if ($request->filled('role')) {
        $query->where('role', $request->role);
    }

    // Filter by status
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // Filter by school
    if ($request->filled('school_id')) {
        $query->where('school_id', $request->school_id);
    }

    $users = $query->orderBy('created_at', 'desc')->paginate(25);

    // Get all schools for filtering
    $schools = School::orderBy('name')->get();

    // Get statistics
    $stats = [
        'total_users' => User::count(),
        'super_admins' => User::where('role', 'super_admin')->count(),
        'school_admins' => User::where('role', 'admin')->where('is_super_admin', false)->count(),
        'instructors' => User::where('role', 'instructor')->count(),
        'students' => User::where('role', 'student')->count(),
        'active_users' => User::where('status', 'active')->count(),
        'inactive_users' => User::where('status', 'inactive')->count(),
        'suspended_users' => User::where('status', 'suspended')->count(),
    ];

    return view('admin.users.all-users', compact('users', 'schools', 'currentUser', 'stats'));
}

/**
 * Display all super administrators (Super Admin only)
 */
public function superAdmins(Request $request)
{
    $currentUser = Auth::user();

    // Only super admins can access this
    if (!$currentUser->isSuperAdmin()) {
        abort(403, 'Access denied. Super Administrator privileges required.');
    }

    $query = User::where('role', 'super_admin')->orWhere('is_super_admin', true);

    // Search functionality
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('fname', 'like', "%{$search}%")
              ->orWhere('lname', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // Filter by status
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $superAdmins = $query->orderBy('created_at', 'desc')->paginate(20);

    // Get statistics
    $stats = [
        'total_super_admins' => User::where('role', 'super_admin')->orWhere('is_super_admin', true)->count(),
        'active_super_admins' => User::where(function($q) {
            $q->where('role', 'super_admin')->orWhere('is_super_admin', true);
        })->where('status', 'active')->count(),
        'inactive_super_admins' => User::where(function($q) {
            $q->where('role', 'super_admin')->orWhere('is_super_admin', true);
        })->where('status', '!=', 'active')->count(),
        'recent_logins' => User::where(function($q) {
            $q->where('role', 'super_admin')->orWhere('is_super_admin', true);
        })->whereNotNull('last_login')
          ->where('last_login', '>=', now()->subDays(30))
          ->count(),
    ];

    return view('admin.users.super-admins', compact('superAdmins', 'currentUser', 'stats'));
}

/**
 * Get user statistics for API calls
 */
public function getUserStats()
{
    $currentUser = Auth::user();

    if (!$currentUser->isSuperAdmin()) {
        return response()->json(['error' => 'Access denied'], 403);
    }

    $stats = [
        'total_users' => User::count(),
        'users_by_role' => [
            'super_admin' => User::where('role', 'super_admin')->count(),
            'admin' => User::where('role', 'admin')->where('is_super_admin', false)->count(),
            'instructor' => User::where('role', 'instructor')->count(),
            'student' => User::where('role', 'student')->count(),
        ],
        'users_by_status' => [
            'active' => User::where('status', 'active')->count(),
            'inactive' => User::where('status', 'inactive')->count(),
            'suspended' => User::where('status', 'suspended')->count(),
        ],
        'users_by_school' => School::withCount('users')->orderBy('users_count', 'desc')->take(10)->get(),
        'recent_registrations' => User::where('created_at', '>=', now()->subDays(30))->count(),
        'recent_logins' => User::whereNotNull('last_login')
            ->where('last_login', '>=', now()->subDays(7))
            ->count(),
    ];

    return response()->json($stats);
}
/**
 * Show students list for current school admin
 */
public function students()
{
    $currentUser = Auth::user();

    // Ensure user is admin
    if (!$currentUser || !$currentUser->isAdmin()) {
        abort(403, 'Access denied. Administrator privileges required.');
    }

    $query = User::with(['school'])
        ->where('role', 'student');

    // If school admin, restrict to their school only
    if (!$currentUser->isSuperAdmin() && $currentUser->school_id) {
        $query->where('school_id', $currentUser->school_id);
    }

    // Handle search
    if (request()->filled('search')) {
        $search = request('search');
        $query->where(function($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%")
              ->orWhere('phone', 'LIKE', "%{$search}%");
        });
    }

    // Handle status filter
    if (request()->filled('status')) {
        $query->where('status', request('status'));
    }

    // Handle school filter (super admin only)
    if ($currentUser->isSuperAdmin() && request()->filled('school_id')) {
        $query->where('school_id', request('school_id'));
    }

    $students = $query->orderBy('created_at', 'desc')
        ->paginate(15)
        ->appends(request()->query());

    // Get schools for filter (super admin only)
    $schools = $currentUser->isSuperAdmin()
        ? School::orderBy('name')->get()
        : collect();

    // Get stats
    $stats = [
        'total_students' => User::where('role', 'student')
            ->when(!$currentUser->isSuperAdmin(), function($q) use ($currentUser) {
                return $q->where('school_id', $currentUser->school_id);
            })->count(),
        'active_students' => User::where('role', 'student')
            ->where('status', 'active')
            ->when(!$currentUser->isSuperAdmin(), function($q) use ($currentUser) {
                return $q->where('school_id', $currentUser->school_id);
            })->count(),
        'inactive_students' => User::where('role', 'student')
            ->where('status', 'inactive')
            ->when(!$currentUser->isSuperAdmin(), function($q) use ($currentUser) {
                return $q->where('school_id', $currentUser->school_id);
            })->count(),
    ];

    return view('admin.users.students', compact('students', 'schools', 'stats', 'currentUser'));
}

/**
 * Show instructors list for current school admin
 */
public function instructors()
{
    $currentUser = Auth::user();

    // Ensure user is admin
    if (!$currentUser || !$currentUser->isAdmin()) {
        abort(403, 'Access denied. Administrator privileges required.');
    }

    $query = User::with(['school'])
        ->where('role', 'instructor');

    // If school admin, restrict to their school only
    if (!$currentUser->isSuperAdmin() && $currentUser->school_id) {
        $query->where('school_id', $currentUser->school_id);
    }

    // Handle search
    if (request()->filled('search')) {
        $search = request('search');
        $query->where(function($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%")
              ->orWhere('phone', 'LIKE', "%{$search}%");
        });
    }

    // Handle status filter
    if (request()->filled('status')) {
        $query->where('status', request('status'));
    }

    // Handle school filter (super admin only)
    if ($currentUser->isSuperAdmin() && request()->filled('school_id')) {
        $query->where('school_id', request('school_id'));
    }

    $instructors = $query->orderBy('created_at', 'desc')
        ->paginate(15)
        ->appends(request()->query());

    // Get schools for filter (super admin only)
    $schools = $currentUser->isSuperAdmin()
        ? School::orderBy('name')->get()
        : collect();

    // Get stats
    $stats = [
        'total_instructors' => User::where('role', 'instructor')
            ->when(!$currentUser->isSuperAdmin(), function($q) use ($currentUser) {
                return $q->where('school_id', $currentUser->school_id);
            })->count(),
        'active_instructors' => User::where('role', 'instructor')
            ->where('status', 'active')
            ->when(!$currentUser->isSuperAdmin(), function($q) use ($currentUser) {
                return $q->where('school_id', $currentUser->school_id);
            })->count(),
        'inactive_instructors' => User::where('role', 'instructor')
            ->where('status', 'inactive')
            ->when(!$currentUser->isSuperAdmin(), function($q) use ($currentUser) {
                return $q->where('school_id', $currentUser->school_id);
            })->count(),
    ];

    return view('admin.users.instructors', compact('instructors', 'schools', 'stats', 'currentUser'));
}

/**
 * Show instructor's students (for instructor dashboard)
 */
public function instructorStudents()
{
    $user = Auth::user();

    if (!$user || !in_array($user->role, ['super_admin', 'admin', 'instructor'])) {
        abort(403, 'Access denied. Instructor privileges required.');
    }

    // Get students assigned to this instructor through schedules
    $studentIds = Schedule::where('instructor_id', $user->id)
        ->distinct()
        ->pluck('student_id');

    $query = User::with(['school'])
        ->whereIn('id', $studentIds)
        ->where('role', 'student');

    // Handle search
    if (request()->filled('search')) {
        $search = request('search');
        $query->where(function($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%")
              ->orWhere('phone', 'LIKE', "%{$search}%");
        });
    }

    $students = $query->orderBy('name')
        ->paginate(15)
        ->appends(request()->query());

    // Get stats for this instructor's students
    $stats = [
        'total_students' => $studentIds->count(),
        'active_students' => User::whereIn('id', $studentIds)
            ->where('status', 'active')
            ->count(),
        'total_lessons' => Schedule::where('instructor_id', $user->id)
            ->count(),
        'completed_lessons' => Schedule::where('instructor_id', $user->id)
            ->where('status', 'completed')
            ->count(),
    ];

    return view('instructor.students', compact('students', 'stats'));
}
}

