@extends('admin.layouts.app')

@section('title', 'Subscription Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-credit-card"></i> Subscription Details
        </h1>
        <div>
            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Subscriptions
            </a>
            <a href="{{ route('admin.subscriptions.edit', $subscription) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form method="POST" 
                  action="{{ route('admin.subscriptions.toggle-status', $subscription) }}" 
                  style="display: inline;"
                  onsubmit="return confirm('Are you sure you want to change the subscription status?')">
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

    <!-- School Information -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-school"></i> School Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>School Name:</strong></td>
                                    <td>{{ $subscription->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>School ID:</strong></td>
                                    <td><code>{{ $subscription->id }}</code></td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>
                                        <a href="mailto:{{ $subscription->email }}">{{ $subscription->email }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>
                                        @if($subscription->phone)
                                            <a href="tel:{{ $subscription->phone }}">{{ $subscription->phone }}</a>
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
                                    <td><strong>Address:</strong></td>
                                    <td>
                                        @if($subscription->address)
                                            {{ $subscription->address }}
                                        @else
                                            <span class="text-muted">Not provided</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>
                                        {{ $subscription->created_at->format('M d, Y \a\t H:i') }}
                                        <br>
                                        <small class="text-muted">{{ $subscription->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>
                                        {{ $subscription->updated_at->format('M d, Y \a\t H:i') }}
                                        <br>
                                        <small class="text-muted">{{ $subscription->updated_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @php
                                            $statusClass = match($subscription->subscription_status) {
                                                'active' => 'success',
                                                'trial' => 'info',
                                                'suspended' => 'warning',
                                                'expired' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $statusClass }} badge-lg">
                                            {{ ucfirst($subscription->subscription_status) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-alt"></i> Subscription Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Subscription Status:</strong><br>
                                <span class="badge badge-{{ $statusClass }} badge-lg">
                                    {{ ucfirst($subscription->subscription_status) }}
                                </span>
                                @if($subscription->subscription_status === 'active')
                                    <small class="text-success d-block mt-1">
                                        <i class="fas fa-check-circle"></i> Full access to all features
                                    </small>
                                @elseif($subscription->subscription_status === 'trial')
                                    <small class="text-info d-block mt-1">
                                        <i class="fas fa-clock"></i> Trial period active
                                    </small>
                                @elseif($subscription->subscription_status === 'suspended')
                                    <small class="text-warning d-block mt-1">
                                        <i class="fas fa-pause-circle"></i> Access temporarily suspended
                                    </small>
                                @elseif($subscription->subscription_status === 'expired')
                                    <small class="text-danger d-block mt-1">
                                        <i class="fas fa-times-circle"></i> Subscription has expired
                                    </small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Expiration Date:</strong><br>
                                @if($subscription->subscription_expires_at)
                                    <span class="{{ $subscription->subscription_expires_at->isPast() ? 'text-danger' : 'text-success' }}">
                                        {{ $subscription->subscription_expires_at->format('F d, Y \a\t H:i') }}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        {{ $subscription->subscription_expires_at->diffForHumans() }}
                                    </small>
                                @else
                                    <span class="text-success">No expiration</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($subscription->subscription_expires_at && $subscription->subscription_expires_at->isPast())
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Expired Subscription!</strong> This subscription expired {{ $subscription->subscription_expires_at->diffForHumans() }}.
                            The school may have limited access to features.
                        </div>
                    @elseif($subscription->subscription_expires_at && $subscription->subscription_expires_at->diffInDays() <= 7)
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i>
                            <strong>Expiring Soon!</strong> This subscription will expire {{ $subscription->subscription_expires_at->diffForHumans() }}.
                            Consider renewing to avoid service interruption.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistics Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar"></i> Usage Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h3 class="text-primary">{{ $stats['total_users'] ?? 0 }}</h3>
                        <small class="text-muted">Total Users</small>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="mb-3">
                                <h4 class="text-info">{{ $stats['students'] ?? 0 }}</h4>
                                <small class="text-muted">Students</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <h4 class="text-warning">{{ $stats['instructors'] ?? 0 }}</h4>
                                <small class="text-muted">Instructors</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="mb-3">
                                <h4 class="text-success">{{ $stats['admins'] ?? 0 }}</h4>
                                <small class="text-muted">Admins</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <h4 class="text-secondary">{{ $stats['vehicles'] ?? 0 }}</h4>
                                <small class="text-muted">Vehicles</small>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="text-center">
                        <h4 class="text-primary">{{ $stats['courses'] ?? 0 }}</h4>
                        <small class="text-muted">Active Courses</small>
                    </div>
                </div>
            </div>

            <!-- Revenue Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-dollar-sign"></i> Revenue Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><strong>Monthly Revenue:</strong></span>
                            <span class="text-success">${{ number_format($stats['monthly_revenue'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><strong>Total Revenue:</strong></span>
                            <span class="text-primary">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><strong>Avg. per User:</strong></span>
                            <span class="text-info">
                                ${{ $stats['total_users'] > 0 ? number_format(($stats['total_revenue'] ?? 0) / $stats['total_users'], 2) : '0.00' }}
                            </span>
                        </div>
                    </div>
                    
                    @if(($stats['monthly_revenue'] ?? 0) > 0)
                    <div class="progress mb-2">
                        @php
                            $monthlyTarget = 5000; // Example monthly target
                            $progressPercent = min(100, (($stats['monthly_revenue'] ?? 0) / $monthlyTarget) * 100);
                        @endphp
                        <div class="progress-bar bg-success" 
                             role="progressbar" 
                             style="width: {{ $progressPercent }}%">
                            {{ round($progressPercent) }}%
                        </div>
                    </div>
                    <small class="text-muted">Monthly target: ${{ number_format($monthlyTarget, 2) }}</small>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tools"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.schools.show', $subscription) }}" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-school"></i> Manage School
                        </a>
                        
                        <a href="{{ route('admin.users.index', ['school_id' => $subscription->id]) }}" 
                           class="btn btn-outline-info btn-sm">
                            <i class="fas fa-users"></i> View Users
                        </a>
                        
                        <button type="button" 
                                class="btn btn-outline-success btn-sm"
                                onclick="sendNotification()">
                            <i class="fas fa-envelope"></i> Send Notification
                        </button>
                        
                        <button type="button" 
                                class="btn btn-outline-warning btn-sm"
                                onclick="generateReport()">
                            <i class="fas fa-file-alt"></i> Generate Report
                        </button>
                        
                        <hr class="my-2">
                        
                        @if($subscription->subscription_status === 'suspended')
                            <form method="POST" action="{{ route('admin.subscriptions.toggle-status', $subscription) }}">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm w-100">
                                    <i class="fas fa-play"></i> Reactivate Subscription
                                </button>
                            </form>
                        @elseif($subscription->subscription_status === 'active')
                            <form method="POST" action="{{ route('admin.subscriptions.toggle-status', $subscription) }}">
                                @csrf
                                <button type="submit" 
                                        class="btn btn-warning btn-sm w-100"
                                        onclick="return confirm('Are you sure you want to suspend this subscription?')">
                                    <i class="fas fa-pause"></i> Suspend Subscription
                                </button>
                            </form>
                        @endif
                        
                        <form method="POST" 
                              action="{{ route('admin.subscriptions.destroy', $subscription) }}"
                              onsubmit="return confirm('Are you sure you want to delete this school? This action cannot be undone and will remove all associated data.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                <i class="fas fa-trash"></i> Delete School
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users List -->
    @if($subscription->users && $subscription->users->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-users"></i> School Users ({{ $subscription->users->count() }})
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subscription->users->take(10) as $user)
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $user->fname }} {{ $user->lname }}</div>
                                <small class="text-muted">ID: {{ $user->id }}</small>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @php
                                    $roleClass = match($user->role) {
                                        'admin' => 'danger',
                                        'instructor' => 'warning',
                                        'student' => 'info',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge badge-{{ $roleClass }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($user->status ?? 'inactive') }}
                                </span>
                            </td>
                            <td>
                                @if($user->last_login)
                                    {{ $user->last_login->diffForHumans() }}
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.users.show', $user) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($subscription->users->count() > 10)
                <div class="text-center mt-3">
                    <a href="{{ route('admin.users.index', ['school_id' => $subscription->id]) }}" 
                       class="btn btn-primary">
                        View All {{ $subscription->users->count() }} Users
                    </a>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function sendNotification() {
    alert('Notification feature would be implemented here. This could send emails or in-app notifications to the school administrators.');
}

function generateReport() {
    alert('Report generation feature would be implemented here. This could create PDF reports with school usage statistics.');
}

// Auto-refresh stats every 5 minutes
setInterval(function() {
    // In production, you might update stats via AJAX without full page reload
}, 300000);
</script>
@endpush

@push('styles')
<style>
.table td {
    vertical-align: middle;
}

.badge-lg {
    font-size: 0.9em;
    padding: 0.5rem 0.75rem;
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

.table th {
    border-top: none;
}

.alert {
    border-left: 4px solid;
}

.alert-danger {
    border-left-color: #e74a3b;
}

.alert-warning {
    border-left-color: #f6c23e;
}

.table-borderless td {
    padding: 0.5rem 0;
    border: none;
    vertical-align: top;
}

.table-borderless td:first-child {
    width: 140px;
    font-weight: 500;
}
</style>
@endpush
@endsection