<?php
// routes/web.php (FIXED - No Closure Errors)

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
use App\Http\Controllers\SchoolRegistrationController;
use App\Http\Controllers\Admin\AdminScheduleController;
use App\Http\Controllers\Admin\AdminSubscriptionController;

// School Registration Routes (add these BEFORE the auth middleware group)
Route::get('/register', [SchoolRegistrationController::class, 'showRegistrationForm'])
    ->name('school.register.form')
    ->middleware('guest');

Route::post('/register', [SchoolRegistrationController::class, 'register'])
    ->name('school.register')
    ->middleware('guest');

// Optional: Add a redirect from /school-register to /register for SEO
Route::get('/school-register', function () {
    return redirect('/register', 301);
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
    // Super admins get full access, school admins get view-only access to their own school
    Route::resource('schools', AdminSchoolController::class);
    Route::post('schools/{school}/toggle-status', [AdminSchoolController::class, 'toggleStatus'])->name('schools.toggle-status');
    Route::get('schools/{school}/login-as', [AdminSchoolController::class, 'loginAsSchool'])->name('schools.login-as');
    Route::get('schools/{school}/users-data', [AdminSchoolController::class, 'getSchoolUsers'])->name('schools.users-data');
    Route::get('/return-to-super-admin', [AdminSchoolController::class, 'returnToSuperAdmin'])->name('schools.return-super-admin');

    // === USER MANAGEMENT ===
    // Permission checking is handled in the controllers
    Route::resource('users', AdminUserController::class);
    Route::post('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('users/{user}/schedules', [AdminUserController::class, 'userSchedules'])->name('users.schedules');
    Route::get('users/{user}/invoices', [AdminUserController::class, 'userInvoices'])->name('users.invoices');

    // Super admin only user routes
    Route::get('all-users', [AdminUserController::class, 'allUsers'])->name('users.all');
    Route::get('super-admins', [AdminUserController::class, 'superAdmins'])->name('users.super-admins');

    // === SCHEDULE MANAGEMENT ===
    Route::resource('schedules', AdminScheduleController::class);
    Route::post('schedules/{schedule}/mark-attended', [AdminScheduleController::class, 'markAttended'])->name('schedules.mark-attended');
    Route::get('schedules/calendar', [AdminScheduleController::class, 'calendar'])->name('schedules.calendar');

    // === FLEET MANAGEMENT ===
    Route::resource('fleet', AdminFleetController::class);
    Route::post('fleet/{fleet}/assign-instructor', [AdminFleetController::class, 'assignInstructor'])->name('fleet.assign-instructor');
    Route::get('fleet/{fleet}/schedules', [AdminFleetController::class, 'fleetSchedules'])->name('fleet.schedules');

    // === COURSE MANAGEMENT ===
    Route::resource('courses', AdminCourseController::class);

    // === FINANCIAL MANAGEMENT ===
    Route::resource('invoices', AdminInvoiceController::class);
    Route::post('invoices/{invoice}/send', [AdminInvoiceController::class, 'sendInvoice'])->name('invoices.send');
    Route::get('invoices/{invoice}/pdf', [AdminInvoiceController::class, 'downloadPdf'])->name('invoices.pdf');

    Route::resource('payments', AdminPaymentController::class);
    Route::post('payments/{payment}/verify', [AdminPaymentController::class, 'verifyPayment'])->name('payments.verify');
// Invoice Management Routes
Route::resource('invoices', AdminInvoiceController::class);
Route::post('invoices/{invoice}/send', [AdminInvoiceController::class, 'sendInvoice'])->name('invoices.send');
Route::get('invoices/{invoice}/pdf', [AdminInvoiceController::class, 'downloadPdf'])->name('invoices.downloadPdf');
Route::post('invoices/{invoice}/mark-paid', [AdminInvoiceController::class, 'markAsPaid'])->name('invoices.markAsPaid');

// Payment Management Routes
Route::resource('payments', AdminPaymentController::class);
Route::post('payments/{payment}/verify', [AdminPaymentController::class, 'verifyPayment'])->name('payments.verify');
Route::post('payments/{payment}/refund', [AdminPaymentController::class, 'refund'])->name('payments.refund');
Route::get('payments/stats', [AdminPaymentController::class, 'getStats'])->name('payments.stats');

    // === REPORTS ===
    Route::get('reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('reports/revenue', [AdminReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('reports/students', [AdminReportController::class, 'students'])->name('reports.students');
    Route::get('reports/instructors', [AdminReportController::class, 'instructors'])->name('reports.instructors');
    Route::get('reports/vehicles', [AdminReportController::class, 'vehicles'])->name('reports.vehicles');
    Route::get('reports/export/{type}', [AdminReportController::class, 'export'])->name('reports.export');

    // Super admin only reports
    Route::get('system-reports', [AdminReportController::class, 'systemReports'])->name('reports.system');
    Route::get('system-stats', [AdminController::class, 'systemStats'])->name('system.stats');

    // === SETTINGS & PROFILE ===
    Route::get('settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::get('profile', [AdminController::class, 'profile'])->name('profile');
    Route::post('profile', [AdminController::class, 'updateProfile'])->name('profile.update');
});

// === INSTRUCTOR ROUTES ===
Route::middleware(['auth', 'instructor'])->prefix('instructor')->name('instructor.')->group(function () {
    Route::get('/', [AdminController::class, 'instructorDashboard'])->name('dashboard');
    Route::get('students', [AdminUserController::class, 'instructorStudents'])->name('students');
    Route::get('schedules', [AdminScheduleController::class, 'instructorSchedules'])->name('schedules');
    Route::post('schedules/{schedule}/mark-attended', [AdminScheduleController::class, 'markAttended'])->name('schedules.mark-attended');
    Route::get('vehicles', [AdminFleetController::class, 'instructorVehicles'])->name('vehicles');
    Route::get('profile', [AdminController::class, 'profile'])->name('profile');
    Route::post('profile', [AdminController::class, 'updateProfile'])->name('profile.update');
});

// === API ROUTES FOR AJAX CALLS ===
Route::middleware(['auth', 'admin'])->prefix('api/admin')->name('api.admin.')->group(function () {
    Route::get('schools/{school}/stats', [AdminSchoolController::class, 'getSchoolStats'])->name('schools.stats');
    Route::get('schools/{school}/users', [AdminSchoolController::class, 'getSchoolUsers'])->name('schools.users');
    Route::get('users/stats', [AdminUserController::class, 'getUserStats'])->name('users.stats');
    Route::get('dashboard/stats', [AdminController::class, 'getDashboardStats'])->name('dashboard.stats');
});
// === SUPER ADMIN ONLY ROUTES ===
Route::middleware(['auth', 'super_admin_only'])->prefix('admin')->name('admin.')->group(function () {

    // === SUBSCRIPTION MANAGEMENT ===
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [AdminSubscriptionController::class, 'index'])->name('index');
        Route::get('/create', [AdminSubscriptionController::class, 'create'])->name('create');
        Route::post('/', [AdminSubscriptionController::class, 'store'])->name('store');
        Route::get('/{subscription}', [AdminSubscriptionController::class, 'show'])->name('show');
        Route::get('/{subscription}/edit', [AdminSubscriptionController::class, 'edit'])->name('edit');
        Route::put('/{subscription}', [AdminSubscriptionController::class, 'update'])->name('update');
        Route::delete('/{subscription}', [AdminSubscriptionController::class, 'destroy'])->name('destroy');
        Route::post('/{subscription}/toggle-status', [AdminSubscriptionController::class, 'toggleStatus'])->name('toggle-status');
    });

    // === SYSTEM SETTINGS ===
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/settings', [AdminSystemController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminSystemController::class, 'updateSettings'])->name('settings.update');
        Route::get('/health', [AdminSystemController::class, 'health'])->name('health');
        Route::get('/info', [AdminSystemController::class, 'systemInfo'])->name('info');
        Route::post('/maintenance', [AdminSystemController::class, 'toggleMaintenance'])->name('maintenance');
        Route::post('/cache/clear', [AdminSystemController::class, 'clearCache'])->name('cache.clear');
    });

    // === ACTIVITY LOGS ===
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
