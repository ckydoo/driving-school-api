<?php
// app/Http/Controllers/Admin/AdminSubscriptionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $query = School::with(['users']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by subscription status
        if ($request->filled('subscription_status')) {
            $query->where('subscription_status', $request->subscription_status);
        }

        $schools = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistics
        $stats = [
            'total_schools' => School::count(),
            'trial_subscriptions' => School::where('subscription_status', 'trial')->count(),
            'active_subscriptions' => School::where('subscription_status', 'active')->count(),
            'suspended_subscriptions' => School::where('subscription_status', 'suspended')->count(),
            'expired_subscriptions' => School::where('subscription_status', 'expired')->count(),
            'monthly_revenue' => 0, // Calculate based on your pricing model
        ];

        return view('admin.subscriptions.index', compact('schools', 'stats', 'currentUser'));
    }

    public function show(School $subscription)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        // Load relationships
        $subscription->load(['users']);

        return view('admin.subscriptions.show', compact('subscription', 'currentUser'));
    }

    public function toggleStatus(School $subscription)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $newStatus = match($subscription->subscription_status) {
            'trial' => 'active',
            'active' => 'suspended',
            'suspended' => 'active',
            'expired' => 'trial',
            default => 'trial'
        };

        $subscription->update(['subscription_status' => $newStatus]);

        return back()->with('success', "Subscription status updated to {$newStatus}!");
    }
}
