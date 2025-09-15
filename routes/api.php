<?php
// routes/api.php

use App\Http\Controllers\Api\{
    AuthController,
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

// Authentication routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    // Users
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

// Public health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});
