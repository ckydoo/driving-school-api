@extends('admin.layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-cogs"></i> System Settings
        </h1>
        <div>
            <a href="{{ route('admin.system.health') }}" class="btn btn-info btn-sm">
                <i class="fas fa-heartbeat"></i> System Health
            </a>
            <button type="button" class="btn btn-warning btn-sm" onclick="clearSystemCache()">
                <i class="fas fa-sync"></i> Clear Cache
            </button>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- General Settings -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cog"></i> General System Settings
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.system.settings.update') }}">
                        @csrf
                        
                        <!-- Application Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-desktop"></i> Application Settings
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="app_name">Application Name</label>
                                    <input type="text" 
                                           class="form-control @error('app_name') is-invalid @enderror" 
                                           id="app_name" 
                                           name="app_name" 
                                           value="{{ old('app_name', config('app.name', 'Driving School Management')) }}"
                                           required>
                                    @error('app_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="app_timezone">Application Timezone</label>
                                    <select class="form-control @error('app_timezone') is-invalid @enderror" 
                                            id="app_timezone" 
                                            name="app_timezone">
                                        @php
                                            $timezones = [
                                                'UTC' => 'UTC',
                                                'America/New_York' => 'Eastern Time',
                                                'America/Chicago' => 'Central Time',
                                                'America/Denver' => 'Mountain Time',
                                                'America/Los_Angeles' => 'Pacific Time',
                                                'Europe/London' => 'London',
                                                'Europe/Paris' => 'Paris',
                                                'Asia/Tokyo' => 'Tokyo',
                                                'Australia/Sydney' => 'Sydney',
                                                'Africa/Harare' => 'Harare (Zimbabwe)',
                                            ];
                                        @endphp
                                        @foreach($timezones as $value => $label)
                                            <option value="{{ $value }}" 
                                                {{ old('app_timezone', config('app.timezone', 'UTC')) === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('app_timezone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Business Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-building"></i> Business Settings
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="default_currency">Default Currency</label>
                                    <select class="form-control @error('default_currency') is-invalid @enderror" 
                                            id="default_currency" 
                                            name="default_currency">
                                        <option value="USD" {{ old('default_currency', 'USD') === 'USD' ? 'selected' : '' }}>USD ($)</option>
                                        <option value="EUR" {{ old('default_currency') === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                        <option value="GBP" {{ old('default_currency') === 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                                        <option value="ZWL" {{ old('default_currency') === 'ZWL' ? 'selected' : '' }}>ZWL (Z$)</option>
                                    </select>
                                    @error('default_currency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="default_lesson_duration">Default Lesson Duration (minutes)</label>
                                    <select class="form-control @error('default_lesson_duration') is-invalid @enderror" 
                                            id="default_lesson_duration" 
                                            name="default_lesson_duration">
                                        <option value="30" {{ old('default_lesson_duration', '60') === '30' ? 'selected' : '' }}>30 minutes</option>
                                        <option value="45" {{ old('default_lesson_duration', '60') === '45' ? 'selected' : '' }}>45 minutes</option>
                                        <option value="60" {{ old('default_lesson_duration', '60') === '60' ? 'selected' : '' }}>60 minutes</option>
                                        <option value="90" {{ old('default_lesson_duration', '60') === '90' ? 'selected' : '' }}>90 minutes</option>
                                        <option value="120" {{ old('default_lesson_duration', '60') === '120' ? 'selected' : '' }}>120 minutes</option>
                                    </select>
                                    @error('default_lesson_duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- System Features -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-toggle-on"></i> System Features
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="enable_notifications" 
                                           name="enable_notifications" 
                                           value="1"
                                           {{ old('enable_notifications', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_notifications">
                                        Enable Email Notifications
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="enable_auto_backup" 
                                           name="enable_auto_backup" 
                                           value="1"
                                           {{ old('enable_auto_backup', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_auto_backup">
                                        Enable Automatic Backups
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="maintenance_mode" 
                                           name="maintenance_mode" 
                                           value="1"
                                           {{ old('maintenance_mode', false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="maintenance_mode">
                                        <span class="text-warning">Maintenance Mode</span>
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="debug_mode" 
                                           name="debug_mode" 
                                           value="1"
                                           {{ old('debug_mode', config('app.debug', false)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="debug_mode">
                                        <span class="text-danger">Debug Mode</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Security Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-shield-alt"></i> Security Settings
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="session_timeout">Session Timeout (minutes)</label>
                                    <select class="form-control @error('session_timeout') is-invalid @enderror" 
                                            id="session_timeout" 
                                            name="session_timeout">
                                        <option value="30" {{ old('session_timeout', '120') === '30' ? 'selected' : '' }}>30 minutes</option>
                                        <option value="60" {{ old('session_timeout', '120') === '60' ? 'selected' : '' }}>1 hour</option>
                                        <option value="120" {{ old('session_timeout', '120') === '120' ? 'selected' : '' }}>2 hours</option>
                                        <option value="240" {{ old('session_timeout', '120') === '240' ? 'selected' : '' }}>4 hours</option>
                                        <option value="480" {{ old('session_timeout', '120') === '480' ? 'selected' : '' }}>8 hours</option>
                                    </select>
                                    @error('session_timeout')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_login_attempts">Max Login Attempts</label>
                                    <select class="form-control @error('max_login_attempts') is-invalid @enderror" 
                                            id="max_login_attempts" 
                                            name="max_login_attempts">
                                        <option value="3" {{ old('max_login_attempts', '5') === '3' ? 'selected' : '' }}>3 attempts</option>
                                        <option value="5" {{ old('max_login_attempts', '5') === '5' ? 'selected' : '' }}>5 attempts</option>
                                        <option value="10" {{ old('max_login_attempts', '5') === '10' ? 'selected' : '' }}>10 attempts</option>
                                    </select>
                                    @error('max_login_attempts')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-12">
                                <hr>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Settings
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- System Information Sidebar -->
        <div class="col-lg-4">
            <!-- System Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> System Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>PHP Version:</strong><br>
                        <span class="text-muted">{{ PHP_VERSION }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Laravel Version:</strong><br>
                        <span class="text-muted">{{ app()->version() }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Database:</strong><br>
                        <span class="text-muted">{{ config('database.default') }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Cache Driver:</strong><br>
                        <span class="text-muted">{{ config('cache.default') }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Environment:</strong><br>
                        <span class="badge badge-{{ app()->environment('production') ? 'success' : 'warning' }}">
                            {{ strtoupper(app()->environment()) }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Server Time:</strong><br>
                        <span class="text-muted">{{ now()->format('Y-m-d H:i:s T') }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tools"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-info" onclick="clearSystemCache()">
                            <i class="fas fa-sync"></i> Clear All Cache
                        </button>
                        <a href="{{ route('admin.system.health') }}" class="btn btn-outline-success">
                            <i class="fas fa-heartbeat"></i> System Health Check
                        </a>
                        <a href="{{ route('admin.logs.index') }}" class="btn btn-outline-warning">
                            <i class="fas fa-list-alt"></i> View Activity Logs
                        </a>
                        <a href="{{ route('admin.backups.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-database"></i> Manage Backups
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clock"></i> Recent System Activity
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-user-plus text-success"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="small text-muted">2 hours ago</div>
                                    <div>New user registered</div>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-cog text-info"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="small text-muted">6 hours ago</div>
                                    <div>System settings updated</div>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-database text-warning"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="small text-muted">1 day ago</div>
                                    <div>Automatic backup completed</div>
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
function clearSystemCache() {
    if (confirm('Are you sure you want to clear all system caches? This may temporarily slow down the application.')) {
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
                alert('System cache cleared successfully!');
                location.reload();
            } else {
                alert('Error clearing cache: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error clearing cache. Please try again.');
        });
    }
}

// Auto-save indication
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            // Add visual indication that settings have changed
            const submitBtn = document.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.classList.contains('btn-warning')) {
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-warning');
                submitBtn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Save Changes';
            }
        });
    });
});
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

.form-check-input:checked {
    background-color: #4e73df;
    border-color: #4e73df;
}

.card {
    transition: box-shadow 0.15s ease-in-out;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>
@endpush
@endsection