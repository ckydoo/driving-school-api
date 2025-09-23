{{-- resources/views/admin/payments/index.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Payment Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-credit-card"></i> Payment Management
        </h1>
        <a href="{{ route('admin.payments.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus"></i> Record New Payment
        </a>
    </div>

    <!-- Quick Stats Cards -->
    @if(isset($stats))
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Payments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_payments'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
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
                                Total Amount
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($stats['total_amount'] ?? 0, 2) }}
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
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Amount
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($stats['pending_amount'] ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Failed Payments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['failed_count'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Search & Filters
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.payments.index') }}" class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="search" class="sr-only">Search</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <input type="text"
                                   class="form-control"
                                   id="search"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Search payments, references...">
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="status" class="sr-only">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="Paid" {{ request('status') === 'Paid' ? 'selected' : '' }}>
                                Paid
                            </option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>
                                Pending
                            </option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>
                                Failed
                            </option>
                            <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>
                                Refunded
                            </option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="method" class="sr-only">Payment Method</label>
                        <select class="form-control" id="method" name="method">
                            <option value="">All Methods</option>
                            <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="credit_card" {{ request('method') === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                            <option value="debit_card" {{ request('method') === 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                            <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="check" {{ request('method') === 'check' ? 'selected' : '' }}>Check</option>
                            <option value="other" {{ request('method') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="date_from" class="sr-only">Date From</label>
                        <input type="date"
                               class="form-control"
                               id="date_from"
                               name="date_from"
                               value="{{ request('date_from') }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="date_to" class="sr-only">Date To</label>
                        <input type="date"
                               class="form-control"
                               id="date_to"
                               name="date_to"
                               value="{{ request('date_to') }}">
                    </div>
                </div>

                <div class="col-md-1">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>

            @if(request()->hasAny(['search', 'status', 'method', 'date_from', 'date_to']))
            <div class="row mt-2">
                <div class="col-12">
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i> Clear All Filters
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Payments
                @if(isset($payments))
                    <span class="badge badge-secondary ml-2">{{ $payments->total() ?? 0 }}</span>
                @endif
            </h6>
        </div>
        <div class="card-body">
            @if(isset($payments) && $payments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">Invoice</th>
                                <th width="20%">Student</th>
                                <th width="12%">Amount</th>
                                <th width="10%">Method</th>
                                <th width="10%">Status</th>
                                <th width="13%">Payment Date</th>
                                <th width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                            <tr>
                                <td>{{ $loop->iteration + ($payments->currentPage() - 1) * $payments->perPage() }}</td>
                                <td>
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
                                        <div>
                                            <strong class="text-primary">{{ $invoiceData->invoice_number ?? 'N/A' }}</strong>
                                            <br><small class="text-muted">ID: {{ $invoiceData->id }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">Invoice ID: {{ $invoiceId ?? 'N/A' }}</span>
                                    @endif
                                </td>
                                <td>
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
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-success text-white mr-2">
                                                {{ strtoupper(substr($studentData->fname ?? 'U', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">
                                                    {{ $studentData->fname ?? 'Unknown' }} {{ $studentData->lname ?? '' }}
                                                </div>
                                                <small class="text-muted">{{ $studentData->email ?? 'No email' }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">User ID: {{ $userId ?? 'N/A' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong class="text-success">${{ number_format($payment->amount ?? 0, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-light">{{ ucfirst($payment->method ?? 'Unknown') }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{
                                        ($payment->status ?? '') === 'Paid' ? 'success' :
                                        (($payment->status ?? '') === 'pending' ? 'warning' :
                                        (($payment->status ?? '') === 'failed' ? 'danger' :
                                        (($payment->status ?? '') === 'refunded' ? 'info' : 'secondary')))
                                    }}">
                                        {{ $payment->status ?? 'Unknown' }}
                                    </span>
                                </td>
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
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.payments.show', $payment->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if(in_array($payment->status, ['pending', 'Paid']) && $payment->created_at && $payment->created_at->diffInHours(now()) <= 24)
                                        <a href="{{ route('admin.payments.edit', $payment->id) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Edit Payment">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif

                                        <div class="btn-group" role="group">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-info dropdown-toggle"
                                                    data-toggle="dropdown"
                                                    aria-haspopup="true"
                                                    aria-expanded="false">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                @if($payment->status === 'pending')
                                                <form method="POST"
                                                      action="{{ route('admin.payments.verify', $payment->id) }}"
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="dropdown-item"
                                                            onclick="return confirm('Verify this payment?')">
                                                        <i class="fas fa-check"></i> Verify Payment
                                                    </button>
                                                </form>
                                                @endif

                                                @if($payment->status === 'Paid')
                                                <form method="POST"
                                                      action="{{ route('admin.payments.refund', $payment->id) }}"
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="dropdown-item text-warning"
                                                            onclick="return confirm('Refund this payment? This action cannot be undone.')">
                                                        <i class="fas fa-undo"></i> Refund
                                                    </button>
                                                </form>
                                                @endif

                                                <div class="dropdown-divider"></div>

                                                @if($payment->created_at && $payment->created_at->diffInHours(now()) <= 24)
                                                <form method="POST"
                                                      action="{{ route('admin.payments.destroy', $payment->id) }}"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this payment? This action cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="dropdown-item text-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($payments->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }}
                        of {{ $payments->total() }} payments
                    </div>
                    <div>
                        {{ $payments->withQueryString()->links() }}
                    </div>
                </div>
                @endif

            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-credit-card fa-4x text-gray-300"></i>
                    </div>
                    <h5 class="text-gray-600 mb-3">No Payments Found</h5>

                    @if(request()->hasAny(['search', 'status', 'method', 'date_from', 'date_to']))
                        <p class="text-muted mb-4">
                            No payments match your current filters.
                            <a href="{{ route('admin.payments.index') }}" class="text-primary">
                                Clear filters
                            </a> to see all payments.
                        </p>
                    @else
                        <p class="text-muted mb-4">
                            No payments have been recorded yet. Get started by recording your first payment.
                        </p>
                    @endif

                    <a href="{{ route('admin.payments.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Record Your First Payment
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form when filters change
    $('#status, #method').on('change', function() {
        $(this).closest('form').submit();
    });

    // Show loading state when searching
    $('form').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        const originalHtml = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i>');
        submitBtn.prop('disabled', true);

        // Re-enable after delay
        setTimeout(function() {
            submitBtn.html(originalHtml);
            submitBtn.prop('disabled', false);
        }, 2000);
    });

    // Initialize tooltips
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
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 12px;
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

.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.text-gray-600 {
    color: #858796 !important;
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

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.badge {
    font-size: 0.75em;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.fa-spin {
    animation: spin 1s linear infinite;
}
</style>
@endpush
@endsection
