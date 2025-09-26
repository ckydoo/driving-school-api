<?php
// app/Models/School.php - Updated with subscription functionality

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class School extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'city',
        'license_number',
        'country',
        'website',
        'start_time',
        'end_time',
        'operating_days',
        'invitation_code',
        'status',
        'subscription_status',
        'subscription_package_id',
        'stripe_customer_id',
        'trial_ends_at',
        'subscription_expires_at',
        'subscription_started_at',
        'monthly_fee',
        'max_students',
        'max_instructors',
        'features'
    ];

    protected $casts = [
        'operating_days' => 'array',
        'features' => 'array',
        'trial_ends_at' => 'datetime',
        'subscription_expires_at' => 'datetime',
        'subscription_started_at' => 'datetime',
        'monthly_fee' => 'decimal:2',
    ];

    // RELATIONSHIPS
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscriptionPackage(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPackage::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'student');
    }

    public function instructors(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'instructor');
    }

    public function admins(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'admin');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function fleet(): HasMany
    {
        return $this->hasMany(Fleet::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // SUBSCRIPTION METHODS

    /**
     * Initialize trial subscription for new school
     */
    public function initializeTrial()
    {
        $trialPackage = SubscriptionPackage::where('slug', 'trial')->first();
        
        if (!$trialPackage) {
            // Create a basic trial package if none exists
            $trialPackage = SubscriptionPackage::create([
                'name' => 'Trial',
                'slug' => 'trial',
                'monthly_price' => 0.00,
                'yearly_price' => 0.00,
                'description' => 'Free trial',
                'features' => ['Basic features'],
                'limits' => [
                    'max_students' => 50,
                    'max_instructors' => 5,
                    'max_vehicles' => 10
                ],
                'trial_days' => 30,
                'is_active' => true,
                'sort_order' => 1
            ]);
        }
        
        $this->update([
            'subscription_status' => 'trial',
            'subscription_package_id' => $trialPackage->id,
            'trial_ends_at' => now()->addDays($trialPackage->trial_days),
            'subscription_started_at' => now(),
        ]);
    }

    /**
     * Get remaining trial days
     */
    public function getRemainingTrialDaysAttribute(): int
    {
        if (!$this->trial_ends_at || $this->subscription_status !== 'trial') {
            return 0;
        }
        
        $remaining = now()->diffInDays($this->trial_ends_at, false);
        return max(0, (int) $remaining);
    }

    /**
     * Check if trial has expired
     */
    public function isTrialExpired(): bool
    {
        return $this->subscription_status === 'trial' 
            && $this->trial_ends_at 
            && $this->trial_ends_at->isPast();
    }

    /**
     * Check if subscription has expired
     */
    public function isSubscriptionExpired(): bool
    {
        return $this->subscription_expires_at 
            && $this->subscription_expires_at->isPast();
    }

    /**
     * Check feature access based on package
     */
    public function canAccessFeature(string $feature): bool
    {
        if (!$this->subscriptionPackage) {
            return false;
        }

        return $this->subscriptionPackage->hasFeature($feature);
    }

    /**
     * Check if school has reached a specific limit
     */
    public function hasReachedLimit(string $type): bool
    {
        if (!$this->subscriptionPackage) {
            return true;
        }

        $limit = $this->subscriptionPackage->getLimit($type);
        
        if ($limit === -1) { // Unlimited
            return false;
        }

        $current = match($type) {
            'max_students' => $this->users()->where('role', 'student')->count(),
            'max_instructors' => $this->users()->where('role', 'instructor')->count(),
            'max_vehicles' => $this->fleet()->count(),
            default => 0
        };

        return $current >= $limit;
    }

    /**
     * Get current usage count for a limit type
     */
    public function getCurrentUsage(string $type): int
    {
        return match($type) {
            'max_students' => $this->users()->where('role', 'student')->count(),
            'max_instructors' => $this->users()->where('role', 'instructor')->count(),
            'max_vehicles' => $this->fleet()->count(),
            default => 0
        };
    }

    /**
     * Get usage percentage for a limit type
     */
    public function getUsagePercentage(string $type): float
    {
        if (!$this->subscriptionPackage) {
            return 100.0;
        }
        
        $limit = $this->subscriptionPackage->getLimit($type);
        
        if ($limit === -1) { // Unlimited
            return 0.0;
        }
        
        if ($limit === 0) {
            return 100.0;
        }
        
        $current = $this->getCurrentUsage($type);
        return min(100.0, ($current / $limit) * 100);
    }

    /**
     * Create Stripe customer if not exists
     */
    public function createStripeCustomer(): ?string
    {
        if ($this->stripe_customer_id) {
            return $this->stripe_customer_id;
        }

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            
            $customer = \Stripe\Customer::create([
                'email' => $this->email,
                'name' => $this->name,
                'metadata' => [
                    'school_id' => $this->id,
                ]
            ]);
            
            $this->update(['stripe_customer_id' => $customer->id]);
            
            return $customer->id;
        } catch (\Exception $e) {
            \Log::error('Failed to create Stripe customer: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get monthly revenue (placeholder - implement based on your billing logic)
     */
    public function getMonthlyRevenue(): float
    {
        // This is a placeholder - implement based on your billing system
        return $this->monthly_fee ?? 0.00;
    }

    /**
     * Get total revenue (placeholder - implement based on your billing logic)
     */
    public function getTotalRevenue(): float
    {
        // This is a placeholder - implement based on your billing/payment history
        return $this->invoices()->where('status', 'paid')->sum('amount') ?? 0.00;
    }

    /**
     * Upgrade to a new subscription package
     */
    public function upgradeTo(SubscriptionPackage $package, string $billingPeriod = 'monthly'): bool
    {
        $expiresAt = $billingPeriod === 'yearly' 
            ? now()->addYear() 
            : now()->addMonth();

        return $this->update([
            'subscription_status' => 'active',
            'subscription_package_id' => $package->id,
            'subscription_expires_at' => $expiresAt,
            'monthly_fee' => $billingPeriod === 'yearly' 
                ? $package->yearly_price / 12 
                : $package->monthly_price,
            'subscription_started_at' => now(),
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(): bool
    {
        return $this->update([
            'subscription_status' => 'cancelled'
        ]);
    }

    /**
     * Suspend subscription
     */
    public function suspendSubscription(): bool
    {
        return $this->update([
            'subscription_status' => 'suspended'
        ]);
    }

    /**
     * Reactivate subscription
     */
    public function reactivateSubscription(): bool
    {
        return $this->update([
            'subscription_status' => 'active'
        ]);
    }

    // EXISTING METHODS (keep your existing code)

    public static function generateUniqueInvitationCode(): string
    {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $suffix = '';
        
        // Generate 3 letters + 3 numbers
        for ($i = 0; $i < 3; $i++) {
            $suffix .= $letters[rand(0, strlen($letters) - 1)];
        }
        for ($i = 0; $i < 3; $i++) {
            $suffix .= $numbers[rand(0, strlen($numbers) - 1)];
        }
        
        $code = 'DS' . $suffix;
        
        // Ensure uniqueness
        while (self::where('invitation_code', $code)->exists()) {
            // Regenerate if code exists
            $suffix = '';
            for ($i = 0; $i < 3; $i++) {
                $suffix .= $letters[rand(0, strlen($letters) - 1)];
            }
            for ($i = 0; $i < 3; $i++) {
                $suffix .= $numbers[rand(0, strlen($numbers) - 1)];
            }
            $code = 'DS' . $suffix;
        }
        
        return $code;
    }
}