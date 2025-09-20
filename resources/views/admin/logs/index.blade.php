@extends('admin.layouts.app')

@section('title', 'Activity Logs')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-list-alt"></i> Activity Logs
        </h1>
        <div>
            <button type="button" class="btn btn-warning btn-sm" onclick="clearLogs()">
                <i class="fas fa-trash"></i> Clear Logs
            </button>
            <div class="btn-group">
                <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.logs.export', 'csv') }}">Export as CSV</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.logs.export', 'json') }}">Export as JSON</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.logs.export', 'pdf') }}">Export as PDF</a></li>
                </ul>
            </div>
            <button type="button" class="btn btn-primary btn-sm" onclick="refreshLogs()">
                <i class="fas fa-sync"></i> Refresh
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

    <!-- Log Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Logs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($logs->count()) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
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
                                Today's Activity
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $logs->where('timestamp', '>=', now()->startOfDay())->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
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
                                Active Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $logs->pluck('user')->unique()->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                System Actions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $logs->whereIn('action', ['System Updated', 'Cache Cleared', 'Backup Created'])->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cogs fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Logs</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.logs.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Search actions or users...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="action_type">Action Type</label>
                            <select class="form-control" id="action_type" name="action_type">
                                <option value="">All Actions</option>
                                <option value="login" {{ request('action_type') === 'login' ? 'selected' : '' }}>Login</option>
                                <option value="logout" {{ request('action_type') === 'logout' ? 'selected' : '' }}>Logout</option>
                                <option value="create" {{ request('action_type') === 'create' ? 'selected' : '' }}>Create</option>
                                <option value="update" {{ request('action_type') === 'update' ? 'selected' : '' }}>Update</option>
                                <option value="delete" {{ request('action_type') === 'delete' ? 'selected' : '' }}>Delete</option>
                                <option value="system" {{ request('action_type') === 'system' ? 'selected' : '' }}>System</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="date_from">Date From</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="date_from" 
                                   name="date_from" 
                                   value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="date_to">Date To</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="date_to" 
                                   name="date_to" 
                                   value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Activity Logs Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Activity Logs</h6>
        </div>
        <div class="card-body">
            @if($logs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="logsTable">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>IP Address</th>
                                <th>User Agent</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                            <tr>
                                <td>
                                    <div>{{ $log->timestamp->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $log->timestamp->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ $log->user }}</div>
                                    @php
                                        $actionClass = match(true) {
                                            str_contains(strtolower($log->action), 'login') => 'success',
                                            str_contains(strtolower($log->action), 'logout') => 'info',
                                            str_contains(strtolower($log->action), 'delete') => 'danger',
                                            str_contains(strtolower($log->action), 'create') => 'primary',
                                            str_contains(strtolower($log->action), 'update') => 'warning',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $actionClass }} badge-sm">
                                        {{ ucfirst(explode(' ', strtolower($log->action))[0] ?? 'action') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ $log->action }}</div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $log->details ?? 'No details available' }}">
                                        {{ $log->details ?? 'No details available' }}
                                    </div>
                                </td>
                                <td>
                                    <code class="small">{{ $log->ip_address ?? '127.0.0.1' }}</code>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 150px;" title="{{ $log->user_agent ?? 'Unknown' }}">
                                        {{ $log->user_agent ?? 'Unknown' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.logs.show', $log->id) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form method="POST" 
                                              action="{{ route('admin.logs.destroy', $log->id) }}" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Are you sure you want to delete this log entry?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination would go here if using real pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Showing {{ $logs->count() }} log entries
                    </div>
                    <div>
                        <!-- Add pagination links here when implementing real logs -->
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-list-alt fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">No activity logs found</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'action_type', 'date_from', 'date_to']))
                            No logs match your current filters.
                        @else
                            System activity will appear here as users interact with the application.
                        @endif
                    </p>
                    @if(request()->hasAny(['search', 'action_type', 'date_from', 'date_to']))
                        <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Log Analytics -->
    @if($logs->count() > 0)
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Users by Activity</h6>
                </div>
                <div class="card-body">
                    @php
                        $topUsers = $logs->groupBy('user')->map(function($userLogs) {
                            return $userLogs->count();
                        })->sortDesc()->take(5);
                    @endphp
                    
                    @foreach($topUsers as $user => $count)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong>{{ $user }}</strong>
                        </div>
                        <div>
                            <span class="badge badge-primary">{{ $count }} actions</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Most Common Actions</h6>
                </div>
                <div class="card-body">
                    @php
                        $topActions = $logs->groupBy('action')->map(function($actionLogs) {
                            return $actionLogs->count();
                        })->sortDesc()->take(5);
                    @endphp
                    
                    @foreach($topActions as $action => $count)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong>{{ $action }}</strong>
                        </div>
                        <div>
                            <span class="badge badge-info">{{ $count }} times</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function refreshLogs() {
    location.reload();
}

function clearLogs() {
    if (confirm('Are you sure you want to clear all activity logs? This action cannot be undone.')) {
        fetch('{{ route("admin.logs.clear") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (response.ok) {
                alert('All logs cleared successfully!');
                location.reload();
            } else {
                alert('Error clearing logs. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error clearing logs. Please try again.');
        });
    }
}

// DataTable initialization
$(document).ready(function() {
    $('#logsTable').DataTable({
        "pageLength": 25,
        "responsive": true,
        "searching": false, // We have custom search
        "paging": true,
        "info": true,
        "order": [[ 0, "desc" ]], // Sort by timestamp descending
        "columnDefs": [
            { "orderable": false, "targets": 6 }, // Actions column not sortable
            { "type": "date", "targets": 0 } // Timestamp column
        ]
    });
});

// Auto-refresh every 30 seconds
setInterval(function() {
    if (!document.hidden) {
        // Only refresh if page is visible
        location.reload();
    }
}, 30000);

// Real-time search
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(() => {
        document.getElementById('filterForm').submit();
    }, 500);
});
</script>
@endpush

@push('styles')
<style>
.badge-sm {
    font-size: 0.7em;
}

.text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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

code {
    color: #6c757d;
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>
@endpush
@endsection