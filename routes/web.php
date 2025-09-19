<?php
// Add these routes to your routes/web.php file
// This fixes the missing login route error

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminFleetController;
use App\Http\Controllers\Admin\AdminCourseController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminSchoolController;
use App\Http\Controllers\Admin\AdminInvoiceController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminScheduleController;

// Standard welcome route
Route::get('/', function () {
    return view('welcome');
});

// Authentication routes - ADD THESE TO FIX THE ERROR
Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login']);
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

// Alternative: If you want to use Laravel's built-in auth, uncomment this:
// Auth::routes();

// Redirect to admin dashboard after login for authenticated users
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        // Redirect non-admin users to a different dashboard or home
        return redirect('/')->with('error', 'Access denied.');
    })->name('dashboard');
});

// Admin routes group
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard.index');

    // User Management
    Route::resource('users', AdminUserController::class);
    Route::post('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('users/{user}/schedules', [AdminUserController::class, 'userSchedules'])->name('users.schedules');
    Route::get('users/{user}/invoices', [AdminUserController::class, 'userInvoices'])->name('users.invoices');

    // School Management
    Route::resource('schools', AdminSchoolController::class);
    Route::post('schools/{school}/toggle-status', [AdminSchoolController::class, 'toggleStatus'])->name('schools.toggle-status');
    Route::get('schools/{school}/users', [AdminSchoolController::class, 'schoolUsers'])->name('schools.users');

    // Schedule Management
    Route::resource('schedules', AdminScheduleController::class);
    Route::post('schedules/{schedule}/mark-attended', [AdminScheduleController::class, 'markAttended'])->name('schedules.mark-attended');
    Route::get('schedules/calendar', [AdminScheduleController::class, 'calendar'])->name('schedules.calendar');

    // Fleet Management
    Route::resource('fleet', AdminFleetController::class);
    Route::post('fleet/{fleet}/assign-instructor', [AdminFleetController::class, 'assignInstructor'])->name('fleet.assign-instructor');
    Route::get('fleet/{fleet}/schedules', [AdminFleetController::class, 'fleetSchedules'])->name('fleet.schedules');

    // Course Management
    Route::resource('courses', AdminCourseController::class);

    // Invoice Management
    Route::resource('invoices', AdminInvoiceController::class);
    Route::post('invoices/{invoice}/send', [AdminInvoiceController::class, 'sendInvoice'])->name('invoices.send');
    Route::get('invoices/{invoice}/pdf', [AdminInvoiceController::class, 'downloadPdf'])->name('invoices.pdf');

    // Payment Management
    Route::resource('payments', AdminPaymentController::class);
    Route::post('payments/{payment}/verify', [AdminPaymentController::class, 'verifyPayment'])->name('payments.verify');

    // // Reports
    Route::get('reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('reports/revenue', [AdminReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('reports/students', [AdminReportController::class, 'students'])->name('reports.students');
    Route::get('reports/instructors', [AdminReportController::class, 'instructors'])->name('reports.instructors');
    Route::get('reports/vehicles', [AdminReportController::class, 'vehicles'])->name('reports.vehicles');
    Route::get('reports/export/{type}', [AdminReportController::class, 'export'])->name('reports.export');

    // Settings
    Route::get('settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('settings', [AdminController::class, 'updateSettings'])->name('settings.update');

    // Profile
    Route::get('profile', [AdminController::class, 'profile'])->name('profile');
    Route::post('profile', [AdminController::class, 'updateProfile'])->name('profile.update');
});
