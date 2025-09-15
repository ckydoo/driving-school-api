<?php
// app/Http/Controllers/Api/PaymentController.php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PaymentController extends BaseController
{
    public function index(Request $request)
    {
        $query = Payment::with(['invoice', 'student']);

        // Filter by student
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by invoice
        if ($request->has('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();

        return $this->sendResponse($payments, 'Payments retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,bank_transfer,other',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $invoice = Invoice::find($request->invoice_id);

        // Check if payment amount doesn't exceed remaining balance
        $remainingBalance = $invoice->total_amount - $invoice->amount_paid;
        if ($request->amount > $remainingBalance) {
            return $this->sendError('Payment amount exceeds remaining balance.');
        }

        DB::beginTransaction();

        try {
            // Create payment
            $payment = Payment::create([
                'invoice_id' => $request->invoice_id,
                'student_id' => $invoice->student_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
            ]);

            // Update invoice
            $newAmountPaid = $invoice->amount_paid + $request->amount;
            $newStatus = 'partially_paid';

            if ($newAmountPaid >= $invoice->total_amount) {
                $newStatus = 'paid';
            }

            $invoice->update([
                'amount_paid' => $newAmountPaid,
                'status' => $newStatus,
            ]);

            DB::commit();

            $payment->load(['invoice', 'student']);

            return $this->sendResponse($payment, 'Payment created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('Payment creation failed.', [], 500);
        }
    }

    public function show($id)
    {
        $payment = Payment::with(['invoice', 'student'])->find($id);

        if (is_null($payment)) {
            return $this->sendError('Payment not found.');
        }

        return $this->sendResponse($payment, 'Payment retrieved successfully.');
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::find($id);

        if (is_null($payment)) {
            return $this->sendError('Payment not found.');
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'sometimes|required|numeric|min:0.01',
            'payment_method' => 'sometimes|required|in:cash,card,bank_transfer,other',
            'payment_date' => 'sometimes|required|date',
            'reference_number' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            $oldAmount = $payment->amount;
            $newAmount = $request->amount ?? $oldAmount;

            // Update payment
            $payment->update($request->only([
                'amount', 'payment_method', 'payment_date', 'reference_number', 'notes'
            ]));

            // Update invoice if amount changed
            if ($request->has('amount')) {
                $invoice = $payment->invoice;
                $newAmountPaid = $invoice->amount_paid - $oldAmount + $newAmount;

                $newStatus = 'partially_paid';
                if ($newAmountPaid >= $invoice->total_amount) {
                    $newStatus = 'paid';
                } elseif ($newAmountPaid <= 0) {
                    $newStatus = 'unpaid';
                }

                $invoice->update([
                    'amount_paid' => $newAmountPaid,
                    'status' => $newStatus,
                ]);
            }

            DB::commit();

            $payment->load(['invoice', 'student']);

            return $this->sendResponse($payment, 'Payment updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('Payment update failed.', [], 500);
        }
    }

    public function destroy($id)
    {
        $payment = Payment::find($id);

        if (is_null($payment)) {
            return $this->sendError('Payment not found.');
        }

        DB::beginTransaction();

        try {
            $invoice = $payment->invoice;
            $paymentAmount = $payment->amount;

            // Delete payment
            $payment->delete();

            // Update invoice
            $newAmountPaid = $invoice->amount_paid - $paymentAmount;
            $newStatus = 'unpaid';

            if ($newAmountPaid > 0) {
                $newStatus = 'partially_paid';
            }

            $invoice->update([
                'amount_paid' => $newAmountPaid,
                'status' => $newStatus,
            ]);

            DB::commit();

            return $this->sendResponse([], 'Payment deleted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('Payment deletion failed.', [], 500);
        }
    }
}
