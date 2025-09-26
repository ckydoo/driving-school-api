{{-- resources/views/admin/subscriptions/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Subscription Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-credit-card"></i> Subscription Management
        </h1>
        <div>
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#bulkAssignModal">
                <i class="fas fa-layer-group"></i> Bulk Assign Package
            </button>
            <a href="{{ route('admin.subscriptions.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Add School
            </a>
        </div>
    </div>

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

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Schools</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_schools'] }}</div>
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
                                Active Subscriptions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_subscriptions'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                Trial Subscriptions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['trial_subscriptions'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                Monthly Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($stats['monthly_revenue'], 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.subscriptions.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="School name or email...">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="subscription_status">Subscription Status</label>
                            <select class="form-control" id="subscription_status" name="subscription_status">
                                <option value="">All Statuses</option>
                                <option value="trial" {{ request('subscription_status') === 'trial' ? 'selected' : '' }}>Trial</option>
                                <option value="active" {{ request('subscription_status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="suspended" {{ request('subscription_status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="cancelled" {{ request('subscription_status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="expired" {{ request('subscription_status') === 'expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="package_id">Package</label>
                            <select class="form-control" id="package_id" name="package_id">
                                <option value="">All Packages</option>
                                @foreach($packages as $package)
                                    <option value="{{ $package->id }}" {{ request('package_id') == $package->id ? 'selected' : '' }}>
                                        {{ $package->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
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

    <!-- Subscriptions List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Schools & Subscriptions ({{ $schools->total() }})
            </h6>
        </div>
        <div class="card-body">
            @if($schools->count() > 0)
                <form id="bulkForm">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="30">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th>School</th>
                                    <th>Package</th>
                                    <th>Status</th>
                                    <th>Usage</th>
                                    <th>Revenue</th>
                                    <th>Expires</th>
                                    <th width="200">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($schools as $school)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="school_ids[]" value="{{ $school->id }}" class="school-checkbox">
                                    </td>
                                    <td>
                                        <div class="font-weight-bold">{{ $school->name }}</div>
                                        <small class="text-muted">{{ $school->email }}</small>
                                        <br>
                                        <small class="text-muted">
                                            {{ $school->users()->count() }} users
                                        </small>
                                    </td>
                                    <td>
                                        @if($school->subscriptionPackage)
                                            <div class="font-weight-bold">{{ $school->subscriptionPackage->name }}</div>
                                            <small class="text-success">
                                                {{ $school->subscriptionPackage->getFormattedMonthlyPrice() }}/month
                                            </small>
                                            @if($school->subscriptionPackage->is_popular)
                                                <br><span class="badge badge-warning">Popular</span>
                                            @endif
                                        @else
                                            <span class="text-muted">No package assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusClasses = [
                                                'trial' => 'warning',
                                                'active' => 'success',
                                                'suspended' => 'secondary',
                                                'cancelled' => 'danger',
                                                'expired' => 'dark'
                                            ];
                                            $statusClass = $statusClasses[$school->subscription_status] ?? 'secondary';
                                        @endphp
                                        <span class="badge badge-{{ $statusClass }}">
                                            {{ ucfirst($school->subscription_status) }}
                                        </span>
                                        @if($school->subscription_status === 'trial' && $school->remaining_trial_days)
                                            <br><small class="text-muted">{{ $school->remaining_trial_days }} days left</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($school->subscriptionPackage)
                                            <div class="small">
                                                <div>Students: {{ $school->getCurrentUsage('max_students') }}{{ $school->subscriptionPackage->isUnlimited('max_students') ? '' : '/' . $school->subscriptionPackage->getLimit('max_students') }}</div>
                                                <div>Instructors: {{ $school->getCurrentUsage('max_instructors') }}{{ $school->subscriptionPackage->isUnlimited('max_instructors') ? '' : '/' . $school->subscriptionPackage->getLimit('max_instructors') }}</div>
                                                <div>Vehicles: {{ $school->getCurrentUsage('max_vehicles') }}{{ $school->subscriptionPackage->isUnlimited('max_vehicles') ? '' : '/' . $school->subscriptionPackage->getLimit('max_vehicles') }}</div>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="font-weight-bold">${{ number_format($school->monthly_fee ?? 0, 2) }}</div>
                                        <small class="text-muted">per month</small>
                                    </td>
                                    <td>
                                        @if($school->subscription_expires_at)
                                            <div class="small">
                                                {{ $school->subscription_expires_at->format('M d, Y') }}
                                                @if($school->subscription_expires_at->isPast())
                                                    <br><span class="text-danger">Expired</span>
                                                @elseif($school->subscription_expires_at->diffInDays() < 7)
                                                    <br><span class="text-warning">Expires soon</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.subscriptions.show', $school) }}" 
                                               class="btn btn-info btn-sm" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.subscriptions.edit', $school) }}" 
                                               class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-sm dropdown-toggle" 
                                                        type="button" 
                                                        data-toggle="dropdown">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <form method="POST" action="{{ route('admin.subscriptions.toggle-status', $school) }}">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fas fa-toggle-on"></i> Toggle Status
                                                        </button>
                                                    </form>
                                                    @if($school->subscriptionPackage)
                                                    <form method="POST" action="{{ route('admin.subscriptions.upgrade-package', $school) }}">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fas fa-arrow-up"></i> Upgrade Package
                                                        </button>
                                                    </form>
                                                    @endif
                                                    <form method="POST" action="{{ route('admin.subscriptions.reset-trial', $school) }}">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fas fa-redo"></i> Reset Trial
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        Showing {{ $schools->firstItem() }} to {{ $schools->lastItem() }} of {{ $schools->total() }} results
                    </div>
                    {{ $schools->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-school fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-500">No schools found</h5>
                    <p class="text-gray-400">Try adjusting your search criteria or add a new school.</p>
                    <a href="{{ route('admin.subscriptions.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add First School
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Assign Package Modal -->
<div class="modal fade" id="bulkAssignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Assign Package</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.subscriptions.bulk-assign-package') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="bulk_package_id" class="font-weight-bold">Select Package</label>
                        <select class="form-control" id="bulk_package_id" name="package_id" required>
                            <option value="">Choose a package...</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}">
                                    {{ $package->name }} ({{ $package->getFormattedMonthlyPrice() }}/month)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="bulk_billing_period" class="font-weight-bold">Billing Period</label>
                        <select class="form-control" id="bulk_billing_period" name="billing_period" required>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> This will update the selected schools to use the chosen package and set their status to 'active'.
                    </div>
                    
                    <div id="selectedSchoolsList">
                        <!-- Selected schools will be populated by JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Package</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select All functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const schoolCheckboxes = document.querySelectorAll('.school-checkbox');
    const bulkAssignBtn = document.querySelector('[data-target="#bulkAssignModal"]');
    
    selectAllCheckbox.addEventListener('change', function() {
        schoolCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkAssignButton();
    });

    schoolCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAll();
            updateBulkAssignButton();
        });
    });

    function updateSelectAll() {
        const checkedBoxes = document.querySelectorAll('.school-checkbox:checked');
        selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < schoolCheckboxes.length;
        selectAllCheckbox.checked = checkedBoxes.length === schoolCheckboxes.length;
    }

    function updateBulkAssignButton() {
        const checkedBoxes = document.querySelectorAll('.school-checkbox:checked');
        bulkAssignBtn.disabled = checkedBoxes.length === 0;
        
        if (checkedBoxes.length > 0) {
            bulkAssignBtn.innerHTML = `<i class="fas fa-layer-group"></i> Bulk Assign Package (${checkedBoxes.length})`;
        } else {
            bulkAssignBtn.innerHTML = `<i class="fas fa-layer-group"></i> Bulk Assign Package`;
        }
    }

    // Bulk assign modal functionality
    const bulkAssignModal = document.getElementById('bulkAssignModal');
    const selectedSchoolsList = document.getElementById('selectedSchoolsList');

    bulkAssignModal.addEventListener('show.bs.modal', function() {
        const checkedBoxes = document.querySelectorAll('.school-checkbox:checked');
        let schoolsHtml = '';
        let hiddenInputs = '';

        checkedBoxes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            const schoolName = row.querySelector('td:nth-child(2) .font-weight-bold').textContent;
            schoolsHtml += `<div class="badge badge-primary mr-1 mb-1">${schoolName}</div>`;
            hiddenInputs += `<input type="hidden" name="school_ids[]" value="${checkbox.value}">`;
        });

        selectedSchoolsList.innerHTML = `
            <div class="form-group">
                <label class="font-weight-bold">Selected Schools (${checkedBoxes.length}):</label>
                <div class="mt-2">${schoolsHtml}</div>
                ${hiddenInputs}
            </div>
        `;
    });

    // Auto-submit filters on change
    document.getElementById('subscription_status').addEventListener('change', function() {
        if (this.value) {
            this.form.submit();
        }
    });

    document.getElementById('package_id').addEventListener('change', function() {
        if (this.value) {
            this.form.submit();
        }
    });

    // Confirmation dialogs for actions
    document.querySelectorAll('form[action*="toggle-status"], form[action*="upgrade-package"], form[action*="reset-trial"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const action = this.action.includes('toggle-status') ? 'toggle status' :
                          this.action.includes('upgrade-package') ? 'upgrade package' :
                          this.action.includes('reset-trial') ? 'reset trial' : 'perform this action';
            
            if (!confirm(`Are you sure you want to ${action} for this school?`)) {
                e.preventDefault();
            }
        });
    });

    // Initialize bulk button state
    updateBulkAssignButton();
});
</script>
@endpush