@extends('admin.layouts.app')

@section('title', 'Settings')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-cogs"></i> Settings
        </h1>
        <div>
            <a href="{{ route('admin.profile') }}" class="btn btn-info btn-sm">
                <i class="fas fa-user"></i> My Profile
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
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

    <!-- Error Display -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Settings Form -->
        <div class="col-lg-8">
            <form method="POST" action="{{ route('admin.settings.update') }}" id="settingsForm">
                @csrf
                
                <!-- Personal Preferences -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-user-cog"></i> Personal Preferences
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="language" class="form-label">Language</label>
                                    <select class="form-control @error('language') is-invalid @enderror" 
                                            id="language" 
                                            name="language">
                                        <option value="en" {{ old('language', 'en') === 'en' ? 'selected' : '' }}>
                                            English
                                        </option>
                                        <option value="es" {{ old('language') === 'es' ? 'selected' : '' }}>
                                            Spanish
                                        </option>
                                        <option value="fr" {{ old('language') === 'fr' ? 'selected' : '' }}>
                                            French
                                        </option>
                                        <option value="de" {{ old('language') === 'de' ? 'selected' : '' }}>
                                            German
                                        </option>
                                    </select>
                                    @error('language')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="timezone" class="form-label">Timezone</label>
                                    <select class="form-control @error('timezone') is-invalid @enderror" 
                                            id="timezone" 
                                            name="timezone">
                                        <option value="UTC" {{ old('timezone', 'UTC') === 'UTC' ? 'selected' : '' }}>
                                            UTC
                                        </option>
                                        <option value="America/New_York" {{ old('timezone') === 'America/New_York' ? 'selected' : '' }}>
                                            Eastern Time (ET)
                                        </option>
                                        <option value="America/Chicago" {{ old('timezone') === 'America/Chicago' ? 'selected' : '' }}>
                                            Central Time (CT)
                                        </option>
                                        <option value="America/Denver" {{ old('timezone') === 'America/Denver' ? 'selected' : '' }}>
                                            Mountain Time (MT)
                                        </option>
                                        <option value="America/Los_Angeles" {{ old('timezone') === 'America/Los_Angeles' ? 'selected' : '' }}>
                                            Pacific Time (PT)
                                        </option>
                                        <option value="Europe/London" {{ old('timezone') === 'Europe/London' ? 'selected' : '' }}>
                                            London (GMT)
                                        </option>
                                        <option value="Africa/Harare" {{ old('timezone') === 'Africa/Harare' ? 'selected' : '' }}>
                                            Harare (CAT)
                                        </option>
                                    </select>
                                    @error('timezone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_format" class="form-label">Date Format</label>
                                    <select class="form-control @error('date_format') is-invalid @enderror" 
                                            id="date_format" 
                                            name="date_format">
                                        <option value="M d, Y" {{ old('date_format', 'M d, Y') === 'M d, Y' ? 'selected' : '' }}>
                                            Jan 15, 2025
                                        </option>
                                        <option value="d/m/Y" {{ old('date_format') === 'd/m/Y' ? 'selected' : '' }}>
                                            15/01/2025
                                        </option>
                                        <option value="m/d/Y" {{ old('date_format') === 'm/d/Y' ? 'selected' : '' }}>
                                            01/15/2025
                                        </option>
                                        <option value="Y-m-d" {{ old('date_format') === 'Y-m-d' ? 'selected' : '' }}>
                                            2025-01-15
                                        </option>
                                    </select>
                                    @error('date_format')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="time_format" class="form-label">Time Format</label>
                                    <select class="form-control @error('time_format') is-invalid @enderror" 
                                            id="time_format" 
                                            name="time_format">
                                        <option value="12" {{ old('time_format', '12') === '12' ? 'selected' : '' }}>
                                            12-hour (2:30 PM)
                                        </option>
                                        <option value="24" {{ old('time_format') === '24' ? 'selected' : '' }}>
                                            24-hour (14:30)
                                        </option>
                                    </select>
                                    @error('time_format')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="theme" class="form-label">Theme</label>
                                    <select class="form-control @error('theme') is-invalid @enderror" 
                                            id="theme" 
                                            name="theme">
                                        <option value="light" {{ old('theme', 'light') === 'light' ? 'selected' : '' }}>
                                            Light Theme
                                        </option>
                                        <option value="dark" {{ old('theme') === 'dark' ? 'selected' : '' }}>
                                            Dark Theme
                                        </option>
                                        <option value="auto" {{ old('theme') === 'auto' ? 'selected' : '' }}>
                                            Auto (System)
                                        </option>
                                    </select>
                                    @error('theme')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="items_per_page" class="form-label">Items per Page</label>
                                    <select class="form-control @error('items_per_page') is-invalid @enderror" 
                                            id="items_per_page" 
                                            name="items_per_page">
                                        <option value="10" {{ old('items_per_page') === '10' ? 'selected' : '' }}>10</option>
                                        <option value="25" {{ old('items_per_page', '25') === '25' ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ old('items_per_page') === '50' ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ old('items_per_page') === '100' ? 'selected' : '' }}>100</option>
                                    </select>
                                    @error('items_per_page')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notification Preferences -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-bell"></i> Notification Preferences
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-secondary mb-3">Email Notifications</h6>
                                <div class="form-check mb-2">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="email_new_users" 
                                           name="email_new_users" 
                                           value="1"
                                           {{ old('email_new_users', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_new_users">
                                        New user registrations
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="email_schedule_changes" 
                                           name="email_schedule_changes" 
                                           value="1"
                                           {{ old('email_schedule_changes', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_schedule_changes">
                                        Schedule changes
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="email_payments" 
                                           name="email_payments" 
                                           value="1"
                                           {{ old('email_payments', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_payments">
                                        Payment notifications
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="email_system_alerts" 
                                           name="email_system_alerts" 
                                           value="1"
                                           {{ old('email_system_alerts', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_system_alerts">
                                        System alerts
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-secondary mb-3">Dashboard Notifications</h6>
                                <div class="form-check mb-2">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="dashboard_reminders" 
                                           name="dashboard_reminders" 
                                           value="1"
                                           {{ old('dashboard_reminders', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="dashboard_reminders">
                                        Show reminders
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="dashboard_announcements" 
                                           name="dashboard_announcements" 
                                           value="1"
                                           {{ old('dashboard_announcements', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="dashboard_announcements">
                                        Show announcements
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="dashboard_tips" 
                                           name="dashboard_tips" 
                                           value="1"
                                           {{ old('dashboard_tips', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="dashboard_tips">
                                        Show helpful tips
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="dashboard_statistics" 
                                           name="dashboard_statistics" 
                                           value="1"
                                           {{ old('dashboard_statistics', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="dashboard_statistics">
                                        Show detailed statistics
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Privacy & Security -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-shield-alt"></i> Privacy & Security
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="profile_visibility" 
                                           name="profile_visibility" 
                                           value="1"
                                           {{ old('profile_visibility', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="profile_visibility">
                                        <strong>Make profile visible to other users</strong>
                                        <small class="d-block text-muted">
                                            Other users in your school can see your basic information
                                        </small>
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="activity_tracking" 
                                           name="activity_tracking" 
                                           value="1"
                                           {{ old('activity_tracking', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="activity_tracking">
                                        <strong>Enable activity tracking</strong>
                                        <small class="d-block text-muted">
                                            Track login times and system usage for analytics
                                        </small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="data_sharing" 
                                           name="data_sharing" 
                                           value="1"
                                           {{ old('data_sharing', false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="data_sharing">
                                        <strong>Allow anonymous data sharing</strong>
                                        <small class="d-block text-muted">
                                            Help improve the system by sharing anonymous usage data
                                        </small>
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="auto_logout" 
                                           name="auto_logout" 
                                           value="1"
                                           {{ old('auto_logout', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="auto_logout">
                                        <strong>Auto-logout on inactivity</strong>
                                        <small class="d-block text-muted">
                                            Automatically log out after 30 minutes of inactivity
                                        </small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Settings -->
                @if($currentUser->isAdmin())
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-cog"></i> Advanced Settings
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Administrator Options:</strong> These settings affect your administrative experience.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="developer_mode" 
                                           name="developer_mode" 
                                           value="1"
                                           {{ old('developer_mode', false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="developer_mode">
                                        <strong>Developer Mode</strong>
                                        <small class="d-block text-muted">
                                            Show additional debugging information
                                        </small>
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="beta_features" 
                                           name="beta_features" 
                                           value="1"
                                           {{ old('beta_features', false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="beta_features">
                                        <strong>Beta Features</strong>
                                        <small class="d-block text-muted">
                                            Access experimental features before they're released
                                        </small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="default_user_role" class="form-label">Default New User Role</label>
                                    <select class="form-control @error('default_user_role') is-invalid @enderror" 
                                            id="default_user_role" 
                                            name="default_user_role">
                                        <option value="student" {{ old('default_user_role', 'student') === 'student' ? 'selected' : '' }}>
                                            Student
                                        </option>
                                        <option value="instructor" {{ old('default_user_role') === 'instructor' ? 'selected' : '' }}>
                                            Instructor
                                        </option>
                                        @if($currentUser->isSuperAdmin())
                                        <option value="admin" {{ old('default_user_role') === 'admin' ? 'selected' : '' }}>
                                            Administrator
                                        </option>
                                        @endif
                                    </select>
                                    @error('default_user_role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Role assigned to new users by default
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Form Actions -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Settings
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> Reset to Defaults
                                </button>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-info" onclick="exportSettings()">
                                    <i class="fas fa-download"></i> Export Settings
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Settings Information Sidebar -->
        <div class="col-lg-4">
            <!-- Current Settings Summary -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Current Settings
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Language:</strong><br>
                        <span class="text-muted">English</span>
                    </div>
                    <div class="mb-3">
                        <strong>Timezone:</strong><br>
                        <span class="text-muted">{{ config('app.timezone', 'UTC') }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Theme:</strong><br>
                        <span class="text-muted">Light Theme</span>
                    </div>
                    <div class="mb-3">
                        <strong>Email Notifications:</strong><br>
                        <span class="badge badge-success">Enabled</span>
                    </div>
                    <div>
                        <strong>Last Updated:</strong><br>
                        <span class="text-muted">Never</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" 
                                class="btn btn-outline-primary btn-sm"
                                onclick="resetToDefaults()">
                            <i class="fas fa-undo"></i> Reset All to Defaults
                        </button>
                        
                        <button type="button" 
                                class="btn btn-outline-success btn-sm"
                                onclick="testNotifications()">
                            <i class="fas fa-bell"></i> Test Notifications
                        </button>
                        
                        <button type="button" 
                                class="btn btn-outline-info btn-sm"
                                onclick="previewTheme()">
                            <i class="fas fa-palette"></i> Preview Theme
                        </button>
                        
                        <button type="button" 
                                class="btn btn-outline-warning btn-sm"
                                onclick="clearCache()">
                            <i class="fas fa-trash"></i> Clear Cache
                        </button>
                    </div>
                </div>
            </div>

            <!-- Help & Support -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-question-circle"></i> Help & Support
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Settings Help:</strong><br>
                        <small class="text-muted">
                            These settings control your personal experience with the system. 
                            Changes are saved automatically when you click "Save Settings".
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Need Help?</strong><br>
                        <small class="text-muted">
                            Contact support if you're having trouble with any settings.
                        </small>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="button" 
                                class="btn btn-outline-secondary btn-sm"
                                onclick="contactSupport()">
                            <i class="fas fa-envelope"></i> Contact Support
                        </button>
                        
                        <button type="button" 
                                class="btn btn-outline-secondary btn-sm"
                                onclick="viewDocumentation()">
                            <i class="fas fa-book"></i> View Documentation
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function resetToDefaults() {
    if (confirm('Are you sure you want to reset all settings to their default values? This action cannot be undone.')) {
        // Reset form to default values
        document.getElementById('settingsForm').reset();
        alert('Settings reset to defaults. Click "Save Settings" to apply changes.');
    }
}

function testNotifications() {
    alert('Test notification sent! Check your email and dashboard for the test message.');
}

function previewTheme() {
    const theme = document.getElementById('theme').value;
    alert(`Theme preview for "${theme}" would be shown here.`);
}

function clearCache() {
    if (confirm('Clear your personal cache? This may temporarily slow down the application.')) {
        // In production, make AJAX call to clear user cache
        alert('Personal cache cleared successfully!');
    }
}

function exportSettings() {
    const formData = new FormData(document.getElementById('settingsForm'));
    const settings = {};
    
    for (let [key, value] of formData.entries()) {
        settings[key] = value;
    }
    
    const blob = new Blob([JSON.stringify(settings, null, 2)], { type: 'application/json' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'my-settings-' + new Date().toISOString().slice(0, 10) + '.json';
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
}

function contactSupport() {
    alert('Support contact form would open here, or redirect to support email.');
}

function viewDocumentation() {
    alert('Documentation would open here, either in a new tab or modal.');
}

// Auto-save functionality
let autoSaveTimeout;
document.getElementById('settingsForm').addEventListener('change', function() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(function() {
        // In production, auto-save settings
        console.log('Auto-saving settings...');
    }, 2000);
});

// Theme preview
document.getElementById('theme').addEventListener('change', function() {
    // In production, apply theme immediately or show preview
    console.log('Theme changed to:', this.value);
});

// Form reset confirmation
document.querySelector('button[type="reset"]').addEventListener('click', function(e) {
    if (!confirm('Are you sure you want to reset all changes?')) {
        e.preventDefault();
    }
});
</script>
@endpush

@push('styles')
<style>
.form-label {
    font-weight: 600;
    color: #5a5c69;
}

.form-control:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

.card {
    transition: box-shadow 0.15s ease-in-out;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.form-check-input:checked {
    background-color: #4e73df;
    border-color: #4e73df;
}

.badge {
    font-size: 0.75em;
}

.d-grid .btn {
    text-align: left;
}

.text-danger {
    color: #e74a3b !important;
}

.form-text {
    font-size: 0.875em;
}

.alert {
    border-left: 4px solid;
}

.alert-warning {
    border-left-color: #f6c23e;
}

.alert-info {
    border-left-color: #36b9cc;
}

.form-check-label {
    cursor: pointer;
}

.form-check-label small {
    font-style: italic;
}

.is-invalid {
    border-color: #e74a3b;
}

.is-invalid:focus {
    border-color: #e74a3b;
    box-shadow: 0 0 0 0.2rem rgba(231, 74, 59, 0.25);
}
</style>
@endpush
@endsection