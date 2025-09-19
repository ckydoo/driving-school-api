<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLogController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();

        // Placeholder for activity logs
        $logs = collect([
            (object) ['id' => 1, 'action' => 'User Login', 'user' => 'Super Admin', 'timestamp' => now()],
            (object) ['id' => 2, 'action' => 'School Created', 'user' => 'Super Admin', 'timestamp' => now()->subHour()],
        ]);

        return view('admin.logs.index', compact('currentUser', 'logs'));
    }

    public function show($id)
    {
        $currentUser = Auth::user();
        $log = (object) ['id' => $id, 'action' => 'Sample Log', 'details' => 'Log details here'];

        return view('admin.logs.show', compact('currentUser', 'log'));
    }

    public function clear()
    {
        // Implement log clearing logic
        return back()->with('success', 'Activity logs cleared successfully!');
    }
}
