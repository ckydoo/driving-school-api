{{-- resources/views/admin/schools/edit.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Edit ' . $school->name)

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.super.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.schools.index') }}">Schools</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.schools.show', $school) }}">{{ $school->name }}</a></li>
        <li class="breadcrumb-item active">Edit</li>
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
                        <i class="fas fa-edit text-primary"></i> Edit School
                    </h1>
                    <p class="mb-0 text-muted">Update {{ $school->name }} information</p>
                </div>
                <div>
                    <a href="{{ route('admin.schools.show', $school) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to School
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit School Form --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-school"></i> School Information
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.schools.update', $school) }}">
                        @csrf
                        @method('PUT')

                        {{-- School Details --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">School Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $school->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">School Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email', $school->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone', $school->phone) }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="license_number" class="form-label">License Number</label>
                                <input type="text" class="form-control @error('license_number') is-invalid @enderror"
                                       id="license_number" name="license_number" value="{{ old('license_number', $school->license_number) }}">
                                @error('license_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="address" name="address" rows="2" required>{{ old('address', $school->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror"
                                       id="city" name="city" value="{{ old('city', $school->city) }}" required>
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">School Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="active" {{ old('status', $school->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $school->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="suspended" {{ old('status', $school->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="subscription_status" class="form-label">Subscription Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('subscription_status') is-invalid @enderror"
                                        id="subscription_status" name="subscription_status" required>
                                    <option value="">Select Subscription</option>
                                    <option value="trial" {{ old('subscription_status', $school->subscription_status) === 'trial' ? 'selected' : '' }}>Trial</option>
                                    <option value="active" {{ old('subscription_status', $school->subscription_status) === 'active' ? 'selected' : '' }}>Active (Paid)</option>
                                    <option value="suspended" {{ old('subscription_status', $school->subscription_status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    <option value="expired" {{ old('subscription_status', $school->subscription_status) === 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                                @error('subscription_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Submit Buttons --}}
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.schools.show', $school) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update School
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Info Sidebar --}}
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle"></i> School Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-primary">
                            <i class="fas fa-calendar-alt"></i> Created
                        </h6>
                        <p class="small text-muted">
                            {{ $school->created_at->format('F d, Y \a\t g:i A') }}<br>
                            <small>({{ $school->created_at->diffForHumans() }})</small>
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-success">
                            <i class="fas fa-users"></i> Current Statistics
                        </h6>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border rounded p-2 mb-2">
                                    <strong class="text-primary">{{ $school->users()->count() }}</strong>
                                    <small class="d-block text-muted">Total Users</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 mb-2">
                                    <strong class="text-success">{{ $school->users()->where('status', 'active')->count() }}</strong>
                                    <small class="d-block text-muted">Active Users</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 mb-2">
                                    <strong class="text-info">{{ $school->users()->where('role', 'student')->count() }}</strong>
                                    <small class="d-block text-muted">Students</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <strong class="text-warning">{{ $school->users()->where('role', 'instructor')->count() }}</strong>
                                    <small class="d-block text-muted">Instructors</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-warning">
                            <i class="fas fa-exclamation-triangle"></i> Important Notes
                        </h6>
                        <ul class="small text-muted">
                            <li>Changing the status to "Inactive" will deactivate all users</li>
                            <li>Suspended schools cannot access the system</li>
                            <li>Email changes may affect login for school administrators</li>
                            <li>License number changes should be verified with authorities</li>
                        </ul>
                    </div>

                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-lightbulb"></i>
                            <strong>Tip:</strong> Contact school administrators before making major changes to ensure smooth operations.
                        </small>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.schools.show', $school) }}" class="btn btn-outline-info">
                            <i class="fas fa-eye"></i> View School Details
                        </a>
                        <a href="{{ route('admin.users.index') }}?school_filter={{ $school->id }}" class="btn btn-outline-success">
                            <i class="fas fa-users"></i> Manage Users
                        </a>
                        @if($school->status === 'active' && $school->users()->where('role', 'admin')->where('status', 'active')->count() > 0)
                            <a href="{{ route('admin.schools.login-as', $school) }}"
                               class="btn btn-outline-warning"
                               onclick="return confirm('Login as {{ $school->name }} admin?')">
                                <i class="fas fa-sign-in-alt"></i> Login as Admin
                            </a>
                        @endif
                        <hr>
                        <form action="{{ route('admin.schools.toggle-status', $school) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="btn btn-outline-{{ $school->status === 'active' ? 'warning' : 'success' }} w-100"
                                    onclick="return confirm('{{ $school->status === 'active' ? 'Deactivate' : 'Activate' }} this school?')">
                                <i class="fas fa-{{ $school->status === 'active' ? 'pause' : 'play' }}"></i>
                                {{ $school->status === 'active' ? 'Deactivate' : 'Activate' }} School
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Form validation and enhancement
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-format phone number
        const phoneInput = document.getElementById('phone');
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
            }
            e.target.value = value;
        });

        // Auto-format ZIP code
        const zipInput = document.getElementById('zip_code');
        zipInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 5) {
                value = value.replace(/(\d{5})(\d{0,4})/, '$1-$2');
            }
            e.target.value = value;
        });

        // Confirm form submission with status change
        const form = document.querySelector('form');
        const statusSelect = document.getElementById('status');
        const originalStatus = '{{ $school->status }}';

        form.addEventListener('submit', function(e) {
            const newStatus = statusSelect.value;

            if (newStatus !== originalStatus) {
                let message = '';
                if (newStatus === 'inactive' && originalStatus === 'active') {
                    message = 'Changing status to "Inactive" will deactivate all school users. Continue?';
                } else if (newStatus === 'suspended') {
                    message = 'Suspending this school will prevent all access. Continue?';
                } else if (newStatus === 'active' && originalStatus !== 'active') {
                    message = 'Activating this school will restore access. Continue?';
                }

                if (message && !confirm(message)) {
                    e.preventDefault();
                }
            }
        });

        // Email validation
        const emailInput = document.getElementById('email');
        emailInput.addEventListener('blur', function() {
            const email = this.value;
            const originalEmail = '{{ $school->email }}';

            if (email !== originalEmail && email.length > 0) {
                if (!confirm('Changing the email address may affect school administrator logins. Continue?')) {
                    this.value = originalEmail;
                }
            }
        });
    });
</script>
@endpush

@push('styles')
<style>
    .text-gray-800 {
        color: #5a5c69 !important;
    }

    .card {
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    .btn-primary {
        background-color: #4e73df;
        border-color: #4e73df;
    }

    .btn-primary:hover {
        background-color: #2e59d9;
        border-color: #2653d4;
    }

    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }
</style>
@endpush
@endsection
