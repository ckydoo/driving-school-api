<?php

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
     * Check if current user can access admin features
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
     * Check if current user can access super admin features
     */
    protected function ensureSuperAdminAccess()
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }
        return $user;
    }

    /**
     * Main admin dashboard - shows data based on user role
     */
    public function dashboard()
    {
        $currentUser = $this->ensureAdminAccess();

        if ($currentUser->isSuperAdmin()) {
            return $this->superAdminDashboard();
        } else {
            return $this->schoolAdminDashboard();
        }
    }

    /**
     * Super Admin Dashboard - System-wide data
     */
    public function superAdminDashboard()
    {
        // Allow access for super admins only OR if coming from dashboard redirect
        $currentUser = Auth::user();
        if (!$currentUser->isSuperAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Super admin access required.');
        }

        try {
            // System-wide statistics
            $stats = [
                'total_schools' => School::count(),
                'active_schools' => School::where('status', 'active')->count(),
                'total_users' => User::count(),
                'active_users' => User::where('status', 'active')->count(),
                'total_students' => User::where('role', 'student')->count(),
                'total_instructors' => User::where('role', 'instructor')->count(),
                'super_admins' => User::where('role', 'super_admin')->count(),
                'school_admins' => User::where('role', 'admin')->count(),
                'total_vehicles' => Fleet::count(),
                'total_schedules' => Schedule::count(),
                'total_invoices' => Invoice::count(),
                'total_revenue' => Payment::where('status', 'completed')->sum('amount') ?? 0,
            ];

            // Recent activity
            $recentUsers = User::with('school')
                ->latest()
                ->take(10)
                ->get();

            $recentSchools = School::withCount('users')
                ->latest()
                ->take(5)
                ->get();

            $topSchools = School::withCount([
                'users as students_count' => function($q) {
                    $q->where('role', 'student');
                }
            ])
            ->orderBy('students_count', 'desc')
            ->take(5)
            ->get();

            // Monthly revenue data for chart
            $monthlyRevenue = [];
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $revenue = Payment::where('status', 'completed')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('amount') ?? 0;

                $monthlyRevenue[] = [
                    'month' => $date->format('M Y'),
                    'revenue' => $revenue
                ];
            }

            return view('admin.super-dashboard', compact(
                'stats',
                'recentUsers',
                'recentSchools',
                'topSchools',
                'monthlyRevenue'
            ));

        } catch (\Exception $e) {
            Log::error('Super Admin Dashboard Error: ' . $e->getMessage());

            return view('admin.super-dashboard', [
                'stats' => $this->getDefaultStats(),
                'recentUsers' => collect(),
                'recentSchools' => collect(),
                'topSchools' => collect(),
                'monthlyRevenue' => [],
                'error' => 'Unable to load dashboard data. Please try again.'
            ]);
        }
    }

    /**
     * School Admin Dashboard - School-specific data
     */
    public function schoolAdminDashboard()
    {
        $currentUser = $this->ensureAdminAccess();

        // Super admins shouldn't normally use this, but allow it
        if ($currentUser->isSuperAdmin()) {
            return redirect()->route('admin.super.dashboard')
                ->with('info', 'Super admins should use the Super Admin dashboard.');
        }

        try {
            $schoolId = $currentUser->school_id;

            if (!$schoolId) {
                return redirect()->route('admin.profile')
                    ->with('error', 'Please contact administrator to assign you to a school.');
            }

            // **FIX: Get the school object**
            $school = School::find($schoolId);
            
            if (!$school) {
                return redirect()->route('admin.profile')
                    ->with('error', 'School not found. Please contact administrator.');
            }

            $stats = [
                'total_students' => User::where('school_id', $schoolId)->where('role', 'student')->count(),
                'active_students' => User::where('school_id', $schoolId)->where('role', 'student')->where('status', 'active')->count(),
                'total_instructors' => User::where('school_id', $schoolId)->where('role', 'instructor')->count(),
                'active_schedules' => Schedule::whereHas('student', function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })->where('status', 'scheduled')->count(),
                'total_schedules' => Schedule::whereHas('student', function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })->count(),
                'completed_lessons' => Schedule::whereHas('student', function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })->where('status', 'completed')->count(),
                'total_vehicles' => Fleet::where('school_id', $schoolId)->count(),
                'available_vehicles' => Fleet::where('school_id', $schoolId)->where('status', 'available')->count(),
                'pending_invoices' => Invoice::whereHas('student', function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })->where('status', 'pending')->count(),
                'total_revenue' => Payment::whereHas('user', function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })->where('status', 'completed')->sum('amount') ?? 0,
                'monthly_revenue' => Payment::whereHas('user', function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })->where('status', 'completed')
                  ->whereMonth('created_at', Carbon::now()->month)
                  ->sum('amount') ?? 0,
            ];

            // Recent activity for this school
            $recentStudents = User::where('school_id', $schoolId)
                ->where('role', 'student')
                ->latest()
                ->take(5)
                ->get();

            $upcomingSchedules = Schedule::whereHas('student', function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })
                ->with(['student', 'instructor'])
                ->where('start', '>=', Carbon::today())
                ->orderBy('start')
                ->take(10)
                ->get();

            // Monthly lessons data for chart
            $monthlyLessons = [];
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $total = Schedule::whereHas('student', function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })
                ->whereYear('start', $date->year)
                ->whereMonth('start', $date->month)
                ->count();

                $monthlyLessons[] = (object) [
                    'month' => $date->format('M Y'),
                    'total' => $total
                ];
            }

            // **FIX: Pass the school object to the view**
            return view('admin.school-dashboard', compact(
                'school',           // <-- This was missing!
                'stats',
                'recentStudents',
                'upcomingSchedules',
                'monthlyLessons'    // <-- This was also missing!
            ));

        } catch (\Exception $e) {
            Log::error('School Admin Dashboard Error: ' . $e->getMessage());

            return view('admin.school-dashboard', [
                'school' => null,   // <-- Provide fallback
                'stats' => $this->getSchoolDefaultStats(),
                'recentStudents' => collect(),
                'upcomingSchedules' => collect(),
                'monthlyLessons' => [],  // <-- Add this fallback too
                'error' => 'Unable to load dashboard data. Please try again.'
            ]);
        }
    }

    /**
     * System stats for super admin (called via route)
     */
    public function systemStats()
    {
        $this->ensureSuperAdminAccess();

        // Return JSON data for charts/widgets
        return response()->json([
            'user_growth' => $this->getUserGrowthData(),
            'revenue_trends' => $this->getRevenueTrends(),
            'school_performance' => $this->getSchoolPerformance(),
        ]);
    }

    /**
     * Settings page
     */
    public function settings()
    {
        $currentUser = $this->ensureAdminAccess();
        return view('admin.settings', compact('currentUser'));
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        $this->ensureAdminAccess();
        // Implementation for updating settings
        return back()->with('success', 'Settings updated successfully!');
    }

    /**
     * Profile page
     */
    public function profile()
    {
        $currentUser = $this->ensureAdminAccess();
        return view('admin.profile', compact('currentUser'));
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $this->ensureAdminAccess();
        // Implementation for updating profile
        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Instructor dashboard
     */
    public function instructorDashboard()
    {
        $user = Auth::user();

        if (!$user || !in_array($user->role, ['super_admin', 'admin', 'instructor'])) {
            abort(403, 'Access denied. Instructor privileges required.');
        }

        // Implementation for instructor dashboard
        return view('instructor.dashboard', compact('user'));
    }

    // === HELPER METHODS ===

    /**
     * Get default stats when there's an error (Super Admin)
     */
    private function getDefaultStats(): array
    {
        return [
            'total_schools' => 0,
            'active_schools' => 0,
            'total_users' => 0,
            'total_students' => 0,
            'total_instructors' => 0,
            'active_users' => 0,
            'super_admins' => 0,
            'school_admins' => 0,
            'total_schedules' => 0,
            'total_invoices' => 0,
            'total_vehicles' => 0,
            'total_revenue' => 0,
        ];
    }

    /**
     * Get default stats when there's an error (School Admin)
     */
    private function getSchoolDefaultStats(): array
    {
        return [
            'total_students' => 0,
            'active_students' => 0,
            'total_instructors' => 0,
            'active_schedules' => 0,
            'total_schedules' => 0,
            'completed_lessons' => 0,
            'total_vehicles' => 0,
            'available_vehicles' => 0,
            'pending_invoices' => 0,
            'total_revenue' => 0,
            'monthly_revenue' => 0,
        ];
    }

    /**
     * Get user growth data for charts
     */
    private function getUserGrowthData(): array
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = User::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $data[] = [
                'month' => $date->format('M Y'),
                'users' => $count
            ];
        }
        return $data;
    }

    /**
     * Get revenue trends for charts
     */
    private function getRevenueTrends(): array
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenue = Payment::where('status', 'completed')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount') ?? 0;

            $data[] = [
                'month' => $date->format('M Y'),
                'revenue' => $revenue
            ];
        }
        return $data;
    }

    /**
     * Get school performance data
     */
    private function getSchoolPerformance(): array
    {
        return School::withCount([
            'users as students_count' => function($q) {
                $q->where('role', 'student');
            },
            'users as instructors_count' => function($q) {
                $q->where('role', 'instructor');
            }
        ])
        ->orderBy('students_count', 'desc')
        ->take(10)
        ->get()
        ->toArray();
    }
}