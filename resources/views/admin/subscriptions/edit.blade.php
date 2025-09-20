@extends('admin.layouts.app')

@section('title', 'Edit Subscription')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit"></i> Edit Subscription
        </h1>
        <div>
            <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="btn btn-info btn-sm">
                <i class="fas fa-eye"></i> View Details
            </a>
            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Subscriptions
            </a>
        </div>
    </div>

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
        <!-- Edit Form -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-school"></i> School Information
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.subscriptions.update', $subscription) }}" id="editSubscriptionForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic School Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-info-circle"></i> Basic Information
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">
                                        School Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $subscription->name) }}"
                                           required
                                           maxlength="255">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        Email Address <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $subscription->email) }}"
                                           required
                                           maxlength="255">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone', $subscription->phone) }}"
                                           maxlength="20"
                                           placeholder="+1 (555) 123-4567">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subscription_status" class="form-label">
                                        Subscription Status <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('subscription_status') is-invalid @enderror" 
                                            id="subscription_status" 
                                            name="subscription_status"
                                            required>
                                        <option value="">Select Status</option>
                                        <option value="trial" {{ old('subscription_status', $subscription->subscription_status) === 'trial' ? 'selected' : '' }}>
                                            Trial
                                        </option>
                                        <option value="active" {{ old('subscription_status', $subscription->subscription_status) === 'active' ? 'selected' : '' }}>
                                            Active
                                        </option>
                                        <option value="suspended" {{ old('subscription_status', $subscription->subscription_status) === 'suspended' ? 'selected' : '' }}>
                                            Suspended
                                        </option>
                                        <option value="expired" {{ old('subscription_status', $subscription->subscription_status) === 'expired' ? 'selected' : '' }}>
                                            Expired
                                        </option>
                                    </select>
                                    @error('subscription_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" 
                                              name="address" 
                                              rows="3"
                                              maxlength="500"
                                              placeholder="Enter school address">{{ old('address', $subscription->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Subscription Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-calendar-alt"></i> Subscription Settings
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subscription_expires_at" class="form-label">Expiration Date</label>
                                    <input type="datetime-local" 
                                           class="form-control @error('subscription_expires_at') is-invalid @enderror" 
                                           id="subscription_expires_at" 
                                           name="subscription_expires_at" 
                                           value="{{ old('subscription_expires_at', $subscription->subscription_expires_at ? $subscription->subscription_expires_at->format('Y-m-d\TH:i') : '') }}">
                                    <small class="form-text text-muted">Leave empty for no expiration</small>
                                    @error('subscription_expires_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status" class="form-label">Account Status</label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" 
                                            name="status">
                                        <option value="active" {{ old('status', $subscription->status ?? 'active') === 'active' ? 'selected' : '' }}>
                                            Active
                                        </option>
                                        <option value="inactive" {{ old('status', $subscription->status ?? 'active') === 'inactive' ? 'selected' : '' }}>
                                            Inactive
                                        </option>
                                        <option value="pending" {{ old('status', $subscription->status ?? 'active') === 'pending' ? 'selected' : '' }}>
                                            Pending
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-cogs"></i> Advanced Settings
                                </h6>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="settings" class="form-label">Custom Settings (JSON)</label>
                                    <textarea class="form-control @error('settings') is-invalid @enderror" 
                                              id="settings" 
                                              name="settings" 
                                              rows="4"
                                              placeholder='{"max_users": 100, "features": ["reports", "analytics"]}'
                                              style="font-family: monospace;">{{ old('settings', $subscription->settings ? json_encode($subscription->settings, JSON_PRETTY_PRINT) : '') }}</textarea>
                                    <small class="form-text text-muted">
                                        Enter JSON configuration for custom school settings. Leave empty for default settings.
                                    </small>
                                    @error('settings')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Subscription
                                        </button>
                                        <button type="reset" class="btn btn-secondary">
                                            <i class="fas fa-undo"></i> Reset Changes
                                        </button>
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="btn btn-outline-info">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Information Sidebar -->
        <div class="col-lg-4">
            <!-- Current Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Current Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>School ID:</strong><br>
                        <code>{{ $subscription->id }}</code>
                    </div>
                    <div class="mb-3">
                        <strong>Current Status:</strong><br>
                        @php
                            $statusClass = match($subscription->subscription_status) {
                                'active' => 'success',
                                'trial' => 'info',
                                'suspended' => 'warning',
                                'expired' => 'danger',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge badge-{{ $statusClass }}">
                            {{ ucfirst($subscription->subscription_status) }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Created:</strong><br>
                        {{ $subscription->created_at->format('M d, Y \a\t H:i') }}
                        <br>
                        <small class="text-muted">{{ $subscription->created_at->diffForHumans() }}</small>
                    </div>
                    <div class="mb-3">
                        <strong>Last Updated:</strong><br>
                        {{ $subscription->updated_at->format('M d, Y \a\t H:i') }}
                        <br>
                        <small class="text-muted">{{ $subscription->updated_at->diffForHumans() }}</small>
                    </div>
                    <div>
                        <strong>Total Users:</strong><br>
                        {{ $subscription->users->count() }} users
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
                                class="btn btn-outline-success btn-sm"
                                onclick="extendSubscription()">
                            <i class="fas fa-calendar-plus"></i> Extend Subscription
                        </button>
                        
                        <button type="button" 
                                class="btn btn-outline-info btn-sm"
                                onclick="resetExpiration()">
                            <i class="fas fa-calendar-times"></i> Remove Expiration
                        </button>
                        
                        <button type="button" 
                                class="btn btn-outline-warning btn-sm"
                                onclick="copySettings()">
                            <i class="fas fa-copy"></i> Copy Settings
                        </button>
                        
                        <button type="button" 
                                class="btn btn-outline-secondary btn-sm"
                                onclick="validateSettings()">
                            <i class="fas fa-check"></i> Validate JSON
                        </button>
                    </div>
                </div>
            </div>

            <!-- Status Guide -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-question-circle"></i> Status Guide
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="badge badge-info">Trial</span>
                        <small class="text-muted d-block">Limited access, evaluation period</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge badge-success">Active</span>
                        <small class="text-muted d-block">Full access to all features</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge badge-warning">Suspended</span>
                        <small class="text-muted d-block">Temporary access restriction</small>
                    </div>
                    <div>
                        <span class="badge badge-danger">Expired</span>
                        <small class="text-muted d-block">Subscription has ended</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function extendSubscription() {
    const currentDate = document.getElementById('subscription_expires_at').value;
    let newDate;
    
    if (currentDate) {
        newDate = new Date(currentDate);
        newDate.setMonth(newDate.getMonth() + 1);
    } else {
        newDate = new Date();
        newDate.setMonth(newDate.getMonth() + 1);
    }
    
    document.getElementById('subscription_expires_at').value = newDate.toISOString().slice(0, 16);
}

function resetExpiration() {
    if (confirm('Are you sure you want to remove the expiration date? This will give the school unlimited access.')) {
        document.getElementById('subscription_expires_at').value = '';
    }
}

function copySettings() {
    const settings = document.getElementById('settings').value;
    if (settings) {
        navigator.clipboard.writeText(settings).then(function() {
            alert('Settings copied to clipboard!');
        });
    } else {
        alert('No settings to copy.');
    }
}

function validateSettings() {
    const settings = document.getElementById('settings').value.trim();
    
    if (!settings) {
        alert('Settings field is empty.');
        return;
    }
    
    try {
        JSON.parse(settings);
        alert('JSON is valid!');
        document.getElementById('settings').classList.remove('is-invalid');
        document.getElementById('settings').classList.add('is-valid');
    } catch (e) {
        alert('Invalid JSON: ' + e.message);
        document.getElementById('settings').classList.remove('is-valid');
        document.getElementById('settings').classList.add('is-invalid');
    }
}

// Auto-save draft functionality
let autoSaveTimeout;
document.getElementById('editSubscriptionForm').addEventListener('input', function() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(function() {
        // In production, save draft to localStorage or send to server
        console.log('Auto-saving draft...');
    }, 2000);
});

// Form validation
document.getElementById('editSubscriptionForm').addEventListener('submit', function(e) {
    const settings = document.getElementById('settings').value.trim();
    
    if (settings) {
        try {
            JSON.parse(settings);
        } catch (error) {
            e.preventDefault();
            alert('Please fix the JSON settings before submitting.');
            document.getElementById('settings').focus();
            return false;
        }
    }
    
    return true;
});

// Reset form handler
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

.is-valid {
    border-color: #1cc88a !important;
}

.is-invalid {
    border-color: #e74a3b !important;
}

.card {
    transition: box-shadow 0.15s ease-in-out;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.badge {
    font-size: 0.75em;
}

code {
    color: #6c757d;
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
}

.d-grid .btn {
    text-align: left;
}

textarea[style*="font-family: monospace"] {
    font-size: 0.875rem;
    line-height: 1.4;
}

.text-danger {
    color: #e74a3b !important;
}

.form-text {
    font-size: 0.875em;
}
</style>
@endpush
@endsection