{{-- resources/views/admin/invoices/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Invoices Management')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-invoice-dollar"></i> Invoices Management
        </h1>
        <div class="btn-group">
            <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Invoice
            </a>
            @if(isset($invoices) && $invoices->count() > 0)
            <button class="btn btn-success" onclick="exportInvoices()">
                <i class="fas fa-download"></i> Export
            </button>
            @endif
        </div>
    </div>

    <!-- Statistics Cards -->
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_invoices'] ?? 0 }}</div>
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
                                Total Amount
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($stats['total_amount'] ?? 0, 2) }}</div>
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
                                Pending
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_count'] ?? 0 }}</div>
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
                                Overdue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['overdue_count'] ?? 0 }}</div>
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

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <!-- Filters and Search -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filter Invoices
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.invoices.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="search">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Invoice #, student name, email...">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="student_id">Student</label>
                        <select class="form-control" id="student_id" name="student_id">
                            <option value="">All Students</option>
                            @foreach($students ?? [] as $student)
                                <option value="{{ $student->id }}" 
                                        {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->fname }} {{ $student->lname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="date_from">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="date_to">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Invoices
                @if(isset($invoices))
                    <span class="badge badge-primary ml-2">{{ $invoices->total() > 0 ? $invoices->total() : 0 }}</span>
                @endif
            </h6>
        </div>
        <div class="card-body">
            @if(isset($invoices) && $invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="invoicesTable">
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
                            <tr class="{{ $invoice->is_overdue ? 'table-danger' : '' }}">
                                <td>{{ $loop->iteration + ($invoices->currentPage() - 1) * $invoices->perPage() }}</td>
                                
                                <!-- Invoice Number -->
                                <td>
                                    <strong class="text-primary">{{ $invoice->invoice_number ?? 'N/A' }}</strong>
                                </td>
                                
                                <!-- Student Information -->
                                <td>
                                    @if($invoice->student)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-info text-white mr-2">
                                                {{ strtoupper(substr($invoice->student->fname ?? 'U', 0, 1)) }}{{ strtoupper(substr($invoice->student->lname ?? '', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">
                                                    {{ $invoice->student->fname ?? 'Unknown' }} {{ $invoice->student->lname ?? '' }}
                                                </div>
                                                <small class="text-muted">{{ $invoice->student->email ?? 'No email' }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-secondary text-white mr-2">
                                                U
                                            </div>
                                            <div>
                                                <div class="font-weight-bold text-muted">Unknown Student</div>
                                                <small class="text-muted">No email</small>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                
                                <!-- Course Information -->
                                <td>
                                    @if($invoice->course)
                                        <div>
                                            <div class="font-weight-bold">{{ $invoice->course->name ?? 'Unknown Course' }}</div>
                                            <small class="text-muted">{{ $invoice->lessons ?? 0 }} lessons</small>
                                        </div>
                                    @else
                                        <div>
                                            <div class="font-weight-bold text-muted">{{ $invoice->courseName ?? 'Unknown Course' }}</div>
                                            <small class="text-muted">{{ $invoice->lessons ?? 0 }} lessons</small>
                                        </div>
                                    @endif
                                </td>
                                
                                <!-- Amount -->
                                <td>
                                    <strong>${{ number_format($invoice->total_amount ?? 0, 2) }}</strong>
                                </td>
                                
                                <!-- Amount Paid -->
                                <td>
                                    ${{ number_format($invoice->amountpaid ?? 0, 2) }}
                                </td>
                                
                                <!-- Status -->
                                <td>
                                    <span class="badge {{ $invoice->status_badge_class ?? 'badge-secondary' }}">
                                        {{ ucfirst($invoice->status_display ?? 'unknown') }}
                                    </span>
                                    @if($invoice->is_overdue)
                                        <br><small class="text-danger">{{ $invoice->days_overdue }} days overdue</small>
                                    @endif
                                </td>
                                
                                <!-- Due Date -->
                                <td>
                                    @if($invoice->due_date)
                                        {{ $invoice->due_date->format('M j, Y') }}
                                        <br><small class="text-muted">{{ $invoice->due_date->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">No due date</span>
                                    @endif
                                </td>
                                
                                <!-- Actions -->
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.invoices.show', $invoice->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($invoice->status !== 'paid')
                                        <a href="{{ route('admin.invoices.edit', $invoice->id) }}" 
                                           class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                        
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    data-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('admin.invoices.downloadPdf', $invoice->id) }}">
                                                    <i class="fas fa-download"></i> Download PDF
                                                </a>
                                                
                                                @if($invoice->status !== 'paid')
                                                <a class="dropdown-item" href="{{ route('admin.payments.create', ['invoice_id' => $invoice->id]) }}">
                                                    <i class="fas fa-plus"></i> Add Payment
                                                </a>
                                                
                                                <form method="POST" action="{{ route('admin.invoices.markAsPaid', $invoice->id) }}" 
                                                      style="display: inline;" 
                                                      onsubmit="return confirm('Mark this invoice as paid?')">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="fas fa-check-circle"></i> Mark as Paid
                                                    </button>
                                                </form>
                                                @endif
                                                
                                                <div class="dropdown-divider"></div>
                                                
                                                <form method="POST" action="{{ route('admin.invoices.destroy', $invoice->id) }}" 
                                                      style="display: inline;" 
                                                      onsubmit="return confirm('Are you sure you want to delete this invoice?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
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
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        Showing {{ $invoices->firstItem() ?? 0 }} to {{ $invoices->lastItem() ?? 0 }} 
                        of {{ $invoices->total() }} results
                    </div>
                    {{ $invoices->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice-dollar fa-5x text-gray-300 mb-4"></i>
                    @if(request()->hasAny(['search', 'status', 'student_id', 'date_from', 'date_to']))
                        <p class="text-muted mb-4">
                            No invoices found matching your filters. 
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

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form when filters change
    $('#status, #student_id').on('change', function() {
        $(this).closest('form').submit();
    });

    // Show loading state when searching
    $('form').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        const originalHtml = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Filtering...');
        submitBtn.prop('disabled', true);

        // Re-enable after delay
        setTimeout(function() {
            submitBtn.html(originalHtml);
            submitBtn.prop('disabled', false);
        }, 2000);
    });

    // Initialize tooltips
    $('[title]').tooltip();
    
    // Initialize DataTable for better sorting/searching (optional)
    $('#invoicesTable').DataTable({
        "paging": false,
        "searching": false,
        "info": false,
        "ordering": true,
        "order": [[ 1, "desc" ]], // Sort by invoice number descending
        "columnDefs": [
            { "orderable": false, "targets": [8] } // Disable sorting on Actions column
        ]
    });
});

// Flash message auto-hide
@if(session()->has('success') || session()->has('error') || session()->has('warning'))
setTimeout(function() {
    $('.alert').fadeOut('slow');
}, 5000);
@endif

// Export function
function exportInvoices() {
    // Get current filter parameters to maintain them in export
    const urlParams = new URLSearchParams(window.location.search);
    const exportUrl = '{{ route("admin.invoices.export") }}?' + urlParams.toString();
    
    // Show loading message
    const exportBtn = event.target;
    const originalHtml = exportBtn.innerHTML;
    exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
    exportBtn.disabled = true;
    
    // Navigate to export URL
    window.location.href = exportUrl;
    
    // Re-enable button after a delay
    setTimeout(function() {
        exportBtn.innerHTML = originalHtml;
        exportBtn.disabled = false;
    }, 3000);
}
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

.table-danger {
    background-color: rgba(220, 53, 69, 0.1);
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