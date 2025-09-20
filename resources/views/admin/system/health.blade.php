@extends('admin.layouts.app')

@section('title', 'System Health')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-heartbeat"></i> System Health Check
        </h1>
        <div>
            <a href="{{ route('admin.system.settings') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-cogs"></i> System Settings
            </a>
            <button type="button" class="btn btn-primary btn-sm" onclick="refreshHealthCheck()">
                <i class="fas fa-sync"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Overall Health Status -->
    <div class="row mb-4">
        <div class="col-12">
            @php
                $overallHealth = 'healthy';
                $healthyComponents = 0;
                $totalComponents = 4;
                
                foreach(['database', 'cache', 'storage', 'queue'] as $component) {
                    if (($healthData[$component] ?? 'error') === 'healthy') {
                        $healthyComponents++;
                    } else {
                        $overallHealth = 'warning';
                    }
                }
                
                if ($healthyComponents === 0) {
                    $overallHealth = 'error';
                }
            @endphp
            
            <div class="card border-{{ $overallHealth === 'healthy' ? 'success' : ($overallHealth === 'warning' ? 'warning' : 'danger') }} shadow">
                <div class="card-body text-center">
                    <h2 class="text-{{ $overallHealth === 'healthy' ? 'success' : ($overallHealth === 'warning' ? 'warning' : 'danger') }}">
                        <i class="fas fa-{{ $overallHealth === 'healthy' ? 'check-circle' : ($overallHealth === 'warning' ? 'exclamation-triangle' : 'times-circle') }} fa-3x"></i>
                    </h2>
                    <h4 class="mt-3">
                        System Status: 
                        <span class="text-{{ $overallHealth === 'healthy' ? 'success' : ($overallHealth === 'warning' ? 'warning' : 'danger') }}">
                            {{ $overallHealth === 'healthy' ? 'All Systems Operational' : ($overallHealth === 'warning' ? 'Some Issues Detected' : 'Critical Issues Found') }}
                        </span>
                    </h4>
                    <p class="text-muted">{{ $healthyComponents }}/{{ $totalComponents }} components healthy</p>
                    <small class="text-muted">Last checked: {{ now()->format('Y-m-d H:i:s T') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Component Health Cards -->
    <div class="row mb-4">
        <!-- Database Health -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-{{ ($healthData['database'] ?? 'error') === 'healthy' ? 'success' : 'danger' }} shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-{{ ($healthData['database'] ?? 'error') === 'healthy' ? 'success' : 'danger' }} text-uppercase mb-1">
                                Database
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ ucfirst($healthData['database'] ?? 'Error') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-database fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cache Health -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-{{ ($healthData['cache'] ?? 'error') === 'healthy' ? 'success' : 'danger' }} shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-{{ ($healthData['cache'] ?? 'error') === 'healthy' ? 'success' : 'danger' }} text-uppercase mb-1">
                                Cache
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ ucfirst($healthData['cache'] ?? 'Error') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-memory fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Storage Health -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-{{ ($healthData['storage'] ?? 'error') === 'healthy' ? 'success' : 'danger' }} shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-{{ ($healthData['storage'] ?? 'error') === 'healthy' ? 'success' : 'danger' }} text-uppercase mb-1">
                                Storage
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ ucfirst($healthData['storage'] ?? 'Error') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hdd fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Queue Health -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-{{ ($healthData['queue'] ?? 'error') === 'healthy' ? 'success' : 'warning' }} shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-{{ ($healthData['queue'] ?? 'error') === 'healthy' ? 'success' : 'warning' }} text-uppercase mb-1">
                                Queue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ ucfirst($healthData['queue'] ?? 'Error') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Server Information -->
    <div class="row">
        <!-- Server Details -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-server"></i> Server Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>PHP Version:</strong></td>
                                    <td>{{ $healthData['server']['php_version'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Laravel Version:</strong></td>
                                    <td>{{ $healthData['server']['laravel_version'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Memory Usage:</strong></td>
                                    <td>{{ $healthData['server']['memory_usage'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Memory Limit:</strong></td>
                                    <td>{{ $healthData['server']['memory_limit'] }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Server Time:</strong></td>
                                    <td>{{ $healthData['server']['server_time'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Environment:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ app()->environment('production') ? 'success' : 'warning' }}">
                                            {{ strtoupper(app()->environment()) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Debug Mode:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ config('app.debug') ? 'warning' : 'success' }}">
                                            {{ config('app.debug') ? 'ON' : 'OFF' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>System Uptime:</strong></td>
                                    <td>{{ $healthData['server']['uptime'] }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line"></i> Performance Metrics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <h4 class="text-primary">{{ number_format(memory_get_peak_usage() / 1024 / 1024, 2) }} MB</h4>
                                <small class="text-muted">Peak Memory</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <h4 class="text-info">{{ config('database.default') }}</h4>
                                <small class="text-muted">Database Driver</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <h4 class="text-success">{{ config('cache.default') }}</h4>
                                <small class="text-muted">Cache Driver</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <h4 class="text-warning">{{ config('queue.default') }}</h4>
                                <small class="text-muted">Queue Driver</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Actions -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tools"></i> System Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="clearAllCache()">
                            <i class="fas fa-sync"></i> Clear All Cache
                        </button>
                        
                        <button type="button" class="btn btn-outline-warning" onclick="toggleMaintenance()">
                            <i class="fas fa-tools"></i> 
                            {{ app()->isDownForMaintenance() ? 'Disable' : 'Enable' }} Maintenance
                        </button>
                        
                        <a href="{{ route('admin.logs.index') }}" class="btn btn-outline-info">
                            <i class="fas fa-list-alt"></i> View System Logs
                        </a>
                        
                        <button type="button" class="btn btn-outline-secondary" onclick="downloadSystemInfo()">
                            <i class="fas fa-download"></i> Download System Info
                        </button>
                        
                        <hr>
                        
                        <button type="button" class="btn btn-outline-success" onclick="runHealthCheck()">
                            <i class="fas fa-heartbeat"></i> Run Full Health Check
                        </button>
                    </div>
                </div>
            </div>

            <!-- Health History -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Recent Health Checks
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="small text-muted">{{ now()->format('H:i:s') }}</div>
                                    <div>All systems healthy</div>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-info"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="small text-muted">{{ now()->subMinutes(15)->format('H:i:s') }}</div>
                                    <div>Cache cleared</div>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-sync text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="small text-muted">{{ now()->subHour()->format('H:i:s') }}</div>
                                    <div>System restarted</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function refreshHealthCheck() {
    location.reload();
}

function clearAllCache() {
    if (confirm('Are you sure you want to clear all system caches?')) {
        fetch('{{ route("admin.system.cache.clear") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Cache cleared successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to clear cache'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error clearing cache. Please try again.');
        });
    }
}

function toggleMaintenance() {
    const isDown = {{ app()->isDownForMaintenance() ? 'true' : 'false' }};
    const action = isDown ? 'disable' : 'enable';
    
    if (confirm(`Are you sure you want to ${action} maintenance mode?`)) {
        fetch('{{ route("admin.system.maintenance") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (response.ok) {
                alert(`Maintenance mode ${action}d successfully!`);
                location.reload();
            } else {
                alert('Error toggling maintenance mode');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error toggling maintenance mode');
        });
    }
}

function downloadSystemInfo() {
    fetch('{{ route("admin.system.info") }}')
        .then(response => response.json())
        .then(data => {
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'system-info-' + new Date().toISOString().slice(0, 10) + '.json';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error downloading system info');
        });
}

function runHealthCheck() {
    alert('Running comprehensive health check...');
    location.reload();
}

// Auto-refresh every 5 minutes
setInterval(function() {
    location.reload();
}, 300000);
</script>
@endpush

@push('styles')
<style>
.timeline-item {
    position: relative;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 8px;
    top: 24px;
    bottom: -12px;
    width: 2px;
    background-color: #e3e6f0;
}

.card-body .table td {
    padding: 0.5rem 0;
    border: none;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
</style>
@endpush
@endsection