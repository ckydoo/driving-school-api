<?php
// routes/api.php (Updated with School Scoping)

use App\Http\Controllers\Api\{
    AuthController,
    SchoolController,
    UserController,
    CourseController,
    FleetController,
    ScheduleController,
    InvoiceController,
    PaymentController,
    SyncController,
    ProductionSyncController
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// === PUBLIC ROUTES (No authentication required) ===

// School Management - Public endpoints
Route::prefix('schools')->group(function () {
    Route::post('/register', [SchoolController::class, 'register']);
    Route::post('/find', [SchoolController::class, 'find']);
    Route::post('/authenticate', [SchoolController::class, 'authenticateUser']);
});

// Traditional Authentication routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

Route::middleware(['auth:sanctum', 'school.member'])->group(function () {
    
    // Production sync endpoints
    Route::prefix('sync')->group(function () {
        Route::get('/school-state', [ProductionSyncController::class, 'getSchoolSyncState']);
        Route::post('/register-device', [ProductionSyncController::class, 'registerDevice']);
        Route::get('/download-all', [ProductionSyncController::class, 'downloadAllSchoolData']);
        Route::get('/download-incremental', [ProductionSyncController::class, 'downloadIncrementalChanges']);
        Route::get('/download-table', [ProductionSyncController::class, 'downloadTableData']);
        Route::post('/upload-changes', [ProductionSyncController::class, 'uploadChanges']);
        Route::get('/status', [ProductionSyncController::class, 'getSyncStatus']);
        
        // Legacy endpoints (keep for backward compatibility)
        Route::get('/download', [SyncController::class, 'download']);
        Route::post('/upload', [SyncController::class, 'upload']);
    });
});
Route::middleware('auth:sanctum')->prefix('subscription')->group(function () {
    Route::get('/packages', [SubscriptionController::class, 'getPackages']);
    Route::get('/status', [SubscriptionController::class, 'getSubscriptionStatus']);
    Route::post('/create-payment-intent', [SubscriptionController::class, 'createPaymentIntent']);
    Route::post('/confirm-payment', [SubscriptionController::class, 'handleSuccessfulPayment']);
});
// Health check (no auth required)
Route::get('/health', [ProductionSyncController::class, 'health']);

// === PROTECTED ROUTES (Authentication required) ===
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    // === SUPER ADMIN ONLY API ROUTES ===
    Route::middleware('super_admin')->group(function () {
        // System-wide statistics
        Route::get('/admin/system/stats', function () {
            return response()->json([
                'total_schools' => \App\Models\School::count(),
                'active_schools' => \App\Models\School::where('status', 'active')->count(),
                'trial_schools' => \App\Models\School::where('subscription_status', 'trial')->count(),
                'paid_schools' => \App\Models\School::where('subscription_status', 'active')->count(),
                'total_users' => \App\Models\User::count(),
                'super_admins' => \App\Models\User::superAdmins()->count(),
                'school_admins' => \App\Models\User::schoolAdmins()->count(),
                'instructors' => \App\Models\User::instructors()->count(),
                'students' => \App\Models\User::students()->count(),
            ]);
        });

        // All schools management
        Route::apiResource('admin/schools', SchoolController::class);
        
        // System-wide user management
        Route::get('/admin/users/all', [UserController::class, 'allUsers']);
        Route::get('/admin/users/super-admins', [UserController::class, 'superAdmins']);
    });

    // === ADMIN ROUTES (Both Super Admin and School Admin) ===
    Route::middleware(['admin', 'school_scope'])->group(function () {
        
        // School Management - Protected endpoints
        Route::prefix('schools')->group(function () {
            Route::get('/dashboard', [SchoolController::class, 'dashboard']);
            Route::put('/settings', [SchoolController::class, 'update']);
        });

        // Users (with automatic school isolation for school admins)
        Route::apiResource('users', UserController::class);
        Route::get('/users/role/students', [UserController::class, 'students']);
        Route::get('/users/role/instructors', [UserController::class, 'instructors']);
        Route::get('/users/role/admins', [UserController::class, 'admins']);

        // Courses (scoped to school)
        Route::apiResource('courses', CourseController::class);

        // Fleet (scoped to school)
        Route::apiResource('fleet', FleetController::class);
        Route::get('/fleet/status/available', [FleetController::class, 'available']);

        // Schedules (scoped to school)
        Route::apiResource('schedules', ScheduleController::class);
        Route::post('/schedules/{id}/attend', [ScheduleController::class, 'markAttended']);

        // Invoices (scoped to school)
        Route::apiResource('invoices', InvoiceController::class);
        Route::get('/invoices/student/{studentId}', [InvoiceController::class, 'studentInvoices']);

        // Payments (scoped to school)
        Route::apiResource('payments', PaymentController::class);

        // Sync endpoints (scoped to school)
        Route::post('/sync/upload', [SyncController::class, 'upload']);
        Route::get('/sync/download', [SyncController::class, 'download']);
        Route::get('/sync/status', [SyncController::class, 'status']);
    });

    // === INSTRUCTOR ROUTES ===
    Route::middleware('instructor')->group(function () {
        // Instructor-specific endpoints
        Route::get('/instructor/schedules', [ScheduleController::class, 'instructorSchedules']);
        Route::get('/instructor/students', [UserController::class, 'instructorStudents']);
        Route::get('/instructor/fleet', [FleetController::class, 'instructorVehicles']);
    });

    // === STUDENT ROUTES ===
    Route::middleware('student')->group(function () {
        // Student-specific endpoints
        Route::get('/student/schedules', [ScheduleController::class, 'studentSchedules']);
        Route::get('/student/invoices', [InvoiceController::class, 'studentInvoices']);
        Route::get('/student/payments', [PaymentController::class, 'studentPayments']);
    });
});