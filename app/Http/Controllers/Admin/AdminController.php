<?php
// app/Http/Controllers/Admin/AdminController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\School;
use App\Models\Schedule;
use App\Models\Fleet;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
        ];

        // Recent activities
        $recentUsers = User::orderBy('created_at', 'desc')->take(5)->get();
        $recentSchedules = Schedule::with(['student', 'instructor', 'course'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        $recentPayments = Payment::with(['student'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Monthly revenue chart data
        $monthlyRevenue = Payment::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('SUM(amount) as total')
        )
        ->where('status', 'completed')
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

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
        // You can store app settings in a settings table or config files
        $settings = [
            'app_name' => config('app.name'),
            'app_timezone' => config('app.timezone'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
            // Add more settings as needed
        ];

        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'app_name' => 'required|string|max:255',
            'app_timezone' => 'required|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Here you would typically update your .env file or settings table
        // For now, we'll just flash a success message
        return back()->with('success', 'Settings updated successfully!');
    }

    public function profile()
    {
        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'current_password' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check current password if provided
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }
        }

        // Update user data
        $userData = $request->only(['fname', 'lname', 'email', 'phone', 'address']);

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return back()->with('success', 'Profile updated successfully!');
    }
}
