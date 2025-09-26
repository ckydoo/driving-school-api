<?php
// app/Http/Controllers/Admin/AdminSubscriptionPackageController.php - FIXED VERSION

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminSubscriptionPackageController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $packages = SubscriptionPackage::ordered()->get();
        
        return view('admin.subscription-packages.index', compact('packages', 'currentUser'));
    }

    public function create()
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        return view('admin.subscription-packages.create', compact('currentUser'));
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'monthly_price' => 'required|numeric|min:0',
            'yearly_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'features' => 'required|array|min:1',
            'features.*' => 'string|max:255',
            'max_students' => 'required|integer|min:-1',
            'max_instructors' => 'required|integer|min:-1',
            'max_vehicles' => 'nullable|integer|min:-1',
            'trial_days' => 'required|integer|min:0'
        ]);

        SubscriptionPackage::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'monthly_price' => $request->monthly_price,
            'yearly_price' => $request->yearly_price,
            'description' => $request->description,
            'features' => $request->features,
            'limits' => [
                'max_students' => $request->max_students,
                'max_instructors' => $request->max_instructors,
                'max_vehicles' => $request->max_vehicles ?? -1
            ],
            'trial_days' => $request->trial_days,
            'is_popular' => $request->boolean('is_popular'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => SubscriptionPackage::max('sort_order') + 1
        ]);

        return redirect()->route('admin.subscription-packages.index')
                        ->with('success', 'Subscription package created successfully');
    }

    // FIXED: Parameter name should match what's defined in routes
    public function show(SubscriptionPackage $package)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $package->load(['schools']);
        
        $stats = [
            'total_schools' => $package->schools()->count(),
            'active_schools' => $package->schools()->where('subscription_status', 'active')->count(),
            'trial_schools' => $package->schools()->where('subscription_status', 'trial')->count(),
            'monthly_revenue' => $package->schools()
                ->where('subscription_status', 'active')
                ->sum('monthly_fee') ?? 0,
        ];

        return view('admin.subscription-packages.show', compact('package', 'currentUser', 'stats'));
    }

    // FIXED: Parameter name should match what's defined in routes
    public function edit(SubscriptionPackage $package)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        return view('admin.subscription-packages.edit', compact('package', 'currentUser'));
    }

    // FIXED: Parameter name should match what's defined in routes
    public function update(Request $request, SubscriptionPackage $package)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'monthly_price' => 'required|numeric|min:0',
            'yearly_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'features' => 'required|array|min:1',
            'features.*' => 'string|max:255',
            'max_students' => 'required|integer|min:-1',
            'max_instructors' => 'required|integer|min:-1',
            'max_vehicles' => 'nullable|integer|min:-1',
            'trial_days' => 'required|integer|min:0'
        ]);

        $package->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'monthly_price' => $request->monthly_price,
            'yearly_price' => $request->yearly_price,
            'description' => $request->description,
            'features' => $request->features,
            'limits' => [
                'max_students' => $request->max_students,
                'max_instructors' => $request->max_instructors,
                'max_vehicles' => $request->max_vehicles ?? -1
            ],
            'trial_days' => $request->trial_days,
            'is_popular' => $request->boolean('is_popular'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.subscription-packages.show', $package)
                        ->with('success', 'Package updated successfully');
    }

    // FIXED: Parameter name should match what's defined in routes
    public function destroy(SubscriptionPackage $package)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        // Check if package has active subscriptions
        if ($package->schools()->count() > 0) {
            return back()->with('error', 'Cannot delete package with existing subscriptions');
        }

        $packageName = $package->name;
        $package->delete();

        return redirect()->route('admin.subscription-packages.index')
                        ->with('success', "Package '{$packageName}' deleted successfully");
    }

    // FIXED: Parameter name should match what's defined in routes
    public function toggleStatus(SubscriptionPackage $package)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $package->update(['is_active' => !$package->is_active]);

        $status = $package->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Package {$status} successfully");
    }
}