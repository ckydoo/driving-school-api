<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminLogController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        // Since you don't have a real logs table yet, let's create mock data
        // In a real implementation, you would fetch from your activity_logs table
        $logs = collect([
            (object) [
                'id' => 1,
                'action' => 'User Login',
                'user' => 'Super Admin',
                'timestamp' => now(),
                'details' => 'Successful login from admin dashboard',
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'session_id' => 'sess_' . uniqid(),
                'method' => 'POST',
                'url' => '/admin/login'
            ],
            (object) [
                'id' => 2,
                'action' => 'School Created',
                'user' => 'Super Admin',
                'timestamp' => now()->subHour(),
                'details' => 'New school "ABC Driving School" created with ID 15',
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'session_id' => 'sess_' . uniqid(),
                'method' => 'POST',
                'url' => '/admin/schools'
            ],
            (object) [
                'id' => 3,
                'action' => 'System Settings Updated',
                'user' => 'Super Admin',
                'timestamp' => now()->subHours(2),
                'details' => 'Application settings modified: timezone changed to UTC',
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'session_id' => 'sess_' . uniqid(),
                'method' => 'POST',
                'url' => '/admin/system/settings'
            ],
            (object) [
                'id' => 4,
                'action' => 'User Created',
                'user' => 'School Admin',
                'timestamp' => now()->subHours(3),
                'details' => 'New instructor "John Doe" added to system',
                'ip_address' => '192.168.1.150',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'session_id' => 'sess_' . uniqid(),
                'method' => 'POST',
                'url' => '/admin/users'
            ],
            (object) [
                'id' => 5,
                'action' => 'Cache Cleared',
                'user' => 'Super Admin',
                'timestamp' => now()->subHours(4),
                'details' => 'System cache cleared manually from admin panel',
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'session_id' => 'sess_' . uniqid(),
                'method' => 'POST',
                'url' => '/admin/system/cache/clear'
            ],
            (object) [
                'id' => 6,
                'action' => 'Payment Recorded',
                'user' => 'School Admin',
                'timestamp' => now()->subDay(),
                'details' => 'Payment of $150 recorded for student Sarah Wilson',
                'ip_address' => '192.168.1.151',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15',
                'session_id' => 'sess_' . uniqid(),
                'method' => 'POST',
                'url' => '/admin/payments'
            ],
            (object) [
                'id' => 7,
                'action' => 'Schedule Created',
                'user' => 'Instructor',
                'timestamp' => now()->subDay()->subHours(2),
                'details' => 'New lesson scheduled for student Mike Johnson on 2025-09-21',
                'ip_address' => '192.168.1.152',
                'user_agent' => 'Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/88.0',
                'session_id' => 'sess_' . uniqid(),
                'method' => 'POST',
                'url' => '/admin/schedules'
            ],
            (object) [
                'id' => 8,
                'action' => 'User Logout',
                'user' => 'School Admin',
                'timestamp' => now()->subDays(2),
                'details' => 'User logged out successfully',
                'ip_address' => '192.168.1.150',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'session_id' => 'sess_' . uniqid(),
                'method' => 'POST',
                'url' => '/admin/logout'
            ]
        ]);

        // Apply filters
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $logs = $logs->filter(function($log) use ($search) {
                return str_contains(strtolower($log->action), $search) ||
                       str_contains(strtolower($log->user), $search) ||
                       str_contains(strtolower($log->details), $search) ||
                       str_contains($log->ip_address, $search);
            });
        }

        if ($request->filled('action_type')) {
            $actionType = $request->action_type;
            $logs = $logs->filter(function($log) use ($actionType) {
                return str_contains(strtolower($log->action), $actionType);
            });
        }

        if ($request->filled('date_from')) {
            $dateFrom = Carbon::parse($request->date_from)->startOfDay();
            $logs = $logs->filter(function($log) use ($dateFrom) {
                return $log->timestamp >= $dateFrom;
            });
        }

        if ($request->filled('date_to')) {
            $dateTo = Carbon::parse($request->date_to)->endOfDay();
            $logs = $logs->filter(function($log) use ($dateTo) {
                return $log->timestamp <= $dateTo;
            });
        }

        return view('admin.logs.index', compact('logs', 'currentUser'));
    }

    public function show($id)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        // Mock single log data - in real implementation, fetch from database
        $log = (object) [
            'id' => $id,
            'action' => 'System Settings Updated',
            'user' => 'Super Admin',
            'timestamp' => now()->subHours(2),
            'details' => 'Application settings modified: timezone changed to UTC, notification settings updated, cache driver changed to Redis',
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'session_id' => 'sess_' . uniqid(),
            'method' => 'POST',
            'url' => '/admin/system/settings',
            'metadata' => [
                'previous_values' => [
                    'timezone' => 'America/New_York',
                    'cache_driver' => 'file'
                ],
                'new_values' => [
                    'timezone' => 'UTC',
                    'cache_driver' => 'redis'
                ]
            ]
        ];

        return view('admin.logs.show', compact('log', 'currentUser'));
    }

    public function destroy($id)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        // In real implementation:
        // $log = ActivityLog::findOrFail($id);
        // $log->delete();

        return redirect()->route('admin.logs.index')
                        ->with('success', 'Log entry deleted successfully.');
    }

    public function clear()
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Access denied'], 403);
            }
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        try {
            // In real implementation:
            // ActivityLog::truncate();
            
            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'All logs cleared successfully!']);
            }
            
            return back()->with('success', 'All activity logs cleared successfully!');
        } catch (\Exception $e) {
            \Log::error('Log clearing failed: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to clear logs: ' . $e->getMessage()]);
            }
            
            return back()->with('error', 'Failed to clear logs. Please try again.');
        }
    }

    public function export($type)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        // Mock data for export - in real implementation, fetch all logs
        $logs = collect([
            [
                'id' => 1,
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'user' => 'Super Admin',
                'action' => 'User Login',
                'details' => 'Successful login from admin dashboard',
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ],
            [
                'id' => 2,
                'timestamp' => now()->subHour()->format('Y-m-d H:i:s'),
                'user' => 'Super Admin',
                'action' => 'School Created',
                'details' => 'New school "ABC Driving School" created',
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);

        $filename = 'activity-logs-' . now()->format('Y-m-d-H-i-s');

        switch ($type) {
            case 'csv':
                return $this->exportCsv($logs, $filename);
            case 'json':
                return $this->exportJson($logs, $filename);
            case 'pdf':
                return $this->exportPdf($logs, $filename);
            default:
                return back()->with('error', 'Invalid export format.');
        }
    }

    private function exportCsv($logs, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, ['ID', 'Timestamp', 'User', 'Action', 'Details', 'IP Address', 'User Agent']);
            
            // Add data rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log['id'],
                    $log['timestamp'],
                    $log['user'],
                    $log['action'],
                    $log['details'],
                    $log['ip_address'],
                    $log['user_agent']
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportJson($logs, $filename)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}.json\"",
        ];

        return response()->json([
            'export_date' => now()->toISOString(),
            'total_logs' => $logs->count(),
            'logs' => $logs->values()
        ])->withHeaders($headers);
    }

    private function exportPdf($logs, $filename)
    {
        // For now, return a simple response
        // In a real implementation, you'd use a PDF library like DomPDF or TCPDF
        return response()->json([
            'message' => 'PDF export functionality would be implemented here using a PDF library like DomPDF.',
            'logs_count' => $logs->count()
        ]);
    }
}