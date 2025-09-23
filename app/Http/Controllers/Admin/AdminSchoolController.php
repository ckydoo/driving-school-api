<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminSchoolController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();

        // Super admins can view all schools - NO RESTRICTIONS
        // School admins can view all schools but with limited actions

        $query = School::withCount([
            'users',
            'users as students_count' => function($q) {
                $q->where('role', 'student');
            },
            'users as instructors_count' => function($q) {
                $q->where('role', 'instructor');
            },
            'users as admins_count' => function($q) {
                $q->where('role', 'admin');
            }
        ]);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by subscription status
        if ($request->filled('subscription_status')) {
            $query->where('subscription_status', $request->subscription_status);
        }

        $schools = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.schools.index', compact('schools', 'currentUser'));
    }

    public function create()
    {
        $currentUser = Auth::user();

        // Only super admins can create schools
        if (!$currentUser->isSuperAdmin()) {
            return redirect()->route('admin.schools.index')
                ->with('error', 'Only super administrators can create new schools.');
        }

        return view('admin.schools.create', compact('currentUser'));
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();

        // Only super admins can create schools
        if (!$currentUser->isSuperAdmin()) {
            return redirect()->route('admin.schools.index')
                ->with('error', 'Only super administrators can create new schools.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:schools,name',
            'email' => 'required|email|unique:schools,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'license_number' => 'nullable|string|max:100|unique:schools,license_number',
            'subscription_status' => 'required|in:trial,active,suspended,expired',
            'status' => 'required|in:active,inactive,suspended',

            // Admin user details
            'admin_fname' => 'required|string|max:255',
            'admin_lname' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
            'admin_phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Create school
            $school = School::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'license_number' => $request->license_number,
                'subscription_status' => $request->subscription_status,
                'status' => $request->status,
                'created_by' => $currentUser->id,
            ]);

            // Create school admin
            User::create([
                'fname' => $request->admin_fname,
                'lname' => $request->admin_lname,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'phone' => $request->admin_phone,
                'role' => 'admin',
                'status' => 'active',
                'school_id' => $school->id,
                'gender' => 'other', // Default, can be updated later
                'date_of_birth' => now()->subYears(25), // Default, can be updated later
            ]);

            return redirect()->route('admin.schools.show', $school)
                ->with('success', 'School and admin user created successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create school: ' . $e->getMessage())->withInput();
        }
    }

    public function show(School $school)
    {
        $currentUser = Auth::user();

        // Super admins can view any school - NO RESTRICTIONS
        // School admins can only view their own school
        if ($currentUser->isSchoolAdmin() && $school->id !== $currentUser->school_id) {
            return redirect()->route('admin.schools.index')
                ->with('error', 'You can only view your own school details.');
        }

        $school->load(['users']);

        $stats = [
            'total_users' => $school->users->count(),
            'students' => $school->users->where('role', 'student')->count(),
            'instructors' => $school->users->where('role', 'instructor')->count(),
            'admins' => $school->users->where('role', 'admin')->count(),
            'active_users' => $school->users->where('status', 'active')->count(),
            'inactive_users' => $school->users->where('status', 'inactive')->count(),
        ];

        $recentUsers = $school->users()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $schoolAdmins = $school->users()
            ->where('role', 'admin')
            ->where('status', 'active')
            ->get();

        return view('admin.schools.show', compact('school', 'stats', 'recentUsers', 'schoolAdmins', 'currentUser'));
    }

    public function edit(School $school)
    {
        $currentUser = Auth::user();

        // Only super admins can edit schools
        if (!$currentUser->isSuperAdmin()) {
            return redirect()->route('admin.schools.show', $school)
                ->with('error', 'Only super administrators can edit school information.');
        }

        return view('admin.schools.edit', compact('school', 'currentUser'));
    }

    public function update(Request $request, School $school)
    {
        $currentUser = Auth::user();

        // Only super admins can update schools
        if (!$currentUser->isSuperAdmin()) {
            return redirect()->route('admin.schools.show', $school)
                ->with('error', 'Only super administrators can edit school information.');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', Rule::unique('schools')->ignore($school->id)],
            'email' => ['required', 'email', Rule::unique('schools')->ignore($school->id)],
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'license_number' => ['nullable', 'string', 'max:100', Rule::unique('schools')->ignore($school->id)],
            'subscription_status' => 'required|in:trial,active,suspended,expired',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $school->update($request->all());

        return redirect()->route('admin.schools.show', $school)
            ->with('success', 'School updated successfully!');
    }

    public function destroy(School $school)
    {
        $currentUser = Auth::user();

        // Only super admins can delete schools
        if (!$currentUser->isSuperAdmin()) {
            return redirect()->route('admin.schools.show', $school)
                ->with('error', 'Only super administrators can delete schools.');
        }

        // Check if school has active users
        if ($school->users()->where('status', 'active')->count() > 0) {
            return back()->with('error', 'Cannot delete school with active users. Please deactivate all users first.');
        }

        // Store school name for success message
        $schoolName = $school->name;

        // Delete all inactive users first
        $school->users()->delete();

        // Delete school
        $school->delete();

        return redirect()->route('admin.schools.index')
            ->with('success', "School '{$schoolName}' and all associated data deleted successfully!");
    }

    public function toggleStatus(School $school)
    {
        $currentUser = Auth::user();

        // Only super admins can toggle school status
        if (!$currentUser->isSuperAdmin()) {
            return redirect()->route('admin.schools.show', $school)
                ->with('error', 'Only super administrators can change school status.');
        }

        $newStatus = $school->status === 'active' ? 'inactive' : 'active';
        $school->update(['status' => $newStatus]);

        // Also update all users' status in the school
        if ($newStatus === 'inactive') {
            $school->users()->update(['status' => 'inactive']);
            $message = "School '{$school->name}' and all its users have been deactivated.";
        } else {
            $message = "School '{$school->name}' has been activated. You can now activate individual users.";
        }

        return back()->with('success', $message);
    }

    /**
     * Login as school admin (Super admin feature)
     */
    public function loginAsSchool(Request $request, School $school)
    {
        $currentUser = Auth::user();

        // Only super admins can impersonate
        if (!$currentUser->isSuperAdmin()) {
            return redirect()->route('admin.schools.show', $school)
                ->with('error', 'Only super administrators can login as school admins.');
        }

        // Find an active school admin for this school
        $schoolAdmin = $school->users()
            ->where('role', 'admin')
            ->where('status', 'active')
            ->first();

        if (!$schoolAdmin) {
            return back()->with('error', 'No active school administrator found for this school.');
        }

        // Store the super admin ID in session so we can switch back
        session(['super_admin_id' => $currentUser->id]);
        session(['impersonating_school' => $school->id]);

        // Login as the school admin
        Auth::login($schoolAdmin);

        return redirect()->route('admin.dashboard')
            ->with('success', "You are now logged in as {$schoolAdmin->full_name} ({$school->name}). Click 'Return to Super Admin' to switch back.");
    }

    /**
     * Return to super admin account
     */
    public function returnToSuperAdmin()
    {
        $superAdminId = session('super_admin_id');

        if (!$superAdminId) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $superAdmin = User::find($superAdminId);

        if (!$superAdmin || !$superAdmin->isSuperAdmin()) {
            return redirect()->route('login')->with('error', 'Invalid session. Please login again.');
        }

        // Clear impersonation session
        session()->forget(['super_admin_id', 'impersonating_school']);

        // Login back as super admin
        Auth::login($superAdmin);

        return redirect()->route('admin.super.dashboard')
            ->with('success', 'Welcome back to Super Administrator panel!');
    }

    /**
     * Get school users (for AJAX/API calls)
     */
    public function getSchoolUsers(School $school)
    {
        $currentUser = Auth::user();

        // Super admins can view any school's users - NO RESTRICTIONS
        // School admins can only view their own school's users
        if ($currentUser->isSchoolAdmin() && $school->id !== $currentUser->school_id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $users = $school->users()
            ->select('id', 'fname', 'lname', 'email', 'role', 'status', 'created_at')
            ->orderBy('role')
            ->orderBy('fname')
            ->get();

        return response()->json($users);
    }

    /**
     * Show current user's school (for school admins)
     */
    public function mySchool()
    {
        $currentUser = Auth::user();

        if ($currentUser->isSuperAdmin()) {
            return redirect()->route('admin.schools.index')
                ->with('info', 'Super admins can view all schools from the Schools page.');
        }

        if (!$currentUser->school) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No school assigned to your account. Please contact the system administrator.');
        }

        return $this->show($currentUser->school);
    }

    /**
     * Show current user's school users (for school admins)
     */
    public function mySchoolUsers()
    {
        $currentUser = Auth::user();

        if ($currentUser->isSuperAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('info', 'Super admins can manage all users from the Users page.');
        }

        if (!$currentUser->school) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No school assigned to your account.');
        }

        return redirect()->route('admin.users.index');
    }
}
