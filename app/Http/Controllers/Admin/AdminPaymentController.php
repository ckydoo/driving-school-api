<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Payment, Invoice, User, School};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminPaymentController extends Controller
{
    /**
     * Get school scope for current user
     */
    private function getSchoolScope()
    {
        $user = auth()->user();

        if ($user->role === 'super_admin') {
            return null; // No restriction
        }

        return $user->school_id;
    }

    /**
     * Display a listing of payments
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        $schoolId = $this->getSchoolScope();

        // Build query with proper relationships
        $query = Payment::with(['invoice.student', 'invoice.course', 'user'])
                       ->when($schoolId, function($q) use ($schoolId) {
                           $q->whereHas('invoice.student', function($sq) use ($schoolId) {
                               $sq->where('school_id', $schoolId);
                           });
                       });

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%")
                  ->orWhereHas('user', function($sq) use ($search) {
                      $sq->where('fname', 'like', "%{$search}%")
                        ->orWhere('lname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('invoice', function($iq) use ($search) {
                      $iq->where('invoice_number', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        // Filter by student
        if ($request->filled('userId')) {
            $query->where('userId', $request->userId);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('paymentDate', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('paymentDate', '<=', $request->date_to);
        }

        $payments = $query->orderBy('paymentDate', 'desc')->paginate(15);

        // Get filter options
        $students = User::where('role', 'student')
                       ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                       ->orderBy('fname')
                       ->get();

        $schools = $user->role === 'super_admin' ?
                   School::orderBy('name')->get() : collect();

        // Calculate statistics
        $stats = [
            'total_payments' => $payments->total(),
            'total_amount' => Payment::when($schoolId, function($q) use ($schoolId) {
                                $q->whereHas('invoice', function($iq) use ($schoolId) {
                                    $iq->where('school_id', $schoolId);
                                });
                            })->where('status', 'completed')->sum('amount'),
            'pending_amount' => Payment::when($schoolId, function($q) use ($schoolId) {
                                  $q->whereHas('invoice', function($iq) use ($schoolId) {
                                      $iq->where('school_id', $schoolId);
                                  });
                              })->where('status', 'pending')->sum('amount'),
            'failed_count' => Payment::when($schoolId, function($q) use ($schoolId) {
                                $q->whereHas('invoice', function($iq) use ($schoolId) {
                                    $iq->where('school_id', $schoolId);
                                });
                            })->where('status', 'failed')->count(),
        ];

        return view('admin.payments.index', compact('payments', 'students', 'schools', 'stats'));
    }

    /**
     * Show the form for creating a new payment
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        $schoolId = $this->getSchoolScope();

        // Get unpaid/partially paid invoices
        $invoices = Invoice::with(['student', 'course'])
                          ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                          ->whereIn('status', ['unpaid', 'partially_paid'])
                          ->orderBy('created_at', 'desc')
                          ->get();

        // Pre-fill if invoice_id is provided
        $selectedInvoice = null;
        if ($request->filled('invoice_id')) {
            $selectedInvoice = Invoice::with(['student', 'course'])->find($request->invoice_id);

            // Check permissions
            if ($schoolId && $selectedInvoice && $selectedInvoice->school_id !== $schoolId) {
                $selectedInvoice = null;
            }
        }

        return view('admin.payments.create', compact('invoices', 'selectedInvoice'));
    }

    /**
     * Store a newly created payment
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        $schoolId = $this->getSchoolScope();

        $validator = Validator::make($request->all(), [
            'invoiceId' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'method' => 'required|in:cash,credit_card,debit_card,bank_transfer,check,paypal,other',
            'paymentDate' => 'required|date|before_or_equal:today',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Get and verify invoice
        $invoice = Invoice::find($request->invoiceId);

        if ($schoolId && $invoice->student->school_id !== $schoolId) {
            return redirect()->back()
                           ->with('error', 'Invoice does not belong to your school.')
                           ->withInput();
        }

        // Check if payment amount doesn't exceed remaining balance
        $remainingBalance = $invoice->total_amount - $invoice->amountpaid;
        if ($request->amount > $remainingBalance) {
            return redirect()->back()
                           ->with('error', "Payment amount cannot exceed remaining balance of $" . number_format($remainingBalance, 2))
                           ->withInput();
        }

        try {
            DB::beginTransaction();

            // Create payment
            $payment = Payment::create([
                'invoiceId' => $request->invoiceId,
                'userId' => $invoice->student,
                'amount' => $request->amount,
                'method' => $request->method,
                'paymentDate' => $request->paymentDate,
                'reference' => $request->reference,
                'status' => 'Paid',
                'notes' => $request->notes,
            ]);

            // Update invoice amounts and status
            $newAmountPaid = $invoice->amountpaid + $request->amount;
            $newStatus = 'partial';

            if ($newAmountPaid >= $invoice->total_amount) {
                $newStatus = 'paid';
            }

            $invoice->update([
                'amountpaid' => $newAmountPaid,
                'status' => $newStatus,
            ]);

            DB::commit();

            return redirect()->route('admin.payments.show', $payment)
                           ->with('success', 'Payment recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment creation failed: ' . $e->getMessage());

            return redirect()->back()
                           ->with('error', 'Failed to record payment. Please try again.')
                           ->withInput();
        }
    }

    /**
     * Display the specified payment
     */
    public function show(Payment $payment)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        $schoolId = $this->getSchoolScope();

        // Load relationships safely first
        try {
            $payment->load(['invoice', 'user']);
        } catch (\Exception $e) {
            \Log::error('Error loading payment relationships: ' . $e->getMessage());
        }

        // Check permissions - safe access to nested relationships
        if ($schoolId) {
            $hasAccess = false;

            // Try multiple ways to get the school ID for permission check
            if ($payment->invoice && is_object($payment->invoice)) {
                // Try to load the student relationship on the invoice
                try {
                    $payment->invoice->load('student');
                    if ($payment->invoice->student && is_object($payment->invoice->student)) {
                        $hasAccess = ($payment->invoice->student->school_id === $schoolId);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error loading invoice student: ' . $e->getMessage());
                }

                // If invoice->student relationship failed, try direct query
                if (!$hasAccess && is_numeric($payment->invoice->student)) {
                    $student = \App\Models\User::find($payment->invoice->student);
                    if ($student && $student->school_id === $schoolId) {
                        $hasAccess = true;
                    }
                }
            }

            // If we still don't have access and can't determine school, deny access
            if (!$hasAccess) {
                abort(403, 'Access denied.');
            }
        }

        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the payment
     */
    public function edit(Payment $payment)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        $schoolId = $this->getSchoolScope();

        // Load relationships safely
        try {
            $payment->load(['invoice', 'user']);
        } catch (\Exception $e) {
            \Log::error('Error loading payment relationships for edit: ' . $e->getMessage());
        }

        // Check permissions - safe access to nested relationships
        if ($schoolId) {
            $hasAccess = false;

            if ($payment->invoice && is_object($payment->invoice)) {
                // Try to load the student relationship
                try {
                    $payment->invoice->load('student');
                    if ($payment->invoice->student && is_object($payment->invoice->student)) {
                        $hasAccess = ($payment->invoice->student->school_id === $schoolId);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error loading invoice student for edit: ' . $e->getMessage());
                }

                // Fallback to direct query if relationship failed
                if (!$hasAccess && is_numeric($payment->invoice->student)) {
                    $student = \App\Models\User::find($payment->invoice->student);
                    if ($student && $student->school_id === $schoolId) {
                        $hasAccess = true;
                    }
                }
            }

            if (!$hasAccess) {
                abort(403, 'Access denied.');
            }
        }

        // Don't allow editing completed payments older than 24 hours
        if ($payment->status === 'Paid' && $payment->created_at->diffInHours(now()) > 24) {
            return redirect()->route('admin.payments.show', $payment)
                           ->with('error', 'Cannot edit payments older than 24 hours.');
        }

        return view('admin.payments.edit', compact('payment'));
    }

    /**
     * Update the specified payment
     */
    public function update(Request $request, Payment $payment)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        $schoolId = $this->getSchoolScope();

        // Check permissions with safe relationship access
        if ($schoolId) {
            $hasAccess = false;

            // Load invoice relationship if not already loaded
            if (!$payment->relationLoaded('invoice')) {
                $payment->load('invoice');
            }

            if ($payment->invoice && is_object($payment->invoice)) {
                // Try to get student school ID safely
                try {
                    $payment->invoice->load('student');
                    if ($payment->invoice->student && is_object($payment->invoice->student)) {
                        $hasAccess = ($payment->invoice->student->school_id === $schoolId);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error checking permissions for payment update: ' . $e->getMessage());
                }

                // Fallback to direct query
                if (!$hasAccess && is_numeric($payment->invoice->student)) {
                    $student = \App\Models\User::find($payment->invoice->student);
                    if ($student && $student->school_id === $schoolId) {
                        $hasAccess = true;
                    }
                }
            }

            if (!$hasAccess) {
                abort(403, 'Access denied.');
            }
        }

        // Don't allow editing completed payments older than 24 hours
        if ($payment->status === 'Paid' && $payment->created_at->diffInHours(now()) > 24) {
            return redirect()->route('admin.payments.show', $payment)
                           ->with('error', 'Cannot edit payments older than 24 hours.');
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'method' => 'required|in:cash,credit_card,debit_card,bank_transfer,check,paypal,other',
            'paymentDate' => 'required|date|before_or_equal:today',
            'reference' => 'nullable|string|max:255',
            'status' => 'required|in:pending,Paid,failed,refunded',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Get invoice and check payment amount constraints
        $invoice = $payment->invoice;
        if ($invoice && is_object($invoice)) {
            $otherPayments = $invoice->payments()->where('id', '!=', $payment->id)->where('status', 'Paid')->sum('amount');
            $maxAmount = $invoice->total_amount - $otherPayments;

            if ($request->amount > $maxAmount) {
                return redirect()->back()
                               ->with('error', "Payment amount cannot exceed $" . number_format($maxAmount, 2))
                               ->withInput();
            }
        }

        try {
            DB::beginTransaction();

            $oldAmount = $payment->amount;
            $oldStatus = $payment->status;

            // Update payment
            $payment->update([
                'amount' => $request->amount,
                'method' => $request->method,
                'paymentDate' => $request->paymentDate,
                'reference' => $request->reference,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            // Recalculate invoice amounts if amount or status changed
            if ($invoice && is_object($invoice) && ($oldAmount != $request->amount || $oldStatus != $request->status)) {
                $completedPayments = $invoice->payments()->where('status', 'Paid')->sum('amount');

                $newStatus = 'unpaid';
                if ($completedPayments > 0) {
                    $newStatus = $completedPayments >= $invoice->total_amount ? 'paid' : 'partial';
                }

                $invoice->update([
                    'amountpaid' => $completedPayments,
                    'status' => $newStatus,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.payments.show', $payment)
                           ->with('success', 'Payment updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment update failed: ' . $e->getMessage());

            return redirect()->back()
                           ->with('error', 'Failed to update payment. Please try again.')
                           ->withInput();
        }
    }

    /**
     * Remove the specified payment
     */
    public function destroy(Payment $payment)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        $schoolId = $this->getSchoolScope();

        // Check permissions with safe relationship access
        if ($schoolId) {
            $hasAccess = false;

            try {
                $payment->load('invoice');
                if ($payment->invoice && is_object($payment->invoice)) {
                    $payment->invoice->load('student');
                    if ($payment->invoice->student && is_object($payment->invoice->student)) {
                        $hasAccess = ($payment->invoice->student->school_id === $schoolId);
                    } elseif (is_numeric($payment->invoice->student)) {
                        // Fallback to direct query
                        $student = \App\Models\User::find($payment->invoice->student);
                        if ($student && $student->school_id === $schoolId) {
                            $hasAccess = true;
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error checking permissions for payment deletion: ' . $e->getMessage());
            }

            if (!$hasAccess) {
                abort(403, 'Access denied.');
            }
        }

        // Don't allow deleting completed payments older than 24 hours
        if ($payment->status === 'Paid' && $payment->created_at->diffInHours(now()) > 24) {
            return redirect()->route('admin.payments.index')
                           ->with('error', 'Cannot delete payments older than 24 hours.');
        }

        try {
            DB::beginTransaction();

            $invoice = $payment->invoice;

            // Delete payment
            $payment->delete();

            // Recalculate invoice amounts if invoice exists and is an object
            if ($invoice && is_object($invoice)) {
                $completedPayments = $invoice->payments()->where('status', 'Paid')->sum('amount');

                $newStatus = 'unpaid';
                if ($completedPayments > 0) {
                    $newStatus = $completedPayments >= $invoice->total_amount ? 'paid' : 'partial';
                }

                $invoice->update([
                    'amountpaid' => $completedPayments,
                    'status' => $newStatus,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.payments.index')
                           ->with('success', 'Payment deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment deletion failed: ' . $e->getMessage());

            return redirect()->route('admin.payments.index')
                           ->with('error', 'Failed to delete payment. Please try again.');
        }
    }

    /**
     * Verify payment (for pending payments)
     */
    public function verifyPayment(Payment $payment)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        $schoolId = $this->getSchoolScope();

        // Check permissions
        if ($schoolId && $payment->invoice->school_id !== $schoolId) {
            abort(403, 'Access denied.');
        }

        if ($payment->status !== 'pending') {
            return redirect()->back()
                           ->with('info', 'Payment is not in pending status.');
        }

        try {
            DB::beginTransaction();

            // Update payment status
            $payment->update([
                'status' => 'completed',
                'verified_by' => $user->id,
                'verified_at' => now(),
            ]);

            // Update invoice amounts
            $completedPayments = $payment->invoice->payments()->where('status', 'completed')->sum('amount');

            $newStatus = $completedPayments >= $payment->invoice->total_amount ? 'paid' : 'partially_paid';

            $updateData = [
                'amount_paid' => $completedPayments,
                'status' => $newStatus,
            ];

            if ($newStatus === 'paid') {
                $updateData['paid_at'] = now();
            }

            $payment->invoice->update($updateData);

            DB::commit();

            return redirect()->route('admin.payments.show', $payment)
                           ->with('success', 'Payment verified successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment verification failed: ' . $e->getMessage());

            return redirect()->back()
                           ->with('error', 'Failed to verify payment. Please try again.');
        }
    }

    /**
     * Refund payment
     */
    public function refund(Payment $payment)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        $schoolId = $this->getSchoolScope();

        // Check permissions
        if ($schoolId && $payment->invoice->school_id !== $schoolId) {
            abort(403, 'Access denied.');
        }

        if ($payment->status !== 'completed') {
            return redirect()->back()
                           ->with('error', 'Can only refund completed payments.');
        }

        if ($payment->status === 'refunded') {
            return redirect()->back()
                           ->with('info', 'Payment is already refunded.');
        }

        try {
            DB::beginTransaction();

            // Update payment status
            $payment->update([
                'status' => 'refunded',
                'refunded_by' => $user->id,
                'refunded_at' => now(),
            ]);

            // Update invoice amounts
            $completedPayments = $payment->invoice->payments()->where('status', 'completed')->sum('amount');

            $newStatus = 'unpaid';
            if ($completedPayments > 0) {
                $newStatus = $completedPayments >= $payment->invoice->total_amount ? 'paid' : 'partially_paid';
            }

            $updateData = [
                'amount_paid' => $completedPayments,
                'status' => $newStatus,
            ];

            if ($newStatus === 'paid' && !$payment->invoice->paid_at) {
                $updateData['paid_at'] = now();
            } elseif ($newStatus !== 'paid') {
                $updateData['paid_at'] = null;
            }

            $payment->invoice->update($updateData);

            DB::commit();

            return redirect()->route('admin.payments.show', $payment)
                           ->with('success', 'Payment refunded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment refund failed: ' . $e->getMessage());

            return redirect()->back()
                           ->with('error', 'Failed to refund payment. Please try again.');
        }
    }

    /**
     * Get payment statistics for dashboard
     */
    public function getStats(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $schoolId = $this->getSchoolScope();

        try {
            $stats = [
                'today' => Payment::when($schoolId, function($q) use ($schoolId) {
                            $q->whereHas('invoice', function($iq) use ($schoolId) {
                                $iq->where('school_id', $schoolId);
                            });
                        })
                        ->whereDate('payment_date', today())
                        ->where('status', 'completed')
                        ->sum('amount'),

                'this_week' => Payment::when($schoolId, function($q) use ($schoolId) {
                                $q->whereHas('invoice', function($iq) use ($schoolId) {
                                    $iq->where('school_id', $schoolId);
                                });
                            })
                            ->whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()])
                            ->where('status', 'completed')
                            ->sum('amount'),

                'this_month' => Payment::when($schoolId, function($q) use ($schoolId) {
                                 $q->whereHas('invoice', function($iq) use ($schoolId) {
                                     $iq->where('school_id', $schoolId);
                                 });
                             })
                             ->whereMonth('payment_date', now()->month)
                             ->whereYear('payment_date', now()->year)
                             ->where('status', 'completed')
                             ->sum('amount'),

                'pending_count' => Payment::when($schoolId, function($q) use ($schoolId) {
                                    $q->whereHas('invoice', function($iq) use ($schoolId) {
                                        $iq->where('school_id', $schoolId);
                                    });
                                })
                                ->where('status', 'pending')
                                ->count(),
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Payment stats failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get stats'], 500);
        }
    }
}
