<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

    public function create()
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        return view('admin.subscriptions.create', compact('currentUser'));
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:schools,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'subscription_status' => 'required|in:trial,active,suspended,expired',
            'subscription_expires_at' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        School::create($request->only([
            'name', 'email', 'phone', 'address', 'subscription_status', 'subscription_expires_at'
        ]));

        return redirect()->route('admin.subscriptions.index')
                        ->with('success', 'School subscription created successfully.');
    }

    public function show(School $subscription)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        // Load relationships
        $subscription->load(['users', 'fleet', 'courses']);

        // Get subscription stats
        $stats = [
            'total_users' => $subscription->users()->count(),
            'students' => $subscription->students()->count(),
            'instructors' => $subscription->instructors()->count(),
            'admins' => $subscription->admins()->count(),
            'vehicles' => $subscription->fleet()->count(),
            'courses' => $subscription->courses()->count(),
            'monthly_revenue' => $subscription->getMonthlyRevenue(),
            'total_revenue' => $subscription->getTotalRevenue(),
        ];

        return view('admin.subscriptions.show', compact('subscription', 'currentUser', 'stats'));
    }

    public function edit(School $subscription)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        return view('admin.subscriptions.edit', compact('subscription', 'currentUser'));
    }

    public function update(Request $request, School $subscription)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:schools,email,' . $subscription->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'subscription_status' => 'required|in:trial,active,suspended,expired',
            'subscription_expires_at' => 'nullable|date|after:today',
            'status' => 'nullable|in:active,inactive,pending',
            'settings' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        try {
            // Prepare update data
            $updateData = $request->only([
                'name', 'email', 'phone', 'address', 'subscription_status', 'subscription_expires_at', 'status'
            ]);

            // Handle settings JSON
            if ($request->filled('settings')) {
                try {
                    $updateData['settings'] = json_decode($request->settings, true);
                } catch (\Exception $e) {
                    return redirect()->back()
                                   ->withErrors(['settings' => 'Invalid JSON format in settings field.'])
                                   ->withInput();
                }
            } else {
                $updateData['settings'] = null;
            }

            // Update the subscription
            $subscription->update($updateData);

            return redirect()->route('admin.subscriptions.show', $subscription)
                            ->with('success', 'Subscription updated successfully.');

        } catch (\Exception $e) {
            \Log::error('Subscription update failed: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'Failed to update subscription. Please try again.')
                           ->withInput();
        }
    }

    public function destroy(School $subscription)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        // Check if school has users
        if ($subscription->users()->count() > 0) {
            return back()->with('error', 'Cannot delete school with existing users. Please transfer or remove users first.');
        }

        $schoolName = $subscription->name;
        $subscription->delete();

        return redirect()->route('admin.subscriptions.index')
                        ->with('success', "School '{$schoolName}' deleted successfully.");
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