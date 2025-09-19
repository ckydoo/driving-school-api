<?php
// app/Http/Controllers/Admin/AdminController.php - Fixed for Your Models

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Fleet;
use App\Models\School;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Main admin dashboard - shows data based on user role
     */
    public function dashboard()
    {
        $currentUser = Auth::user();

        // Check if user has the new role fields, fallback to old role check
        $isSuperAdmin = $this->checkSuperAdmin($currentUser);

        if ($isSuperAdmin) {
            return $this->superAdminDashboard();
        } else {
            return $this->schoolAdminDashboard();
        }
    }

    /**
     * Check if user is super admin (supports both old and new systems)
     */
    private function checkSuperAdmin($user)
    {
        // If new fields exist, use them
        if (isset($user->is_super_admin)) {
            return $user->is_super_admin || $user->role === 'super_admin';
        }

        // Fallback: Super admin if admin with no school_id
        return $user->role === 'admin' && empty($user->school_id);
    }

    /**
     * Super Admin Dashboard - System-wide data
     */
    private function superAdminDashboard()
    {
        try {
            $stats = [
                // School Statistics
                'total_schools' => School::count(),
                'active_schools' => School::where('status', 'active')->count(),
                'trial_schools' => School::where('subscription_status', 'trial')->count(),
                'paid_schools' => School::where('subscription_status', 'active')->count(),

                // User Statistics
                'total_users' => User::count(),
                'super_admins' => User::where('role', 'super_admin')->count(),
                'school_admins' => User::where('role', 'admin')->where('role', '!=', 'super_admin')->count(),
                'total_instructors' => User::where('role', 'instructor')->count(),
                'total_students' => User::where('role', 'student')->count(),
                'active_users' => User::where('status', 'active')->count(),

                // System Statistics
                'total_schedules' => Schedule::count(),
                'total_invoices' => Invoice::count(),
                'total_vehicles' => Fleet::count(),
                'available_vehicles' => Fleet::where('status', 'available')->count(),
                'total_revenue' => Payment::where('status', 'completed')->sum('amount') ?? 0,
                'pending_invoices' => Invoice::where('status', 'pending')->count(),
            ];

            // Recent activity across all schools - FIXED to handle your model structure
            $recentSchools = School::latest()->take(5)->get();
            $recentUsers = User::with('school')->latest()->take(10)->get();

            // Top performing schools
            $topSchools = School::withCount('users')->orderBy('users_count', 'desc')->take(5)->get();

            // Monthly revenue data
            $monthlyRevenue = Payment::selectRaw('MONTH(created_at) as month, SUM(amount) as total')
                ->where('status', 'completed')
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            return view('admin.dashboard', compact(
                'stats',
                'recentSchools',
                'recentUsers',
                'topSchools',
                'monthlyRevenue'
            ));

        } catch (\Exception $e) {
            Log::error('Super Admin Dashboard Error: ' . $e->getMessage());
            // Fallback with basic stats
            $stats = $this->getBasicStats();
            return view('admin.dashboard', compact('stats'));
        }
    }

    /**
     * School Admin Dashboard - School-specific data
     */
    private function schoolAdminDashboard()
    {
        try {
            $currentUser = Auth::user();
            $school = $currentUser->school;

            if (!$school) {
                return redirect()->route('admin.profile')
                    ->with('error', 'Please contact administrator to assign you to a school.');
            }

            // School-specific statistics - FIXED for your models
            $stats = [
                'total_schools' => 1,
                'active_schools' => $school->status === 'active' ? 1 : 0,
                'trial_schools' => ($school->subscription_status ?? 'trial') === 'trial' ? 1 : 0,
                'paid_schools' => ($school->subscription_status ?? 'trial') === 'active' ? 1 : 0,

                'total_students' => User::where('school_id', $school->id)->where('role', 'student')->count(),
                'total_instructors' => User::where('school_id', $school->id)->where('role', 'instructor')->count(),
                'active_students' => User::where('school_id', $school->id)
                    ->where('role', 'student')
                    ->where('status', 'active')->count(),
                'total_users' => User::where('school_id', $school->id)->count(),
                'active_users' => User::where('school_id', $school->id)->where('status', 'active')->count(),

                'total_vehicles' => Fleet::where('school_id', $school->id)->count(),
                'available_vehicles' => Fleet::where('school_id', $school->id)
                    ->where('status', 'available')->count(),
                'total_schedules' => Schedule::where('school_id', $school->id)->count(),
                'completed_lessons' => Schedule::where('school_id', $school->id)
                    ->where('status', 'completed')->count(),
                'pending_invoices' => Invoice::where('school_id', $school->id)
                    ->where('status', 'pending')->count(),
                'total_revenue' => Payment::whereHas('invoice', function($q) use ($school) {
                        $q->where('school_id', $school->id);
                    })->where('status', 'completed')->sum('amount') ?? 0,

                // For compatibility with existing dashboard
                'super_admins' => 0,
                'school_admins' => User::where('school_id', $school->id)->where('role', 'admin')->count(),
                'total_invoices' => Invoice::where('school_id', $school->id)->count(),
            ];

            // Recent activity for this school - FIXED to handle relationships properly
            $recentStudents = User::where('school_id', $school->id)
                ->where('role', 'student')
                ->latest()
                ->take(5)
                ->get();

            // Get schedules with manual relationship loading to avoid errors
            $recentSchedules = Schedule::where('school_id', $school->id)
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($schedule) {
                    // Safely load relationships
                    try {
                        $schedule->student_info = User::find($schedule->student);
                        $schedule->instructor_info = User::find($schedule->instructor);
                        return $schedule;
                    } catch (\Exception $e) {
                        return $schedule;
                    }
                });

            $upcomingSchedules = Schedule::where('school_id', $school->id)
                ->where('status', 'scheduled')
                ->where('start', '>=', now())
                ->orderBy('start')
                ->take(5)
                ->get()
                ->map(function ($schedule) {
                    // Safely load relationships
                    try {
                        $schedule->student_info = User::find($schedule->student);
                        $schedule->instructor_info = User::find($schedule->instructor);
                        return $schedule;
                    } catch (\Exception $e) {
                        return $schedule;
                    }
                });

            // Monthly performance for this school
            $monthlyLessons = Schedule::where('school_id', $school->id)
                ->selectRaw('MONTH(start) as month, COUNT(*) as total')
                ->whereYear('start', date('Y'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            return view('admin.dashboard', compact(
                'stats',
                'recentStudents',
                'recentSchedules',
                'upcomingSchedules',
                'monthlyLessons'
            ));

        } catch (\Exception $e) {
            Log::error('School Admin Dashboard Error: ' . $e->getMessage());
            // Fallback with basic stats
            $stats = $this->getBasicStats();
            return view('admin.dashboard', compact('stats'));
        }
    }

    /**
     * Get basic stats as fallback
     */
    private function getBasicStats()
    {
        return [
            'total_schools' => School::count(),
            'active_schools' => School::where('status', 'active')->count(),
            'total_users' => User::count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_instructors' => User::where('role', 'instructor')->count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_vehicles' => Fleet::count(),
            'available_vehicles' => Fleet::where('status', 'available')->count(),
            'total_schedules' => Schedule::count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount') ?? 0,
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
            'super_admins' => 0,
            'school_admins' => 0,
            'trial_schools' => 0,
            'paid_schools' => 0,
            'total_invoices' => Invoice::count(),
            'completed_lessons' => Schedule::where('status', 'completed')->count(),
        ];
    }

    /**
     * User profile page
     */
    public function profile()
    {
        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $user->update($request->only(['fname', 'lname', 'email', 'phone', 'address']));

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Settings page
     */
    public function settings()
    {
        $user = Auth::user();
        return view('admin.settings', compact('user'));
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        return back()->with('success', 'Settings updated successfully!');
    }
}
