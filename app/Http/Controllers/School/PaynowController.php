<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionInvoice;
use App\Services\PaynowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaynowController extends Controller
{
    protected $paynowService;

    public function __construct(PaynowService $paynowService)
    {
        $this->paynowService = $paynowService;
    }

    public function initiatePayment(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:subscription_invoices,id'
        ]);

        $user = Auth::user();
        $invoice = SubscriptionInvoice::findOrFail($request->invoice_id);

        if ($invoice->school_id !== $user->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to invoice'
            ], 403);
        }

        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already paid'
            ], 400);
        }

        $result = $this->paynowService->initializePayment($invoice);

        return response()->json($result);
    }

    public function initiateMobilePayment(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:subscription_invoices,id',
            'phone_number' => 'required|regex:/^07[0-9]{8}$/',
            'method' => 'required|in:ecocash,onemoney'
        ]);

        $user = Auth::user();
        $invoice = SubscriptionInvoice::findOrFail($request->invoice_id);

        if ($invoice->school_id !== $user->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to invoice'
            ], 403);
        }

        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already paid'
            ], 400);
        }

        $result = $this->paynowService->initializeMobilePayment(
            $invoice,
            $request->phone_number,
            $request->method
        );

        return response()->json($result);
    }

    public function checkStatus(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:subscription_invoices,id'
        ]);

        $user = Auth::user();
        $invoice = SubscriptionInvoice::findOrFail($request->invoice_id);

        if ($invoice->school_id !== $user->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to invoice'
            ], 403);
        }

        $gatewayData = $invoice->payment_gateway_data;

        if (!isset($gatewayData['poll_url'])) {
            return response()->json([
                'success' => false,
                'message' => 'No payment initiated for this invoice'
            ], 400);
        }

        $result = $this->paynowService->checkPaymentStatus($gatewayData['poll_url']);

        if ($result['success'] && $result['paid']) {
            $paymentData = [
                'amount' => $result['amount'],
                'payment_method' => 'paynow',
                'status' => 'completed',
                'payment_date' => now(),
                'transaction_id' => $result['paynow_reference'],
                'reference_number' => $result['reference'],
                'gateway_response' => $result,
                'notes' => 'Payment processed via Paynow'
            ];

            $invoice->school->processSubscriptionPayment($invoice, $paymentData);
        }

        return response()->json($result);
    }

    public function handleCallback(Request $request)
    {
        Log::info('Paynow callback received', $request->all());

        $result = $this->paynowService->processCallback($request->all());

        return response($result['success'] ? 'OK' : 'ERROR', $result['success'] ? 200 : 400);
    }

    public function handleReturn(Request $request)
    {
        $reference = $request->get('reference');

        if (!$reference) {
            return redirect()->route('school.subscription.billing')
                ->with('error', 'Invalid return from payment gateway');
        }

        $invoice = SubscriptionInvoice::where('invoice_number', $reference)->first();

        if (!$invoice) {
            return redirect()->route('school.subscription.billing')
                ->with('error', 'Invoice not found');
        }

        $gatewayData = $invoice->payment_gateway_data;
        
        if (isset($gatewayData['poll_url'])) {
            $result = $this->paynowService->checkPaymentStatus($gatewayData['poll_url']);

            if ($result['success'] && $result['paid']) {
                return redirect()->route('school.subscription.billing')
                    ->with('success', 'Payment successful! Your subscription has been updated.');
            }
        }

        return redirect()->route('school.subscription.billing')
            ->with('info', 'Payment is being processed. Please wait a few moments and refresh the page.');
    }
}