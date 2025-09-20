<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\School;
use App\Models\Schedule;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Fleet;
use Carbon\Carbon;

class AdminReportController extends Controller
{
    /**
     * Show the main reports dashboard
     */
    public function index()
    {
        $currentUser = Auth::user();

        // Check if user has admin access
        if (!$currentUser || !$currentUser->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        return view('admin.reports.index', compact('currentUser'));
    }

    /**
     * System Reports - Super Admin Only
     */
    public function systemReports()
    {
        $currentUser = Auth::user();

        // Ensure only super admins can access system reports
        if (!$currentUser || !$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        try {
            // System-wide statistics
            $systemStats = [
                'total_schools' => School::count(),
                'total_users' => User::count(),
                'total_students' => User::where('role', 'student')->count(),
                'total_instructors' => User::where('role', 'instructor')->count(),
                'total_admins' => User::whereIn('role', ['admin', 'super_admin'])->count(),
                'total_schedules' => Schedule::count(),
                'total_vehicles' => Fleet::count(),
                'total_invoices' => Invoice::count(),
                'total_payments' => Payment::count(),
            ];

            // Revenue statistics
            $revenueStats = [
                'total_revenue' => Payment::sum('amount') ?? 0,
                'monthly_revenue' => Payment::whereMonth('created_at', Carbon::now()->month)
                                          ->whereYear('created_at', Carbon::now()->year)
                                          ->sum('amount') ?? 0,
                'pending_invoices' => Invoice::where('status', 'unpaid')->sum('total_amount') ?? 0,
                'paid_invoices' => Invoice::where('status', 'paid')->sum('total_amount') ?? 0,
            ];

            // School performance data - Fixed to handle missing relationships
            $schoolPerformance = School::with(['users'])
                ->withCount(['users'])
                ->get()
                ->map(function($school) {
                    // Get fleet count manually since relationship might not exist
                    $fleetCount = Fleet::where('school_id', $school->id)->count();

                    // Get payments through users since school->payments relationship might not exist
                    $monthlyRevenue = Payment::whereHas('invoice', function($query) use ($school) {
                        $query->whereHas('student', function($q) use ($school) {
                            $q->where('school_id', $school->id);
                        });
                    })
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->sum('amount') ?? 0;

                    return [
                        'name' => $school->name,
                        'total_users' => $school->users_count,
                        'total_vehicles' => $fleetCount,
                        'students' => $school->users()->where('role', 'student')->count(),
                        'instructors' => $school->users()->where('role', 'instructor')->count(),
                        'monthly_revenue' => $monthlyRevenue,
                    ];
                });

            // Recent activity
            $recentActivity = collect([
                'new_users' => User::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
                'new_schedules' => Schedule::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
                'new_payments' => Payment::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
                'new_invoices' => Invoice::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            ]);

            // System health indicators
            $systemHealth = [
                'database_status' => $this->checkDatabaseHealth(),
                'storage_usage' => $this->getStorageUsage(),
                'cache_status' => $this->checkCacheHealth(),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
            ];

            return view('admin.reports.system', compact(
                'currentUser',
                'systemStats',
                'revenueStats',
                'schoolPerformance',
                'recentActivity',
                'systemHealth'
            ));

        } catch (\Exception $e) {
            \Log::error('System reports error: ' . $e->getMessage());

            return back()->with('error', 'Unable to load system reports. Please try again.');
        }
    }

    /**
     * Revenue Reports
     */
    public function revenue()
    {
        $currentUser = Auth::user();

        if (!$currentUser || !$currentUser->isAdmin()) {
            abort(403, 'Access denied.');
        }

        // Add revenue report logic here
        return view('admin.reports.revenue', compact('currentUser'));
    }

    /**
     * Student Reports
     */
    public function students()
    {
        $currentUser = Auth::user();

        if (!$currentUser || !$currentUser->isAdmin()) {
            abort(403, 'Access denied.');
        }

        // Add student report logic here
        return view('admin.reports.students', compact('currentUser'));
    }

    /**
     * Instructor Reports
     */
    public function instructors()
    {
        $currentUser = Auth::user();

        if (!$currentUser || !$currentUser->isAdmin()) {
            abort(403, 'Access denied.');
        }

        // Add instructor report logic here
        return view('admin.reports.instructors', compact('currentUser'));
    }

    /**
     * Vehicle Reports
     */
    public function vehicles()
    {
        $currentUser = Auth::user();

        if (!$currentUser || !$currentUser->isAdmin()) {
            abort(403, 'Access denied.');
        }

        // Add vehicle report logic here
        return view('admin.reports.vehicles', compact('currentUser'));
    }

    /**
     * Export Reports
     */
    public function export($type)
    {
        $currentUser = Auth::user();

        if (!$currentUser || !$currentUser->isAdmin()) {
            abort(403, 'Access denied.');
        }

        // Add export logic here based on $type
        return response()->json(['message' => 'Export functionality coming soon']);
    }

    /**
     * Helper Methods
     */
    private function checkDatabaseHealth()
    {
        try {
            DB::connection()->getPdo();
            return 'healthy';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private function getStorageUsage()
    {
        try {
            $bytes = disk_free_space(storage_path());
            return $bytes ? round($bytes / 1024 / 1024 / 1024, 2) . ' GB available' : 'unknown';
        } catch (\Exception $e) {
            return 'unknown';
        }
    }

    private function checkCacheHealth()
    {
        try {
            cache()->put('health_check', 'ok', 60);
            return cache()->get('health_check') === 'ok' ? 'healthy' : 'error';
        } catch (\Exception $e) {
            return 'error';
        }
    }
}
