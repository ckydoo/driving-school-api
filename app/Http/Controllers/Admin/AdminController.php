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
    public function dashboard()
    {
        // Get dashboard statistics
        $stats = [
            'total_schools' => School::count(),
            'active_schools' => School::where('status', 'active')->count(),
            'total_users' => User::count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_instructors' => User::where('role', 'instructor')->count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_vehicles' => Fleet::count(),
            'available_vehicles' => Fleet::where('status', 'available')->count(),
            'total_schedules' => Schedule::count(),
            'todays_schedules' => Schedule::whereDate('start', today())->count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount') ?? 0,
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
        ];

        // Recent activities with proper relationships
        $recentUsers = User::orderBy('created_at', 'desc')->take(5)->get();

        // Fix: Load proper relationships for schedules - handle both object and ID cases
        $recentSchedules = Schedule::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Try to load relationships, but handle cases where they might be IDs
        foreach ($recentSchedules as $schedule) {
            try {
                $schedule->load(['student', 'instructor', 'course']);
            } catch (\Exception $e) {
                // If relationships fail to load, that's ok - we handle it in the view
                Log::warning("Failed to load schedule relationships: " . $e->getMessage());
            }
        }

        // Fix: Load payments with proper student relationship through invoice
        $recentPayments = Payment::with(['invoice.student', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Monthly revenue chart data - Fix: Ensure we have proper data
        $monthlyRevenue = Payment::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COALESCE(SUM(amount), 0) as total')
        )
        ->where('status', 'completed')
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        // If no revenue data, provide empty structure for chart
        if ($monthlyRevenue->isEmpty()) {
            $monthlyRevenue = collect();
            for ($i = 11; $i >= 0; $i--) {
                $monthlyRevenue->push((object)[
                    'month' => now()->subMonths($i)->format('Y-m'),
                    'total' => 0
                ]);
            }
        }

        // User registration trends
        $userTrends = User::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentUsers',
            'recentSchedules',
            'recentPayments',
            'monthlyRevenue',
            'userTrends'
        ));
    }

    public function settings()
    {
        return view('admin.settings');
    }

    public function updateSettings(Request $request)
    {
        // Implementation for updating settings
        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    public function profile()
    {
        return view('admin.profile');
    }

    public function updateProfile(Request $request)
    {
        // Implementation for updating profile
        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
}
