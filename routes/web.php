<?php
// routes/web.php (FIXED - Controller Names)

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminLogController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminFleetController;
use App\Http\Controllers\Admin\AdminBackupController;
use App\Http\Controllers\Admin\AdminCourseController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminSchoolController;
use App\Http\Controllers\Admin\AdminSystemController;
use App\Http\Controllers\Admin\AdminInvoiceController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\PublicSchoolRegistrationController; // FIXED: Changed to PublicSchoolRegistrationController
use App\Http\Controllers\Admin\AdminScheduleController;
use App\Http\Controllers\Admin\AdminSubscriptionController;

// School Registration Routes (add these BEFORE the auth middleware group)
Route::get('/register', [PublicSchoolRegistrationController::class, 'showRegistrationForm'])
    ->name('school.register.form')
    ->middleware('guest');

Route::post('/register', [PublicSchoolRegistrationController::class, 'register'])
    ->name('school.register')
    ->middleware('guest');

// Optional: Add a redirect from /school-register to /register for SEO
Route::get('/school-register', function () {
    return redirect('/register', 301);
});
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin/subscription-packages')->group(function () {
    Route::get('/', [SubscriptionPackageController::class, 'index']);
    Route::get('/create', [SubscriptionPackageController::class, 'create']);
    Route::post('/', [SubscriptionPackageController::class, 'store']);
    Route::get('/{package}/edit', [SubscriptionPackageController::class, 'edit']);
    Route::put('/{package}', [SubscriptionPackageController::class, 'update']);
    Route::delete('/{package}', [SubscriptionPackageController::class, 'destroy']);
});
// === LANDING PAGES ===
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/features', [LandingController::class, 'features'])->name('features');
Route::get('/pricing', [LandingController::class, 'pricing'])->name('pricing');
Route::get('/about', [LandingController::class, 'about'])->name('about');
Route::get('/contact', [LandingController::class, 'contact'])->name('contact');
Route::post('/contact', [LandingController::class, 'submitContact'])->name('contact.submit');

// Authentication routes
Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login']);
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

// Redirect to appropriate dashboard after login
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // Redirect non-admin users
        return redirect('/')->with('error', 'Access denied.');
    })->name('dashboard');
});

// === ALL ADMIN ROUTES (Super Admin gets unlimited access, School Admin gets restricted) ===
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // === DASHBOARD ROUTES ===
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard.index');
    Route::get('/super', [AdminController::class, 'superAdminDashboard'])->name('super.dashboard');

    // === SCHOOL MANAGEMENT ===
    Route::resource('schools', AdminSchoolController::class);
    Route::post('schools/{school}/toggle-status', [AdminSchoolController::class, 'toggleStatus'])->name('schools.toggle-status');
    Route::get('schools/{school}/login-as', [AdminSchoolController::class, 'loginAsSchool'])->name('schools.login-as');
    Route::get('schools/{school}/users-data', [AdminSchoolController::class, 'getSchoolUsers'])->name('schools.users-data');
    Route::get('/return-to-super-admin', [AdminSchoolController::class, 'returnToSuperAdmin'])->name('schools.return-super-admin');

    // === USER MANAGEMENT ===
    Route::resource('users', AdminUserController::class);
    Route::post('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('users/{user}/schedules', [AdminUserController::class, 'userSchedules'])->name('users.schedules');
    Route::get('users/{user}/invoices', [AdminUserController::class, 'userInvoices'])->name('users.invoices');
    Route::get('all-users', [AdminUserController::class, 'allUsers'])->name('users.all');
    Route::get('super-admins', [AdminUserController::class, 'superAdmins'])->name('users.super-admins');

    // === SCHEDULE MANAGEMENT ===
    Route::get('schedules/calendar', [AdminScheduleController::class, 'calendar'])->name('schedules.calendar');
    Route::get('schedules/{schedule}/conflicts', [AdminScheduleController::class, 'checkConflicts'])->name('schedules.conflicts');
    Route::post('schedules/{schedule}/toggle-status', [AdminScheduleController::class, 'toggleStatus'])->name('schedules.toggle-status');
    Route::post('schedules/{schedule}/mark-attended', [AdminScheduleController::class, 'markAttended'])->name('schedules.mark-attended');
    Route::resource('schedules', AdminScheduleController::class);

    // === COURSE MANAGEMENT ===
    Route::resource('courses', AdminCourseController::class);

    // === FLEET MANAGEMENT ===
    Route::get('fleet/{fleet}/schedules', [AdminFleetController::class, 'fleetSchedules'])->name('fleet.schedules');
    Route::post('fleet/{fleet}/assign-instructor', [AdminFleetController::class, 'assignInstructor'])->name('fleet.assign-instructor');
    Route::post('fleet/{fleet}/toggle-status', [AdminFleetController::class, 'toggleStatus'])->name('fleet.toggle-status');
    Route::resource('fleet', AdminFleetController::class);

    // === INVOICE MANAGEMENT === (FIXED - Remove Duplicates)
    Route::resource('invoices', AdminInvoiceController::class);
    
    // Add the export route
    Route::get('invoices/export', [AdminInvoiceController::class, 'export'])
         ->name('invoices.export');
    
    Route::post('invoices/{invoice}/mark-as-paid', [AdminInvoiceController::class, 'markAsPaid'])
         ->name('invoices.markAsPaid');
    
    Route::get('invoices/{invoice}/download-pdf', [AdminInvoiceController::class, 'downloadPdf'])
         ->name('invoices.downloadPdf');

    // === PAYMENT MANAGEMENT ===
    Route::post('payments/{payment}/verify', [AdminPaymentController::class, 'verify'])->name('payments.verify');
    Route::resource('payments', AdminPaymentController::class);

    // === SUBSCRIPTION MANAGEMENT ===
    Route::post('subscriptions/{subscription}/cancel', [AdminSubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::post('subscriptions/{subscription}/reactivate', [AdminSubscriptionController::class, 'reactivate'])->name('subscriptions.reactivate');
    Route::resource('subscriptions', AdminSubscriptionController::class);

    // === REPORTS ===
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [AdminReportController::class, 'index'])->name('index');
        Route::get('/revenue', [AdminReportController::class, 'revenue'])->name('revenue');
        Route::get('/students', [AdminReportController::class, 'students'])->name('students');
        Route::get('/instructors', [AdminReportController::class, 'instructors'])->name('instructors');
        Route::get('/schedules', [AdminReportController::class, 'schedules'])->name('schedules');
        Route::get('/export/{type}', [AdminReportController::class, 'export'])->name('export');
        Route::get('/vehicles', [AdminReportController::class, 'vehicles'])->name('vehicles');
        Route::get('/system', [AdminReportController::class, 'systemReports'])->name('system'); // Added this
    });

    // === SETTINGS & PROFILE === (MOVED TO MAIN GROUP - ACCESSIBLE TO ALL ADMINS)
    Route::get('settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::get('profile', [AdminController::class, 'profile'])->name('profile');
    Route::post('profile', [AdminController::class, 'updateProfile'])->name('profile.update');

    // === SYSTEM MANAGEMENT ===
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/', [AdminSystemController::class, 'index'])->name('index');
        Route::get('/settings', [AdminSystemController::class, 'settings'])->name('settings'); // This is admin.system.settings
        Route::post('/settings', [AdminSystemController::class, 'updateSettings'])->name('settings.update');
        Route::get('/maintenance', [AdminSystemController::class, 'maintenance'])->name('maintenance');
        Route::post('/maintenance/enable', [AdminSystemController::class, 'enableMaintenance'])->name('maintenance.enable');
        Route::post('/maintenance/disable', [AdminSystemController::class, 'disableMaintenance'])->name('maintenance.disable');
        Route::post('/cache/clear', [AdminSystemController::class, 'clearCache'])->name('cache.clear');
        Route::get('/health', [AdminSystemController::class, 'health'])->name('health');
        Route::get('/info', [AdminSystemController::class, 'systemInfo'])->name('info');
        Route::get('/stats', [AdminController::class, 'systemStats'])->name('stats');
    });

    // === LOG MANAGEMENT ===
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/', [AdminLogController::class, 'index'])->name('index');
        Route::get('/{log}', [AdminLogController::class, 'show'])->name('show');
        Route::delete('/{log}', [AdminLogController::class, 'destroy'])->name('destroy');
        Route::post('/clear', [AdminLogController::class, 'clear'])->name('clear');
        Route::get('/export/{type}', [AdminLogController::class, 'export'])->name('export');
    });

    // === BACKUP MANAGEMENT ===
    Route::prefix('backups')->name('backups.')->group(function () {
        Route::get('/', [AdminBackupController::class, 'index'])->name('index');
        Route::post('/create', [AdminBackupController::class, 'create'])->name('create');
        Route::get('/{backup}/download', [AdminBackupController::class, 'download'])->name('download');
        Route::delete('/{backup}', [AdminBackupController::class, 'destroy'])->name('destroy');
        Route::post('/{backup}/restore', [AdminBackupController::class, 'restore'])->name('restore');
        Route::post('/config', [AdminBackupController::class, 'config'])->name('config');
        Route::post('/cleanup', [AdminBackupController::class, 'cleanup'])->name('cleanup');
        Route::post('/test', [AdminBackupController::class, 'test'])->name('test');
    });
    
    // === ADDITIONAL SYSTEM ROUTES ===
    Route::get('/email-settings', [AdminSystemController::class, 'emailSettings'])->name('email.settings');
    Route::post('/email-settings', [AdminSystemController::class, 'updateEmailSettings'])->name('email.settings.update');
    Route::post('/test-email', [AdminSystemController::class, 'testEmail'])->name('email.test');
    Route::get('/database-stats', [AdminSystemController::class, 'databaseStats'])->name('database.stats');
    Route::get('/server-info', [AdminSystemController::class, 'serverInfo'])->name('server.info');
});
// === SCHOOL ADMIN SPECIFIC ROUTES ===
Route::middleware(['auth', 'school_admin_only'])->prefix('admin')->name('admin.')->group(function () {
    // === SCHOOL-SPECIFIC ROUTES ===
    Route::get('/students', [AdminUserController::class, 'students'])->name('students.index');
    Route::get('/instructors', [AdminUserController::class, 'instructors'])->name('instructors.index');
    Route::get('/my-school', [AdminSchoolController::class, 'mySchool'])->name('my-school');
    Route::get('/my-school/edit', [AdminSchoolController::class, 'editMySchool'])->name('my-school.edit');
    Route::put('/my-school', [AdminSchoolController::class, 'updateMySchool'])->name('my-school.update');
});

// === FALLBACK ROUTE ===
Route::fallback(function () {
    return redirect()->route('home');
});