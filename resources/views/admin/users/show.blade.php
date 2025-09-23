@extends('admin.layouts.app')

@section('title', 'User Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user"></i> User Details
        </h1>
        <div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit User
            </a>
            <form method="POST" 
                  action="{{ route('admin.users.toggle-status', $user) }}" 
                  style="display: inline;"
                  onsubmit="return confirm('Are you sure you want to change the user status?')">
                @csrf
                <button type="submit" class="btn btn-info btn-sm">
                    <i class="fas fa-toggle-on"></i> Toggle Status
                </button>
            </form>
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
        <!-- User Information -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-circle"></i> Personal Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Full Name:</strong></td>
                                    <td>{{ $user->fname }} {{ $user->lname }}</td>
                                </tr>
                                <tr>
                                    <td><strong>User ID:</strong></td>
                                    <td><code>{{ $user->id }}</code></td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>
                                        <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                                        @if($user->email_verified_at)
                                            <span class="badge badge-success badge-sm ml-1">Verified</span>
                                        @else
                                            <span class="badge badge-warning badge-sm ml-1">Unverified</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>
                                        @if($user->phone)
                                            <a href="tel:{{ $user->phone }}">{{ $user->phone }}</a>
                                        @else
                                            <span class="text-muted">Not provided</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>ID Number:</strong></td>
                                    <td>
                                        @if($user->idnumber)
                                            <code>{{ $user->idnumber }}</code>
                                        @else
                                            <span class="text-muted">Not provided</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Gender:</strong></td>
                                    <td>
                                        @if($user->gender)
                                            {{ ucfirst($user->gender) }}
                                        @else
                                            <span class="text-muted">Not specified</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Date of Birth:</strong></td>
                                    <td>
                                        @if($user->date_of_birth)
                                            {{ $user->date_of_birth->format('F d, Y') }}
                                            <br>
                                            <small class="text-muted">{{ $user->date_of_birth->age }} years old</small>
                                        @else
                                            <span class="text-muted">Not provided</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Address:</strong></td>
                                    <td>
                                        @if($user->address)
                                            {{ $user->address }}
                                        @else
                                            <span class="text-muted">Not provided</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Role:</strong></td>
                                    <td>
                                        @php
                                            $roleClass = match($user->role) {
                                                'super_admin' => 'danger',
                                                'admin' => 'warning',
                                                'instructor' => 'info',
                                                'student' => 'primary',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $roleClass }} badge-lg text-dark">
                                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                        </span>
                                        @if($user->is_super_admin)
                                            <span class="badge badge-danger badge-sm ml-1 text-dark">
                                                <i class="fas fa-crown"></i> Super Admin
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ ($user->status ?? 'active') === 'active' ? 'success' : 'secondary' }} badge-lg text-dark">
                                            {{ ucfirst($user->status ?? 'Active') }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-key"></i> Account Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>School:</strong><br>
                                @if($user->school)
                                    <a href="{{ route('admin.schools.show', $user->school) }}" class="text-decoration-none">
                                        {{ $user->school->name }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $user->school->email }}</small>
                                @else
                                    <span class="text-muted">No school assigned</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Account Created:</strong><br>
                                {{ $user->created_at->format('F d, Y \a\t H:i') }}
                                <br>
                                <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Last Updated:</strong><br>
                                {{ $user->updated_at->format('F d, Y \a\t H:i') }}
                                <br>
                                <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Last Login:</strong><br>
                                @if($user->last_login)
                                    {{ $user->last_login->format('F d, Y \a\t H:i') }}
                                    <br>
                                    <small class="text-muted">{{ $user->last_login->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">Never logged in</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Email Verified:</strong><br>
                                @if($user->email_verified_at)
                                    <span class="text-success">
                                        <i class="fas fa-check-circle"></i> 
                                        {{ $user->email_verified_at->format('M d, Y') }}
                                    </span>
                                @else
                                    <span class="text-warning">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        Not verified
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity & Statistics -->
            @if($user->role === 'student')
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-graduation-cap"></i> Student Progress
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h4 class="text-primary">{{ $stats['total_schedules'] ?? 0 }}</h4>
                            <small class="text-muted">Total Lessons</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-success">{{ $stats['completed_lessons'] ?? 0 }}</h4>
                            <small class="text-muted">Completed</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-warning">{{ $stats['pending_lessons'] ?? 0 }}</h4>
                            <small class="text-muted">Pending</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-info">${{ number_format($stats['total_payments'] ?? 0, 2) }}</h4>
                            <small class="text-muted">Total Paid</small>
                        </div>
                    </div>
                </div>
            </div>
            @elseif($user->role === 'instructor')
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chalkboard-teacher"></i> Instructor Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h4 class="text-primary">{{ $stats['total_students'] ?? 0 }}</h4>
                            <small class="text-muted">Students</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-success">{{ $stats['total_lessons'] ?? 0 }}</h4>
                            <small class="text-muted">Lessons Taught</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-warning">{{ $stats['assigned_vehicles'] ?? 0 }}</h4>
                            <small class="text-muted">Vehicles</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-info">{{ number_format($stats['avg_rating'] ?? 0, 1) }}</h4>
                            <small class="text-muted">Avg Rating</small>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar"></i> Quick Stats
                    </h6>
                </div>
                <div class="card-body">
                    @if($user->role === 'student')
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span><strong>Progress:</strong></span>
                                <span>{{ round(($stats['completed_lessons'] ?? 0) / max(($stats['total_schedules'] ?? 1), 1) * 100) }}%</span>
                            </div>
                            <div class="progress mt-1">
                                <div class="progress-bar bg-success" 
                                     style="width: {{ round(($stats['completed_lessons'] ?? 0) / max(($stats['total_schedules'] ?? 1), 1) * 100) }}%">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span><strong>Outstanding Balance:</strong></span>
                                <span class="text-danger">${{ number_format($stats['outstanding_balance'] ?? 0, 2) }}</span>
                            </div>
                        </div>
                    @elseif($user->role === 'instructor')
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span><strong>Active Students:</strong></span>
                                <span class="text-primary">{{ $stats['active_students'] ?? 0 }}</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span><strong>This Month:</strong></span>
                                <span class="text-success">{{ $stats['monthly_lessons'] ?? 0 }} lessons</span>
                            </div>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><strong>Account Age:</strong></span>
                            <span class="text-info">{{ $user->created_at->diffInDays() }} days</span>
                        </div>
                    </div>
                    
                    <div>
                        <div class="d-flex justify-content-between">
                            <span><strong>Activity Level:</strong></span>
                            @php
                                $activityScore = ($stats['total_schedules'] ?? 0) + ($stats['total_payments'] ?? 0);
                                $activityLevel = $activityScore > 20 ? 'High' : ($activityScore > 10 ? 'Medium' : 'Low');
                                $activityClass = $activityScore > 20 ? 'success' : ($activityScore > 10 ? 'warning' : 'secondary');
                            @endphp
                            <span class="badge badge-{{ $activityClass }}">{{ $activityLevel }}</span>
                        </div>
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
                        <a href="{{ route('admin.users.edit', $user) }}" 
                           class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit User
                        </a>
                        
                        @if($user->role === 'student')
                            <a href="{{ route('admin.users.schedules', $user) }}" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-calendar"></i> View Schedules
                            </a>
                            
                            <a href="{{ route('admin.users.invoices', $user) }}" 
                               class="btn btn-outline-success btn-sm">
                                <i class="fas fa-file-invoice"></i> View Invoices
                            </a>
                        @endif
                        
                        @if($user->role === 'instructor')
                            <a href="{{ route('admin.users.schedules', $user) }}" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-calendar"></i> View Teaching Schedule
                            </a>
                        @endif
                        
                        <button type="button" 
                                class="btn btn-outline-info btn-sm"
                                onclick="sendMessage()">
                            <i class="fas fa-envelope"></i> Send Message
                        </button>
                        
                        @if(!$user->email_verified_at)
                            <button type="button" 
                                    class="btn btn-outline-success btn-sm"
                                    onclick="resendVerification()">
                                <i class="fas fa-check-circle"></i> Resend Verification
                            </button>
                        @endif
                        
                        <button type="button" 
                                class="btn btn-outline-secondary btn-sm"
                                onclick="resetPassword()">
                            <i class="fas fa-key"></i> Reset Password
                        </button>
                        
                        <hr class="my-2">
                        
                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                            @csrf
                            <button type="submit" 
                                    class="btn btn-{{ ($user->status ?? 'active') === 'active' ? 'warning' : 'success' }} btn-sm w-100">
                                <i class="fas fa-{{ ($user->status ?? 'active') === 'active' ? 'pause' : 'play' }}"></i> 
                                {{ ($user->status ?? 'active') === 'active' ? 'Deactivate' : 'Activate' }} User
                            </button>
                        </form>
                        
                        @if($user->role !== 'super_admin' && !$user->is_super_admin)
                            <form method="POST" 
                                  action="{{ route('admin.users.destroy', $user) }}"
                                  onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm w-100">
                                    <i class="fas fa-trash"></i> Delete User
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Recent Activity
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @php
                            // Mock recent activity - in production, fetch from activity logs
                            $activities = [
                                (object)[
                                    'action' => 'Logged in',
                                    'timestamp' => $user->last_login ?? now()->subHours(2),
                                    'icon' => 'fa-sign-in-alt',
                                    'color' => 'success'
                                ],
                                (object)[
                                    'action' => 'Profile updated',
                                    'timestamp' => $user->updated_at,
                                    'icon' => 'fa-edit',
                                    'color' => 'info'
                                ],
                                (object)[
                                    'action' => 'Account created',
                                    'timestamp' => $user->created_at,
                                    'icon' => 'fa-user-plus',
                                    'color' => 'primary'
                                ]
                            ];
                        @endphp
                        
                        @foreach($activities as $activity)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas {{ $activity->icon }} text-{{ $activity->color }}"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="small text-muted">{{ $activity->timestamp->format('M d, H:i') }}</div>
                                    <div>{{ $activity->action }}</div>
                                    <small class="text-muted">{{ $activity->timestamp->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function sendMessage() {
    alert('Message functionality would be implemented here. This could send emails or in-app notifications to the user.');
}

function resendVerification() {
    if (confirm('Send verification email to {{ $user->email }}?')) {
        // In production, make AJAX call to resend verification
        alert('Verification email would be sent to the user.');
    }
}

function resetPassword() {
    if (confirm('Send password reset email to {{ $user->email }}?')) {
        // In production, make AJAX call to send password reset
        alert('Password reset email would be sent to the user.');
    }
}

// Auto-refresh user stats every 5 minutes
setInterval(function() {
    // In production, update stats via AJAX
}, 300000);
</script>
@endpush

@push('styles')
<style>
.table-borderless td {
    padding: 0.5rem 0;
    border: none;
    vertical-align: top;
}

.table-borderless td:first-child {
    width: 140px;
    font-weight: 500;
}

.badge-lg {
    font-size: 0.9em;
    padding: 0.5rem 0.75rem;
}

.badge-sm {
    font-size: 0.7em;
    padding: 0.25rem 0.5rem;
}

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

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.progress {
    height: 8px;
}

.d-grid .btn {
    text-align: left;
}

code {
    color: #6c757d;
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
}
</style>
@endpush
@endsection