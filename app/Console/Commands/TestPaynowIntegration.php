<?php
// Location: app/Console/Commands/TestPaynowIntegration.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PaynowService;
use App\Models\SubscriptionInvoice;

class TestPaynowIntegration extends Command
{
    protected $signature = 'paynow:test {invoice_id?}';
    protected $description = 'Test Paynow integration with an invoice';

    public function handle()
    {
        $this->info('🔍 Testing Paynow Integration...');
        $this->newLine();

        // Test configuration
        $this->info('Configuration Check:');
        $this->line('Integration ID: ' . (config('services.paynow.integration_id') ? '✅ Set' : '❌ Not Set'));
        $this->line('Integration Key: ' . (config('services.paynow.integration_key') ? '✅ Set' : '❌ Not Set'));
        $this->line('Result URL: ' . config('services.paynow.result_url'));
        $this->line('Return URL: ' . config('services.paynow.return_url'));
        $this->newLine();

        if (!config('services.paynow.integration_id') || !config('services.paynow.integration_key')) {
            $this->error('❌ Paynow credentials not configured. Please check your .env file.');
            return 1;
        }

        // Get invoice
        $invoiceId = $this->argument('invoice_id');
        
        if (!$invoiceId) {
            $invoice = SubscriptionInvoice::where('status', 'pending')->first();
            
            if (!$invoice) {
                $this->error('❌ No pending invoices found. Please provide an invoice ID.');
                return 1;
            }
            
            $this->info("Using invoice: {$invoice->invoice_number}");
        } else {
            $invoice = SubscriptionInvoice::find($invoiceId);
            
            if (!$invoice) {
                $this->error("❌ Invoice not found with ID: {$invoiceId}");
                return 1;
            }
        }

        $this->newLine();
        $this->info('Invoice Details:');
        $this->line("Number: {$invoice->invoice_number}");
        $this->line("Amount: \${$invoice->total_amount}");
        $this->line("Status: {$invoice->status}");
        $this->line("School: {$invoice->school->name}");
        $this->newLine();

        // Ask for test type
        $testType = $this->choice(
            'Select test type',
            ['Web Payment', 'Mobile Payment (EcoCash)', 'Check Status', 'Cancel'],
            0
        );

        $paynowService = new PaynowService();

        switch ($testType) {
            case 'Web Payment':
                $this->testWebPayment($paynowService, $invoice);
                break;

            case 'Mobile Payment (EcoCash)':
                $this->testMobilePayment($paynowService, $invoice);
                break;

            case 'Check Status':
                $this->testCheckStatus($paynowService, $invoice);
                break;

            default:
                $this->info('Test cancelled.');
                break;
        }

        return 0;
    }

    protected function testWebPayment($paynowService, $invoice)
    {
        $this->info('🌐 Initializing web payment...');
        
        $result = $paynowService->initializePayment($invoice);

        if ($result['success']) {
            $this->info('✅ Payment initialized successfully!');
            $this->newLine();
            $this->line('Redirect URL: ' . $result['redirect_url']);
            $this->line('Poll URL: ' . $result['poll_url']);
            $this->newLine();
            $this->info('Please visit the redirect URL to complete the payment.');
        } else {
            $this->error('❌ Payment initialization failed: ' . $result['error']);
        }
    }

    protected function testMobilePayment($paynowService, $invoice)
    {
        $phoneNumber = $this->ask('Enter mobile number (e.g., 0771234567)', '0771111111');

        if (!preg_match('/^07[0-9]{8}$/', $phoneNumber)) {
            $this->error('❌ Invalid phone number format. Must be 07XXXXXXXX');
            return;
        }

        $this->info('📱 Initializing EcoCash payment...');
        
        $result = $paynowService->initializeMobilePayment($invoice, $phoneNumber, 'ecocash');

        if ($result['success']) {
            $this->info('✅ Payment initialized successfully!');
            $this->newLine();
            $this->line('Instructions: ' . $result['instructions']);
            $this->line('Poll URL: ' . $result['poll_url']);
            $this->newLine();
            $this->info('Check your phone for the payment prompt.');
            
            if ($this->confirm('Would you like to check the payment status?', true)) {
                sleep(3);
                $this->testCheckStatus($paynowService, $invoice);
            }
        } else {
            $this->error('❌ Payment initialization failed: ' . $result['error']);
        }
    }

    protected function testCheckStatus($paynowService, $invoice)
    {
        $gatewayData = $invoice->payment_gateway_data;

        if (!isset($gatewayData['poll_url'])) {
            $this->error('❌ No poll URL found. Please initialize a payment first.');
            return;
        }

        $this->info('🔄 Checking payment status...');
        
        $result = $paynowService->checkPaymentStatus($gatewayData['poll_url']);

        if ($result['success']) {
            $this->newLine();
            $this->info('Payment Status:');
            $this->line('Paid: ' . ($result['paid'] ? '✅ Yes' : '❌ No'));
            $this->line('Status: ' . $result['status']);
            $this->line('Amount: $' . $result['amount']);
            $this->line('Reference: ' . $result['reference']);
            
            if (isset($result['paynow_reference'])) {
                $this->line('Paynow Reference: ' . $result['paynow_reference']);
            }
        } else {
            $this->error('❌ Status check failed: ' . $result['error']);
        }
    }
}