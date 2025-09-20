@extends('admin.layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-circle"></i> My Profile
        </h1>
        <div>
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
        <!-- Profile Form -->
        <div class="col-lg-8">
            <form method="POST" action="{{ route('admin.profile.update') }}" id="profileForm">
                @csrf
                
                <!-- Personal Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-user"></i> Personal Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fname" class="form-label">
                                        First Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('fname') is-invalid @enderror" 
                                           id="fname" 
                                           name="fname" 
                                           value="{{ old('fname', $currentUser->fname) }}"
                                           required
                                           maxlength="255">
                                    @error('fname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lname" class="form-label">
                                        Last Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('lname') is-invalid @enderror" 
                                           id="lname" 
                                           name="lname" 
                                           value="{{ old('lname', $currentUser->lname) }}"
                                           required
                                           maxlength="255">
                                    @error('lname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        Email Address <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $currentUser->email) }}"
                                           required
                                           maxlength="255">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($currentUser->email_verified_at)
                                        <small class="text-success">
                                            <i class="fas fa-check-circle"></i> Email verified on {{ $currentUser->email_verified_at->format('M d, Y') }}
                                        </small>
                                    @else
                                        <small class="text-warning">
                                            <i class="fas fa-exclamation-triangle"></i> Email not verified
                                        </small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone', $currentUser->phone) }}"
                                           maxlength="20"
                                           placeholder="+1 (555) 123-4567">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-control @error('gender') is-invalid @enderror" 
                                            id="gender" 
                                            name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender', $currentUser->gender) === 'male' ? 'selected' : '' }}>
                                            Male
                                        </option>
                                        <option value="female" {{ old('gender', $currentUser->gender) === 'female' ? 'selected' : '' }}>
                                            Female
                                        </option>
                                        <option value="other" {{ old('gender', $currentUser->gender) === 'other' ? 'selected' : '' }}>
                                            Other
                                        </option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" 
                                           class="form-control @error('date_of_birth') is-invalid @enderror" 
                                           id="date_of_birth" 
                                           name="date_of_birth" 
                                           value="{{ old('date_of_birth', $currentUser->date_of_birth ? $currentUser->date_of_birth->format('Y-m-d') : '') }}"
                                           max="{{ date('Y-m-d') }}">
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" 
                                              name="address" 
                                              rows="3"
                                              maxlength="500"
                                              placeholder="Enter your address">{{ old('address', $currentUser->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-key"></i> Change Password
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Leave password fields empty if you don't want to change your password.
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" 
                                           class="form-control @error('current_password') is-invalid @enderror" 
                                           id="current_password" 
                                           name="current_password" 
                                           placeholder="Enter current password">
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           minlength="8"
                                           placeholder="Enter new password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Minimum 8 characters required
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           placeholder="Confirm new password">
                                    <small class="form-text text-muted">
                                        Must match the new password
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> Reset Changes
                                </button>
                            </div>
                            <div>
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Profile Information Sidebar -->
        <div class="col-lg-4">
            <!-- Current Account Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Account Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-placeholder bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px; font-size: 2rem;">
                            {{ strtoupper(substr($currentUser->fname, 0, 1) . substr($currentUser->lname, 0, 1)) }}
                        </div>
                        <h5 class="mt-2 mb-0">{{ $currentUser->fname }} {{ $currentUser->lname }}</h5>
                        @php
                            $roleClass = match($currentUser->role) {
                                'super_admin' => 'danger',
                                'admin' => 'warning',
                                'instructor' => 'info',
                                'student' => 'primary',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge badge-{{ $roleClass }} badge-lg">
                            {{ ucfirst(str_replace('_', ' ', $currentUser->role)) }}
                        </span>
                        @if($currentUser->is_super_admin)
                            <span class="badge badge-danger badge-sm ml-1">
                                <i class="fas fa-crown"></i> Super Admin
                            </span>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <strong>User ID:</strong><br>
                        <code>{{ $currentUser->id }}</code>
                    </div>
                    
                    <div class="mb-3">
                        <strong>School:</strong><br>
                        @if($currentUser->school)
                            {{ $currentUser->school->name }}
                        @else
                            <span class="text-muted">No school assigned</span>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <strong>Account Created:</strong><br>
                        {{ $currentUser->created_at->format('M d, Y') }}
                        <br>
                        <small class="text-muted">{{ $currentUser->created_at->diffForHumans() }}</small>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Last Login:</strong><br>
                        @if($currentUser->last_login)
                            {{ $currentUser->last_login->format('M d, Y \a\t H:i') }}
                            <br>
                            <small class="text-muted">{{ $currentUser->last_login->diffForHumans() }}</small>
                        @else
                            <span class="text-muted">First time login</span>
                        @endif
                    </div>
                    
                    <div>
                        <strong>Account Status:</strong><br>
                        <span class="badge badge-{{ ($currentUser->status ?? 'active') === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($currentUser->status ?? 'Active') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-shield-alt"></i> Security Settings
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Email Verification:</strong><br>
                        @if($currentUser->email_verified_at)
                            <span class="text-success">
                                <i class="fas fa-check-circle"></i> Verified
                            </span>
                        @else
                            <span class="text-warning">
                                <i class="fas fa-exclamation-triangle"></i> Not verified
                            </span>
                            <br>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-1" onclick="resendVerification()">
                                Resend Verification
                            </button>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <strong>Password Strength:</strong><br>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: 85%"></div>
                        </div>
                        <small class="text-muted">Strong</small>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Two-Factor Authentication:</strong><br>
                        <span class="text-secondary">
                            <i class="fas fa-times-circle"></i> Not enabled
                        </span>
                        <br>
                        <button type="button" class="btn btn-sm btn-outline-success mt-1" onclick="setup2FA()">
                            Enable 2FA
                        </button>
                    </div>
                    
                    <div>
                        <strong>Login Sessions:</strong><br>
                        <span class="text-info">1 active session</span>
                        <br>
                        <button type="button" class="btn btn-sm btn-outline-warning mt-1" onclick="logoutOtherSessions()">
                            Logout Other Sessions
                        </button>
                    </div>
                </div>
            </div>

            <!-- Activity Summary -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line"></i> Activity Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-primary">{{ $activityStats['profile_updates'] ?? 0 }}</h4>
                            <small class="text-muted">Profile Updates</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ $activityStats['login_count'] ?? 0 }}</h4>
                            <small class="text-muted">Total Logins</small>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span><strong>This Month:</strong></span>
                            <span>{{ $activityStats['monthly_logins'] ?? 0 }} logins</span>
                        </div>
                    </div>
                    
                    <div>
                        <div class="d-flex justify-content-between">
                            <span><strong>Last Update:</strong></span>
                            <span>{{ $currentUser->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function resendVerification() {
    if (confirm('Send verification email to {{ $currentUser->email }}?')) {
        // In production, make AJAX call to resend verification
        alert('Verification email would be sent.');
    }
}

function setup2FA() {
    alert('Two-Factor Authentication setup would be implemented here.');
}

function logoutOtherSessions() {
    if (confirm('Logout all other active sessions? You will remain logged in on this device.')) {
        // In production, make AJAX call to logout other sessions
        alert('Other sessions would be logged out.');
    }
}

// Password confirmation validation
document.getElementById('password_confirmation').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmation = this.value;
    
    if (password && confirmation && password !== confirmation) {
        this.setCustomValidity('Passwords do not match');
        this.classList.add('is-invalid');
    } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
    }
});

// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    // Simple password strength calculation
    let strength = 0;
    if (password.length >= 8) strength += 25;
    if (/[a-z]/.test(password)) strength += 25;
    if (/[A-Z]/.test(password)) strength += 25;
    if (/[0-9!@#$%^&*]/.test(password)) strength += 25;
    
    // Update strength indicator if you add one to the sidebar
    console.log('Password strength:', strength + '%');
});

// Auto-save draft functionality
let autoSaveTimeout;
document.getElementById('profileForm').addEventListener('input', function() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(function() {
        // In production, save draft to localStorage
        console.log('Auto-saving profile draft...');
    }, 2000);
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
.avatar-placeholder {
    font-weight: bold;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

.badge-lg {
    font-size: 0.9em;
    padding: 0.5rem 0.75rem;
}

.badge-sm {
    font-size: 0.7em;
    padding: 0.25rem 0.5rem;
}

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

.progress {
    height: 8px;
    border-radius: 4px;
}

code {
    color: #6c757d;
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
}

.text-danger {
    color: #e74a3b !important;
}

.form-text {
    font-size: 0.875em;
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