@extends('admin.layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-edit"></i> Edit User
        </h1>
        <div>
            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-info btn-sm">
                <i class="fas fa-eye"></i> View Details
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Users
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
            <form method="POST" action="{{ route('admin.users.update', $user) }}" id="editUserForm">
                @csrf
                @method('PUT')
                
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
                                           value="{{ old('fname', $user->fname) }}"
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
                                           value="{{ old('lname', $user->lname) }}"
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
                                           value="{{ old('email', $user->email) }}"
                                           required
                                           maxlength="255">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($user->email_verified_at)
                                        <small class="text-success">
                                            <i class="fas fa-check-circle"></i> Email verified
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
                                           value="{{ old('phone', $user->phone) }}"
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
                                        <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>
                                            Male
                                        </option>
                                        <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>
                                            Female
                                        </option>
                                        <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>
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
                                           value="{{ old('date_of_birth', $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '') }}"
                                           max="{{ date('Y-m-d') }}">
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="idnumber" class="form-label">ID Number</label>
                                    <input type="text" 
                                           class="form-control @error('idnumber') is-invalid @enderror" 
                                           id="idnumber" 
                                           name="idnumber" 
                                           value="{{ old('idnumber', $user->idnumber) }}"
                                           maxlength="50"
                                           placeholder="Driver's License, Passport, etc.">
                                    @error('idnumber')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status" class="form-label">
                                        Account Status <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" 
                                            name="status"
                                            required>
                                        <option value="active" {{ old('status', $user->status ?? 'active') === 'active' ? 'selected' : '' }}>
                                            Active
                                        </option>
                                        <option value="inactive" {{ old('status', $user->status ?? 'active') === 'inactive' ? 'selected' : '' }}>
                                            Inactive
                                        </option>
                                        <option value="suspended" {{ old('status', $user->status ?? 'active') === 'suspended' ? 'selected' : '' }}>
                                            Suspended
                                        </option>
                                    </select>
                                    @error('status')
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
                                              placeholder="Enter user's address">{{ old('address', $user->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Settings -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-cog"></i> Account Settings
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role" class="form-label">
                                        User Role <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('role') is-invalid @enderror" 
                                            id="role" 
                                            name="role"
                                            required>
                                        @foreach($availableRoles as $roleValue => $roleLabel)
                                            <option value="{{ $roleValue }}" 
                                                {{ old('role', $user->role) === $roleValue ? 'selected' : '' }}>
                                                {{ $roleLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Changing the role will affect user permissions and access levels.
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="school_id" class="form-label">
                                        School <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('school_id') is-invalid @enderror" 
                                            id="school_id" 
                                            name="school_id"
                                            required>
                                        <option value="">Select School</option>
                                        @foreach($schools as $school)
                                            <option value="{{ $school->id }}" 
                                                {{ old('school_id', $user->school_id) == $school->id ? 'selected' : '' }}>
                                                {{ $school->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('school_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        @if(Auth::user()->isSuperAdmin())
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="is_super_admin" 
                                           name="is_super_admin" 
                                           value="1"
                                           {{ old('is_super_admin', $user->is_super_admin) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_super_admin">
                                        <strong class="text-danger">Super Administrator</strong>
                                        <small class="d-block text-muted">
                                            Grant system-wide administrative privileges. Use with extreme caution.
                                        </small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="courseIds" class="form-label">Course Enrollments</label>
                                    <input type="text" 
                                           class="form-control @error('courseIds') is-invalid @enderror" 
                                           id="courseIds" 
                                           name="courseIds" 
                                           value="{{ old('courseIds', is_array($user->courseIds) ? implode(',', $user->courseIds) : $user->courseIds) }}"
                                           placeholder="1,2,3 (comma-separated course IDs)">
                                    @error('courseIds')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Enter course IDs separated by commas. Leave empty if no courses assigned.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Password Reset -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-key"></i> Password Management
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           minlength="8"
                                           placeholder="Leave empty to keep current password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Minimum 8 characters. Leave empty to keep current password.
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           placeholder="Confirm new password">
                                    <small class="form-text text-muted">
                                        Must match the new password above.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="send_password_email" 
                                           name="send_password_email" 
                                           value="1">
                                    <label class="form-check-label" for="send_password_email">
                                        Send password reset email to user
                                        <small class="d-block text-muted">
                                            If checked, the user will receive an email with instructions to set a new password.
                                        </small>
                                    </label>
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
                                    <i class="fas fa-save"></i> Update User
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> Reset Changes
                                </button>
                            </div>
                            <div>
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-info">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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
                        <strong>User ID:</strong><br>
                        <code>{{ $user->id }}</code>
                    </div>
                    <div class="mb-3">
                        <strong>Current Role:</strong><br>
                        @php
                            $roleClass = match($user->role) {
                                'super_admin' => 'danger',
                                'admin' => 'warning',
                                'instructor' => 'info',
                                'student' => 'primary',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge badge-{{ $roleClass }}">
                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                        </span>
                        @if($user->is_super_admin)
                            <span class="badge badge-danger badge-sm ml-1">
                                <i class="fas fa-crown"></i> Super Admin
                            </span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <strong>Current School:</strong><br>
                        @if($user->school)
                            {{ $user->school->name }}
                        @else
                            <span class="text-muted">No school assigned</span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <strong>Account Created:</strong><br>
                        {{ $user->created_at->format('M d, Y \a\t H:i') }}
                        <br>
                        <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                    </div>
                    <div class="mb-3">
                        <strong>Last Updated:</strong><br>
                        {{ $user->updated_at->format('M d, Y \a\t H:i') }}
                        <br>
                        <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                    </div>
                    <div>
                        <strong>Last Login:</strong><br>
                        @if($user->last_login)
                            {{ $user->last_login->format('M d, Y \a\t H:i') }}
                            <br>
                            <small class="text-muted">{{ $user->last_login->diffForHumans() }}</small>
                        @else
                            <span class="text-muted">Never logged in</span>
                        @endif
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
                        @if(!$user->email_verified_at)
                            <button type="button" 
                                    class="btn btn-outline-success btn-sm"
                                    onclick="markEmailVerified()">
                                <i class="fas fa-check-circle"></i> Mark Email Verified
                            </button>
                        @endif
                        
                        <button type="button" 
                                class="btn btn-outline-info btn-sm"
                                onclick="generateRandomPassword()">
                            <i class="fas fa-random"></i> Generate Random Password
                        </button>
                        
                        <button type="button" 
                                class="btn btn-outline-warning btn-sm"
                                onclick="resetFormToDefaults()">
                            <i class="fas fa-undo"></i> Reset to Original Values
                        </button>
                        
                        <button type="button" 
                                class="btn btn-outline-secondary btn-sm"
                                onclick="validateForm()">
                            <i class="fas fa-check"></i> Validate Form
                        </button>
                    </div>
                </div>
            </div>

            <!-- Role Guide -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-question-circle"></i> Role Guide
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="badge badge-danger">Super Admin</span>
                        <small class="text-muted d-block">System-wide access, all permissions</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge badge-warning">Admin</span>
                        <small class="text-muted d-block">School management, user management</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge badge-info">Instructor</span>
                        <small class="text-muted d-block">Teaching, student management</small>
                    </div>
                    <div>
                        <span class="badge badge-primary">Student</span>
                        <small class="text-muted d-block">Learning, limited access</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function markEmailVerified() {
    if (confirm('Mark this email as verified?')) {
        // In production, make AJAX call to verify email
        alert('Email would be marked as verified.');
    }
}

function generateRandomPassword() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
    let password = '';
    for (let i = 0; i < 12; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    document.getElementById('password').value = password;
    document.getElementById('password_confirmation').value = password;
    alert('Random password generated. Make sure to save it or send it to the user.');
}

function resetFormToDefaults() {
    if (confirm('Reset all fields to their original values?')) {
        document.getElementById('editUserForm').reset();
    }
}

function validateForm() {
    const form = document.getElementById('editUserForm');
    const isValid = form.checkValidity();
    
    if (isValid) {
        alert('Form validation passed. All required fields are filled correctly.');
    } else {
        alert('Form validation failed. Please check required fields.');
        form.reportValidity();
    }
}

// Password confirmation validation
document.getElementById('password_confirmation').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmation = this.value;
    
    if (password && confirmation && password !== confirmation) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});

// Auto-save draft functionality
let autoSaveTimeout;
document.getElementById('editUserForm').addEventListener('input', function() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(function() {
        // In production, save draft to localStorage or send to server
        console.log('Auto-saving draft...');
    }, 2000);
});

// Role change warning
document.getElementById('role').addEventListener('change', function() {
    const originalRole = '{{ $user->role }}';
    if (this.value !== originalRole) {
        alert('Warning: Changing the user role will affect their permissions and access to the system.');
    }
});

// Super admin checkbox warning
const superAdminCheckbox = document.getElementById('is_super_admin');
if (superAdminCheckbox) {
    superAdminCheckbox.addEventListener('change', function() {
        if (this.checked) {
            if (!confirm('Are you sure you want to grant Super Administrator privileges? This gives the user access to all system functions.')) {
                this.checked = false;
            }
        }
    });
}
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

.badge-sm {
    font-size: 0.7em;
    padding: 0.25rem 0.5rem;
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

.text-danger {
    color: #e74a3b !important;
}

.form-text {
    font-size: 0.875em;
}

.form-check-input:checked {
    background-color: #4e73df;
    border-color: #4e73df;
}
</style>
@endpush
@endsection