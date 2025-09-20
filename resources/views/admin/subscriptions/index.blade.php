@extends('admin.layouts.app')

@section('title', 'Subscription Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-credit-card"></i> Subscription Management
        </h1>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Schools
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_schools']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-school fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Subscriptions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['active_subscriptions']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Trial Subscriptions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['trial_subscriptions']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Suspended/Expired
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['suspended_subscriptions'] + $stats['expired_subscriptions']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Subscriptions</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.subscriptions.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="search">Search Schools</label>
                            <input type="text"
                                   class="form-control"
                                   id="search"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Search by name or email...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="subscription_status">Subscription Status</label>
                            <select class="form-control" id="subscription_status" name="subscription_status">
                                <option value="">All Statuses</option>
                                <option value="trial" {{ request('subscription_status') === 'trial' ? 'selected' : '' }}>
                                    Trial
                                </option>
                                <option value="active" {{ request('subscription_status') === 'active' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="suspended" {{ request('subscription_status') === 'suspended' ? 'selected' : '' }}>
                                    Suspended
                                </option>
                                <option value="expired" {{ request('subscription_status') === 'expired' ? 'selected' : '' }}>
                                    Expired
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Schools Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Schools & Subscriptions</h6>
        </div>
        <div class="card-body">
            @if($schools->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="subscriptionsTable">
                        <thead>
                            <tr>
                                <th>School Name</th>
                                <th>Contact Info</th>
                                <th>Users</th>
                                <th>Subscription Status</th>
                                <th>Expires</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schools as $school)
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ $school->name }}</div>
                                    <small class="text-muted">ID: {{ $school->id }}</small>
                                </td>
                                <td>
                                    <div>
                                        <i class="fas fa-envelope text-muted"></i>
                                        {{ $school->email }}
                                    </div>
                                    @if($school->phone)
                                    <div class="mt-1">
                                        <i class="fas fa-phone text-muted"></i>
                                        {{ $school->phone }}
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-center">
                                        <span class="badge badge-info badge-pill">
                                            {{ $school->users->count() }} users
                                        </span>
                                        <div class="small text-muted mt-1">
                                            {{ $school->users->where('role', 'student')->count() }} students<br>
                                            {{ $school->users->where('role', 'instructor')->count() }} instructors
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($school->subscription_status) {
                                            'active' => 'success',
                                            'trial' => 'info',
                                            'suspended' => 'warning',
                                            'expired' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $statusClass }}">
                                        {{ ucfirst($school->subscription_status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($school->subscription_expires_at)
                                        <div class="{{ $school->subscription_expires_at->isPast() ? 'text-danger' : '' }}">
                                            {{ $school->subscription_expires_at->format('M d, Y') }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $school->subscription_expires_at->diffForHumans() }}
                                        </small>
                                    @else
                                        <span class="text-muted">No expiry</span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $school->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $school->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.subscriptions.show', $school) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.subscriptions.edit', $school) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('admin.subscriptions.toggle-status', $school) }}"
                                              style="display: inline;"
                                              onsubmit="return confirm('Are you sure you want to change the subscription status?')">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-warning"
                                                    title="Toggle Status">
                                                <i class="fas fa-toggle-on"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Dropdown for more actions -->
                                    <div class="dropdown mt-1">
                                        <button class="btn btn-sm btn-outline-info dropdown-toggle"
                                                type="button"
                                                data-toggle="dropdown">
                                            More
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin.schools.show', $school) }}">
                                                <i class="fas fa-school"></i> Manage School
                                            </a>
                                            <a class="dropdown-item" href="{{ route('admin.users.index', ['school_id' => $school->id]) }}">
                                                <i class="fas fa-users"></i> View Users
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <form method="POST"
                                                  action="{{ route('admin.subscriptions.destroy', $school) }}"
                                                  onsubmit="return confirm('Are you sure you want to delete this school? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash"></i> Delete School
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Showing {{ $schools->firstItem() }} to {{ $schools->lastItem() }} of {{ $schools->total() }} results
                    </div>
                    <div>
                        {{ $schools->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-school fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">No schools found</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'subscription_status']))
                            No schools match your current filters.
                        @else
                            Get started by adding your first school.
                        @endif
                    </p>
                    @if(!request()->hasAny(['search', 'subscription_status']))
                        <a href="{{ route('admin.subscriptions.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add First School
                        </a>
                    @else
                        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Bulk Actions Card (if needed) -->
    @if($schools->count() > 0)
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bulk Actions</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Quick Stats</h6>
                    <ul class="list-unstyled">
                        <li><strong>Active:</strong> {{ $stats['active_subscriptions'] }} schools</li>
                        <li><strong>Trial:</strong> {{ $stats['trial_subscriptions'] }} schools</li>
                        <li><strong>Issues:</strong> {{ $stats['suspended_subscriptions'] + $stats['expired_subscriptions'] }} schools</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>System Actions</h6>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-info" onclick="exportSubscriptions()">
                            <i class="fas fa-download"></i> Export Data
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="refreshSubscriptions()">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function exportSubscriptions() {
    // Implement export functionality
    alert('Export functionality coming soon!');
}

function refreshSubscriptions() {
    window.location.reload();
}

// Auto-refresh every 5 minutes for real-time updates
setTimeout(function() {
    window.location.reload();
}, 300000);

// DataTable initialization if you want sorting/filtering
$(document).ready(function() {
    $('#subscriptionsTable').DataTable({
        "pageLength": 25,
        "responsive": true,
        "searching": false, // We have custom search
        "paging": false,    // We use Laravel pagination
        "info": false,      // We show custom info
        "order": [[ 5, "desc" ]], // Sort by created date
        "columnDefs": [
            { "orderable": false, "targets": 6 } // Actions column not sortable
        ]
    });
});
</script>
@endpush

@push('styles')
<style>
.badge-pill {
    border-radius: 10rem;
}

.dropdown-menu {
    min-width: 160px;
}

.table td {
    vertical-align: middle;
}

.btn-group .btn {
    border-radius: 0;
}

.btn-group .btn:first-child {
    border-top-left-radius: 0.25rem;
    border-bottom-left-radius: 0.25rem;
}

.btn-group .btn:last-child {
    border-top-right-radius: 0.25rem;
    border-bottom-right-radius: 0.25rem;
}
</style>
@endpush
@endsection
