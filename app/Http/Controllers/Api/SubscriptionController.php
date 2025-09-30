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
        $packages = SubscriptionPackage::active()
            ->ordered()
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'slug' => $package->slug,
                    'monthly_price' => $package->monthly_price,
                    'yearly_price' => $package->yearly_price,
                    'description' => $package->description,
                    'features' => $package->features,
                    'limits' => $package->limits,
                    'trial_days' => $package->trial_days,
                    'is_popular' => $package->is_popular,
                    'yearly_discount' => $package->getYearlyDiscount(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $packages
        ]);
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
     * Create payment intent for subscription
     */
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:subscription_packages,id',
            'billing_period' => 'required|in:monthly,yearly'
        ]);

        $user = Auth::user();
        $school = $user->school;
        $package = SubscriptionPackage::findOrFail($request->package_id);

        // Check if trying to get trial package
        if ($package->slug === 'trial') {
            // Check eligibility using existing trial_ends_at column
            if (!$school->canStartTrial()) {
                $eligibility = $school->getTrialEligibility();

                Log::warning("Trial activation blocked for school {$school->id}: {$eligibility['reason']}");

                return response()->json([
                    'success' => false,
                    'message' => $eligibility['reason'] ?? 'Trial not available'
                ], 403);
            }

            // Activate trial without payment
            return $this->activateTrialSubscription($school, $package);
        }

        // For paid packages, create Stripe payment intent
        $amount = $request->billing_period === 'yearly'
            ? $package->yearly_price * 100 // Convert to cents
            : $package->monthly_price * 100;

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            // Create or get Stripe customer
            $stripeCustomerId = $school->stripe_customer_id;
            if (!$stripeCustomerId) {
                $stripeCustomerId = $school->createStripeCustomer();
            }

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'usd',
                'customer' => $stripeCustomerId,
                'metadata' => [
                    'school_id' => $school->id,
                    'package_id' => $package->id,
                    'billing_period' => $request->billing_period,
                    'type' => 'subscription'
                ]
            ]);

            Log::info("Payment intent created for school {$school->id}", [
                'amount' => $amount / 100,
                'package' => $package->name,
                'billing_period' => $request->billing_period
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'client_secret' => $paymentIntent->client_secret,
                    'amount' => $amount,
                    'package_name' => $package->name,
                    'billing_period' => $request->billing_period
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe payment intent creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment intent. Please try again.'
            ], 500);
        }
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
        $request->validate([
            'payment_intent_id' => 'required|string',
            'package_id' => 'required|exists:subscription_packages,id',
            'billing_period' => 'required|in:monthly,yearly'
        ]);

        $user = Auth::user();
        $school = $user->school;
        $package = SubscriptionPackage::findOrFail($request->package_id);

        // Verify payment with Stripe
        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $paymentIntent = \Stripe\PaymentIntent::retrieve($request->payment_intent_id);

            if ($paymentIntent->status !== 'succeeded') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not completed'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Stripe payment verification failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed'
            ], 500);
        }

        // Calculate expiration date
        $expiresAt = $request->billing_period === 'yearly'
            ? now()->addYear()
            : now()->addMonth();

        // Update subscription
        // IMPORTANT: If upgrading from trial, trial_ends_at stays set (marking trial as used forever)
        $school->update([
            'subscription_status' => 'active',
            'subscription_package_id' => $package->id,
            'subscription_expires_at' => $expiresAt,
            'monthly_fee' => $package->monthly_price,
            'subscription_started_at' => $school->subscription_started_at ?? now(),
            // trial_ends_at is NOT changed - it stays set to mark trial was used
        ]);

        // Create invoice record if method exists
        if (method_exists($school, 'createSubscriptionInvoice')) {
            $school->createSubscriptionInvoice($package, $request->billing_period);
        }

        Log::info("Subscription activated for school {$school->id}", [
            'package' => $package->name,
            'expires_at' => $expiresAt,
            'billing_period' => $request->billing_period
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription activated successfully',
            'data' => [
                'package_name' => $package->name,
                'expires_at' => $expiresAt->toISOString()
            ]
        ]);
    }


  /**
     * Get current subscription status with trial eligibility
     */
    public function getSubscriptionStatus()
    {
        $user = Auth::user();
        $school = $user->school;

        if (!$school) {
            return response()->json([
                'success' => false,
                'message' => 'School not found'
            ], 404);
        }

        // Get trial eligibility using existing columns
        $trialEligibility = $school->getTrialEligibility();

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
                'trial_eligibility' => $trialEligibility, // NOW INCLUDED!
            ]
        ]);
    }



}
