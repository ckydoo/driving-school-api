{{-- resources/views/admin/payments/show.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Payment Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.payments.index') }}" class="text-decoration-none">Payments</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Payment #{{ $payment->id }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-credit-card"></i> Payment Details
            </h1>
        </div>
        <div class="btn-group" role="group">
            @if($payment->status !== 'refunded' && $payment->created_at && $payment->created_at->diffInHours(now()) <= 24)
            <a href="{{ route('admin.payments.edit', $payment->id) }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-edit"></i> Edit Payment
            </a>
            @endif
            <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                @if($payment->status === 'pending')
                <form method="POST" action="{{ route('admin.payments.verify', $payment->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item"
                            onclick="return confirm('Verify this payment?')">
                        <i class="fas fa-check"></i> Verify Payment
                    </button>
                </form>
                @endif
                @if($payment->status === 'Paid')
                <form method="POST" action="{{ route('admin.payments.refund', $payment->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item text-warning"
                            onclick="return confirm('Refund this payment? This action cannot be undone.')">
                        <i class="fas fa-undo"></i> Refund Payment
                    </button>
                </form>
                @endif
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('admin.payments.index') }}">
                    <i class="fas fa-list"></i> All Payments
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Payment Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Payment Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Created:</label>
                                <p class="text-muted mb-0">
                                    {{ $payment->created_at ? \Carbon\Carbon::parse($payment->created_at)->format('M d, Y g:i A') : 'Unknown' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Last Updated:</label>
                                <p class="text-muted mb-0">
                                    {{ $payment->updated_at ? \Carbon\Carbon::parse($payment->updated_at)->format('M d, Y g:i A') : 'Unknown' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Information -->
            @php
                // Safe access to invoice data
                $invoiceData = null;
                $invoiceId = $payment->invoiceId;

                if (method_exists($payment, 'invoice') && is_object($payment->invoice)) {
                    $invoiceData = $payment->invoice;
                } elseif (is_numeric($invoiceId)) {
                    $invoiceData = \App\Models\Invoice::find($invoiceId);
                }
            @endphp

            @if($invoiceData)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-invoice"></i> Related Invoice
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-2">{{ $invoiceData->invoice_number ?? 'Invoice #' . $invoiceData->id }}</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Total Amount:</strong> ${{ number_format($invoiceData->total_amount ?? 0, 2) }}</p>
                                    <p class="mb-1"><strong>Amount Paid:</strong> ${{ number_format($invoiceData->amountpaid ?? 0, 2) }}</p>
                                    <p class="mb-0"><strong>Balance:</strong> ${{ number_format(($invoiceData->total_amount ?? 0) - ($invoiceData->amountpaid ?? 0), 2) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <strong>Status:</strong>
                                        <span class="badge badge-{{
                                            ($invoiceData->status ?? '') === 'paid' ? 'success' :
                                            (($invoiceData->status ?? '') === 'partial' ? 'warning' : 'secondary')
                                        }}">
                                            {{ ucfirst($invoiceData->status ?? 'Unknown') }}
                                        </span>
                                    </p>
                                    @if($invoiceData->due_date)
                                    <p class="mb-0">
                                        <strong>Due Date:</strong> {{ \Carbon\Carbon::parse($invoiceData->due_date)->format('M d, Y') }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-right">
                            <a href="{{ route('admin.invoices.show', $invoiceData->id) }}"
                               class="btn btn-outline-primary">
                                <i class="fas fa-eye"></i> View Invoice
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Student Information -->
            @php
                // Safe access to student data
                $studentData = null;
                $userId = $payment->userId;

                if (method_exists($payment, 'user') && is_object($payment->user)) {
                    $studentData = $payment->user;
                } elseif (is_numeric($userId)) {
                    $studentData = \App\Models\User::find($userId);
                }
            @endphp

            @if($studentData)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user"></i> Student Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <div class="avatar-circle bg-info text-white mx-auto">
                                {{ strtoupper(substr($studentData->fname ?? 'U', 0, 1)) }}{{ strtoupper(substr($studentData->lname ?? '', 0, 1)) }}
                            </div>
                        </div>
                        <div class="col-md-10">
                            <h5 class="mb-1">{{ $studentData->fname ?? 'Unknown' }} {{ $studentData->lname ?? '' }}</h5>
                            <p class="text-muted mb-1">
                                <i class="fas fa-envelope"></i> {{ $studentData->email ?? 'No email' }}
                            </p>
                            @if($studentData->phone)
                            <p class="text-muted mb-1">
                                <i class="fas fa-phone"></i> {{ $studentData->phone }}
                            </p>
                            @endif
                            <a href="{{ route('admin.users.show', $studentData->id) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Payment Status -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Payment Status
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="h1 mb-0 text-{{
                            ($payment->status ?? '') === 'Paid' ? 'success' :
                            (($payment->status ?? '') === 'pending' ? 'warning' :
                            (($payment->status ?? '') === 'failed' ? 'danger' :
                            (($payment->status ?? '') === 'refunded' ? 'info' : 'secondary')))
                        }}">
                            <i class="fas fa-{{
                                ($payment->status ?? '') === 'Paid' ? 'check-circle' :
                                (($payment->status ?? '') === 'pending' ? 'clock' :
                                (($payment->status ?? '') === 'failed' ? 'times-circle' :
                                (($payment->status ?? '') === 'refunded' ? 'undo' : 'question-circle')))
                            }}"></i>
                        </div>
                        <h4 class="text-{{
                            ($payment->status ?? '') === 'Paid' ? 'success' :
                            (($payment->status ?? '') === 'pending' ? 'warning' :
                            (($payment->status ?? '') === 'failed' ? 'danger' :
                            (($payment->status ?? '') === 'refunded' ? 'info' : 'secondary')))
                        }}">
                            {{ ucfirst($payment->status ?? 'Unknown') }}
                        </h4>
                    </div>

                    <div class="border-top pt-3">
                        <div class="row text-center">
                            <div class="col-12 mb-2">
                                <div class="h3 mb-0 text-success">${{ number_format($payment->amount ?? 0, 2) }}</div>
                                <small class="text-muted">Payment Amount</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($payment->status === 'pending')
                        <form method="POST" action="{{ route('admin.payments.verify', $payment->id) }}" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm btn-block"
                                    onclick="return confirm('Verify this payment?')">
                                <i class="fas fa-check"></i> Verify Payment
                            </button>
                        </form>
                        @endif

                        @if($payment->status === 'Paid' && (!$payment->created_at || $payment->created_at->diffInDays(now()) <= 30))
                        <form method="POST" action="{{ route('admin.payments.refund', $payment->id) }}" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm btn-block"
                                    onclick="return confirm('Refund this payment? This action cannot be undone.')">
                                <i class="fas fa-undo"></i> Process Refund
                            </button>
                        </form>
                        @endif

                        @if($payment->status !== 'refunded' && $payment->created_at && $payment->created_at->diffInHours(now()) <= 24)
                        <a href="{{ route('admin.payments.edit', $payment->id) }}"
                           class="btn btn-outline-secondary btn-sm mb-2">
                            <i class="fas fa-edit"></i> Edit Payment
                        </a>
                        @endif

                        @if($invoiceData)
                        <a href="{{ route('admin.invoices.show', $invoiceData->id) }}"
                           class="btn btn-info btn-sm mb-2">
                            <i class="fas fa-file-invoice"></i> View Invoice
                        </a>
                        @endif

                        <a href="{{ route('admin.payments.index') }}"
                           class="btn btn-outline-dark btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Payments
                        </a>
                    </div>
                </div>
            </div>

            <!-- Payment Timeline -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Payment Timeline
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Payment Created</h6>
                                <p class="timeline-subtitle">
                                    {{ $payment->created_at ? \Carbon\Carbon::parse($payment->created_at)->format('M d, Y g:i A') : 'Unknown' }}
                                </p>
                            </div>
                        </div>

                        @if($payment->paymentDate && $payment->paymentDate != $payment->created_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Payment Date</h6>
                                <p class="timeline-subtitle">
                                    {{ \Carbon\Carbon::parse($payment->paymentDate)->format('M d, Y g:i A') }}
                                </p>
                            </div>
                        </div>
                        @endif

                        @if($payment->status === 'Paid')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Payment Completed</h6>
                                <p class="timeline-subtitle">Successfully processed</p>
                            </div>
                        </div>
                        @elseif($payment->status === 'refunded')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Payment Refunded</h6>
                                <p class="timeline-subtitle">Refund processed</p>
                            </div>
                        </div>
                        @elseif($payment->status === 'failed')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Payment Failed</h6>
                                <p class="timeline-subtitle">Payment could not be processed</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Initialize tooltips
$(document).ready(function() {
    $('[title]').tooltip();
});

// Flash message auto-hide
@if(session()->has('success') || session()->has('error') || session()->has('warning'))
setTimeout(function() {
    $('.alert').fadeOut('slow');
}, 5000);
@endif
</script>
@endpush

@push('styles')
<style>
.avatar-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 20px;
}

.badge-lg {
    font-size: 0.875em;
    padding: 0.5em 0.75em;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-900 {
    color: #3a3b45 !important;
}

.card {
    transition: box-shadow 0.15s ease-in-out;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.breadcrumb {
    background-color: transparent;
    padding: 0;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: ">";
    color: #858796;
}

.d-grid {
    display: grid;
}

.gap-2 {
    gap: 0.5rem;
}

/* Timeline Styles */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 3px #dee2e6;
}

.timeline-content {
    margin-left: 10px;
}

.timeline-title {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.timeline-subtitle {
    font-size: 0.75rem;
    color: #6c757d;
    margin: 0;
}

@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
        width: 100%;
    }

    .btn-group .btn {
        border-radius: 0.35rem !important;
        margin-bottom: 0.5rem;
    }

    .dropdown-menu {
        position: relative !important;
        transform: none !important;
        float: none;
        display: block;
        margin-top: 0.5rem;
    }
}
</style>
@endpush
@endsection class="form-group">
                                <label class="font-weight-bold text-gray-800">Payment ID:</label>
                                <p class="text-gray-900 mb-0 h5">#{{ $payment->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Status:</label>
                                <p class="mb-0">
                                    <span class="badge badge-{{
                                        ($payment->status ?? '') === 'Paid' ? 'success' :
                                        (($payment->status ?? '') === 'pending' ? 'warning' :
                                        (($payment->status ?? '') === 'failed' ? 'danger' :
                                        (($payment->status ?? '') === 'refunded' ? 'info' : 'secondary')))
                                    }} badge-lg">
                                        {{ $payment->status ?? 'Unknown' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Amount:</label>
                                <p class="text-success h4 mb-0">${{ number_format($payment->amount ?? 0, 2) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Payment Method:</label>
                                <p class="mb-0">
                                    <span class="badge badge-light badge-lg">
                                        {{ ucfirst(str_replace('_', ' ', $payment->method ?? 'Unknown')) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Payment Date:</label>
                                <p class="text-gray-900 mb-0">
                                    @if($payment->paymentDate)
                                        {{ \Carbon\Carbon::parse($payment->paymentDate)->format('M d, Y g:i A') }}
                                        <small class="text-muted d-block">
                                            {{ \Carbon\Carbon::parse($payment->paymentDate)->diffForHumans() }}
                                        </small>
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Reference Number:</label>
                                <p class="text-gray-900 mb-0">{{ $payment->reference ?? 'N/A' }}</p>
                            </div>
                        </div>
                        @if($payment->notes)
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Notes:</label>
                                <p class="text-gray-900 mb-0">{{ $payment->notes }}</p>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <div
