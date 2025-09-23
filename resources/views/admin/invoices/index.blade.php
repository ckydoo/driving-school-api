{{-- resources/views/admin/invoices/index.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'Invoice Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-invoice-dollar"></i> Invoice Management
        </h1>
        <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus"></i> Create New Invoice
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
                                Total Invoices
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_invoices'] ?? 0 }}
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
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Revenue
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
                                Overdue Invoices
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['overdue_count'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
            <form method="GET" action="{{ route('admin.invoices.index') }}" class="row">
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
                                   placeholder="Search invoices, students...">
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="status" class="sr-only">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>
                                Unpaid
                            </option>
                            <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>
                                Partially Paid
                            </option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>
                                Paid
                            </option>
                            <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>
                                Overdue
                            </option>
                        </select>
                    </div>
                </div>

                @if(isset($students) && $students->count() > 0)
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="student" class="sr-only">Student</label>
                        <select class="form-control" id="student" name="student">
                            <option value="">All Students</option>
                            @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ request('student') == $student->id ? 'selected' : '' }}>
                                {{ $student->fname }} {{ $student->lname }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="date_from" class="sr-only">Date From</label>
                        <input type="date"
                               class="form-control"
                               id="date_from"
                               name="date_from"
                               value="{{ request('date_from') }}"
                               placeholder="Date From">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="date_to" class="sr-only">Date To</label>
                        <input type="date"
                               class="form-control"
                               id="date_to"
                               name="date_to"
                               value="{{ request('date_to') }}"
                               placeholder="Date To">
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

            @if(request()->hasAny(['search', 'status', 'student', 'date_from', 'date_to']))
            <div class="row mt-2">
                <div class="col-12">
                    <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i> Clear All Filters
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Invoices
                @if(isset($invoices))
                    <span class="badge badge-secondary ml-2">{{ $invoices->total() ?? 0 }}</span>
                @endif
            </h6>
        </div>
        <div class="card-body">
            @if(isset($invoices) && $invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">Invoice Number</th>
                                <th width="20%">Student</th>
                                <th width="15%">Course</th>
                                <th width="10%">Amount</th>
                                <th width="10%">Paid</th>
                                <th width="10%">Status</th>
                                <th width="10%">Due Date</th>
                                <th width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                            <tr>
                                <td>{{ $loop->iteration + ($invoices->currentPage() - 1) * $invoices->perPage() }}</td>
                                <td>
                                    <strong class="text-primary">{{ $invoice->invoice_number ?? 'N/A' }}</strong>
                                </td>
                                <td>
                                    @if($invoice->student)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-info text-white mr-2">
                                                {{ strtoupper(substr($invoice->student->fname ?? 'U', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">
                                                    {{ $invoice->student->fname ?? 'Unknown' }} {{ $invoice->student->lname ?? '' }}
                                                </div>
                                                <small class="text-muted">{{ $invoice->student->email ?? 'No email' }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">No Student</span>
                                    @endif
                                </td>
                                <td>
                                    @if($invoice->course)
                                        <div>
                                            <div class="font-weight-bold">{{ $invoice->course->name ?? 'Unknown Course' }}</div>
                                            <small class="text-muted">{{ $invoice->lessons ?? 0 }} lessons</small>
                                        </div>
                                    @else
                                        <span class="text-muted">No Course</span>
                                    @endif
                                </td>
                                <td>
                                    <strong class="text-success">${{ number_format($invoice->total_amount ?? 0, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="text-info">${{ number_format($invoice->amountpaid ?? 0, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{
                                        ($invoice->status ?? '') === 'paid' ? 'success' :
                                        (($invoice->status ?? '') === 'partial' ? 'warning' :
                                        (($invoice->status ?? '') === 'overdue' ? 'danger' : 'secondary'))
                                    }}">
                                        {{ ucfirst($invoice->status ?? 'Unknown') }}
                                    </span>
                                </td>
                                <td>
                                    @if($invoice->due_date)
                                        <div class="small">
                                            {{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ \Carbon\Carbon::parse($invoice->due_date)->diffForHumans() }}
                                        </div>
                                    @else
                                        <span class="text-muted">No due date</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.invoices.show', $invoice->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if($invoice->status !== 'paid')
                                        <a href="{{ route('admin.invoices.edit', $invoice->id) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Edit Invoice">
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
                                                <a class="dropdown-item"
                                                   href="{{ route('admin.invoices.downloadPdf', $invoice->id) }}">
                                                    <i class="fas fa-download"></i> Download PDF
                                                </a>

                                                @if($invoice->status !== 'paid')
                                                <form method="POST"
                                                      action="{{ route('admin.invoices.markAsPaid', $invoice->id) }}"
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="dropdown-item"
                                                            onclick="return confirm('Mark this invoice as paid?')">
                                                        <i class="fas fa-check-circle"></i> Mark as Paid
                                                    </button>
                                                </form>
                                                @endif

                                                <div class="dropdown-divider"></div>

                                                @if($invoice->status !== 'paid' && !$invoice->payments->count())
                                                <form method="POST"
                                                      action="{{ route('admin.invoices.destroy', $invoice->id) }}"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this invoice? This action cannot be undone.')">
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
                @if($invoices->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }}
                        of {{ $invoices->total() }} invoices
                    </div>
                    <div>
                        {{ $invoices->withQueryString()->links() }}
                    </div>
                </div>
                @endif

            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-file-invoice-dollar fa-4x text-gray-300"></i>
                    </div>
                    <h5 class="text-gray-600 mb-3">No Invoices Found</h5>

                    @if(request()->hasAny(['search', 'status', 'student', 'date_from', 'date_to']))
                        <p class="text-muted mb-4">
                            No invoices match your current filters.
                            <a href="{{ route('admin.invoices.index') }}" class="text-primary">
                                Clear filters
                            </a> to see all invoices.
                        </p>
                    @else
                        <p class="text-muted mb-4">
                            You haven't created any invoices yet. Get started by creating your first invoice.
                        </p>
                    @endif

                    <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Your First Invoice
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
    $('#status, #student').on('change', function() {
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
