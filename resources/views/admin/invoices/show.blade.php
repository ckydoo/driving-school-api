{{-- resources/views/admin/invoices/show.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Invoice Details - ' . ($invoice->invoice_number ?? 'Unknown'))

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
                        <a href="{{ route('admin.invoices.index') }}" class="text-decoration-none">Invoices</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $invoice->invoice_number ?? 'Unknown' }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-file-invoice-dollar"></i> Invoice Details
            </h1>
        </div>
        <div class="btn-group" role="group">
            @if($invoice->status !== 'paid')
            <a href="{{ route('admin.invoices.edit', $invoice->id) }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-edit"></i> Edit Invoice
            </a>
            @endif
            <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="{{ route('admin.invoices.downloadPdf', $invoice->id) }}">
                    <i class="fas fa-download"></i> Download PDF
                </a>
                <a class="dropdown-item" href="{{ route('admin.payments.create') }}?invoiceId={{ $invoice->id }}">
                    <i class="fas fa-credit-card"></i> Record Payment
                </a>
                @if($invoice->status !== 'paid')
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('admin.invoices.markAsPaid', $invoice->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item"
                            onclick="return confirm('Mark this invoice as paid?')">
                        <i class="fas fa-check-circle"></i> Mark as Paid
                    </button>
                </form>
                @endif
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('admin.invoices.index') }}">
                    <i class="fas fa-list"></i> All Invoices
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Invoice Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Invoice Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Invoice Number:</label>
                                <p class="text-gray-900 mb-0 h5">{{ $invoice->invoice_number ?? 'Not assigned' }}</p>
                            </div>
                </div>
            </div>

            <!-- Student Information -->
            @if($invoice->student)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-graduate"></i> Student Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <div class="avatar-circle bg-info text-white mx-auto">
                                {{ strtoupper(substr($invoice->student->fname ?? 'U', 0, 1)) }}{{ strtoupper(substr($invoice->student->lname ?? '', 0, 1)) }}
                            </div>
                        </div>
                        <div class="col-md-10">
                            <h5 class="mb-1">{{ $invoice->student->fname ?? 'Unknown' }} {{ $invoice->student->lname ?? '' }}</h5>
                            <p class="text-muted mb-1">
                                <i class="fas fa-envelope"></i> {{ $invoice->student->email ?? 'No email' }}
                            </p>
                            @if($invoice->student->phone)
                            <p class="text-muted mb-1">
                                <i class="fas fa-phone"></i> {{ $invoice->student->phone }}
                            </p>
                            @endif
                            <a href="{{ route('admin.users.show', $invoice->student->id) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Course Information -->
            @if($invoice->course)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-graduation-cap"></i> Course Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="mb-2">{{ $invoice->course->name ?? 'Unknown Course' }}</h5>
                            @if($invoice->course->description)
                            <p class="text-muted mb-2">{{ $invoice->course->description }}</p>
                            @endif
                            <p class="mb-1">
                                <strong>Course Price:</strong> ${{ number_format($invoice->course->price ?? 0, 2) }}
                            </p>
                            <p class="mb-0">
                                <strong>Status:</strong>
                                <span class="badge badge-{{ $invoice->course->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($invoice->course->status ?? 'Unknown') }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4 text-md-right">
                            <a href="{{ route('admin.courses.show', $invoice->course->id) }}"
                               class="btn btn-outline-primary">
                                <i class="fas fa-eye"></i> View Course
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Payment History -->
            @if($invoice->payments && $invoice->payments->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-credit-card"></i> Payment History
                        <span class="badge badge-secondary ml-2">{{ $invoice->payments->count() }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Reference</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->payments as $payment)
                                <tr>
                                    <td>
                                        @if($payment->paymentDate)
                                        <div class="small">
                                            {{ \Carbon\Carbon::parse($payment->paymentDate)->format('M d, Y') }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ \Carbon\Carbon::parse($payment->paymentDate)->format('g:i A') }}
                                        </div>
                                        @else
                                            <span class="text-muted">No date</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-success">${{ number_format($payment->amount ?? 0, 2) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-light">{{ ucfirst($payment->method ?? 'Unknown') }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ ($payment->status ?? '') === 'Paid' ? 'success' : 'secondary' }}">
                                            {{ $payment->status ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $payment->reference ?? 'N/A' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.payments.show', $payment->id) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Payment Summary -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calculator"></i> Payment Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-12">
                            <div class="border-bottom pb-2 mb-3">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Amount
                                </div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">
                                    ${{ number_format($invoice->total_amount ?? 0, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="border-right">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Paid
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-success">
                                    ${{ number_format($invoice->amountpaid ?? 0, 2) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Remaining
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-warning">
                                ${{ number_format(($invoice->total_amount ?? 0) - ($invoice->amountpaid ?? 0), 2) }}
                            </div>
                        </div>
                    </div>

                    @if(($invoice->total_amount ?? 0) > 0)
                    <div class="progress mb-3">
                        @php
                            $percentage = (($invoice->amountpaid ?? 0) / ($invoice->total_amount ?? 1)) * 100;
                        @endphp
                        <div class="progress-bar bg-success"
                             role="progressbar"
                             style="width: {{ $percentage }}%"
                             aria-valuenow="{{ $percentage }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                            {{ number_format($percentage, 1) }}%
                        </div>
                    </div>
                    @endif

                    <div class="text-center">
                        <small class="text-muted">
                            {{ $invoice->lessons ?? 0 }} lessons × ${{ number_format($invoice->price_per_lesson ?? 0, 2) }}
                        </small>
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
                        @if($invoice->status !== 'paid')
                        <a href="{{ route('admin.payments.create') }}?invoiceId={{ $invoice->id }}"
                           class="btn btn-success btn-sm mb-2">
                            <i class="fas fa-credit-card"></i> Record Payment
                        </a>
                        @endif

                        <a href="{{ route('admin.invoices.downloadPdf', $invoice->id) }}"
                           class="btn btn-info btn-sm mb-2">
                            <i class="fas fa-download"></i> Download PDF
                        </a>

                        @if($invoice->status !== 'paid')
                        <a href="{{ route('admin.invoices.edit', $invoice->id) }}"
                           class="btn btn-outline-secondary btn-sm mb-2">
                            <i class="fas fa-edit"></i> Edit Invoice
                        </a>
                        @endif

                        <a href="{{ route('admin.invoices.index') }}"
                           class="btn btn-outline-dark btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Invoices
                        </a>
                    </div>
                </div>
            </div>

            <!-- Invoice Status Timeline -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Status Timeline
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Invoice Created</h6>
                                <p class="timeline-subtitle">
                                    {{ $invoice->created_at ? \Carbon\Carbon::parse($invoice->created_at)->format('M d, Y g:i A') : 'Unknown' }}
                                </p>
                            </div>
                        </div>

                        @if($invoice->payments && $invoice->payments->count() > 0)
                        @foreach($invoice->payments->sortBy('paymentDate') as $payment)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Payment Received</h6>
                                <p class="timeline-subtitle">
                                    ${{ number_format($payment->amount ?? 0, 2) }} -
                                    {{ $payment->paymentDate ? \Carbon\Carbon::parse($payment->paymentDate)->format('M d, Y') : 'No date' }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                        @endif

                        @if($invoice->status === 'paid')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Fully Paid</h6>
                                <p class="timeline-subtitle">Invoice completed</p>
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

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
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

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
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
@endsection
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Status:</label>
                                <p class="mb-0">
                                    <span class="badge badge-{{
                                        ($invoice->status ?? '') === 'paid' ? 'success' :
                                        (($invoice->status ?? '') === 'partial' ? 'warning' :
                                        (($invoice->status ?? '') === 'overdue' ? 'danger' : 'secondary'))
                                    }} badge-lg">
                                        {{ ucfirst($invoice->status ?? 'Unknown') }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Total Amount:</label>
                                <p class="text-success h4 mb-0">${{ number_format($invoice->total_amount ?? 0, 2) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Amount Paid:</label>
                                <p class="text-info h5 mb-0">${{ number_format($invoice->amountpaid ?? 0, 2) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Remaining Balance:</label>
                                <p class="text-warning h5 mb-0">
                                    ${{ number_format(($invoice->total_amount ?? 0) - ($invoice->amountpaid ?? 0), 2) }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Due Date:</label>
                                <p class="text-gray-900 mb-0">
                                    @if($invoice->due_date)
                                        {{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}
                                        <small class="text-muted d-block">
                                            {{ \Carbon\Carbon::parse($invoice->due_date)->diffForHumans() }}
                                        </small>
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Lessons:</label>
                                <p class="text-gray-900 mb-0">{{ $invoice->lessons ?? 0 }} lessons</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Price per Lesson:</label>
                                <p class="text-gray-900 mb-0">${{ number_format($invoice->price_per_lesson ?? 0, 2) }}</p>
                            </div>
                        </div>
                        @if($invoice->notes)
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Notes:</label>
                                <p class="text-gray-900 mb-0">{{ $invoice->notes }}</p>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Created:</label>
                                <p class="text-muted mb-0">
                                    {{ $invoice->created_at ? \Carbon\Carbon::parse($invoice->created_at)->format('M d, Y g:i A') : 'Unknown' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="font-weight-bold text-gray-800">Last Updated:</label>
                                <p class="text-muted mb-0">
                                    {{ $invoice->updated_at ? \Carbon\Carbon::parse($invoice->updated_at)->format('M d, Y g:i A') : 'Unknown' }}
                                </p>
                            </div>
                        </div>
                    </div>
