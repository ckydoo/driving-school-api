{{-- resources/views/admin/subscriptions/edit.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Edit Subscription: ' . $subscription->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit"></i> Edit Subscription: {{ $subscription->name }}
        </h1>
        <div>
            <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="btn btn-info btn-sm">
                <i class="fas fa-eye"></i> View Details
            </a>
            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Subscriptions
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <!-- Edit Form -->
        <div class="col-lg-8">
            <form method="POST" action="{{ route('admin.subscriptions.update', $subscription) }}">
                @csrf
                @method('PUT')
                
                <!-- Basic School Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-school"></i> School Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="font-weight-bold">School Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $subscription->name) }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="font-weight-bold">Email <span class="text-danger">*</span></label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $subscription->email) }}" 
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone" class="font-weight-bold">Phone</label>
                                    <input type="text" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone', $subscription->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status" class="font-weight-bold">School Status</label>
                                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                        <option value="active" {{ old('status', $subscription->status) === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $subscription->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="suspended" {{ old('status', $subscription->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address" class="font-weight-bold">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" 
                                      name="address" 
                                      rows="3">{{ old('address', $subscription->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Subscription Package Assignment -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-box"></i> Subscription Package
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subscription_package_id" class="font-weight-bold">Subscription Package <span class="text-danger">*</span></label>
                                    <select class="form-control @error('subscription_package_id') is-invalid @enderror" 
                                            id="subscription_package_id" 
                                            name="subscription_package_id" 
                                            required>
                                        <option value="">Select a package...</option>
                                        @foreach($packages as $package)
                                            <option value="{{ $package->id }}" 
                                                    {{ old('subscription_package_id', $subscription->subscription_package_id) == $package->id ? 'selected' : '' }}
                                                    data-monthly-price="{{ $package->monthly_price }}"
                                                    data-yearly-price="{{ $package->yearly_price }}"
                                                    data-limits="{{ json_encode($package->limits) }}">
                                                {{ $package->name }} 
                                                ({{ $package->getFormattedMonthlyPrice() }}/month)
                                                @if($package->hasYearlyPricing())
                                                    - {{ $package->getFormattedYearlyPrice() }}/year
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('subscription_package_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="billing_period" class="font-weight-bold">Billing Period</label>
                                    <select class="form-control @error('billing_period') is-invalid @enderror" 
                                            id="billing_period" 
                                            name="billing_period">
                                        <option value="monthly" {{ old('billing_period', 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="yearly" {{ old('billing_period') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                                    </select>
                                    @error('billing_period')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Package Preview -->
                        <div id="package-preview" style="display: none;">
                            <div class="alert alert-info">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Monthly Price:</strong>
                                        <span id="preview-monthly-price">-</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Yearly Price:</strong>
                                        <span id="preview-yearly-price">-</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Yearly Savings:</strong>
                                        <span id="preview-savings">-</span>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <strong>Limits:</strong>
                                        <span id="preview-limits">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscription Status -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-calendar-alt"></i> Subscription Status
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="subscription_status" class="font-weight-bold">Subscription Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('subscription_status') is-invalid @enderror" 
                                            id="subscription_status" 
                                            name="subscription_status" 
                                            required>
                                        <option value="trial" {{ old('subscription_status', $subscription->subscription_status) === 'trial' ? 'selected' : '' }}>Trial</option>
                                        <option value="active" {{ old('subscription_status', $subscription->subscription_status) === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="suspended" {{ old('subscription_status', $subscription->subscription_status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                        <option value="cancelled" {{ old('subscription_status', $subscription->subscription_status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        <option value="expired" {{ old('subscription_status', $subscription->subscription_status) === 'expired' ? 'selected' : '' }}>Expired</option>
                                    </select>
                                    @error('subscription_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="trial_ends_at" class="font-weight-bold">Trial Ends At</label>
                                    <input type="datetime-local" 
                                           class="form-control @error('trial_ends_at') is-invalid @enderror" 
                                           id="trial_ends_at" 
                                           name="trial_ends_at" 
                                           value="{{ old('trial_ends_at', $subscription->trial_ends_at ? $subscription->trial_ends_at->format('Y-m-d\TH:i') : '') }}">
                                    @error('trial_ends_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="subscription_expires_at" class="font-weight-bold">Subscription Expires At</label>
                                    <input type="datetime-local" 
                                           class="form-control @error('subscription_expires_at') is-invalid @enderror" 
                                           id="subscription_expires_at" 
                                           name="subscription_expires_at" 
                                           value="{{ old('subscription_expires_at', $subscription->subscription_expires_at ? $subscription->subscription_expires_at->format('Y-m-d\TH:i') : '') }}">
                                    @error('subscription_expires_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="monthly_fee" class="font-weight-bold">Monthly Fee ($)</label>
                                    <input type="number" 
                                           class="form-control @error('monthly_fee') is-invalid @enderror" 
                                           id="monthly_fee" 
                                           name="monthly_fee" 
                                           step="0.01" 
                                           min="0" 
                                           value="{{ old('monthly_fee', $subscription->monthly_fee) }}">
                                    <small class="form-text text-muted">Leave empty to auto-calculate from package</small>
                                    @error('monthly_fee')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Subscription
                            </button>
                            <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Current Usage Sidebar -->
        <div class="col-lg-4">
            <!-- Current Package Info -->
            @if($subscription->subscriptionPackage)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Current Package</h6>
                </div>
                <div class="card-body">
                    <h5>{{ $subscription->subscriptionPackage->name }}</h5>
                    <p class="text-muted">{{ $subscription->subscriptionPackage->description }}</p>
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-right">
                                <div class="h6 mb-0">{{ $subscription->getCurrentUsage('max_students') }}</div>
                                <small class="text-muted">Students</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-right">
                                <div class="h6 mb-0">{{ $subscription->getCurrentUsage('max_instructors') }}</div>
                                <small class="text-muted">Instructors</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="h6 mb-0">{{ $subscription->getCurrentUsage('max_vehicles') }}</div>
                            <small class="text-muted">Vehicles</small>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($subscription->subscriptionPackage)
                        <form method="POST" action="{{ route('admin.subscriptions.upgrade-package', $subscription) }}">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm btn-block"
                                    onclick="return confirm('Upgrade this school to a higher tier package?')">
                                <i class="fas fa-arrow-up"></i> Quick Upgrade
                            </button>
                        </form>
                        @endif
                        
                        <form method="POST" action="{{ route('admin.subscriptions.reset-trial', $subscription) }}">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm btn-block"
                                    onclick="return confirm('Reset trial period for this school?')">
                                <i class="fas fa-redo"></i> Reset Trial
                            </button>
                        </form>
                        
                        <form method="POST" action="{{ route('admin.subscriptions.toggle-status', $subscription) }}">
                            @csrf
                            <button type="submit" class="btn btn-{{ $subscription->subscription_status === 'active' ? 'secondary' : 'success' }} btn-sm btn-block">
                                <i class="fas fa-{{ $subscription->subscription_status === 'active' ? 'pause' : 'play' }}"></i> 
                                {{ $subscription->subscription_status === 'active' ? 'Suspend' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const packageSelect = document.getElementById('subscription_package_id');
    const billingSelect = document.getElementById('billing_period');
    const monthlyFeeInput = document.getElementById('monthly_fee');
    const preview = document.getElementById('package-preview');

    function updatePackagePreview() {
        const selectedOption = packageSelect.options[packageSelect.selectedIndex];
        
        if (selectedOption.value) {
            const monthlyPrice = parseFloat(selectedOption.getAttribute('data-monthly-price'));
            const yearlyPrice = parseFloat(selectedOption.getAttribute('data-yearly-price'));
            const limits = JSON.parse(selectedOption.getAttribute('data-limits') || '{}');
            const billingPeriod = billingSelect.value;

            // Update preview
            document.getElementById('preview-monthly-price').textContent = '$' + monthlyPrice.toFixed(2);
            document.getElementById('preview-yearly-price').textContent = yearlyPrice ? '$' + yearlyPrice.toFixed(2) : 'Not available';
            
            if (yearlyPrice && monthlyPrice) {
                const yearlyEquivalent = monthlyPrice * 12;
                const savings = yearlyEquivalent - yearlyPrice;
                const discountPercent = Math.round((savings / yearlyEquivalent) * 100);
                document.getElementById('preview-savings').textContent = savings > 0 ? 
                    '$' + savings.toFixed(2) + ' (' + discountPercent + '% off)' : 'No savings';
            } else {
                document.getElementById('preview-savings').textContent = 'No yearly option';
            }

            // Update limits
            let limitsText = '';
            Object.keys(limits).forEach(key => {
                const value = limits[key];
                const displayValue = value === -1 ? 'Unlimited' : value;
                limitsText += key.replace('max_', '').replace('_', ' ') + ': ' + displayValue + ' ';
            });
            document.getElementById('preview-limits').textContent = limitsText;

            // Auto-update monthly fee
            const selectedPrice = billingPeriod === 'yearly' && yearlyPrice ? 
                (yearlyPrice / 12) : monthlyPrice;
            monthlyFeeInput.value = selectedPrice.toFixed(2);

            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    }

    packageSelect.addEventListener('change', updatePackagePreview);
    billingSelect.addEventListener('change', updatePackagePreview);

    // Initial update
    updatePackagePreview();
});
</script>
@endpush