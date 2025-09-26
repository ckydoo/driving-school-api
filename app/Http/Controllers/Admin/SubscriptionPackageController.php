<?php
// app/Http/Controllers/Admin/SubscriptionPackageController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPackage;
use Illuminate\Http\Request;

class SubscriptionPackageController extends Controller
{
    public function index()
    {
        $packages = SubscriptionPackage::ordered()->get();
        return view('admin.subscription-packages.index', compact('packages'));
    }

    public function create()
    {
        return view('admin.subscription-packages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'monthly_price' => 'required|numeric|min:0',
            'yearly_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'features' => 'required|array',
            'features.*' => 'string',
            'max_students' => 'required|integer|min:-1',
            'max_instructors' => 'required|integer|min:-1',
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
            ->with('success', 'Package created successfully');
    }

    // Update and delete methods...
}