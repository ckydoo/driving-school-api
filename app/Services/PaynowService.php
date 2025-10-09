<?php

namespace App\Services;

use Paynow\Payments\Paynow;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPayment;
use Illuminate\Support\Facades\Log;

class PaynowService
{
    protected $paynow;

    public function __construct()
    {
        $this->paynow = new Paynow(
            config('services.paynow.integration_id'),
            config('services.paynow.integration_key'),
            config('services.paynow.result_url'),
            config('services.paynow.return_url')
        );
    }

    public function initializePayment(SubscriptionInvoice $invoice)
    {
        try {
            $payment = $this->paynow->createPayment(
                $invoice->invoice_number,
                $invoice->school->email
            );

            $payment->add(
                "Subscription: {$invoice->invoice_data['package_name']} ({$invoice->billing_period})",
                $invoice->total_amount
            );

            $response = $this->paynow->send($payment);

            if ($response->success()) {
                $invoice->update([
                    'payment_gateway_data' => [
                        'gateway' => 'paynow',
                        'poll_url' => $response->pollUrl(),
                        'redirect_url' => $response->redirectUrl(),
                        'initiated_at' => now()->toIso8601String()
                    ]
                ]);

                return [
                    'success' => true,
                    'redirect_url' => $response->redirectUrl(),
                    'poll_url' => $response->pollUrl()
                ];
            }

            Log::error('Paynow payment initialization failed', [
                'invoice_id' => $invoice->id,
                'error' => $response->errors()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to initialize payment. Please try again.'
            ];

        } catch (\Exception $e) {
            Log::error('Paynow payment exception', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'An error occurred while processing your payment.'
            ];
        }
    }

    public function initializeMobilePayment(SubscriptionInvoice $invoice, string $phoneNumber, string $method = 'ecocash')
    {
        try {
            $payment = $this->paynow->createPayment(
                $invoice->invoice_number,
                $invoice->school->email
            );

            $payment->add(
                "Subscription: {$invoice->invoice_data['package_name']} ({$invoice->billing_period})",
                $invoice->total_amount
            );

            $response = $this->paynow->sendMobile($payment, $phoneNumber, $method);

            if ($response->success()) {
                $invoice->update([
                    'payment_gateway_data' => [
                        'gateway' => 'paynow_mobile',
                        'method' => $method,
                        'phone' => $phoneNumber,
                        'poll_url' => $response->pollUrl(),
                        'initiated_at' => now()->toIso8601String()
                    ]
                ]);

                return [
                    'success' => true,
                    'instructions' => $response->instructions(),
                    'poll_url' => $response->pollUrl()
                ];
            }

            Log::error('Paynow mobile payment initialization failed', [
                'invoice_id' => $invoice->id,
                'error' => $response->errors()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to initialize mobile payment. Please try again.'
            ];

        } catch (\Exception $e) {
            Log::error('Paynow mobile payment exception', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'An error occurred while processing your payment.'
            ];
        }
    }

    public function checkPaymentStatus(string $pollUrl)
    {
        try {
            $status = $this->paynow->pollTransaction($pollUrl);

            return [
                'success' => true,
                'paid' => $status->paid(),
                'status' => $status->status(),
                'amount' => $status->amount(),
                'reference' => $status->reference(),
                'paynow_reference' => $status->paynowreference(),
                'hash' => $status->hash()
            ];

        } catch (\Exception $e) {
            Log::error('Paynow status check exception', [
                'poll_url' => $pollUrl,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to check payment status.'
            ];
        }
    }

    public function processCallback(array $data)
    {
        try {
            $invoice = SubscriptionInvoice::where('invoice_number', $data['reference'])->first();

            if (!$invoice) {
                Log::warning('Paynow callback for unknown invoice', ['reference' => $data['reference']]);
                return ['success' => false, 'error' => 'Invoice not found'];
            }

            if (!$this->verifyHash($data)) {
                Log::warning('Paynow callback hash verification failed', ['data' => $data]);
                return ['success' => false, 'error' => 'Invalid hash'];
            }

            if (strtolower($data['status']) === 'paid') {
                $paymentData = [
                    'amount' => $data['amount'],
                    'payment_method' => 'paynow',
                    'status' => 'completed',
                    'payment_date' => now(),
                    'transaction_id' => $data['paynowreference'] ?? null,
                    'reference_number' => $data['reference'],
                    'gateway_response' => $data,
                    'notes' => 'Payment processed via Paynow'
                ];

                $invoice->school->processSubscriptionPayment($invoice, $paymentData);

                return ['success' => true, 'message' => 'Payment processed successfully'];
            }

            Log::info('Paynow callback with non-paid status', [
                'status' => $data['status'],
                'reference' => $data['reference']
            ]);

            return ['success' => true, 'message' => 'Payment status updated'];

        } catch (\Exception $e) {
            Log::error('Paynow callback processing exception', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return ['success' => false, 'error' => 'Failed to process callback'];
        }
    }

    protected function verifyHash(array $data): bool
    {
        if (!isset($data['hash'])) {
            return false;
        }

        $receivedHash = $data['hash'];
        unset($data['hash']);

        $string = '';
        foreach ($data as $key => $value) {
            $string .= $value;
        }
        $string .= config('services.paynow.integration_key');

        $calculatedHash = hash('sha512', $string);

        return hash_equals(strtoupper($calculatedHash), strtoupper($receivedHash));
    }
}