<?php
// app/Http/Controllers/Api/InvoiceController.php

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends BaseController
{
    public function index(Request $request)
    {
        $query = Invoice::with(['student', 'course', 'payments']);


        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }

        if ($request->has('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        $invoices = $query->orderBy('created_at', 'desc')->get();

        return $this->sendResponse($invoices, 'Invoices retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student' => 'required|exists:users,id',
            'course' => 'required|exists:courses,id',
            'lessons' => 'required|integer|min:1',
            'price_per_lesson' => 'required|numeric|min:0',
            'due_date' => 'required|date|after:today',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        // Generate invoice number
        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT);

        $totalAmount = $request->lessons * $request->price_per_lesson;

        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'student' => $request->student,
            'course' => $request->course,
            'lessons' => $request->lessons,
            'price_per_lesson' => $request->price_per_lesson,
            'total_amount' => $totalAmount,
            'amount_paid' => 0,
            'due_date' => $request->due_date,
            'status' => 'unpaid',
            'notes' => $request->notes,
        ]);

        $invoice->load(['student', 'course']);

        return $this->sendResponse($invoice, 'Invoice created successfully.');
    }

    public function show($id)
    {
        $invoice = Invoice::with(['student', 'course', 'payments'])->find($id);

        if (is_null($invoice)) {
            return $this->sendError('Invoice not found.');
        }

        return $this->sendResponse($invoice, 'Invoice retrieved successfully.');
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::find($id);

        if (is_null($invoice)) {
            return $this->sendError('Invoice not found.');
        }

        $validator = Validator::make($request->all(), [
            'lessons' => 'sometimes|required|integer|min:1',
            'price_per_lesson' => 'sometimes|required|numeric|min:0',
            'due_date' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:unpaid,partially_paid,paid,overdue',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        // Recalculate total if lessons or price changed
        if ($request->has('lessons') || $request->has('price_per_lesson')) {
            $lessons = $request->lessons ?? $invoice->lessons;
            $pricePerLesson = $request->price_per_lesson ?? $invoice->price_per_lesson;
            $request->merge(['total_amount' => $lessons * $pricePerLesson]);
        }

        $invoice->update($request->only([
            'lessons', 'price_per_lesson', 'total_amount', 'due_date', 'status', 'notes'
        ]));

        $invoice->load(['student', 'course', 'payments']);

        return $this->sendResponse($invoice, 'Invoice updated successfully.');
    }

    public function destroy($id)
    {
        $invoice = Invoice::find($id);

        if (is_null($invoice)) {
            return $this->sendError('Invoice not found.');
        }

        // Check if invoice has payments
        if ($invoice->payments()->count() > 0) {
            return $this->sendError('Cannot delete invoice with existing payments.');
        }

        $invoice->delete();

        return $this->sendResponse([], 'Invoice deleted successfully.');
    }

    public function studentInvoices($studentId)
    {
        $student = User::find($studentId);

        if (is_null($student)) {
            return $this->sendError('Student not found.');
        }

        $invoices = Invoice::with(['course', 'payments'])
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->sendResponse($invoices, 'Student invoices retrieved successfully.');
    }
}
