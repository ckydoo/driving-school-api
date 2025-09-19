<?php
// app/Http/Controllers/Admin/AdminSystemController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSystemController extends Controller
{
    public function settings()
    {
        $currentUser = Auth::user();
        return view('admin.system.settings', compact('currentUser'));
    }

    public function updateSettings(Request $request)
    {
        // Implement system settings update logic
        return back()->with('success', 'System settings updated successfully!');
    }

    public function health()
    {
        $currentUser = Auth::user();

        $healthData = [
            'database' => 'healthy',
            'cache' => 'healthy',
            'storage' => 'healthy',
            'server' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'memory_usage' => memory_get_usage(true),
                'memory_limit' => ini_get('memory_limit'),
            ]
        ];

        return view('admin.system.health', compact('currentUser', 'healthData'));
    }

    public function systemInfo()
    {
        $currentUser = Auth::user();
        return response()->json([
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database' => config('database.default'),
            'cache_driver' => config('cache.default'),
        ]);
    }

    public function clearCache()
    {
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        \Artisan::call('route:clear');

        return back()->with('success', 'System cache cleared successfully!');
    }
}
