<?php
// app/Http/Controllers/School/SchoolSubscriptionController.php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolSubscriptionController extends Controller
{
    /**
     * Show subscription dashboard for school admin
     */
    public function index()
    {
        $user = Auth::user();
        $school = $user->school;

        if (!$school) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No school associated with your account.');
        }

        // Load subscription data
        $school->load(['subscriptionPackage']);

        // Safely load billing data
        try {
            $school->load([
                'subscriptionInvoices' => function($query) {
                    $query->orderBy('invoice_date', 'desc')->limit(5);
                },
                'subscriptionPayments' => function($query) {
                    $query->orderBy('payment_date', 'desc')->limit(5);
                },
                'subscriptionHistory' => function($query) {
                    $query->orderBy('created_at', 'desc')->limit(10);
                }
            ]);
        } catch (\Exception $e) {
            \Log::info('Billing tables not ready: ' . $e->getMessage());
        }

        // Get subscription stats
        $stats = $school->getSubscriptionStats();

        // Get usage statistics
        $usage = [
            'students' => [
                'current' => $school->getCurrentUsage('max_students'),
                'limit' => $school->subscriptionPackage?->getLimit('max_students') ?? 0,
                'unlimited' => $school->subscriptionPackage?->isUnlimited('max_students') ?? false,
                'percentage' => $school->getUsagePercentage('max_students')
            ],
            'instructors' => [
                'current' => $school->getCurrentUsage('max_instructors'),
                'limit' => $school->subscriptionPackage?->getLimit('max_instructors') ?? 0,
                'unlimited' => $school->subscriptionPackage?->isUnlimited('max_instructors') ?? false,
                'percentage' => $school->getUsagePercentage('max_instructors')
            ],
            'vehicles' => [
                'current' => $school->getCurrentUsage('max_vehicles'),
                'limit' => $school->subscriptionPackage?->getLimit('max_vehicles') ?? 0,
                'unlimited' => $school->subscriptionPackage?->isUnlimited('max_vehicles') ?? false,
                'percentage' => $school->getUsagePercentage('max_vehicles')
            ]
        ];

        // Get available packages for upgrade
        $availablePackages = SubscriptionPackage::active()
            ->where('monthly_price', '>', $school->subscriptionPackage?->monthly_price ?? 0)
            ->ordered()
            ->get();

        return view('school.subscription.index', compact(
            'school', 
            'stats', 
            'usage', 
            'availablePackages'
        ));
    }

    /**
     * Show billing history
     */
    public function billing()
    {
        $user = Auth::user();
        $school = $user->school;

        if (!$school) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No school associated with your account.');
        }

        // Load billing data
        try {
            $school->load([
                'subscriptionInvoices' => function($query) {
                    $query->orderBy('invoice_date', 'desc');
                },
                'subscriptionPayments' => function($query) {
                    $query->orderBy('payment_date', 'desc');
                }
            ]);
        } catch (\Exception $e) {
            $school->subscriptionInvoices = collect();
            $school->subscriptionPayments = collect();
        }

        $stats = $school->getSubscriptionStats();

        return view('school.subscription.billing', compact('school', 'stats'));
    }

    /**
     * Show upgrade options
     */
    public function upgrade()
    {
        $user = Auth::user();
        $school = $user->school;

        if (!$school) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No school associated with your account.');
        }

        $currentPackage = $school->subscriptionPackage;
        
        // Get available upgrade packages
        $availablePackages = SubscriptionPackage::active()
            ->where('monthly_price', '>', $currentPackage?->monthly_price ?? 0)
            ->ordered()
            ->get();

        // Get current usage for comparison
        $usage = [
            'students' => $school->getCurrentUsage('max_students'),
            'instructors' => $school->getCurrentUsage('max_instructors'),
            'vehicles' => $school->getCurrentUsage('max_vehicles')
        ];

        return view('school.subscription.upgrade', compact(
            'school', 
            'currentPackage', 
            'availablePackages', 
            'usage'
        ));
    }

    /**
     * Process upgrade request
     */
    public function processUpgrade(Request $request)
    {
        $user = Auth::user();
        $school = $user->school;

        if (!$school) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No school associated with your account.');
        }

        $request->validate([
            'package_id' => 'required|exists:subscription_packages,id',
            'billing_period' => 'required|in:monthly,yearly'
        ]);

        $package = SubscriptionPackage::findOrFail($request->package_id);
        $billingPeriod = $request->billing_period;

        try {
            // Create payment intent for the upgrade
            $clientSecret = $this->createUpgradePaymentIntent($school, $package, $billingPeriod);

            return response()->json([
                'success' => true,
                'client_secret' => $clientSecret,
                'package_name' => $package->name,
                'amount' => $package->getPriceForPeriod($billingPeriod)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process upgrade: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Pay outstanding invoices
     */
    public function payInvoice(Request $request)
    {
        $user = Auth::user();
        $school = $user->school;

        $request->validate([
            'invoice_id' => 'required|exists:subscription_invoices,id'
        ]);

        $invoice = $school->subscriptionInvoices()->findOrFail($request->invoice_id);

        if ($invoice->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is not payable'
            ], 400);
        }

        try {
            $clientSecret = $this->createInvoicePaymentIntent($school, $invoice);

            return response()->json([
                'success' => true,
                'client_secret' => $clientSecret,
                'invoice_number' => $invoice->invoice_number,
                'amount' => $invoice->total_amount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Handle successful payment webhook/callback
     */
    public function handlePaymentSuccess(Request $request)
    {
        $user = Auth::user();
        $school = $user->school;

        $request->validate([
            'payment_intent_id' => 'required|string',
            'invoice_id' => 'required|exists:subscription_invoices,id'
        ]);

        $invoice = $school->subscriptionInvoices()->findOrFail($request->invoice_id);

        // Process the payment
        $paymentData = [
            'amount' => $invoice->total_amount,
            'payment_method' => 'stripe',
            'status' => 'completed',
            'payment_date' => now(),
            'transaction_id' => $request->payment_intent_id,
            'notes' => 'Payment via school admin portal'
        ];

        $payment = $school->processSubscriptionPayment($invoice, $paymentData);

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully!',
            'payment_id' => $payment->id
        ]);
    }

    /**
     * Create payment intent for upgrade
     */
    private function createUpgradePaymentIntent($school, $package, $billingPeriod)
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $amount = $package->getPriceForPeriod($billingPeriod) * 100; // Convert to cents

        // Create or get Stripe customer
        $stripeCustomerId = $school->createStripeCustomer();

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'usd',
            'customer' => $stripeCustomerId,
            'metadata' => [
                'school_id' => $school->id,
                'package_id' => $package->id,
                'billing_period' => $billingPeriod,
                'type' => 'subscription_upgrade'
            ]
        ]);

        return $paymentIntent->client_secret;
    }

    /**
     * Create payment intent for invoice
     */
    private function createInvoicePaymentIntent($school, $invoice)
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $amount = $invoice->total_amount * 100; // Convert to cents

        // Create or get Stripe customer
        $stripeCustomerId = $school->createStripeCustomer();

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'usd',
            'customer' => $stripeCustomerId,
            'metadata' => [
                'school_id' => $school->id,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'type' => 'subscription_payment'
            ]
        ]);

        return $paymentIntent->client_secret;
    }

    /**
     * Download invoice
     */
    public function downloadInvoice($invoiceId)
    {
        $user = Auth::user();
        $school = $user->school;

        $invoice = $school->subscriptionInvoices()->findOrFail($invoiceId);

        // Here you would generate and return the PDF invoice
        // For now, return a simple response
        return response()->json([
            'message' => 'Invoice download feature - implement PDF generation',
            'invoice' => $invoice
        ]);
    }
}