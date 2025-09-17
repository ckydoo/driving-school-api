<?php

use App\Http\Controllers\Api\{
    AuthController,
    SchoolController, // Add this
    UserController,
    CourseController,
    FleetController,
    ScheduleController,
    InvoiceController,
    PaymentController,
    SyncController
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

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0',
        'environment' => app()->environment(),
    ]);
});

// === PROTECTED ROUTES (Authentication required) ===
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    // School Management - Protected endpoints
    Route::prefix('schools')->group(function () {
        Route::get('/dashboard', [SchoolController::class, 'dashboard']);
        Route::put('/settings', [SchoolController::class, 'update']);
    });

    // Users (with school isolation)
    Route::apiResource('users', UserController::class);
    Route::get('/users/role/students', [UserController::class, 'students']);
    Route::get('/users/role/instructors', [UserController::class, 'instructors']);

    // Courses
    Route::apiResource('courses', CourseController::class);

    // Fleet
    Route::apiResource('fleet', FleetController::class);
    Route::get('/fleet/status/available', [FleetController::class, 'available']);

    // Schedules
    Route::apiResource('schedules', ScheduleController::class);
    Route::post('/schedules/{id}/attend', [ScheduleController::class, 'markAttended']);

    // Invoices
    Route::apiResource('invoices', InvoiceController::class);
    Route::get('/invoices/student/{studentId}', [InvoiceController::class, 'studentInvoices']);

    // Payments
    Route::apiResource('payments', PaymentController::class);

    // Sync endpoints
    Route::post('/sync/upload', [SyncController::class, 'upload']);
    Route::get('/sync/download', [SyncController::class, 'download']);
    Route::get('/sync/status', [SyncController::class, 'status']);
});

// === ADMIN ONLY ROUTES ===
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Admin-only endpoints can go here
    Route::get('/admin/schools/stats', function () {
        return response()->json([
            'total_schools' => \App\Models\School::count(),
            'active_schools' => \App\Models\School::active()->count(),
            'trial_schools' => \App\Models\School::where('subscription_status', 'trial')->count(),
            'paid_schools' => \App\Models\School::where('subscription_status', 'active')->count(),
        ]);
    });
});