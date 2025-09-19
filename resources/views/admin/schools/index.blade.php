{{-- resources/views/admin/schools/index.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'School Management')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.super.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Schools</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-school text-primary"></i> School Management
                    </h1>
                    <p class="mb-0 text-muted">Manage all driving schools in the system</p>
                </div>
                <div>
                    <a href="{{ route('admin.schools.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Add New School
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters and Search --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.schools.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search Schools</label>
                            <input type="text" class="form-control" id="search" name="search"
                                   value="{{ request('search') }}" placeholder="Name, email, city...">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="subscription_status" class="form-label">Subscription</label>
                            <select class="form-select" id="subscription_status" name="subscription_status">
                                <option value="">All Subscriptions</option>
                                <option value="trial" {{ request('subscription_status') === 'trial' ? 'selected' : '' }}>Trial</option>
                                <option value="active" {{ request('subscription_status') === 'active' ? 'selected' : '' }}>Active (Paid)</option>
                                <option value="suspended" {{ request('subscription_status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="expired" {{ request('subscription_status') === 'expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.schools.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Schools Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list"></i> All Schools ({{ $schools->total() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($schools->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>School</th>
                                        <th>Contact Info</th>
                                        <th>Location</th>
                                        <th>Users</th>
                                        <th>Status</th>
                                        <th>Subscription</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schools as $school)
                                        <tr class="{{ $school->status !== 'active' ? 'table-warning' : '' }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-soft-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                                        <i class="fas fa-school text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">
                                                            <a href="{{ route('admin.schools.show', $school) }}" class="text-decoration-none">
                                                                {{ $school->name }}
                                                            </a>
                                                        </h6>
                                                        @if($school->license_number)
                                                            <small class="text-muted">License: {{ $school->license_number }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <a href="mailto:{{ $school->email }}" class="text-decoration-none d-block">
                                                        <i class="fas fa-envelope text-muted me-1"></i>
                                                        {{ $school->email }}
                                                    </a>
                                                    <a href="tel:{{ $school->phone }}" class="text-decoration-none">
                                                        <i class="fas fa-phone text-muted me-1"></i>
                                                        {{ $school->phone }}
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div>{{ $school->city }}, {{ $school->state }}</div>
                                                    <small class="text-muted">{{ $school->zip_code }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-center">
                                                    <div class="mb-1">
                                                        <span class="badge badge-info">{{ $school->users_count ?? 0 }}</span>
                                                        <small class="text-muted d-block">Total</small>
                                                    </div>
                                                    <div class="small text-muted">
                                                        {{ $school->students_count ?? 0 }} students<br>
                                                        {{ $school->instructors_count ?? 0 }} instructors
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <form action="{{ route('admin.schools.toggle-status', $school) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-{{ $school->status === 'active' ? 'success' : 'secondary' }} border-0"
                                                            onclick="return confirm('Are you sure you want to {{ $school->status === 'active' ? 'deactivate' : 'activate' }} this school?')"
                                                            title="Click to {{ $school->status === 'active' ? 'deactivate' : 'activate' }}">
                                                        <span class="badge badge-{{ $school->status === 'active' ? 'success' : ($school->status === 'suspended' ? 'danger' : 'warning') }}">
                                                            {{ ucfirst($school->status) }}
                                                        </span>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ ($school->subscription_status ?? 'trial') === 'active' ? 'success' : (($school->subscription_status ?? 'trial') === 'trial' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($school->subscription_status ?? 'trial') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <div>{{ $school->created_at->format('M d, Y') }}</div>
                                                    <small class="text-muted">{{ $school->created_at->diffForHumans() }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.schools.show', $school) }}"
                                                       class="btn btn-sm btn-outline-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.schools.edit', $school) }}"
                                                       class="btn btn-sm btn-outline-primary" title="Edit School">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($school->status === 'active' && $school->admins_count > 0)
                                                        <a href="{{ route('admin.schools.login-as', $school) }}"
                                                           class="btn btn-sm btn-outline-warning"
                                                           title="Login as School Admin"
                                                           onclick="return confirm('You will be logged in as this school\'s admin. Continue?')">
                                                            <i class="fas fa-sign-in-alt"></i>
                                                        </a>
                                                    @endif
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Delete School"
                                                            onclick="deleteSchool({{ $school->id }}, '{{ $school->name }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <small class="text-muted">
                                    Showing {{ $schools->firstItem() }} to {{ $schools->lastItem() }} of {{ $schools->total() }} schools
                                </small>
                            </div>
                            <div>
                                {{ $schools->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        {{-- Empty State --}}
                        <div class="text-center py-5">
                            <i class="fas fa-school fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Schools Found</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['search', 'status', 'subscription_status']))
                                    No schools match your current filters.
                                    <a href="{{ route('admin.schools.index') }}" class="text-decoration-none">Clear filters</a>
                                @else
                                    Get started by adding your first driving school to the system.
                                @endif
                            </p>
                            <a href="{{ route('admin.schools.create') }}" class="btn btn-success">
                                <i class="fas fa-plus"></i> Add First School
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Stats Cards --}}
    @if($schools->count() > 0)
    <div class="row mt-4">
        <div class="col-md-3 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Schools</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $schools->where('status', 'active')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Trial Schools</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $schools->where('subscription_status', 'trial')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $schools->sum('users_count') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Students</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $schools->sum('students_count') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger"></i> Confirm Deletion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="schoolName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> This action cannot be undone. All school data, users, and related information will be permanently deleted.
                </div>
                <p class="text-muted">To confirm, type the school name below:</p>
                <input type="text" class="form-control" id="confirmName" placeholder="School name">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" id="confirmDelete" disabled>
                        <i class="fas fa-trash"></i> Delete School
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }
    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }
    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }
    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }
    .text-gray-300 {
        color: #dddfeb !important;
    }
    .text-gray-800 {
        color: #5a5c69 !important;
    }
    .avatar-sm {
        width: 40px;
        height: 40px;
    }
    .bg-soft-primary {
        background-color: rgba(78, 115, 223, 0.1) !important;
    }
    .badge-success {
        background-color: #1cc88a !important;
        color: #fff !important;
    }
    .badge-warning {
        background-color: #f6c23e !important;
        color: #fff !important;
    }
    .badge-danger {
        background-color: #e74a3b !important;
        color: #fff !important;
    }
    .badge-info {
        background-color: #36b9cc !important;
        color: #fff !important;
    }
</style>
@endpush

@push('scripts')
<script>
    function deleteSchool(schoolId, schoolName) {
        // Set school name in modal
        document.getElementById('schoolName').textContent = schoolName;
        document.getElementById('confirmName').value = '';
        document.getElementById('confirmDelete').disabled = true;

        // Set form action
        document.getElementById('deleteForm').action = `{{ route('admin.schools.index') }}/${schoolId}`;

        // Show modal
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    // Enable delete button only when school name is correctly typed
    document.getElementById('confirmName').addEventListener('input', function() {
        const schoolName = document.getElementById('schoolName').textContent;
        const confirmName = this.value;
        const deleteBtn = document.getElementById('confirmDelete');

        if (confirmName === schoolName) {
            deleteBtn.disabled = false;
            deleteBtn.classList.remove('btn-secondary');
            deleteBtn.classList.add('btn-danger');
        } else {
            deleteBtn.disabled = true;
            deleteBtn.classList.remove('btn-danger');
            deleteBtn.classList.add('btn-secondary');
        }
    });

    // Auto-refresh every 30 seconds to show live updates
    setInterval(function() {
        // Only refresh if no modals are open
        if (!document.querySelector('.modal.show')) {
            location.reload();
        }
    }, 30000);

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + N to add new school
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            window.location.href = '{{ route("admin.schools.create") }}';
        }

        // Escape to close modals
        if (e.key === 'Escape') {
            const modal = document.querySelector('.modal.show');
            if (modal) {
                bootstrap.Modal.getInstance(modal).hide();
            }
        }
    });

    // Tooltip initialization for action buttons
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>
@endpush
@endsection
