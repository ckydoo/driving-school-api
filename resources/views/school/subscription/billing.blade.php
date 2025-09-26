{{-- resources/views/school/subscription/billing.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Billing History')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-invoice"></i> Billing History
        </h1>
        <div>
            <a href="{{ route('school.subscription.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Subscription
            </a>
        </div>
    </div>

    <!-- Billing Summary -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Paid</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($stats['total_paid'], 2) }}
                            </div>
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
                                Outstanding</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($stats['outstanding_balance'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                                Total Invoices</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_invoices'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Current MRR</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($stats['current_mrr'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sync fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Invoices -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-invoice"></i> Invoices
                    </h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="?status=all">All Invoices</a>
                            <a class="dropdown-item" href="?status=pending">Pending</a>
                            <a class="dropdown-item" href="?status=paid">Paid</a>
                            <a class="dropdown-item" href="?status=overdue">Overdue</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($school->subscriptionInvoices && $school->subscriptionInvoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Date</th>
                                        <th>Period</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($school->subscriptionInvoices as $invoice)
                                    <tr>
                                        <td>
                                            <strong>{{ $invoice->invoice_number }}</strong>
                                            @if($invoice->billing_period === 'yearly')
                                                <span class="badge badge-info">Yearly</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $invoice->invoice_date->format('M d, Y') }}</div>
                                            @if($invoice->due_date->isPast() && $invoice->status === 'pending')
                                                <small class="text-danger">Due {{ $invoice->due_date->format('M d, Y') }}</small>
                                            @else
                                                <small class="text-muted">Due {{ $invoice->due_date->format('M d, Y') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <small>
                                                {{ $invoice->period_start->format('M d, Y') }} - 
                                                {{ $invoice->period_end->format('M d, Y') }}
                                            </small>
                                        </td>
                                        <td>
                                            <strong>${{ number_format($invoice->total_amount, 2) }}</strong>
                                            @if($invoice->tax_amount > 0)
                                                <br><small class="text-muted">(incl. ${{ number_format($invoice->tax_amount, 2) }} tax)</small>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $statusClasses = [
                                                    'pending' => $invoice->isOverdue() ? 'danger' : 'warning',
                                                    'paid' => 'success',
                                                    'failed' => 'danger',
                                                    'cancelled' => 'secondary'
                                                ];
                                                $statusClass = $statusClasses[$invoice->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge badge-{{ $statusClass }}">
                                                @if($invoice->status === 'pending' && $invoice->isOverdue())
                                                    Overdue
                                                @else
                                                    {{ ucfirst($invoice->status) }}
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                @if($invoice->status === 'pending')
                                                    <button class="btn btn-primary" 
                                                            onclick="payInvoice({{ $invoice->id }}, {{ $invoice->total_amount }})"
                                                            title="Pay Invoice">
                                                        <i class="fas fa-credit-card"></i>
                                                    </button>
                                                @endif
                                                
                                                <button class="btn btn-info" 
                                                        onclick="viewInvoiceDetails({{ $invoice->id }})"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                <a href="{{ route('school.subscription.invoice.download', $invoice->id) }}" 
                                                   class="btn btn-secondary"
                                                   title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-invoice fa-3x text-gray-300 mb-3"></i>
                            <h5 class="text-gray-500">No Invoices Found</h5>
                            <p class="text-gray-400">Your billing history will appear here once invoices are generated.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payment History -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-money-bill"></i> Payment History
                    </h6>
                </div>
                <div class="card-body">
                    @if($school->subscriptionPayments && $school->subscriptionPayments->count() > 0)
                        <div class="payment-history">
                            @foreach($school->subscriptionPayments as $payment)
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                <div>
                                    <div class="font-weight-bold">
                                        ${{ number_format(abs($payment->amount), 2) }}
                                        @if($payment->amount < 0)
                                            <span class="text-danger">(Refund)</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">
                                        {{ $payment->payment_date->format('M d, Y') }}
                                        <br>{{ ucfirst($payment->payment_method) }}
                                        @if($payment->reference_number)
                                            <br>Ref: {{ $payment->reference_number }}
                                        @endif
                                    </small>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-{{ $payment->status === 'completed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                    @if($payment->fee_amount > 0)
                                        <br><small class="text-muted">
                                            Fee: ${{ number_format($payment->fee_amount, 2) }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-money-bill fa-2x text-gray-300 mb-3"></i>
                            <p class="text-muted mb-0">No payments recorded</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    @if($stats['outstanding_balance'] > 0)
                        <button class="btn btn-danger btn-block mb-2" onclick="payOutstandingBalance()">
                            <i class="fas fa-credit-card"></i> Pay Outstanding Balance
                        </button>
                    @endif
                    
                    <a href="{{ route('school.subscription.upgrade') }}" class="btn btn-success btn-block mb-2">
                        <i class="fas fa-arrow-up"></i> Upgrade Plan
                    </a>
                    
                    <button class="btn btn-info btn-block" onclick="contactSupport()">
                        <i class="fas fa-question-circle"></i> Contact Support
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Invoice Details Modal -->
<div class="modal fade" id="invoiceDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Invoice Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="invoice-details-content">
                <!-- Invoice details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="pay-from-details" style="display: none;">
                    Pay Invoice
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
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
<script src="https://js.stripe.com/v3/"></script>
<script>
// Initialize Stripe (reuse from subscription dashboard)
const stripe = Stripe('{{ config("services.stripe.key") }}');
let elements, paymentElement;
let currentInvoiceId = null;
let currentAmount = 0;

document.addEventListener('DOMContentLoaded', function() {
    elements = stripe.elements({
        appearance: {
            theme: 'stripe',
        }
    });
});

// Payment functions (reuse from subscription dashboard)
async function payInvoice(invoiceId, amount) {
    // Same implementation as in subscription dashboard
    currentInvoiceId = invoiceId;
    currentAmount = amount;
    
    try {
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

async function payOutstandingBalance() {
    const outstandingAmount = {{ $stats['outstanding_balance'] }};
    
    if (outstandingAmount <= 0) {
        showError('No outstanding balance to pay');
        return;
    }

    const pendingInvoices = @json($school->subscriptionInvoices->where('status', 'pending')->values());
    
    if (pendingInvoices.length > 0) {
        payInvoice(pendingInvoices[0].id, pendingInvoices[0].total_amount);
    } else {
        showError('No pending invoices found');
    }
}

function viewInvoiceDetails(invoiceId) {
    // Load invoice details via AJAX and show in modal
    const invoice = @json($school->subscriptionInvoices->keyBy('id'));
    const invoiceData = invoice[invoiceId];
    
    if (invoiceData) {
        const content = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Invoice Information</h6>
                    <p><strong>Number:</strong> ${invoiceData.invoice_number}</p>
                    <p><strong>Date:</strong> ${new Date(invoiceData.invoice_date).toLocaleDateString()}</p>
                    <p><strong>Due Date:</strong> ${new Date(invoiceData.due_date).toLocaleDateString()}</p>
                    <p><strong>Status:</strong> <span class="badge badge-${invoiceData.status === 'paid' ? 'success' : 'warning'}">${invoiceData.status.toUpperCase()}</span></p>
                </div>
                <div class="col-md-6">
                    <h6>Billing Information</h6>
                    <p><strong>Period:</strong> ${invoiceData.billing_period.toUpperCase()}</p>
                    <p><strong>Amount:</strong> $${parseFloat(invoiceData.amount).toFixed(2)}</p>
                    ${invoiceData.tax_amount > 0 ? `<p><strong>Tax:</strong> $${parseFloat(invoiceData.tax_amount).toFixed(2)}</p>` : ''}
                    <p><strong>Total:</strong> $${parseFloat(invoiceData.total_amount).toFixed(2)}</p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12">
                    <h6>Service Period</h6>
                    <p>${new Date(invoiceData.period_start).toLocaleDateString()} - ${new Date(invoiceData.period_end).toLocaleDateString()}</p>
                </div>
            </div>
        `;
        
        document.getElementById('invoice-details-content').innerHTML = content;
        
        const payButton = document.getElementById('pay-from-details');
        if (invoiceData.status === 'pending') {
            payButton.style.display = 'inline-block';
            payButton.onclick = () => {
                bootstrap.Modal.getInstance(document.getElementById('invoiceDetailsModal')).hide();
                payInvoice(invoiceId, parseFloat(invoiceData.total_amount));
            };
        } else {
            payButton.style.display = 'none';
        }
        
        const modal = new bootstrap.Modal(document.getElementById('invoiceDetailsModal'));
        modal.show();
    }
}

function contactSupport() {
    // Open mailto or show contact information
    window.location.href = 'mailto:support@yourcompany.com?subject=Billing Support Request&body=Hello, I need help with my subscription billing...';
}

// Utility functions (same as subscription dashboard)
function showPaymentModal(clientSecret, description, amount) {
    document.getElementById('paymentModal').querySelector('.modal-title').textContent = 
        `Pay ${description} - $${amount.toFixed(2)}`;
    
    paymentElement = elements.create('payment');
    const paymentElementDiv = document.getElementById('payment-element');
    paymentElementDiv.innerHTML = '';
    paymentElement.mount('#payment-element');

    document.getElementById('submit-payment').setAttribute('data-client-secret', clientSecret);
    
    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    modal.show();
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

// Payment submission handler (same as subscription dashboard)
document.getElementById('submit-payment').addEventListener('click', async function() {
    const submitButton = this;
    const buttonText = document.getElementById('button-text');
    const messageDiv = document.getElementById('payment-message');
    const clientSecret = submitButton.getAttribute('data-client-secret');

    submitButton.disabled = true;
    buttonText.textContent = 'Processing...';
    messageDiv.style.display = 'none';

    try {
        const { error, paymentIntent } = await stripe.confirmPayment({
            elements,
            confirmParams: {
                return_url: window.location.href,
            },
            redirect: 'if_required'
        });

        if (error) {
            messageDiv.textContent = error.message;
            messageDiv.style.display = 'block';
            submitButton.disabled = false;
            buttonText.textContent = 'Pay Now';
        } else if (paymentIntent.status === 'succeeded') {
            await handlePaymentSuccess(paymentIntent.id);
        }
    } catch (err) {
        messageDiv.textContent = 'An unexpected error occurred: ' + err.message;
        messageDiv.style.display = 'block';
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
            const modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
            modal.hide();
            
            alert('Payment successful! Page will reload.');
            window.location.reload();
        } else {
            document.getElementById('payment-message').textContent = result.message;
            document.getElementById('payment-message').style.display = 'block';
        }
    } catch (error) {
        document.getElementById('payment-message').textContent = 'Failed to confirm payment: ' + error.message;
        document.getElementById('payment-message').style.display = 'block';
    }
}
</script>
@endpush