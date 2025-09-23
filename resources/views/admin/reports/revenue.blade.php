{{-- resources/views/admin/reports/revenue.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Revenue Reports')

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
                        <a href="{{ route('admin.reports.index') }}" class="text-decoration-none">Reports</a>
                    </li>
                    <li class="breadcrumb-item active">Revenue Reports</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-line"></i> Revenue Reports
            </h1>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-primary" onclick="exportRevenueReport()">
                <i class="fas fa-download"></i> Export Report
            </button>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>

    <!-- Revenue Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format(\App\Models\Payment::sum('amount'), 2) }}
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
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">This Month</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format(\App\Models\Payment::whereMonth('created_at', date('m'))->sum('amount'), 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pending</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format(\App\Models\Invoice::where('status', 'unpaid')->sum('total_amount'), 2) }}
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
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Avg Per Student</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $totalRevenue = \App\Models\Payment::sum('amount');
                                    $totalStudents = \App\Models\User::where('role', 'student')->count();
                                    $avgPerStudent = $totalStudents > 0 ? $totalRevenue / $totalStudents : 0;
                                @endphp
                                ${{ number_format($avgPerStudent, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-chart-area"></i> Revenue Trend (Last 12 Months)
            </h6>
        </div>
        <div class="card-body">
            <div style="height: 300px; background: #f8f9fc; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                <div class="text-center text-muted">
                    <i class="fas fa-chart-line fa-3x mb-3"></i>
                    <p>Revenue chart will be displayed here</p>
                    <small>Chart integration coming soon</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Recent Payments
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Invoice</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(\App\Models\Payment::with(['user', 'invoice'])->latest()->limit(10)->get() as $payment)
                        <tr>
                            <td>{{ $payment->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-placeholder bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center me-2" 
                                         style="width: 28px; height: 28px; font-size: 0.7rem;">
                                        {{ strtoupper(substr($payment->user->fname ?? 'U', 0, 1) . substr($payment->user->lname ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $payment->user->full_name ?? 'Unknown User' }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($payment->invoice)
                                    <a href="{{ route('admin.invoices.show', $payment->invoice) }}" class="text-decoration-none">
                                        #{{ $payment->invoice->invoice_number }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-success">${{ number_format($payment->amount, 2) }}</strong>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ ucfirst($payment->payment_method ?? 'Cash') }}</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($payment->invoice)
                                    <a href="{{ route('admin.invoices.show', $payment->invoice) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-credit-card fa-3x mb-3"></i>
                                <p>No payments found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    function exportRevenueReport() {
        // Implement revenue report export
        window.location.href = '{{ route("admin.reports.export", "revenue") }}';
    }
</script>
@endsection
@endsection