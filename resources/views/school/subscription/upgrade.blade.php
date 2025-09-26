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
            <div class="card h-100 shadow {{ $package->is_popular ? 'border-warning' : '' }}">
                @if($package->is_popular)
                <div class="card-header bg-warning text-dark text-center">
                    <i class="fas fa-star"></i> Most Popular
                </div>
                @endif
                
                <div class="card-body text-center">
                    <h5 class="card-title text-primary">{{ $package->name }}</h5>
                    <p class="text-muted">{{ $package->description }}</p>
                    
                    <!-- Pricing -->
                    <div class="pricing-section mb-3">
                        <div class="h4 text-success">{{ $package->getFormattedMonthlyPrice() }}</div>
                        <small class="text-muted">per month</small>
                        
                        @if($package->hasYearlyPricing())
                        <div class="mt-2">
                            <div class="h5 text-info">{{ $package->getFormattedYearlyPrice() }}</div>
                            <small class="text-success">
                                per year (save {{ $package->getYearlyDiscount() }}%)
                            </small>
                        </div>
                        @endif
                    </div>

                    <!-- Limits Comparison -->
                    <div class="limits-section mb-3">
                        <h6 class="text-muted mb-2">What you get:</h6>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="border-end">
                                    <div class="h6 mb-1">
                                        {{ $package->getLimitDescription('max_students') }}
                                    </div>
                                    <small class="text-muted">Students</small>
                                    @if($usage['students'] > $package->getLimit('max_students') && $package->getLimit('max_students') !== -1)
                                        <br><small class="text-danger">⚠ Over limit</small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border-end">
                                    <div class="h6 mb-1">
                                        {{ $package->getLimitDescription('max_instructors') }}
                                    </div>
                                    <small class="text-muted">Instructors</small>
                                    @if($usage['instructors'] > $package->getLimit('max_instructors') && $package->getLimit('max_instructors') !== -1)
                                        <br><small class="text-danger">⚠ Over limit</small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="h6 mb-1">
                                    {{ $package->getLimitDescription('max_vehicles') }}
                                </div>
                                <small class="text-muted">Vehicles</small>
                                @if($usage['vehicles'] > $package->getLimit('max_vehicles') && $package->getLimit('max_vehicles') !== -1)
                                    <br><small class="text-danger">⚠ Over limit</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Features -->
                    <div class="features-section text-left mb-3">
                        @foreach($package->features as $feature)
                        <div class="mb-1">
                            <i class="fas fa-check text-success mr-2"></i>
                            <small>{{ $feature }}</small>
                        </div>
                        @endforeach
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
                    <div class="row">
                        <div class="col-6">
                            <strong>New Plan:</strong>
                        </div>
                        <div class="col-6" id="upgrade-plan-name">
                            Professional
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <strong>Billing Period:</strong>
                        </div>
                        <div class="col-6" id="upgrade-billing-period">
                            Monthly
                        </div>
                    </div>
                    <div class="row">
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
                    <strong>Note:</strong> Your subscription will be upgraded immediately after payment confirmation. 
                    You'll be charged the prorated amount for the current billing period.
                </div>

                <div id="upgrade-payment-element">
                    <!-- Stripe payment elements will be mounted here -->
                </div>
                <div id="upgrade-payment-message" class="text-danger mt-2" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirm-upgrade">
                    <span id="upgrade-button-text">Confirm Upgrade</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
const stripe = Stripe('{{ config("services.stripe.key") }}');
let elements, paymentElement;
let currentUpgradeData = {};

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Stripe
    elements = stripe.elements({
        appearance: {
            theme: 'stripe',
        }
    });

    // Handle upgrade button clicks
    document.querySelectorAll('.upgrade-btn').forEach(button => {
        button.addEventListener('click', handleUpgradeClick);
    });
});

async function handleUpgradeClick(event) {
    const button = event.target;
    const packageId = button.getAttribute('data-package-id');
    const packageName = button.getAttribute('data-package-name');
    const monthlyPrice = parseFloat(button.getAttribute('data-monthly-price'));
    const yearlyPrice = parseFloat(button.getAttribute('data-yearly-price'));
    
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

    try {
        // Get payment intent from server
        const response = await fetch('{{ route("school.subscription.process-upgrade") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                package_id: packageId,
                billing_period: billingPeriod
            })
        });

        const result = await response.json();
        
        if (result.success) {
            showUpgradeModal(result.client_secret);
        } else {
            showError(result.message);
        }
    } catch (error) {
        showError('Failed to initialize upgrade: ' + error.message);
    }
}

function showUpgradeModal(clientSecret) {
    // Update modal content
    document.getElementById('upgrade-title').textContent = `Upgrade to ${currentUpgradeData.packageName}`;
    document.getElementById('upgrade-plan-name').textContent = currentUpgradeData.packageName;
    document.getElementById('upgrade-billing-period').textContent = 
        currentUpgradeData.billingPeriod === 'yearly' ? 'Yearly' : 'Monthly';
    document.getElementById('upgrade-amount').textContent = `$${currentUpgradeData.amount.toFixed(2)}`;

    // Create payment element
    paymentElement = elements.create('payment');
    const paymentElementDiv = document.getElementById('upgrade-payment-element');
    paymentElementDiv.innerHTML = '';
    paymentElement.mount('#upgrade-payment-element');

    // Store client secret
    document.getElementById('confirm-upgrade').setAttribute('data-client-secret', clientSecret);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('upgradeModal'));
    modal.show();
}

// Handle upgrade confirmation
document.getElementById('confirm-upgrade').addEventListener('click', async function() {
    const submitButton = this;
    const buttonText = document.getElementById('upgrade-button-text');
    const messageDiv = document.getElementById('upgrade-payment-message');
    const clientSecret = submitButton.getAttribute('data-client-secret');

    // Disable button and show loading state
    submitButton.disabled = true;
    buttonText.textContent = 'Processing...';
    messageDiv.style.display = 'none';

    try {
        const { error, paymentIntent } = await stripe.confirmPayment({
            elements,
            confirmParams: {
                return_url: '{{ route("school.subscription.index") }}',
            },
            redirect: 'if_required'
        });

        if (error) {
            showUpgradeError(error.message);
            submitButton.disabled = false;
            buttonText.textContent = 'Confirm Upgrade';
        } else if (paymentIntent.status === 'succeeded') {
            // Upgrade successful
            const modal = bootstrap.Modal.getInstance(document.getElementById('upgradeModal'));
            modal.hide();
            
            showSuccess('Upgrade successful! Redirecting to subscription dashboard...');
            setTimeout(() => {
                window.location.href = '{{ route("school.subscription.index") }}';
            }, 2000);
        }
    } catch (err) {
        showUpgradeError('An unexpected error occurred: ' + err.message);
        submitButton.disabled = false;
        buttonText.textContent = 'Confirm Upgrade';
    }
});

function showUpgradeError(message) {
    const messageDiv = document.getElementById('upgrade-payment-message');
    messageDiv.textContent = message;
    messageDiv.style.display = 'block';
}

function showError(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.innerHTML = `
        <i class="fas fa-exclamation-triangle"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

function showSuccess(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.innerHTML = `
        <i class="fas fa-check-circle"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
}

// Handle modal cleanup
document.getElementById('upgradeModal').addEventListener('hidden.bs.modal', function () {
    const submitButton = document.getElementById('confirm-upgrade');
    const buttonText = document.getElementById('upgrade-button-text');
    submitButton.disabled = false;
    buttonText.textContent = 'Confirm Upgrade';
    
    if (paymentElement) {
        paymentElement.unmount();
    }
    
    const messageDiv = document.getElementById('upgrade-payment-message');
    messageDiv.style.display = 'none';
    messageDiv.textContent = '';
    
    currentUpgradeData = {};
});
</script>
@endpush