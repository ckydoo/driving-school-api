<?php
// app/Http/Controllers/Api/SubscriptionController.php

namespace App\Http\Controllers\Api;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SubscriptionPackage;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * Get all active subscription packages
     */
    public function getPackages()
    {
        try {
            $packages = SubscriptionPackage::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(function ($package) {
                    return [
                        'id' => $package->id,
                        'name' => $package->name,
                        'slug' => $package->slug,
                        'description' => $package->description,
                        'monthly_price' => (float) $package->monthly_price,
                        'yearly_price' => $package->yearly_price ? (float) $package->yearly_price : null,
                        'features' => $package->features ?? [],
                        'limits' => $package->limits ?? [],
                        'is_popular' => (bool) $package->is_popular,
                        'trial_days' => $package->trial_days ?? 0,
                        'sort_order' => $package->sort_order,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $packages
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get packages: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load packages'
            ], 500);
        }
    }

    /**
     * Get subscription status
     */
    public function getSubscriptionStatus()
    {
        try {
            $user = Auth::user();
            $school = $user->school;

            if (!$school) {
                return response()->json([
                    'success' => false,
                    'message' => 'School not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'subscription_status' => $school->subscription_status,
                    'current_package' => $school->subscriptionPackage ? [
                        'id' => $school->subscriptionPackage->id,
                        'name' => $school->subscriptionPackage->name,
                        'slug' => $school->subscriptionPackage->slug,
                        'monthly_price' => $school->subscriptionPackage->monthly_price,
                    ] : null,
                    'trial_ends_at' => $school->trial_ends_at?->toISOString(),
                    'remaining_trial_days' => $school->remaining_trial_days,
                    'subscription_expires_at' => $school->subscription_expires_at?->toISOString(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get subscription status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load subscription status'
            ], 500);
        }
    }

    /**
     * Create payment intent for subscription
     * FIXED VERSION - Better error handling and logging
     */
    public function createPaymentIntent(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'package_id' => 'required|exists:subscription_packages,id',
                'billing_period' => 'required|in:monthly,yearly'
            ]);

            Log::info('Creating payment intent', [
                'package_id' => $request->package_id,
                'billing_period' => $request->billing_period
            ]);

            $user = Auth::user();
            $school = $user->school;

            if (!$school) {
                Log::error('School not found for user: ' . $user->id);
                return response()->json([
                    'success' => false,
                    'message' => 'School not found'
                ], 404);
            }

            $package = SubscriptionPackage::findOrFail($request->package_id);

            // Calculate amount in cents
            $amount = $request->billing_period === 'yearly' 
                ? $package->yearly_price * 100
                : $package->monthly_price * 100;

            Log::info('Amount calculated', [
                'amount' => $amount,
                'package' => $package->name
            ]);

            // Ensure Stripe customer exists
            if (!$school->stripe_customer_id) {
                Log::info('Creating Stripe customer for school: ' . $school->id);
                $school->createStripeCustomer();
                $school->refresh(); // Reload the model to get the new stripe_customer_id
            }

            if (!$school->stripe_customer_id) {
                Log::error('Failed to create Stripe customer for school: ' . $school->id);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create payment customer'
                ], 500);
            }

            Log::info('Using Stripe customer', [
                'customer_id' => $school->stripe_customer_id
            ]);

            // Set Stripe API key
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            // Create payment intent
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'usd',
                'customer' => $school->stripe_customer_id,
                'metadata' => [
                    'school_id' => $school->id,
                    'school_name' => $school->name,
                    'package_id' => $package->id,
                    'package_name' => $package->name,
                    'billing_period' => $request->billing_period,
                    'type' => 'subscription',
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                ],
                'description' => "Subscription: {$package->name} ({$request->billing_period})",
            ]);

            Log::info('Payment intent created successfully', [
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => substr($paymentIntent->client_secret, 0, 20) . '...'
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'client_secret' => $paymentIntent->client_secret,
                    'payment_intent_id' => $paymentIntent->id,
                    'amount' => $amount / 100, // Convert back to dollars
                    'package_name' => $package->name,
                    'billing_period' => $request->billing_period
                ]
            ]);

        } catch (\Stripe\Exception\CardException $e) {
            // Card was declined
            Log::error('Stripe card error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Card was declined: ' . $e->getMessage()
            ], 400);

        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests to Stripe
            Log::error('Stripe rate limit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Service temporarily unavailable. Please try again.'
            ], 429);

        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters
            Log::error('Stripe invalid request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Invalid payment parameters'
            ], 400);

        } catch (\Stripe\Exception\AuthenticationException $e) {
            // Authentication with Stripe failed
            Log::error('Stripe authentication failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment system configuration error'
            ], 500);

        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed
            Log::error('Stripe connection error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to connect to payment service. Please try again.'
            ], 503);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Generic Stripe error
            Log::error('Stripe API error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment processing error. Please try again.'
            ], 500);

        } catch (\Exception $e) {
            // Catch-all for any other errors
            Log::error('Payment intent creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment intent. Please try again.'
            ], 500);
        }
    }




 /**
     * Standalone endpoint to check trial eligibility
     */
    public function checkTrialEligibility()
    {
        $user = Auth::user();
        $school = $user->school;

        if (!$school) {
            return response()->json([
                'success' => false,
                'message' => 'School not found'
            ], 404);
        }

        $eligibility = $school->getTrialEligibility();

        return response()->json([
            'success' => true,
            'data' => $eligibility
        ]);
    }


    /**
     * Activate trial subscription without payment
     * Uses existing trial_ends_at to mark trial as used
     */
    private function activateTrialSubscription(School $school, SubscriptionPackage $package)
    {
        $success = $school->initializeTrial();

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Trial has already been used'
            ], 403);
        }

        Log::info("Trial activated for school {$school->id}");

        return response()->json([
            'success' => true,
            'message' => 'Trial activated successfully',
            'data' => [
                'trial_ends_at' => $school->trial_ends_at->toISOString(),
                'trial_days' => $package->trial_days ?? 30,
            ]
        ]);
    }

     /**
     * Handle successful subscription payment
     */
    public function handleSuccessfulPayment(Request $request)
    {
        try {
            $request->validate([
                'payment_intent_id' => 'required|string',
                'package_id' => 'required|exists:subscription_packages,id',
                'billing_period' => 'required|in:monthly,yearly'
            ]);

            Log::info('Processing payment confirmation', [
                'payment_intent_id' => $request->payment_intent_id,
                'package_id' => $request->package_id
            ]);

            $user = Auth::user();
            $school = $user->school;
            $package = SubscriptionPackage::findOrFail($request->package_id);

            // Verify payment intent with Stripe
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $paymentIntent = \Stripe\PaymentIntent::retrieve($request->payment_intent_id);

            if ($paymentIntent->status !== 'succeeded') {
                Log::warning('Payment intent not succeeded', [
                    'status' => $paymentIntent->status,
                    'payment_intent_id' => $request->payment_intent_id
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment not completed'
                ], 400);
            }

            // Calculate expiration date
            $expiresAt = $request->billing_period === 'yearly' 
                ? now()->addYear()
                : now()->addMonth();

            // Create invoice
            $invoice = $school->createSubscriptionInvoice($package, $request->billing_period);

            // Process payment
            $payment = $school->processSubscriptionPayment($invoice, [
                'amount' => $paymentIntent->amount / 100,
                'payment_method' => 'stripe',
                'status' => 'completed',
                'payment_date' => now(),
                'transaction_id' => $paymentIntent->id,
                'reference_number' => $invoice->invoice_number,
            ]);

            // Update school subscription
            $school->update([
                'subscription_status' => 'active',
                'subscription_package_id' => $package->id,
                'subscription_expires_at' => $expiresAt,
                'monthly_fee' => $request->billing_period === 'yearly' 
                    ? $package->yearly_price / 12 
                    : $package->monthly_price,
            ]);

            Log::info('Payment processed successfully', [
                'school_id' => $school->id,
                'package' => $package->name,
                'payment_id' => $payment->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment successful',
                'data' => [
                    'subscription_status' => 'active',
                    'package_name' => $package->name,
                    'expires_at' => $expiresAt->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Payment confirmation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
 * Create Stripe Checkout Session (for desktop platforms)
 * This works on Windows, Linux, macOS
 */
public function createCheckoutSession(Request $request)
{
    try {
        $request->validate([
            'package_id' => 'required|exists:subscription_packages,id',
            'billing_period' => 'required|in:monthly,yearly'
        ]);

        Log::info('Creating Stripe Checkout session', [
            'package_id' => $request->package_id,
            'billing_period' => $request->billing_period
        ]);

        $user = Auth::user();
        $school = $user->school;

        if (!$school) {
            return response()->json([
                'success' => false,
                'message' => 'School not found'
            ], 404);
        }

        $package = SubscriptionPackage::findOrFail($request->package_id);

        // Calculate amount (in cents)
        $amount = $request->billing_period === 'yearly' 
            ? $package->yearly_price * 100
            : $package->monthly_price * 100;

        // Set Stripe API key
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        // Create Stripe Checkout Session
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $package->name . ' Subscription',
                        'description' => $package->description,
                    ],
                    'unit_amount' => $amount,
                    'recurring' => [
                        'interval' => $request->billing_period === 'yearly' ? 'year' : 'month',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => config('app.url') . '/subscription/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => config('app.url') . '/subscription/cancel',
            'customer_email' => $school->email,
            'client_reference_id' => $school->id,
            'metadata' => [
                'school_id' => $school->id,
                'school_name' => $school->name,
                'package_id' => $package->id,
                'package_name' => $package->name,
                'billing_period' => $request->billing_period,
                'user_id' => $user->id,
                'user_email' => $user->email,
            ],
        ]);

        Log::info('Stripe Checkout session created', [
            'session_id' => $session->id,
            'url' => $session->url
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'session_id' => $session->id,
                'checkout_url' => $session->url,
                'package_name' => $package->name,
                'amount' => $amount / 100,
                'billing_period' => $request->billing_period
            ]
        ]);

    } catch (\Stripe\Exception\ApiErrorException $e) {
        Log::error('Stripe Checkout error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to create checkout session: ' . $e->getMessage()
        ], 500);

    } catch (\Exception $e) {
        Log::error('Checkout session creation failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to create checkout session'
        ], 500);
    }
}

/**
 * Handle successful checkout (webhook or redirect)
 */
public function handleCheckoutSuccess(Request $request)
{
    try {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect('/subscription')->with('error', 'Invalid session');
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        // Retrieve the session
        $session = \Stripe\Checkout\Session::retrieve($sessionId);

        if ($session->payment_status === 'paid') {
            // Get school from metadata
            $schoolId = $session->metadata->school_id;
            $packageId = $session->metadata->package_id;
            $billingPeriod = $session->metadata->billing_period;

            $school = School::findOrFail($schoolId);
            $package = SubscriptionPackage::findOrFail($packageId);

            // Update subscription
            $expiresAt = $billingPeriod === 'yearly' 
                ? now()->addYear()
                : now()->addMonth();

            // Create invoice
            $invoice = $school->createSubscriptionInvoice($package, $billingPeriod);

            // Mark as paid
            $school->processSubscriptionPayment($invoice, [
                'amount' => $session->amount_total / 100,
                'payment_method' => 'stripe_checkout',
                'status' => 'completed',
                'payment_date' => now(),
                'transaction_id' => $session->payment_intent,
                'reference_number' => $invoice->invoice_number,
            ]);

            // Update school subscription
            $school->update([
                'subscription_status' => 'active',
                'subscription_package_id' => $package->id,
                'subscription_expires_at' => $expiresAt,
                'monthly_fee' => $billingPeriod === 'yearly' 
                    ? $package->yearly_price / 12 
                    : $package->monthly_price,
            ]);

            Log::info('Checkout payment processed', [
                'school_id' => $schoolId,
                'package' => $package->name
            ]);

            return redirect('/subscription')->with('success', 'Payment successful!');
        }

        return redirect('/subscription')->with('error', 'Payment not completed');

    } catch (\Exception $e) {
        Log::error('Checkout success handler failed: ' . $e->getMessage());
        return redirect('/subscription')->with('error', 'Payment verification failed');
    }
}

/**
 * Handle cancelled checkout
 */
public function handleCheckoutCancel()
{
    return redirect('/subscription')->with('info', 'Payment was cancelled');
}

/**
 * Handle Stripe Webhook Events
 * This receives payment confirmations from Stripe
 */
public function handleWebhook(Request $request)
{
    \Log::info('🔔 Stripe webhook received');

    // Get the raw body
    $payload = $request->getContent();
    $sigHeader = $request->header('Stripe-Signature');

    try {
        // Verify webhook signature (IMPORTANT for security)
        $webhookSecret = config('services.stripe.webhook_secret');
        
        if ($webhookSecret) {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );
        } else {
            // For testing without webhook secret
            $event = json_decode($payload, true);
            \Log::warning('⚠️ Webhook verification skipped - no secret configured');
        }

        \Log::info('Webhook event type: ' . $event['type']);

        // Handle different event types
        switch ($event['type']) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event['data']['object']);
                break;

            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event['data']['object']);
                break;

            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($event['data']['object']);
                break;

            default:
                \Log::info('Unhandled webhook event type: ' . $event['type']);
        }

        return response()->json(['received' => true], 200);

    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        \Log::error('❌ Webhook signature verification failed: ' . $e->getMessage());
        return response()->json(['error' => 'Invalid signature'], 400);

    } catch (\Exception $e) {
        \Log::error('❌ Webhook handler error: ' . $e->getMessage());
        return response()->json(['error' => 'Webhook handler failed'], 500);
    }
}

/**
 * Handle checkout.session.completed event
 */
private function handleCheckoutSessionCompleted($session)
{
    try {
        \Log::info('💳 Processing checkout session completed', [
            'session_id' => $session->id,
            'payment_status' => $session->payment_status
        ]);

        // Only process if payment was successful
        if ($session->payment_status !== 'paid') {
            \Log::warning('Payment not completed yet');
            return;
        }

        // Get metadata
        $schoolId = $session->metadata->school_id ?? null;
        $packageId = $session->metadata->package_id ?? null;
        $billingPeriod = $session->metadata->billing_period ?? 'monthly';

        if (!$schoolId || !$packageId) {
            \Log::error('Missing school_id or package_id in session metadata');
            return;
        }

        $school = \App\Models\School::find($schoolId);
        $package = \App\Models\SubscriptionPackage::find($packageId);

        if (!$school || !$package) {
            \Log::error('School or package not found', [
                'school_id' => $schoolId,
                'package_id' => $packageId
            ]);
            return;
        }

        // Calculate expiration
        $expiresAt = $billingPeriod === 'yearly' 
            ? now()->addYear()
            : now()->addMonth();

        // Create invoice
        $invoice = $school->createSubscriptionInvoice($package, $billingPeriod);

        // Record payment
        $payment = $school->processSubscriptionPayment($invoice, [
            'amount' => $session->amount_total / 100, // Convert from cents
            'payment_method' => 'stripe_checkout',
            'status' => 'completed',
            'payment_date' => now(),
            'transaction_id' => $session->payment_intent,
            'reference_number' => $invoice->invoice_number,
            'notes' => 'Payment via Stripe Checkout',
        ]);

        // Update school subscription
        $school->update([
            'subscription_status' => 'active',
            'subscription_package_id' => $package->id,
            'subscription_expires_at' => $expiresAt,
            'monthly_fee' => $billingPeriod === 'yearly' 
                ? $package->yearly_price / 12 
                : $package->monthly_price,
        ]);

        \Log::info('✅ Subscription activated via webhook', [
            'school_id' => $schoolId,
            'package' => $package->name,
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id
        ]);

    } catch (\Exception $e) {
        \Log::error('❌ Failed to process checkout session: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
    }
}

/**
 * Handle payment_intent.succeeded event
 */
private function handlePaymentIntentSucceeded($paymentIntent)
{
    try {
        \Log::info('💰 Processing payment intent succeeded', [
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount / 100
        ]);

        // Get metadata
        $schoolId = $paymentIntent->metadata->school_id ?? null;
        $packageId = $paymentIntent->metadata->package_id ?? null;
        $billingPeriod = $paymentIntent->metadata->billing_period ?? 'monthly';

        if (!$schoolId || !$packageId) {
            \Log::warning('Missing metadata in payment intent');
            return;
        }

        $school = \App\Models\School::find($schoolId);
        $package = \App\Models\SubscriptionPackage::find($packageId);

        if (!$school || !$package) {
            \Log::error('School or package not found');
            return;
        }

        // Check if already processed
        $existingPayment = \App\Models\SubscriptionPayment::where('transaction_id', $paymentIntent->id)->first();
        if ($existingPayment) {
            \Log::info('Payment already processed, skipping');
            return;
        }

        // Calculate expiration
        $expiresAt = $billingPeriod === 'yearly' 
            ? now()->addYear()
            : now()->addMonth();

        // Create invoice if not exists
        $invoice = $school->createSubscriptionInvoice($package, $billingPeriod);

        // Record payment
        $payment = $school->processSubscriptionPayment($invoice, [
            'amount' => $paymentIntent->amount / 100,
            'payment_method' => 'stripe',
            'status' => 'completed',
            'payment_date' => now(),
            'transaction_id' => $paymentIntent->id,
            'reference_number' => $invoice->invoice_number,
        ]);

        // Update subscription
        $school->update([
            'subscription_status' => 'active',
            'subscription_package_id' => $package->id,
            'subscription_expires_at' => $expiresAt,
            'monthly_fee' => $billingPeriod === 'yearly' 
                ? $package->yearly_price / 12 
                : $package->monthly_price,
        ]);

        \Log::info('✅ Subscription updated via payment intent webhook');

    } catch (\Exception $e) {
        \Log::error('❌ Failed to process payment intent: ' . $e->getMessage());
    }
}

/**
 * Handle invoice.payment_succeeded event
 */
private function handleInvoicePaymentSucceeded($invoice)
{
    \Log::info('📄 Invoice payment succeeded', [
        'invoice_id' => $invoice->id,
        'amount_paid' => $invoice->amount_paid / 100
    ]);

    // Handle subscription renewals here if needed
}
}
