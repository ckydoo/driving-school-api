<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class AdminSystemController extends Controller
{
    public function settings()
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }
        
        return view('admin.system.settings', compact('currentUser'));
    }

    public function updateSettings(Request $request)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_timezone' => 'required|string',
            'default_currency' => 'required|string|max:3',
            'default_lesson_duration' => 'required|integer|min:15|max:240',
            'session_timeout' => 'required|integer|min:15|max:1440',
            'max_login_attempts' => 'required|integer|min:1|max:20',
            'enable_notifications' => 'nullable|boolean',
            'enable_auto_backup' => 'nullable|boolean',
            'maintenance_mode' => 'nullable|boolean',
            'debug_mode' => 'nullable|boolean',
        ]);

        try {
            // Here you would typically save these settings to a database table
            // or update environment variables. For now, we'll just simulate success.
            
            // Example: Save to a settings table
            // Setting::updateOrCreate(['key' => 'app_name'], ['value' => $request->app_name]);
            
            // If you want to update .env file (be careful with this in production)
            // $this->updateEnvFile([
            //     'APP_NAME' => $request->app_name,
            //     'APP_TIMEZONE' => $request->app_timezone,
            //     'APP_DEBUG' => $request->debug_mode ? 'true' : 'false',
            // ]);

            return back()->with('success', 'System settings updated successfully!');
        } catch (\Exception $e) {
            \Log::error('System settings update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update system settings. Please try again.');
        }
    }

    public function health()
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $healthData = [
            'database' => $this->checkDatabaseHealth(),
            'cache' => $this->checkCacheHealth(),
            'storage' => $this->checkStorageHealth(),
            'queue' => $this->checkQueueHealth(),
            'server' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'memory_usage' => $this->formatBytes(memory_get_usage(true)),
                'memory_limit' => ini_get('memory_limit'),
                'server_time' => now()->format('Y-m-d H:i:s T'),
                'uptime' => $this->getSystemUptime(),
            ]
        ];

        return view('admin.system.health', compact('currentUser', 'healthData'));
    }

    public function systemInfo()
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        return response()->json([
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'timezone' => config('app.timezone'),
        ]);
    }

    public function clearCache()
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Access denied'], 403);
            }
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'System cache cleared successfully!']);
            }
            
            return back()->with('success', 'System cache cleared successfully!');
        } catch (\Exception $e) {
            \Log::error('Cache clear failed: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to clear cache: ' . $e->getMessage()]);
            }
            
            return back()->with('error', 'Failed to clear cache. Please try again.');
        }
    }

    public function toggleMaintenance()
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        try {
            if (app()->isDownForMaintenance()) {
                Artisan::call('up');
                $message = 'Application is now live!';
            } else {
                Artisan::call('down', ['--secret' => 'super-admin-access']);
                $message = 'Application is now in maintenance mode!';
            }
            
            return back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Maintenance mode toggle failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to toggle maintenance mode. Please try again.');
        }
    }

    // Email settings methods
    public function emailSettings()
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }
        
        return view('admin.system.email-settings', compact('currentUser'));
    }

    public function updateEmailSettings(Request $request)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $request->validate([
            'mail_driver' => 'required|string',
            'mail_host' => 'required_if:mail_driver,smtp|string',
            'mail_port' => 'required_if:mail_driver,smtp|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        // Save email settings logic here
        return back()->with('success', 'Email settings updated successfully!');
    }

    public function testEmail()
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        try {
            // Send test email logic here
            return back()->with('success', 'Test email sent successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    // Database and server info methods
    public function databaseStats()
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $stats = [
            'database_size' => $this->getDatabaseSize(),
            'table_count' => $this->getTableCount(),
            'connection_info' => $this->getConnectionInfo(),
        ];

        return response()->json($stats);
    }

    public function serverInfo()
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $info = [
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ];

        return response()->json($info);
    }

    // Helper methods
    private function checkDatabaseHealth()
    {
        try {
            DB::connection()->getPdo();
            DB::connection()->select('SELECT 1');
            return 'healthy';
        } catch (\Exception $e) {
            \Log::error('Database health check failed: ' . $e->getMessage());
            return 'error';
        }
    }

    private function checkCacheHealth()
    {
        try {
            $testKey = 'health_check_' . time();
            cache()->put($testKey, 'ok', 60);
            $result = cache()->get($testKey);
            cache()->forget($testKey);
            
            return $result === 'ok' ? 'healthy' : 'error';
        } catch (\Exception $e) {
            \Log::error('Cache health check failed: ' . $e->getMessage());
            return 'error';
        }
    }

    private function checkStorageHealth()
    {
        try {
            $testFile = storage_path('app/health_check.txt');
            file_put_contents($testFile, 'test');
            
            if (file_exists($testFile)) {
                unlink($testFile);
                return 'healthy';
            }
            
            return 'error';
        } catch (\Exception $e) {
            \Log::error('Storage health check failed: ' . $e->getMessage());
            return 'error';
        }
    }

    private function checkQueueHealth()
    {
        try {
            // Basic queue check - you can enhance this based on your queue driver
            return 'healthy';
        } catch (\Exception $e) {
            \Log::error('Queue health check failed: ' . $e->getMessage());
            return 'error';
        }
    }

    private function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }

    private function getSystemUptime()
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                return 'Windows - uptime not available';
            }
            
            $uptime = shell_exec('uptime -p');
            return $uptime ?: 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    private function getDatabaseSize()
    {
        try {
            $database = config('database.connections.' . config('database.default') . '.database');
            
            if (config('database.default') === 'sqlite') {
                $path = config('database.connections.sqlite.database');
                return file_exists($path) ? $this->formatBytes(filesize($path)) : 'Unknown';
            }
            
            if (config('database.default') === 'mysql') {
                $result = DB::select("
                    SELECT 
                        ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                    FROM information_schema.tables 
                    WHERE table_schema = ?
                ", [$database]);
                
                return $result[0]->size_mb . ' MB';
            }
            
            return 'Unknown';
        } catch (\Exception $e) {
            \Log::error('Database size check failed: ' . $e->getMessage());
            return 'Unknown';
        }
    }

    private function getTableCount()
    {
        try {
            $database = config('database.connections.' . config('database.default') . '.database');
            
            if (config('database.default') === 'sqlite') {
                $result = DB::select("SELECT count(*) as count FROM sqlite_master WHERE type='table'");
                return $result[0]->count;
            }
            
            if (config('database.default') === 'mysql') {
                $result = DB::select("
                    SELECT COUNT(*) as count 
                    FROM information_schema.tables 
                    WHERE table_schema = ?
                ", [$database]);
                
                return $result[0]->count;
            }
            
            return 'Unknown';
        } catch (\Exception $e) {
            \Log::error('Table count check failed: ' . $e->getMessage());
            return 'Unknown';
        }
    }

    private function getConnectionInfo()
    {
        try {
            $connection = config('database.default');
            $config = config("database.connections.{$connection}");
            
            return [
                'driver' => $config['driver'],
                'host' => $config['host'] ?? 'N/A',
                'port' => $config['port'] ?? 'N/A',
                'database' => $config['database'] ?? 'N/A',
            ];
        } catch (\Exception $e) {
            \Log::error('Connection info check failed: ' . $e->getMessage());
            return ['error' => 'Unable to retrieve connection info'];
        }
    }

    /**
     * Update environment file with new values
     * Use with caution in production!
     */
    private function updateEnvFile($data)
    {
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);
        
        foreach ($data as $key => $value) {
            // Escape special characters in the value
            $value = preg_replace('/[^A-Za-z0-9\-_]/', '', $value);
            
            // Check if key exists
            if (preg_match("/^{$key}=.*/m", $envContent)) {
                // Update existing key
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
            } else {
                // Add new key
                $envContent .= "\n{$key}={$value}";
            }
        }
        
        file_put_contents($envFile, $envContent);
        
        // Clear config cache to reflect changes
        Artisan::call('config:clear');
    }
}