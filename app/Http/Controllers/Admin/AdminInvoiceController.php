<?php

namespace App\Http\Controllers\Admin;

use App\Models\Invoice;
use App\Models\User;
use App\Models\Course;
use App\Models\Payment;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class AdminInvoiceController extends Controller
{
    /**
     * Display a listing of the resource
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        $schoolId = $this->getSchoolScope();

        try {
            // Build query with proper relationships - FIX COLUMN NAME MISMATCH
            $query = Invoice::query()
                           ->with(['student', 'course', 'payments']) // Eager load relationships
                           ->when($schoolId, function($q) use ($schoolId) {
                               // FIXED: Use correct foreign key column name
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

            // Filter by student - FIX COLUMN NAME
            if ($request->filled('student_id')) {
                $query->where('student', $request->student_id); // Use 'student' not 'student_id'
            }

            // Filter by course - FIX COLUMN NAME  
            if ($request->filled('course_id')) {
                $query->where('course', $request->course_id); // Use 'course' not 'course_id'
            }

            // Filter by date range
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Get paginated results with relationships already loaded
            $invoices = $query->orderBy('created_at', 'desc')->paginate(15);

            // Get filter options
            $students = User::where('role', 'student')
                           ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                           ->orderBy('fname')
                           ->get();

            $courses = Course::when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                            ->orderBy('name')
                            ->get();

            $schools = $user->role === 'super_admin' ? 
                      School::orderBy('name')->get() : 
                      collect();

            // Calculate statistics
            $stats = [
                'total_invoices' => $invoices->total(),
                'total_amount' => $invoices->getCollection()->sum('total_amount'),
                'total_paid' => $invoices->getCollection()->sum('amountpaid'),
                'pending_count' => $invoices->getCollection()->where('status', 'unpaid')->count(),
                'overdue_count' => $invoices->getCollection()->where('status', 'overdue')->count(),
            ];

            return view('admin.invoices.index', compact(
                'invoices', 'students', 'courses', 'schools', 'stats'
            ));

        } catch (\Exception $e) {
            Log::error('Invoice index failed: ' . $e->getMessage());
            
            return redirect()->back()
                           ->with('error', 'Failed to load invoices. Please try again.');
        }
    }

    /**
     * Get school scope for the current user
     */
    private function getSchoolScope()
    {
        $user = Auth::user();
        
        if (!$user) {
            return null;
        }

        // Super admin can see all schools
        if ($user->role === 'super_admin') {
            return null;
        }

        // School admin sees only their school
        if ($user->role === 'admin' && $user->school_id) {
            return $user->school_id;
        }

        return null;
    }

    /**
     * Show the form for creating a new resource
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
                'student' => $request->student,  // Using correct column name
                'course' => $request->course,    // Using correct column name
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
     * Export invoices to CSV
     */
    public function export(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }

        $schoolId = $this->getSchoolScope();

        try {
            // Build the same query as index method
            $query = Invoice::query()
                           ->with(['student', 'course', 'payments'])
                           ->when($schoolId, function($q) use ($schoolId) {
                               $q->whereHas('student', function($sq) use ($schoolId) {
                                   $sq->where('school_id', $schoolId);
                               });
                           });

            // Apply the same filters as index
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

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('student_id')) {
                $query->where('student', $request->student_id);
            }

            if ($request->filled('course_id')) {
                $query->where('course', $request->course_id);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Get all results (not paginated for export)
            $invoices = $query->orderBy('created_at', 'desc')->get();

            // Prepare CSV headers
            $headers = [
                'Invoice Number',
                'Student Name',
                'Student Email',
                'Student Phone',
                'Course Name',
                'Lessons',
                'Price Per Lesson',
                'Total Amount',
                'Amount Paid',
                'Balance',
                'Status',
                'Created Date',
                'Due Date',
                'Days Overdue',
                'Notes'
            ];

            // Generate filename with timestamp
            $filename = 'invoices_export_' . date('Y-m-d_H-i-s') . '.csv';

            // Create CSV content
            $csvContent = implode(',', $headers) . "\n";

            foreach ($invoices as $invoice) {
                $studentName = $invoice->student ? 
                              ($invoice->student->fname . ' ' . $invoice->student->lname) : 
                              'Unknown Student';
                
                $studentEmail = $invoice->student ? $invoice->student->email : 'No email';
                $studentPhone = $invoice->student ? $invoice->student->phone : '';
                $courseName = $invoice->course ? $invoice->course->name : ($invoice->courseName ?? 'Unknown Course');
                
                $balance = $invoice->total_amount - $invoice->amountpaid;
                $daysOverdue = $invoice->is_overdue ? $invoice->days_overdue : 0;
                
                $row = [
                    $invoice->invoice_number ?? '',
                    $studentName,
                    $studentEmail,
                    $studentPhone,
                    $courseName,
                    $invoice->lessons ?? 0,
                    number_format($invoice->price_per_lesson ?? 0, 2),
                    number_format($invoice->total_amount ?? 0, 2),
                    number_format($invoice->amountpaid ?? 0, 2),
                    number_format($balance, 2),
                    $invoice->status_display ?? 'unknown',
                    $invoice->created_at ? $invoice->created_at->format('Y-m-d') : '',
                    $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '',
                    $daysOverdue,
                    str_replace(['"', ',', "\n"], ['""', ';', ' '], $invoice->notes ?? '')
                ];

                // Escape CSV fields properly
                $escapedRow = array_map(function($field) {
                    return '"' . str_replace('"', '""', $field) . '"';
                }, $row);

                $csvContent .= implode(',', $escapedRow) . "\n";
            }

            // Return CSV download response
            return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            Log::error('Invoice export failed: ' . $e->getMessage());
            
            return redirect()->back()
                           ->with('error', 'Failed to export invoices. Please try again.');
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
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        try {
            DB::beginTransaction();

            // Calculate new total amount
            $totalAmount = $request->lessons * $request->price_per_lesson;

            $invoice->update([
                'student' => $request->student,
                'course' => $request->course,
                'lessons' => $request->lessons,
                'price_per_lesson' => $request->price_per_lesson,
                'total_amount' => $totalAmount,
                'due_date' => $request->due_date,
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

        // Don't allow deleting paid invoices
        if ($invoice->status === 'paid') {
            return redirect()->back()
                           ->with('error', 'Cannot delete paid invoices.');
        }

        try {
            DB::beginTransaction();

            // Delete related payments first
            $invoice->payments()->delete();
            
            // Delete the invoice
            $invoice->delete();

            DB::commit();

            return redirect()->route('admin.invoices.index')
                           ->with('success', 'Invoice deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice deletion failed: ' . $e->getMessage());

            return redirect()->back()
                           ->with('error', 'Failed to delete invoice. Please try again.');
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

            $remainingAmount = $invoice->total_amount - $invoice->amountpaid;

            // Create payment record
            Payment::create([
                'invoiceId' => $invoice->id,
                'userId' => $invoice->student,
                'amount' => $remainingAmount,
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
            $invoice->load(['student', 'course', 'payments']);

            // For now, return a simple text response
            // You can implement PDF generation using dompdf or similar
            $content = "Invoice: " . $invoice->invoice_number . "\n";
            $content .= "Student: " . ($invoice->student ? $invoice->student->fname . ' ' . $invoice->student->lname : 'Unknown') . "\n";
            $content .= "Course: " . ($invoice->course ? $invoice->course->name : 'Unknown Course') . "\n";
            $content .= "Amount: $" . number_format($invoice->total_amount, 2) . "\n";
            
            return response($content)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', 'attachment; filename="invoice-' . $invoice->invoice_number . '.txt"');

        } catch (\Exception $e) {
            Log::error('Invoice PDF generation failed: ' . $e->getMessage());

            return redirect()->back()
                           ->with('error', 'Failed to generate PDF. Please try again.');
        }
    }
}