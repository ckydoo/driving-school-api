{{-- resources/views/school/subscription/upgrade.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Upgrade Subscription')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-arrow-up"></i> Upgrade Your Subscription
        </h1>
        <a href="{{ route('school.subscription.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Subscription
        </a>
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

    <!-- Current Plan Summary -->
    @if($currentPackage)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Your Current Plan</h6>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="text-primary mb-1">{{ $currentPackage->name }}</h5>
                    <p class="text-muted mb-2">{{ $currentPackage->description }}</p>
                    <div class="text-success font-weight-bold">
                        {{ $currentPackage->getFormattedMonthlyPrice() }}/month
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="h6 mb-1">{{ $usage['students'] }}</div>
                            <small class="text-muted">Students</small>
                        </div>
                        <div class="col-4">
                            <div class="h6 mb-1">{{ $usage['instructors'] }}</div>
                            <small class="text-muted">Instructors</small>
                        </div>
                        <div class="col-4">
                            <div class="h6 mb-1">{{ $usage['vehicles'] }}</div>
                            <small class="text-muted">Vehicles</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Available Upgrades -->
    @if($availablePackages->count() > 0)
    <div class="row">
        @foreach($availablePackages as $package)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 shadow {{ $package->is_popular ? 'border-primary' : '' }}">
                @if($package->is_popular)
                <div class="card-header bg-primary text-white text-center">
                    <i class="fas fa-star"></i> Most Popular
                </div>
                @endif

                <div class="card-body">
                    <h5 class="card-title text-center mb-3">{{ $package->name }}</h5>
                    <h2 class="text-center text-primary mb-3">
                        ${{ number_format($package->monthly_price, 2) }}
                        <small class="text-muted">/month</small>
                    </h2>

                    @if($package->hasYearlyPricing())
                    <div class="text-center mb-3">
                        <span class="badge bg-success">
                            or ${{ number_format($package->yearly_price, 2) }}/year 
                            ({{ $package->getYearlyDiscount() }}% off)
                        </span>
                    </div>
                    @endif

                    <p class="text-muted text-center mb-3">{{ $package->description }}</p>

                    <ul class="list-unstyled">
                        @foreach($package->features as $feature)
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i> {{ $feature }}
                        </li>
                        @endforeach
                    </ul>

                    <hr>

                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <strong>{{ $package->getLimit('max_students') }}</strong>
                            <br><small>Students</small>
                        </div>
                        <div class="col-4">
                            <strong>{{ $package->getLimit('max_instructors') }}</strong>
                            <br><small>Instructors</small>
                        </div>
                        <div class="col-4">
                            <strong>{{ $package->getLimit('max_vehicles') }}</strong>
                            <br><small>Vehicles</small>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <!-- Billing Period Selection -->
                    <div class="mb-3">
                        <label class="font-weight-bold">Select billing period:</label>
                        <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                            <label class="btn btn-outline-primary active">
                                <input type="radio" name="billing_period_{{ $package->id }}" value="monthly" checked> Monthly
                            </label>
                            @if($package->hasYearlyPricing())
                            <label class="btn btn-outline-success">
                                <input type="radio" name="billing_period_{{ $package->id }}" value="yearly"> 
                                Yearly ({{ $package->getYearlyDiscount() }}% off)
                            </label>
                            @endif
                        </div>
                    </div>

                    <button class="btn btn-primary btn-block upgrade-btn" 
                            data-package-id="{{ $package->id }}"
                            data-package-name="{{ $package->name }}"
                            data-monthly-price="{{ $package->monthly_price }}"
                            data-yearly-price="{{ $package->yearly_price }}">
                        <i class="fas fa-arrow-up"></i> Upgrade to {{ $package->name }}
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="card shadow">
        <div class="card-body text-center py-5">
            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
            <h4 class="text-success">You're on the highest plan!</h4>
            <p class="text-muted">You're already enjoying all the features we offer.</p>
            <a href="{{ route('school.subscription.index') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Subscription
            </a>
        </div>
    </div>
    @endif
</div>

<!-- Upgrade Confirmation Modal -->
<div class="modal fade" id="upgradeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Upgrade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-arrow-up fa-3x text-success mb-3"></i>
                    <h5 id="upgrade-title">Upgrade to Professional Plan</h5>
                </div>

                <div class="upgrade-details">
                    <div class="row mb-2">
                        <div class="col-6">
                            <strong>New Plan:</strong>
                        </div>
                        <div class="col-6" id="upgrade-plan-name">
                            Professional
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">
                            <strong>Billing Period:</strong>
                        </div>
                        <div class="col-6" id="upgrade-billing-period">
                            Monthly
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">
                            <strong>Amount:</strong>
                        </div>
                        <div class="col-6 text-success font-weight-bold" id="upgrade-amount">
                            $29.99
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> You will be redirected to Stripe Checkout to complete your payment securely.
                </div>

                <div id="error-message" class="alert alert-danger" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-upgrade">
                    <span class="spinner-border spinner-border-sm" id="loading-spinner" style="display: none;"></span>
                    <span id="button-text">Proceed to Payment</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentUpgradeData = {};

// Handle upgrade button clicks
document.querySelectorAll('.upgrade-btn').forEach(button => {
    button.addEventListener('click', function() {
        const packageId = this.dataset.packageId;
        const packageName = this.dataset.packageName;
        const monthlyPrice = parseFloat(this.dataset.monthlyPrice);
        const yearlyPrice = parseFloat(this.dataset.yearlyPrice);
        
        // Get selected billing period
        const billingPeriodInput = document.querySelector(`input[name="billing_period_${packageId}"]:checked`);
        const billingPeriod = billingPeriodInput ? billingPeriodInput.value : 'monthly';
        
        const amount = billingPeriod === 'yearly' ? yearlyPrice : monthlyPrice;

        currentUpgradeData = {
            packageId,
            packageName,
            billingPeriod,
            amount
        };

        // Update modal
        document.getElementById('upgrade-title').textContent = `Upgrade to ${packageName}`;
        document.getElementById('upgrade-plan-name').textContent = packageName;
        document.getElementById('upgrade-billing-period').textContent = 
            billingPeriod === 'yearly' ? 'Yearly' : 'Monthly';
        document.getElementById('upgrade-amount').textContent = `$${amount.toFixed(2)}`;

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('upgradeModal'));
        modal.show();
    });
});

// Handle confirm upgrade
document.getElementById('confirm-upgrade').addEventListener('click', async function() {
    const button = this;
    const buttonText = document.getElementById('button-text');
    const loadingSpinner = document.getElementById('loading-spinner');
    const errorMessage = document.getElementById('error-message');

    // Show loading
    button.disabled = true;
    buttonText.textContent = 'Creating checkout session...';
    loadingSpinner.style.display = 'inline-block';
    errorMessage.style.display = 'none';

    try {
        // Create Stripe Checkout session
        const response = await fetch('{{ route("school.subscription.process-upgrade") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                package_id: currentUpgradeData.packageId,
                billing_period: currentUpgradeData.billingPeriod
            })
        });

        const result = await response.json();
        
        if (result.success && result.checkout_url) {
            // Redirect to Stripe Checkout
            buttonText.textContent = 'Redirecting to Stripe...';
            window.location.href = result.checkout_url;
        } else {
            throw new Error(result.message || 'Failed to create checkout session');
        }

    } catch (error) {
        console.error('Upgrade error:', error);
        errorMessage.textContent = error.message;
        errorMessage.style.display = 'block';
        
        // Reset button
        button.disabled = false;
        buttonText.textContent = 'Proceed to Payment';
        loadingSpinner.style.display = 'none';
    }
});
</script>
@endpush