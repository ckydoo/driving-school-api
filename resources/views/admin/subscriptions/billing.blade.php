{{-- resources/views/admin/subscriptions/billing.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Subscription Billing - ' . $subscription->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-invoice-dollar"></i> Billing: {{ $subscription->name }}
        </h1>
        <div>
            <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#generateInvoiceModal">
                <i class="fas fa-plus"></i> Generate Invoice
            </button>
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#recordPaymentModal">
                <i class="fas fa-money-bill"></i> Record Payment
            </button>
            <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Details
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Billing Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($billingStats['total_paid'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                Current MRR</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($billingStats['current_mrr'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sync fa-2x text-gray-300"></i>
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
                                Outstanding Balance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($billingStats['outstanding_balance'], 2) }}
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
                                Next Billing</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $billingStats['next_billing_date'] ? $billingStats['next_billing_date']->format('M d, Y') : 'N/A' }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                        <i class="fas fa-file-invoice"></i> Subscription Invoices ({{ $subscription->subscriptionInvoices->count() }})
                    </h6>
                    <small class="text-muted">
                        Paid: {{ $billingStats['paid_invoices'] }} | 
                        Pending: {{ $billingStats['pending_invoices'] }} | 
                        Overdue: {{ $billingStats['overdue_invoices'] }}
                    </small>
                </div>
                <div class="card-body">
                    @if($subscription->subscriptionInvoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Date</th>
                                        <th>Due Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Period</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subscription->subscriptionInvoices as $invoice)
                                    <tr>
                                        <td>
                                            <strong>{{ $invoice->invoice_number }}</strong>
                                            @if($invoice->billing_period === 'yearly')
                                                <span class="badge badge-info">Yearly</span>
                                            @endif
                                        </td>
                                        <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                        <td>
                                            {{ $invoice->due_date->format('M d, Y') }}
                                            @if($invoice->isOverdue())
                                                <br><small class="text-danger">Overdue</small>
                                            @endif
                                        </td>
                                        <td>${{ number_format($invoice->total_amount, 2) }}</td>
                                        <td>
                                            @php
                                                $statusClasses = [
                                                    'pending' => 'warning',
                                                    'paid' => 'success',
                                                    'failed' => 'danger',
                                                    'cancelled' => 'secondary'
                                                ];
                                            @endphp
                                            <span class="badge badge-{{ $statusClasses[$invoice->status] ?? 'secondary' }}">
                                                {{ ucfirst($invoice->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                {{ $invoice->period_start->format('M d, Y') }} - 
                                                {{ $invoice->period_end->format('M d, Y') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if($invoice->status === 'pending')
                                                <form method="POST" action="{{ route('admin.subscription-invoices.mark-paid', $invoice) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" title="Mark as Paid">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.subscription-invoices.cancel', $invoice) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-secondary btn-sm" title="Cancel">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                                @endif
                                                <button class="btn btn-info btn-sm" title="View Details" onclick="viewInvoice({{ $invoice->id }})">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-invoice fa-3x text-gray-300 mb-3"></i>
                            <h5 class="text-gray-500">No invoices found</h5>
                            <p class="text-gray-400">Generate the first invoice to start billing this subscription.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Payment Summary -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12 mb-3">
                            <div class="border-bottom pb-2">
                                <div class="h6 mb-0">${{ number_format($billingStats['total_paid'], 2) }}</div>
                                <small class="text-muted">Total Paid</small>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="border-bottom pb-2">
                                <div class="h6 mb-0">${{ number_format($billingStats['outstanding_balance'], 2) }}</div>
                                <small class="text-muted">Outstanding</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="h6 mb-0">
                                @if($billingStats['is_payment_up_to_date'])
                                    <span class="text-success">✓ Up to date</span>
                                @else
                                    <span class="text-danger">⚠ Payment overdue</span>
                                @endif
                            </div>
                            <small class="text-muted">Payment Status</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Payments</h6>
                </div>
                <div class="card-body">
                    @if($subscription->subscriptionPayments->count() > 0)
                        @foreach($subscription->subscriptionPayments->take(5) as $payment)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <div class="font-weight-bold">${{ number_format($payment->amount, 2) }}</div>
                                <small class="text-muted">
                                    {{ $payment->payment_date->format('M d, Y') }}
                                    <br>{{ ucfirst($payment->payment_method) }}
                                </small>
                            </div>
                            <div>
                                <span class="badge badge-{{ $payment->status === 'completed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                                @if($payment->status === 'completed' && $payment->amount > 0)
                                <button class="btn btn-sm btn-outline-danger ml-1" 
                                        onclick="showRefundModal({{ $payment->id }}, {{ $payment->amount }})"
                                        title="Process Refund">
                                    <i class="fas fa-undo"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                        
                        @if($subscription->subscriptionPayments->count() > 5)
                        <div class="text-center">
                            <small class="text-muted">Showing 5 of {{ $subscription->subscriptionPayments->count() }} payments</small>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-money-bill fa-2x text-gray-300 mb-2"></i>
                            <p class="text-muted mb-0">No payments recorded yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Invoice Modal -->
<div class="modal fade" id="generateInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate New Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.subscriptions.generate-invoice', $subscription) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="billing_period" class="font-weight-bold">Billing Period <span class="text-danger">*</span></label>
                        <select class="form-control" id="billing_period" name="billing_period" required>
                            <option value="monthly">Monthly - ${{ number_format($subscription->subscriptionPackage?->monthly_price ?? 0, 2) }}</option>
                            @if($subscription->subscriptionPackage?->yearly_price)
                            <option value="yearly">Yearly - ${{ number_format($subscription->subscriptionPackage->yearly_price, 2) }}</option>
                            @endif
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="invoice_date">Invoice Date</label>
                                <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="{{ now()->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="due_date">Due Date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date" value="{{ now()->addDays(7)->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Generate Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Manual Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.subscriptions.create-payment', $subscription) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="invoice_id" class="font-weight-bold">Invoice <span class="text-danger">*</span></label>
                        <select class="form-control" id="invoice_id" name="invoice_id" required>
                            <option value="">Select invoice...</option>
                            @foreach($subscription->subscriptionInvoices->where('status', 'pending') as $invoice)
                                <option value="{{ $invoice->id }}">
                                    {{ $invoice->invoice_number }} - ${{ number_format($invoice->total_amount, 2) }} 
                                    (Due: {{ $invoice->due_date->format('M d, Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount" class="font-weight-bold">Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_method" class="font-weight-bold">Payment Method <span class="text-danger">*</span></label>
                                <select class="form-control" id="payment_method" name="payment_method" required>
                                    <option value="stripe">Stripe</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="manual">Manual</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_date">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="reference_number">Reference Number</label>
                                <input type="text" class="form-control" id="reference_number" name="reference_number" placeholder="Transaction ID, Check #, etc.">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional payment notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Process Refund</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="refundForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This will process a refund for the selected payment.
                    </div>

                    <div class="form-group">
                        <label for="refund_amount" class="font-weight-bold">Refund Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="refund_amount" name="refund_amount" step="0.01" min="0.01" required>
                        <small class="form-text text-muted">Maximum refundable: $<span id="max_refund_amount">0.00</span></small>
                    </div>

                    <div class="form-group">
                        <label for="refund_reason" class="font-weight-bold">Refund Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="refund_reason" name="refund_reason" rows="3" required placeholder="Reason for refund..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="refund_method" class="font-weight-bold">Refund Method <span class="text-danger">*</span></label>
                        <select class="form-control" id="refund_method" name="refund_method" required>
                            <option value="original_method">Original Payment Method</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="manual">Manual Process</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Process Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Invoice selection handler for payment modal
    const invoiceSelect = document.getElementById('invoice_id');
    const amountInput = document.getElementById('amount');

    invoiceSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            // Extract amount from option text
            const text = selectedOption.textContent;
            const match = text.match(/\$([0-9,]+\.?\d*)/);
            if (match) {
                const amount = match[1].replace(/,/g, '');
                amountInput.value = amount;
            }
        } else {
            amountInput.value = '';
        }
    });

    // Confirmation for marking invoices as paid
    document.querySelectorAll('form[action*="mark-paid"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to mark this invoice as paid?')) {
                e.preventDefault();
            }
        });
    });

    // Confirmation for cancelling invoices
    document.querySelectorAll('form[action*="cancel"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to cancel this invoice?')) {
                e.preventDefault();
            }
        });
    });
});

// Show refund modal
function showRefundModal(paymentId, maxAmount) {
    const modal = document.getElementById('refundModal');
    const form = document.getElementById('refundForm');
    const maxRefundSpan = document.getElementById('max_refund_amount');
    const refundAmountInput = document.getElementById('refund_amount');

    // Set form action
    form.action = `/admin/subscription-payments/${paymentId}/refund`;
    
    // Set maximum refund amount
    maxRefundSpan.textContent = maxAmount.toFixed(2);
    refundAmountInput.max = maxAmount;
    refundAmountInput.value = maxAmount;

    // Show modal
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
}

// View invoice details (placeholder - you can implement this)
function viewInvoice(invoiceId) {
    // You can implement a detailed invoice view modal or redirect
    console.log('Viewing invoice:', invoiceId);
    // For now, just show an alert
    alert('Invoice details view - you can implement this to show full invoice details');
}

// Auto-populate due date based on invoice date
document.getElementById('invoice_date').addEventListener('change', function() {
    const invoiceDate = new Date(this.value);
    const dueDate = new Date(invoiceDate);
    dueDate.setDate(dueDate.getDate() + 7); // 7 days later
    
    const dueDateInput = document.getElementById('due_date');
    dueDateInput.value = dueDate.toISOString().split('T')[0];
});

// Update pricing when billing period changes
document.getElementById('billing_period').addEventListener('change', function() {
    const billingPeriod = this.value;
    // You can update the preview or show different pricing here
    console.log('Billing period changed to:', billingPeriod);
});
</script>
@endpush