<?php
// bootstrap/app.php (Updated with new middleware)

use Illuminate\Foundation\Application;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Middleware\SchoolAdminMiddleware;
use App\Http\Middleware\SchoolScopeMiddleware;
use App\Http\Middleware\FlexibleAdminMiddleware;
use App\Http\Middleware\SuperAdminOnlyMiddleware;
use App\Http\Middleware\SchoolAdminOnlyMiddleware;
use App\Http\Middleware\SchoolMemberMiddleware;
use App\Http\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => EnsureEmailIsVerified::class,
            'admin' => AdminMiddleware::class,
            'super_admin' => SuperAdminMiddleware::class,
            'school_admin' => SchoolAdminMiddleware::class,
            'school_scope' => SchoolScopeMiddleware::class,
            'flexible_admin' => FlexibleAdminMiddleware::class,
            'super_admin_only' => SuperAdminOnlyMiddleware::class, // NEW
            'school_admin_only' => SchoolAdminOnlyMiddleware::class, // NEW
            'instructor' => \App\Http\Middleware\InstructorMiddleware::class,
            'student' => \App\Http\Middleware\StudentMiddleware::class,
            'school.member' => \App\Http\Middleware\SchoolMemberMiddleware::class,
            'check.subscription' => \App\Http\Middleware\CheckSubscription::class,
            

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
