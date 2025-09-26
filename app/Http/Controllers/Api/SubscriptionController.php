<?php
// app/Http/Controllers/Api/SubscriptionController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPackage;
use App\Models\School;
use Illuminate\Http\Request;
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
     * Get current subscription status
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

        $amount = $request->billing_period === 'yearly' 
            ? $package->yearly_price * 100 // Convert to cents
            : $package->monthly_price * 100;

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'usd',
                'customer' => $school->stripe_customer_id,
                'metadata' => [
                    'school_id' => $school->id,
                    'package_id' => $package->id,
                    'billing_period' => $request->billing_period,
                    'type' => 'subscription'
                ]
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
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
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

        // Calculate expiration date
        $expiresAt = $request->billing_period === 'yearly' 
            ? now()->addYear() 
            : now()->addMonth();

        $school->update([
            'subscription_status' => 'active',
            'subscription_package_id' => $package->id,
            'subscription_expires_at' => $expiresAt,
            'monthly_fee' => $package->monthly_price
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
}