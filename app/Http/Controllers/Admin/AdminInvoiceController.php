<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Invoice, User, Course, Payment, School};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminInvoiceController extends Controller
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
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        $schoolId = $this->getSchoolScope();

        // Build query with proper relationships and safe loading
        $query = Invoice::query()
                       ->when($schoolId, function($q) use ($schoolId) {
                           $q->whereHas('student', function($sq) use ($schoolId) {
                               $sq->where('school_id', $schoolId);
                           });
                       });

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('student', function($sq) use ($search) {
                      $sq->where('fname', 'like', "%{$search}%")
                        ->orWhere('lname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('course', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by student
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by course
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by school (super admin only)
        if ($request->filled('school_id') && !$schoolId) {
            $query->where('school_id', $request->school_id);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15);

        // Load relationships after pagination to avoid issues
        $invoices->load(['student', 'course', 'payments']);

        // Get filter options
        $students = User::where('role', 'student')
                       ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                       ->orderBy('fname')
                       ->get();

        $courses = Course::when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                        ->orderBy('name')
                        ->get();

        $schools = $user->role === 'super_admin' ?
                   School::orderBy('name')->get() : collect();

        // Calculate statistics with safe queries
        $stats = [
            'total_invoices' => $invoices->total(),
            'total_amount' => Invoice::when($schoolId, function($q) use ($schoolId) {
                return $q->whereHas('student', function($sq) use ($schoolId) {
                    $sq->where('school_id', $schoolId);
                });
            })->sum('total_amount') ?? 0,
            'paid_amount' => Invoice::when($schoolId, function($q) use ($schoolId) {
                return $q->whereHas('student', function($sq) use ($schoolId) {
                    $sq->where('school_id', $schoolId);
                });
            })->where('status', 'paid')->sum('total_amount') ?? 0,
            'pending_amount' => Invoice::when($schoolId, function($q) use ($schoolId) {
                return $q->whereHas('student', function($sq) use ($schoolId) {
                    $sq->where('school_id', $schoolId);
                });
            })->whereIn('status', ['unpaid', 'partial'])->sum('total_amount') ?? 0,
            'overdue_count' => Invoice::when($schoolId, function($q) use ($schoolId) {
                return $q->whereHas('student', function($sq) use ($schoolId) {
                    $sq->where('school_id', $schoolId);
                });
            })->where('status', 'overdue')->count(),
        ];

        return view('admin.invoices.index', compact('invoices', 'students', 'courses', 'schools', 'stats'));
    }

    /**
     * Show the form for creating a new invoice
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        $schoolId = $this->getSchoolScope() ?: $user->school_id;

        // Get students and courses for dropdowns
        $students = User::where('role', 'student')
                       ->where('status', 'active')
                       ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                       ->orderBy('fname')
                       ->get();

        $courses = Course::where('status', 'active')
                        ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                        ->orderBy('name')
                        ->get();

        // Pre-fill if student_id is provided
        $selectedStudent = null;
        if ($request->filled('student_id')) {
            $selectedStudent = User::find($request->student_id);
        }

        // Pre-fill if course_id is provided
        $selectedCourse = null;
        if ($request->filled('course_id')) {
            $selectedCourse = Course::find($request->course_id);
        }

        return view('admin.invoices.create', compact('students', 'courses', 'selectedStudent', 'selectedCourse'));
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        $schoolId = $this->getSchoolScope() ?: $user->school_id;

        $validator = Validator::make($request->all(), [
            'student' => 'required|exists:users,id',
            'course' => 'required|exists:courses,id',
            'lessons' => 'required|integer|min:1|max:100',
            'price_per_lesson' => 'required|numeric|min:0|max:9999.99',
            'due_date' => 'required|date|after:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Verify student and course belong to the same school
        $student = User::find($request->student);
        $course = Course::find($request->course);

        if ($schoolId) {
            if ($student->school_id !== $schoolId || $course->school_id !== $schoolId) {
                return redirect()->back()
                               ->with('error', 'Student and course must belong to your school.')
                               ->withInput();
            }
        }

        try {
            DB::beginTransaction();

            // Generate invoice number
            $lastInvoice = Invoice::orderBy('id', 'desc')->first();

            $nextNumber = $lastInvoice ? (intval(substr($lastInvoice->invoice_number, -6)) + 1) : 1;
            $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            // Calculate amounts
            $totalAmount = $request->lessons * $request->price_per_lesson;

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'student' => $request->student,
                'course' => $request->course,
                'lessons' => $request->lessons,
                'price_per_lesson' => $request->price_per_lesson,
                'total_amount' => $totalAmount,
                'amountpaid' => 0,
                'due_date' => $request->due_date,
                'status' => 'unpaid',
                'notes' => $request->notes,
            ]);

            DB::commit();

            return redirect()->route('admin.invoices.show', $invoice)
                           ->with('success', 'Invoice created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice creation failed: ' . $e->getMessage());

            return redirect()->back()
                           ->with('error', 'Failed to create invoice. Please try again.')
                           ->withInput();
        }
    }

    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        $schoolId = $this->getSchoolScope();

        // Check permissions - Load student relationship first
        $invoice->load('student');
        if ($schoolId && $invoice->student && $invoice->student->school_id !== $schoolId) {
            abort(403, 'Access denied.');
        }

        // Load relationships
        $invoice->load([
            'student',
            'course',
            'payments' => function($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);

        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the invoice
     */
    public function edit(Invoice $invoice)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        $schoolId = $this->getSchoolScope();

        // Check permissions - Load student relationship first
        $invoice->load('student');
        if ($schoolId && $invoice->student && $invoice->student->school_id !== $schoolId) {
            abort(403, 'Access denied.');
        }

        // Don't allow editing paid invoices
        if ($invoice->status === 'paid') {
            return redirect()->route('admin.invoices.show', $invoice)
                           ->with('error', 'Cannot edit paid invoices.');
        }

        // Get students and courses for dropdowns
        $students = User::where('role', 'student')
                       ->where('status', 'active')
                       ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                       ->orderBy('fname')
                       ->get();

        $courses = Course::where('status', 'active')
                        ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                        ->orderBy('name')
                        ->get();

        return view('admin.invoices.edit', compact('invoice', 'students', 'courses'));
    }

    /**
     * Update the specified invoice
     */
    public function update(Request $request, Invoice $invoice)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        $schoolId = $this->getSchoolScope();

        // Check permissions - Load student relationship first
        $invoice->load('student');
        if ($schoolId && $invoice->student && $invoice->student->school_id !== $schoolId) {
            abort(403, 'Access denied.');
        }

        // Don't allow editing paid invoices
        if ($invoice->status === 'paid') {
            return redirect()->route('admin.invoices.show', $invoice)
                           ->with('error', 'Cannot edit paid invoices.');
        }

        $validator = Validator::make($request->all(), [
            'student' => 'required|exists:users,id',
            'course' => 'required|exists:courses,id',
            'lessons' => 'required|integer|min:1|max:100',
            'price_per_lesson' => 'required|numeric|min:0|max:9999.99',
            'due_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:unpaid,partial,paid,overdue',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        try {
            DB::beginTransaction();

            // Calculate amounts
            $totalAmount = $request->lessons * $request->price_per_lesson;

            $invoice->update([
                'student' => $request->student,
                'course' => $request->course,
                'lessons' => $request->lessons,
                'price_per_lesson' => $request->price_per_lesson,
                'total_amount' => $totalAmount,
                'due_date' => $request->due_date,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            DB::commit();

            return redirect()->route('admin.invoices.show', $invoice)
                           ->with('success', 'Invoice updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice update failed: ' . $e->getMessage());

            return redirect()->back()
                           ->with('error', 'Failed to update invoice. Please try again.')
                           ->withInput();
        }
    }

    /**
     * Remove the specified invoice
     */
    public function destroy(Invoice $invoice)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        $schoolId = $this->getSchoolScope();

        // Check permissions - Load student relationship first
        $invoice->load('student');
        if ($schoolId && $invoice->student && $invoice->student->school_id !== $schoolId) {
            abort(403, 'Access denied.');
        }

        // Check if invoice has payments
        if ($invoice->payments()->count() > 0) {
            return redirect()->route('admin.invoices.index')
                           ->with('error', 'Cannot delete invoice that has existing payments.');
        }

        // Don't allow deleting paid invoices
        if ($invoice->status === 'paid') {
            return redirect()->route('admin.invoices.index')
                           ->with('error', 'Cannot delete paid invoices.');
        }

        try {
            $invoice->delete();

            return redirect()->route('admin.invoices.index')
                           ->with('success', 'Invoice deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Invoice deletion failed: ' . $e->getMessage());

            return redirect()->route('admin.invoices.index')
                           ->with('error', 'Failed to delete invoice. Please try again.');
        }
    }

    /**
     * Send invoice to student
     */
    public function sendInvoice(Invoice $invoice)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        $schoolId = $this->getSchoolScope();

        // Check permissions - Load student relationship first
        $invoice->load('student');
        if ($schoolId && $invoice->student && $invoice->student->school_id !== $schoolId) {
            abort(403, 'Access denied.');
        }

        try {
            // Here you would implement email sending logic
            // For now, we'll just mark it as sent and return success

            $invoice->update(['sent_at' => now()]);

            return redirect()->route('admin.invoices.show', $invoice)
                           ->with('success', 'Invoice sent successfully.');

        } catch (\Exception $e) {
            Log::error('Invoice sending failed: ' . $e->getMessage());

            return redirect()->back()
                           ->with('error', 'Failed to send invoice. Please try again.');
        }
    }

 /**
 * Download invoice as PDF
 */
public function downloadPdf(Invoice $invoice)
{
    $user = Auth::user();

    if (!$user || !$user->isAdmin()) {
        abort(403, 'Access denied. Administrator privileges required.');
    }

    $schoolId = $this->getSchoolScope();

    // Check permissions - Load student relationship first
    $invoice->load('student');
    if ($schoolId && $invoice->student && $invoice->student->school_id !== $schoolId) {
        abort(403, 'Access denied.');
    }

    try {
        // Load relationships first to ensure they exist
        $invoice->load(['student', 'course']);

        // Generate PDF
        $pdf = Pdf::loadView('admin.invoices.pdf', compact('invoice'))
                 ->setPaper('a4', 'portrait');

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");

    } catch (\Exception $e) {
        Log::error('Invoice PDF generation failed: ' . $e->getMessage());

        return redirect()->back()
                       ->with('error', 'Failed to generate PDF. Please try again.');
    }
}

/**
 * Mark invoice as paid
 */
public function markAsPaid(Invoice $invoice)
{
    $user = Auth::user();

    if (!$user || !$user->isAdmin()) {
        abort(403, 'Access denied. Administrator privileges required.');
    }

    $schoolId = $this->getSchoolScope();

    // Check permissions - Load student relationship first
    $invoice->load('student');
    if ($schoolId && $invoice->student && $invoice->student->school_id !== $schoolId) {
        abort(403, 'Access denied.');
    }

    if ($invoice->status === 'paid') {
        return redirect()->back()
                       ->with('info', 'Invoice is already marked as paid.');
    }

    try {
        DB::beginTransaction();

        // Create payment record
        Payment::create([
            'invoiceId' => $invoice->id,
            'userId' => $invoice->student,
            'amount' => $invoice->total_amount - $invoice->amountpaid,
            'method' => 'cash',
            'paymentDate' => now(),
            'status' => 'Paid',
            'notes' => 'Marked as paid by admin',
        ]);

        // Update invoice
        $invoice->update([
            'amountpaid' => $invoice->total_amount,
            'status' => 'paid',
        ]);

        DB::commit();

        return redirect()->route('admin.invoices.show', $invoice)
                       ->with('success', 'Invoice marked as paid successfully.');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Mark invoice as paid failed: ' . $e->getMessage());

        return redirect()->back()
                       ->with('error', 'Failed to mark invoice as paid. Please try again.');
    }
}
}
