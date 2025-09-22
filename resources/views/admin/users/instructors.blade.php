{{-- resources/views/admin/users/instructors.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Instructors')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Instructors</h1>
            <p class="text-muted mb-0">Manage instructor accounts</p>
        </div>
        <div>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Instructor
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Instructors
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_instructors'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Instructors
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['active_instructors'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Inactive Instructors
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['inactive_instructors'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-times fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.instructors.index') }}" class="row align-items-end">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text"
                           id="search"
                           name="search"
                           class="form-control"
                           placeholder="Search by name, email, or phone"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                @if($currentUser->isSuperAdmin())
                <div class="col-md-3">
                    <label for="school_id" class="form-label">School</label>
                    <select id="school_id" name="school_id" class="form-control">
                        <option value="">All Schools</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Instructors Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Instructors List</h6>
        </div>
        <div class="card-body">
            @if($instructors->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="instructorsTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                @if($currentUser->isSuperAdmin())
                                <th>School</th>
                                @endif
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($instructors as $instructor)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm rounded-circle bg-success text-white me-2">
                                                {{ substr($instructor->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <strong>{{ $instructor->name }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $instructor->email }}</td>
                                    <td>{{ $instructor->phone ?: 'N/A' }}</td>
                                    @if($currentUser->isSuperAdmin())
                                    <td>
                                        @if($instructor->school)
                                            <span class="badge badge-info">{{ $instructor->school->name }}</span>
                                        @else
                                            <span class="text-muted">No school</span>
                                        @endif
                                    </td>
                                    @endif
                                    <td>
                                        <span class="badge badge-{{ $instructor->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($instructor->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $instructor->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.users.show', $instructor) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.users.edit', $instructor) }}"
                                               class="btn btn-sm btn-outline-secondary"
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.users.toggle-status', $instructor) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-{{ $instructor->status === 'active' ? 'warning' : 'success' }}"
                                                        title="{{ $instructor->status === 'active' ? 'Deactivate' : 'Activate' }}"
                                                        onclick="return confirm('Are you sure you want to {{ $instructor->status === 'active' ? 'deactivate' : 'activate' }} this instructor?')">
                                                    <i class="fas fa-{{ $instructor->status === 'active' ? 'user-times' : 'user-check' }}"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <p class="text-muted mb-0">
                            Showing {{ $instructors->firstItem() }} to {{ $instructors->lastItem() }}
                            of {{ $instructors->total() }} results
                        </p>
                    </div>
                    <div>
                        {{ $instructors->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-chalkboard-teacher fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-muted">No instructors found</h5>
                    <p class="text-muted mb-4">
                        @if(request()->hasAny(['search', 'status', 'school_id']))
                            Try adjusting your search criteria or
                            <a href="{{ route('admin.instructors.index') }}">clear all filters</a>.
                        @else
                            Get started by adding your first instructor.
                        @endif
                    </p>
                    @if(!request()->hasAny(['search', 'status', 'school_id']))
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add First Instructor
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.avatar {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form on filter change
    document.querySelectorAll('#status, #school_id').forEach(function(select) {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });

    // Clear search functionality
    const searchInput = document.getElementById('search');
    if (searchInput && searchInput.value) {
        const clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'btn btn-outline-secondary btn-sm ms-2';
        clearBtn.innerHTML = '<i class="fas fa-times"></i>';
        clearBtn.onclick = function() {
            searchInput.value = '';
            searchInput.form.submit();
        };
        searchInput.parentNode.appendChild(clearBtn);
    }
});
</script>
@endpush
