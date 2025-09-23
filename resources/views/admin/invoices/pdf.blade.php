{{-- resources/views/admin/invoices/pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number ?? 'N/A' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4e73df;
            padding-bottom: 20px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #4e73df;
            margin-bottom: 5px;
        }

        .invoice-title {
            font-size: 18px;
            color: #666;
        }

        .invoice-info {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .invoice-info-left,
        .invoice-info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .invoice-info-right {
            text-align: right;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #4e73df;
        }

        .info-line {
            margin-bottom: 5px;
        }

        .label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }

        .invoice-table th,
        .invoice-table td {
            padding: 12px 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .invoice-table th {
            background-color: #f8f9fc;
            font-weight: bold;
            color: #4e73df;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            background-color: #f8f9fc;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }

        .status-unpaid {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-partial {
            background-color: #fff3cd;
            color: #856404;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }

        .payment-summary {
            background-color: #f8f9fc;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #4e73df;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Driving School Management</div>
        <div class="invoice-title">INVOICE</div>
    </div>

    <div class="invoice-info">
        <div class="invoice-info-left">
            <div class="section-title">Bill To:</div>
            @if($invoice->student)
            <div class="info-line">
                <span class="label">Name:</span>
                {{ $invoice->student->fname ?? 'Unknown' }} {{ $invoice->student->lname ?? '' }}
            </div>
            <div class="info-line">
                <span class="label">Email:</span>
                {{ $invoice->student->email ?? 'No email' }}
            </div>
            @if($invoice->student->phone)
            <div class="info-line">
                <span class="label">Phone:</span>
                {{ $invoice->student->phone }}
            </div>
            @endif
            @else
            <div class="info-line">No student information available</div>
            @endif
        </div>

        <div class="invoice-info-right">
            <div class="section-title">Invoice Details:</div>
            <div class="info-line">
                <span class="label">Invoice #:</span>
                {{ $invoice->invoice_number ?? 'N/A' }}
            </div>
            <div class="info-line">
                <span class="label">Date:</span>
                {{ $invoice->created_at ? \Carbon\Carbon::parse($invoice->created_at)->format('M d, Y') : 'N/A' }}
            </div>
            <div class="info-line">
                <span class="label">Due Date:</span>
                {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') : 'N/A' }}
            </div>
            <div class="info-line">
                <span class="label">Status:</span>
                <span class="status-badge status-{{ strtolower($invoice->status ?? 'unpaid') }}">
                    {{ ucfirst($invoice->status ?? 'Unpaid') }}
                </span>
            </div>
        </div>
    </div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>Course</th>
                <th class="text-center">Lessons</th>
                <th class="text-right">Price per Lesson</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    @if($invoice->course)
                        <strong>{{ $invoice->course->name ?? 'Unknown Course' }}</strong>
                        @if($invoice->course->description)
                        <br><small>{{ $invoice->course->description }}</small>
                        @endif
                    @else
                        Unknown Course
                    @endif
                </td>
                <td class="text-center">{{ $invoice->lessons ?? 0 }}</td>
                <td class="text-right">${{ number_format($invoice->price_per_lesson ?? 0, 2) }}</td>
                <td class="text-right">${{ number_format($invoice->total_amount ?? 0, 2) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right">Total Amount:</td>
                <td class="text-right">${{ number_format($invoice->total_amount ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="payment-summary">
        <div class="section-title">Payment Summary</div>
        <div class="info-line">
            <span class="label">Total Amount:</span>
            ${{ number_format($invoice->total_amount ?? 0, 2) }}
        </div>
        <div class="info-line">
            <span class="label">Amount Paid:</span>
            ${{ number_format($invoice->amountpaid ?? 0, 2) }}
        </div>
        <div class="info-line">
            <span class="label"><strong>Balance Due:</strong></span>
            <strong>${{ number_format(($invoice->total_amount ?? 0) - ($invoice->amountpaid ?? 0), 2) }}</strong>
        </div>
    </div>

    @if($invoice->payments && $invoice->payments->count() > 0)
    <div class="section-title">Payment History</div>
    <table class="invoice-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Method</th>
                <th class="text-right">Amount</th>
                <th>Reference</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->payments as $payment)
            <tr>
                <td>
                    {{ $payment->paymentDate ? \Carbon\Carbon::parse($payment->paymentDate)->format('M d, Y') : 'N/A' }}
                </td>
                <td>{{ ucfirst($payment->method ?? 'Unknown') }}</td>
                <td class="text-right">${{ number_format($payment->amount ?? 0, 2) }}</td>
                <td>{{ $payment->reference ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($invoice->notes)
    <div class="section-title">Notes</div>
    <div style="background-color: #f8f9fc; padding: 15px; margin: 20px 0;">
        {{ $invoice->notes }}
    </div>
    @endif

    <div class="footer">
        <p>Thank you for choosing our driving school!</p>
        <p>Generated on {{ now()->format('M d, Y g:i A') }}</p>
    </div>
</body>
</html>
