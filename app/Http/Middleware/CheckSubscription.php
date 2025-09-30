<?php
// app/Http/Middleware/CheckSubscription.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if the school's subscription is active
     * and blocks access if suspended or expired.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Allow if no user (handled by auth middleware)
        if (!$user) {
            return $next($request);
        }

        // Super admins bypass subscription checks
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $school = $user->school;

        // Allow if no school (shouldn't happen, but be safe)
        if (!$school) {
            return response()->json([
                'success' => false,
                'message' => 'No school associated with this account',
                'error_code' => 'NO_SCHOOL'
            ], 403);
        }

        // Check subscription status
        $status = $school->subscription_status;

        Log::info('Subscription Check', [
            'user_id' => $user->id,
            'school_id' => $school->id,
            'school_name' => $school->name,
            'subscription_status' => $status,
            'expires_at' => $school->subscription_expires_at,
            'endpoint' => $request->path()
        ]);

        // Block if subscription is suspended
        if ($status === 'suspended') {
            return response()->json([
                'success' => false,
                'message' => 'Your subscription has been suspended. Please contact support or update your payment method.',
                'error_code' => 'SUBSCRIPTION_SUSPENDED',
                'data' => [
                    'subscription_status' => 'suspended',
                    'school_name' => $school->name,
                    'support_email' => config('mail.support_address', 'support@drivesync.com'),
                    'can_view_subscription' => true
                ]
            ], 403);
        }

        // Block if subscription is expired
        if ($status === 'expired') {
            return response()->json([
                'success' => false,
                'message' => 'Your subscription has expired. Please renew to continue using the service.',
                'error_code' => 'SUBSCRIPTION_EXPIRED',
                'data' => [
                    'subscription_status' => 'expired',
                    'expired_at' => $school->subscription_expires_at,
                    'school_name' => $school->name,
                    'can_renew' => true,
                    'can_view_subscription' => true
                ]
            ], 403);
        }

        // Check if trial has ended
        if ($status === 'trial' && $school->trial_ends_at && $school->trial_ends_at->isPast()) {
            // Update status to expired
            $school->update(['subscription_status' => 'expired']);

            return response()->json([
                'success' => false,
                'message' => 'Your free trial has ended. Please subscribe to continue using the service.',
                'error_code' => 'TRIAL_EXPIRED',
                'data' => [
                    'subscription_status' => 'expired',
                    'trial_ended_at' => $school->trial_ends_at,
                    'school_name' => $school->name,
                    'can_subscribe' => true,
                    'can_view_subscription' => true
                ]
            ], 403);
        }

        // Check if active subscription has expired
        if ($status === 'active' && $school->subscription_expires_at && $school->subscription_expires_at->isPast()) {
            // Update status to expired
            $school->update(['subscription_status' => 'expired']);

            return response()->json([
                'success' => false,
                'message' => 'Your subscription has expired. Please renew to continue using the service.',
                'error_code' => 'SUBSCRIPTION_EXPIRED',
                'data' => [
                    'subscription_status' => 'expired',
                    'expired_at' => $school->subscription_expires_at,
                    'school_name' => $school->name,
                    'can_renew' => true,
                    'can_view_subscription' => true
                ]
            ], 403);
        }

        // Allow if status is 'active' or 'trial' (and not expired)
        if (in_array($status, ['active', 'trial'])) {
            return $next($request);
        }

        // Block any other status
        return response()->json([
            'success' => false,
            'message' => 'Invalid subscription status. Please contact support.',
            'error_code' => 'INVALID_SUBSCRIPTION_STATUS',
            'data' => [
                'subscription_status' => $status,
                'school_name' => $school->name,
                'support_email' => config('mail.support_address', 'support@drivesync.com')
            ]
        ], 403);
    }
}
