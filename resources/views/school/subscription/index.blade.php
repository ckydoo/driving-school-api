{{-- resources/views/school/subscription/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'My Subscription')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-credit-card"></i> My Subscription
        </h1>
        <div>
            @if($availablePackages->count() > 0)
            <a href="{{ route('school.subscription.upgrade') }}" class="btn btn-success btn-sm">
                <i class="fas fa-arrow-up"></i> Upgrade Plan
            </a>
            @endif
            <a href="{{ route('school.subscription.billing') }}" class="btn btn-info btn-sm">
                <i class="fas fa-file-invoice"></i> Billing History
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

    <div class="row">
        <!-- Current Plan -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-box"></i> Current Plan
                    </h6>
                </div>
                <div class="card-body">
                    @if($school->subscriptionPackage)
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="text-primary">{{ $school->subscriptionPackage->name }}</h4>
                            @if($school->subscriptionPackage->is_popular)
                                <span class="badge badge-warning mb-3">Most Popular</span>
                            @endif

                            <p class="text-muted">{{ $school->subscriptionPackage->description }}</p>

                            <div class="pricing-info mb-3">
                                <div class="h5 text-success">
                                    {{ $school->subscriptionPackage->getFormattedMonthlyPrice() }}/month
                                </div>
                                @if($school->subscriptionPackage->hasYearlyPricing())
                                <small class="text-muted">
                                    or {{ $school->subscriptionPackage->getFormattedYearlyPrice() }}/year
                                    (save {{ $school->subscriptionPackage->getYearlyDiscount() }}%)
                                </small>
                                @endif
                            </div>

                            <!-- Plan Status -->
                            <div class="mb-3">
                                <strong>Status:</strong>
                                @php
                                    $statusClasses = [
                                        'trial' => 'warning',
                                        'active' => 'success',
                                        'suspended' => 'danger',
                                        'cancelled' => 'secondary',
                                        'expired' => 'dark'
                                    ];
                                    $statusClass = $statusClasses[$school->subscription_status] ?? 'secondary';
                                @endphp
                                <span class="badge badge-{{ $statusClass }}">
                                    {{ ucfirst($school->subscription_status) }}
                                </span>

                                @if($school->subscription_status === 'trial' && $school->remaining_trial_days)
                                    <br><small class="text-muted">{{ $school->remaining_trial_days }} trial days remaining</small>
                                @endif
                            </div>

                            <!-- Next Billing -->
                            @if($stats['next_billing_date'])
                            <div class="mb-3">
                                <strong>Next Billing:</strong>
                                <span class="text-info">{{ $stats['next_billing_date']->format('M d, Y') }}</span>
                                @if($stats['next_billing_date']->diffInDays() <= 7)
                                    <small class="text-warning">(Due soon)</small>
                                @endif
                            </div>
                            @endif

                            <!-- Payment Status -->
                            <div class="mb-3">
                                <strong>Payment Status:</strong>
                                @if($stats['is_payment_up_to_date'])
                                    <span class="text-success">✓ Up to date</span>
                                @else
                                    <span class="text-danger">⚠ Overdue payments</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Plan Features</h6>
                            <ul class="list-unstyled">
                                @foreach($school->subscriptionPackage->features as $feature)
                                <li class="mb-2">
                                    <i class="fas fa-check text-success mr-2"></i>{{ $feature }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h5 class="text-muted">No Subscription Package</h5>
                        <p class="text-muted">Please contact support to get a subscription package assigned.</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Usage Statistics -->
            @if($school->subscriptionPackage)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar"></i> Usage Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Students Usage -->
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-user-graduate fa-2x text-primary"></i>
                                </div>
                                <h6>Students</h6>
                                <div class="h4">
                                    {{ $usage['students']['current'] }}
                                    @if(!$usage['students']['unlimited'])
                                        / {{ number_format($usage['students']['limit']) }}
                                    @endif
                                </div>
                                @if(!$usage['students']['unlimited'])
                                <div class="progress mb-2" style="height: 8px;">
                                    @php
                                        $percentage = $usage['students']['percentage'];
                                        $progressColor = $percentage > 80 ? 'danger' : ($percentage > 60 ? 'warning' : 'success');
                                    @endphp
                                    <div class="progress-bar bg-{{ $progressColor }}"
                                         style="width: {{ $percentage }}%"></div>
                                </div>
                                <small class="text-muted">{{ round($percentage, 1) }}% used</small>
                                @else
                                <small class="text-success">Unlimited</small>
                                @endif
                            </div>
                        </div>

                        <!-- Instructors Usage -->
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-chalkboard-teacher fa-2x text-info"></i>
                                </div>
                                <h6>Instructors</h6>
                                <div class="h4">
                                    {{ $usage['instructors']['current'] }}
                                    @if(!$usage['instructors']['unlimited'])
                                        / {{ number_format($usage['instructors']['limit']) }}
                                    @endif
                                </div>
                                @if(!$usage['instructors']['unlimited'])
                                <div class="progress mb-2" style="height: 8px;">
                                    @php
                                        $percentage = $usage['instructors']['percentage'];
                                        $progressColor = $percentage > 80 ? 'danger' : ($percentage > 60 ? 'warning' : 'success');
                                    @endphp
                                    <div class="progress-bar bg-{{ $progressColor }}"
                                         style="width: {{ $percentage }}%"></div>
                                </div>
                                <small class="text-muted">{{ round($percentage, 1) }}% used</small>
                                @else
                                <small class="text-success">Unlimited</small>
                                @endif
                            </div>
                        </div>

                        <!-- Vehicles Usage -->
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-car fa-2x text-warning"></i>
                                </div>
                                <h6>Vehicles</h6>
                                <div class="h4">
                                    {{ $usage['vehicles']['current'] }}
                                    @if(!$usage['vehicles']['unlimited'])
                                        / {{ number_format($usage['vehicles']['limit']) }}
                                    @endif
                                </div>
                                @if(!$usage['vehicles']['unlimited'])
                                <div class="progress mb-2" style="height: 8px;">
                                    @php
                                        $percentage = $usage['vehicles']['percentage'];
                                        $progressColor = $percentage > 80 ? 'danger' : ($percentage > 60 ? 'warning' : 'success');
                                    @endphp
                                    <div class="progress-bar bg-{{ $progressColor }}"
                                         style="width: {{ $percentage }}%"></div>
                                </div>
                                <small class="text-muted">{{ round($percentage, 1) }}% used</small>
                                @else
                                <small class="text-success">Unlimited</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Usage Warnings -->
                    @php
                        $warnings = [];
                        if (!$usage['students']['unlimited'] && $usage['students']['percentage'] > 80) {
                            $warnings[] = 'Students usage is at ' . round($usage['students']['percentage'], 1) . '%';
                        }
                        if (!$usage['instructors']['unlimited'] && $usage['instructors']['percentage'] > 80) {
                            $warnings[] = 'Instructors usage is at ' . round($usage['instructors']['percentage'], 1) . '%';
                        }
                        if (!$usage['vehicles']['unlimited'] && $usage['vehicles']['percentage'] > 80) {
                            $warnings[] = 'Vehicles usage is at ' . round($usage['vehicles']['percentage'], 1) . '%';
                        }
                    @endphp
                    @if(count($warnings) > 0)
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Usage Alert:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($warnings as $warning)
                                <li>{{ $warning }} - Consider upgrading your plan</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Account Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12 mb-3">
                            <div class="border-bottom pb-2">
                                <div class="h5 mb-0 text-success">${{ number_format($stats['current_mrr'], 2) }}</div>
                                <small class="text-muted">Monthly Rate</small>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="border-bottom pb-2">
                                <div class="h5 mb-0">${{ number_format($stats['total_paid'], 2) }}</div>
                                <small class="text-muted">Total Paid</small>
                            </div>
                        </div>
                        @if($stats['outstanding_balance'] > 0)
                        <div class="col-12">
                            <div class="h5 mb-0 text-danger">${{ number_format($stats['outstanding_balance'], 2) }}</div>
                            <small class="text-muted">Outstanding Balance</small>
                            <div class="mt-2">
                                <button class="btn btn-sm btn-danger" onclick="payOutstandingBalance()">
                                    <i class="fas fa-credit-card"></i> Pay Now
                                </button>
                            </div>
                        </div>
                        @else
                        <div class="col-12">
                            <div class="h5 mb-0 text-success">$0.00</div>
                            <small class="text-muted">Outstanding Balance</small>
                            <br><small class="text-success">✓ All payments up to date</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Invoices -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Invoices</h6>
                </div>
                <div class="card-body">
                    @if($school->subscriptionInvoices && $school->subscriptionInvoices->count() > 0)
                        @foreach($school->subscriptionInvoices as $invoice)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <div class="font-weight-bold">{{ $invoice->invoice_number }}</div>
                                <small class="text-muted">
                                    {{ $invoice->invoice_date->format('M d, Y') }}
                                    @if($invoice->isOverdue())
                                        <span class="text-danger">(Overdue)</span>
                                    @endif
                                </small>
                            </div>
                            <div class="text-right">
                                <div class="font-weight-bold">${{ number_format($invoice->total_amount, 2) }}</div>
                                <span class="badge badge-{{ $invoice->status === 'paid' ? 'success' : 'warning' }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                                @if($invoice->status === 'pending')
                                <br>
                                <button class="btn btn-sm btn-primary mt-1"
                                        onclick="payInvoice({{ $invoice->id }}, {{ $invoice->total_amount }})">
                                    Pay
                                </button>
                                @endif
                            </div>
                        </div>
                        @endforeach

                        <div class="text-center">
                            <a href="{{ route('school.subscription.billing') }}" class="btn btn-sm btn-outline-primary">
                                View All Invoices
                            </a>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-file-invoice fa-2x text-gray-300 mb-2"></i>
                            <p class="text-muted mb-0">No invoices yet</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Upgrade Options -->
            @if($availablePackages->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Upgrade Available</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Get more features and higher limits with our premium plans.</p>
                    @foreach($availablePackages->take(2) as $package)
                    <div class="mb-3 p-2 border rounded">
                        <div class="font-weight-bold">{{ $package->name }}</div>
                        <div class="text-success">{{ $package->getFormattedMonthlyPrice() }}/month</div>
                        <small class="text-muted">{{ $package->description }}</small>
                    </div>
                    @endforeach
                    <div class="text-center">
                        <a href="{{ route('school.subscription.upgrade') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-arrow-up"></i> View All Upgrades
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Payment Processing Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Process Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="payment-element">
                    <!-- Stripe Elements will create form elements here -->
                </div>
                <div id="payment-message" class="text-danger" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submit-payment">
                    <span id="button-text">Pay Now</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>
<script>
    // Initialize Stripe
    const stripe = Stripe('{{ config("services.stripe.key") }}');
    let elements, paymentElement;

    // Payment variables
    let currentInvoiceId = null;
    let currentAmount = 0;

    // Don't initialize elements here - we'll do it when we have a clientSecret

    // Pay specific invoice
    async function payInvoice(invoiceId, amount) {
        currentInvoiceId = invoiceId;
        currentAmount = parseFloat(amount);

        try {
            // Get payment intent from server
            const response = await fetch('{{ route("school.subscription.pay-invoice") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    invoice_id: invoiceId
                })
            });

            const result = await response.json();

            if (result.success) {
                showPaymentModal(result.client_secret, `Invoice ${result.invoice_number}`, amount);
            } else {
                showError(result.message);
            }
        } catch (error) {
            showError('Failed to initialize payment: ' + error.message);
        }
    }

    // Pay outstanding balance
    async function payOutstandingBalance() {
        const outstandingAmount = {{ $stats['outstanding_balance'] }};

        if (outstandingAmount <= 0) {
            showError('No outstanding balance to pay');
            return;
        }

        // Find the first pending invoice to pay
        const pendingInvoices = @json($school->subscriptionInvoices->where('status', 'pending')->values());

        if (pendingInvoices.length > 0) {
            payInvoice(pendingInvoices[0].id, parseFloat(pendingInvoices[0].total_amount));
        } else {
            showError('No pending invoices found');
        }
    }

    function showPaymentModal(clientSecret, description, amount) {
        // Ensure amount is a number
        const numericAmount = parseFloat(amount) || 0;

        document.getElementById('paymentModal').querySelector('.modal-title').textContent =
            `Pay ${description} - $${numericAmount.toFixed(2)}`;

        // Create Elements with clientSecret
        elements = stripe.elements({
            clientSecret: clientSecret,
            appearance: {
                theme: 'stripe',
                variables: {
                    colorPrimary: '#4e73df',
                }
            }
        });

        // Create payment element
        paymentElement = elements.create('payment');

        // Clear and mount payment element
        const paymentElementDiv = document.getElementById('payment-element');
        paymentElementDiv.innerHTML = '';
        paymentElement.mount('#payment-element');

        // Store client secret for later use
        document.getElementById('submit-payment').setAttribute('data-client-secret', clientSecret);

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        modal.show();
    }

    // Handle payment submission
    document.getElementById('submit-payment').addEventListener('click', async function() {
        const submitButton = this;
        const buttonText = document.getElementById('button-text');
        const messageDiv = document.getElementById('payment-message');
        const clientSecret = submitButton.getAttribute('data-client-secret');

        if (!clientSecret) {
            showError('Payment not properly initialized');
            return;
        }

        // Disable button and show loading state
        submitButton.disabled = true;
        buttonText.textContent = 'Processing...';
        messageDiv.style.display = 'none';

        try {
            const { error, paymentIntent } = await stripe.confirmPayment({
                elements,
                confirmParams: {
                    return_url: window.location.href, // Return to current page
                },
                redirect: 'if_required'
            });

            if (error) {
                // Payment failed
                showPaymentError(error.message);
                submitButton.disabled = false;
                buttonText.textContent = 'Pay Now';
            } else if (paymentIntent.status === 'succeeded') {
                // Payment succeeded
                await handlePaymentSuccess(paymentIntent.id);
            }
        } catch (err) {
            showPaymentError('An unexpected error occurred: ' + err.message);
            submitButton.disabled = false;
            buttonText.textContent = 'Pay Now';
        }
    });

    async function handlePaymentSuccess(paymentIntentId) {
        try {
            const response = await fetch('{{ route("school.subscription.payment-success") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    payment_intent_id: paymentIntentId,
                    invoice_id: currentInvoiceId
                })
            });

            const result = await response.json();

            if (result.success) {
                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
                modal.hide();

                // Show success message and reload page
                showSuccess('Payment successful! Page will reload in 2 seconds.');
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showPaymentError(result.message);
            }
        } catch (error) {
            showPaymentError('Failed to confirm payment: ' + error.message);
        }
    }

    function showPaymentError(message) {
        const messageDiv = document.getElementById('payment-message');
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
    </script>
