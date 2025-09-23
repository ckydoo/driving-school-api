{{-- resources/views/admin/schools/create.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Create New School')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.super.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.schools.index') }}">Schools</a></li>
        <li class="breadcrumb-item active">Create School</li>
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
                        <i class="fas fa-plus-circle text-success"></i> Create New School
                    </h1>
                    <p class="mb-0 text-muted">Add a new driving school to the system</p>
                </div>
                <div>
                    <a href="{{ route('admin.schools.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Schools
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Create School Form --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-school"></i> School Information
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.schools.store') }}">
                        @csrf

                        {{-- School Details --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">School Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">School Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone') }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="license_number" class="form-label">License Number</label>
                                <input type="text" class="form-control @error('license_number') is-invalid @enderror"
                                       id="license_number" name="license_number" value="{{ old('license_number') }}">
                                @error('license_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="address" name="address" rows="2" required>{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror"
                                       id="city" name="city" value="{{ old('city') }}" required>
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
                                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="subscription_status" class="form-label">Subscription Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('subscription_status') is-invalid @enderror"
                                        id="subscription_status" name="subscription_status" required>
                                    <option value="">Select Subscription</option>
                                    <option value="trial" {{ old('subscription_status') === 'trial' ? 'selected' : '' }}>Trial</option>
                                    <option value="active" {{ old('subscription_status') === 'active' ? 'selected' : '' }}>Active (Paid)</option>
                                    <option value="suspended" {{ old('subscription_status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    <option value="expired" {{ old('subscription_status') === 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                                @error('subscription_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- School Admin User --}}
                        <h5 class="mb-3">
                            <i class="fas fa-user-shield text-warning"></i> School Administrator Account
                        </h5>
                        <p class="text-muted mb-3">Create the primary administrator account for this school.</p>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="admin_fname" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('admin_fname') is-invalid @enderror"
                                       id="admin_fname" name="admin_fname" value="{{ old('admin_fname') }}" required>
                                @error('admin_fname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="admin_lname" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('admin_lname') is-invalid @enderror"
                                       id="admin_lname" name="admin_lname" value="{{ old('admin_lname') }}" required>
                                @error('admin_lname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="admin_email" class="form-label">Admin Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('admin_email') is-invalid @enderror"
                                       id="admin_email" name="admin_email" value="{{ old('admin_email') }}" required>
                                @error('admin_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="admin_phone" class="form-label">Admin Phone</label>
                                <input type="tel" class="form-control @error('admin_phone') is-invalid @enderror"
                                       id="admin_phone" name="admin_phone" value="{{ old('admin_phone') }}">
                                @error('admin_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="admin_password" class="form-label">Admin Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('admin_password') is-invalid @enderror"
                                       id="admin_password" name="admin_password" required>
                                @error('admin_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Minimum 8 characters required</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="admin_password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control"
                                       id="admin_password_confirmation" name="admin_password_confirmation" required>
                            </div>
                        </div>

                        {{-- Submit Buttons --}}
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.schools.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Create School & Admin
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Help Sidebar --}}
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-question-circle"></i> Help & Tips
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-primary">
                            <i class="fas fa-lightbulb"></i> School Setup
                        </h6>
                        <p class="small text-muted">
                            Enter the school's official information accurately. This will be used for official correspondence and invoicing.
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-warning">
                            <i class="fas fa-user-shield"></i> Administrator Account
                        </h6>
                        <p class="small text-muted">
                            The administrator account will have full access to manage this school's users, schedules, and settings.
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-success">
                            <i class="fas fa-crown"></i> Super Admin Powers
                        </h6>
                        <p class="small text-muted">
                            As a super admin, you can:
                        </p>
                        <ul class="small text-muted">
                            <li>Login as any school admin</li>
                            <li>Activate/deactivate schools</li>
                            <li>View all school data</li>
                            <li>Manage system-wide settings</li>
                        </ul>
                    </div>

                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> After creation, you can login as the school admin to set up their specific configuration.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-generate admin email based on school email
    document.getElementById('email').addEventListener('input', function() {
        const schoolEmail = this.value;
        const adminEmailField = document.getElementById('admin_email');

        if (schoolEmail && !adminEmailField.value) {
            // Extract domain from school email
            const domain = schoolEmail.split('@')[1];
            if (domain) {
                adminEmailField.value = `admin@${domain}`;
            }
        }
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('admin_password').value;
        const confirmPassword = document.getElementById('admin_password_confirmation').value;

        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Admin passwords do not match!');
            document.getElementById('admin_password_confirmation').focus();
        }
    });
</script>
@endpush
@endsection
