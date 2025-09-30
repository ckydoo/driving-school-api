<?php
// app/Models/School.php - Add these missing methods

namespace App\Models;
use Schema;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;



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

    // ADD THESE MISSING METHODS:

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

        $current = $this->getCurrentUsage($type);
        return $current >= $limit;
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

      protected static function boot()
    {
        parent::boot();

        static::creating(function ($school) {
            // Generate slug if not provided
            if (empty($school->slug)) {
                $school->slug = static::generateUniqueSlug($school->name);
            }

            // Generate invitation code if not provided
            if (empty($school->invitation_code)) {
                $school->invitation_code = static::generateUniqueInvitationCode();
            }
        });

        static::updating(function ($school) {
            if ($school->isDirty('name') && empty($school->slug)) {
                $school->slug = static::generateUniqueSlug($school->name);
            }
        });
    }

    /**
     * Generate a unique slug from the school name
     */
    private static function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;

        $counter = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
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
            Log::error('Failed to create Stripe customer: ' . $e->getMessage());
            return null;
        }
    }



    /**
     * Generate unique invitation code
     */
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

    // EXISTING SCOPES AND METHODS (keep your existing code)
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTrial($query)
    {
        return $query->where('subscription_status', 'trial');
    }

    public function scopePaid($query)
    {
        return $query->where('subscription_status', 'active');
    }
    // SUBSCRIPTION BILLING RELATIONSHIPS
public function subscriptionInvoices(): HasMany
{
    return $this->hasMany(SubscriptionInvoice::class);
}

public function subscriptionPayments(): HasMany
{
    return $this->hasMany(SubscriptionPayment::class);
}

public function subscriptionHistory(): HasMany
{
    return $this->hasMany(SubscriptionHistory::class);
}


/**
 * Get current monthly recurring revenue
 */
public function getCurrentMRR(): float
{
    if ($this->subscription_status !== 'active' || !$this->subscriptionPackage) {
        return 0.00;
    }

    return $this->monthly_fee ?? $this->subscriptionPackage->monthly_price;
}

/**
 * Get outstanding subscription balance
 */
public function getOutstandingBalance(): float
{
    return $this->subscriptionInvoices()
        ->where('status', 'pending')
        ->sum('total_amount') ?? 0.00;
}

/**
 * Check if subscription payments are up to date
 */
public function isPaymentUpToDate(): bool
{
    $overdueInvoices = $this->subscriptionInvoices()
        ->where('status', 'pending')
        ->where('due_date', '<', now())
        ->count();

    return $overdueInvoices === 0;
}

/**
 * Get next billing date
 */
public function getNextBillingDate(): ?\Carbon\Carbon
{
    if ($this->subscription_status !== 'active' || !$this->subscription_expires_at) {
        return null;
    }

    return $this->subscription_expires_at;
}

/**
 * Create subscription invoice
 */
public function createSubscriptionInvoice(SubscriptionPackage $package, string $billingPeriod = 'monthly'): SubscriptionInvoice
{
    $amount = $billingPeriod === 'yearly' ? $package->yearly_price : $package->monthly_price;
    $taxAmount = 0.00; // Calculate tax based on your requirements
    $totalAmount = $amount + $taxAmount;

    $invoiceDate = now();
    $dueDate = $invoiceDate->copy()->addDays(7); // 7 days to pay

    if ($billingPeriod === 'yearly') {
        $periodStart = $invoiceDate->copy();
        $periodEnd = $periodStart->copy()->addYear()->subDay();
    } else {
        $periodStart = $invoiceDate->copy();
        $periodEnd = $periodStart->copy()->addMonth()->subDay();
    }

    return $this->subscriptionInvoices()->create([
        'subscription_package_id' => $package->id,
        'invoice_number' => SubscriptionInvoice::generateInvoiceNumber(),
        'amount' => $amount,
        'tax_amount' => $taxAmount,
        'total_amount' => $totalAmount,
        'billing_period' => $billingPeriod,
        'status' => 'pending',
        'invoice_date' => $invoiceDate,
        'due_date' => $dueDate,
        'period_start' => $periodStart,
        'period_end' => $periodEnd,
        'invoice_data' => [
            'package_name' => $package->name,
            'package_features' => $package->features,
            'package_limits' => $package->limits,
            'school_name' => $this->name,
            'school_email' => $this->email,
        ]
    ]);
}

/**
 * Process subscription payment
 */
public function processSubscriptionPayment(SubscriptionInvoice $invoice, array $paymentData): SubscriptionPayment
{
    $payment = $this->subscriptionPayments()->create([
        'subscription_invoice_id' => $invoice->id,
        'payment_number' => SubscriptionPayment::generatePaymentNumber(),
        'amount' => $paymentData['amount'],
        'payment_method' => $paymentData['payment_method'],
        'status' => $paymentData['status'],
        'payment_date' => $paymentData['payment_date'] ?? now(),
        'transaction_id' => $paymentData['transaction_id'] ?? null,
        'reference_number' => $paymentData['reference_number'] ?? null,
        'gateway_response' => $paymentData['gateway_response'] ?? null,
        'fee_amount' => $paymentData['fee_amount'] ?? 0.00,
        'notes' => $paymentData['notes'] ?? null,
    ]);

    // Update invoice status if fully paid
    if ($payment->isCompleted() && $payment->amount >= $invoice->total_amount) {
        $invoice->update(['status' => 'paid']);

        // Extend subscription period
        $this->extendSubscriptionPeriod($invoice->billing_period);
    }

    return $payment;
}

/**
 * Extend subscription period after successful payment
 */
public function extendSubscriptionPeriod(string $billingPeriod): void
{
    $currentExpiry = $this->subscription_expires_at ?? now();

    if ($billingPeriod === 'yearly') {
        $newExpiry = $currentExpiry->addYear();
    } else {
        $newExpiry = $currentExpiry->addMonth();
    }

    $this->update([
        'subscription_expires_at' => $newExpiry,
        'subscription_status' => 'active'
    ]);

    // Log the change
    SubscriptionHistory::logChange(
        $this->id,
        'subscription_extended',
        ['old_expiry' => $currentExpiry],
        ['new_expiry' => $newExpiry, 'billing_period' => $billingPeriod],
        "Subscription extended due to successful payment"
    );
}

/**
 * Upgrade to a new subscription package (UPDATED - uses subscription billing)
 */
public function upgradeTo(SubscriptionPackage $package, string $billingPeriod = 'monthly'): bool
{
    $oldData = [
        'package_id' => $this->subscription_package_id,
        'package_name' => $this->subscriptionPackage?->name,
        'monthly_fee' => $this->monthly_fee,
        'status' => $this->subscription_status
    ];

    $monthlyFee = $billingPeriod === 'yearly' && $package->yearly_price
        ? ($package->yearly_price / 12)
        : $package->monthly_price;

    $expiresAt = $billingPeriod === 'yearly'
        ? now()->addYear()
        : now()->addMonth();

    $updated = $this->update([
        'subscription_status' => 'active',
        'subscription_package_id' => $package->id,
        'subscription_expires_at' => $expiresAt,
        'monthly_fee' => $monthlyFee,
        'subscription_started_at' => now(),
    ]);

    if ($updated) {
        // Create invoice for the new package
        $this->createSubscriptionInvoice($package, $billingPeriod);

        // Log the upgrade
        SubscriptionHistory::logChange(
            $this->id,
            'upgraded',
            $oldData,
            [
                'package_id' => $package->id,
                'package_name' => $package->name,
                'monthly_fee' => $monthlyFee,
                'billing_period' => $billingPeriod,
                'expires_at' => $expiresAt
            ],
            "Upgraded to {$package->name} package"
        );
    }

    return $updated;
}




/**
 * Get subscription payment stats (SAFE VERSION)
 */
public function getSubscriptionStats(): array
{
    try {
        // Check if subscription billing tables exist
        if (!Schema::hasTable('subscription_invoices')) {
            return $this->getFallbackStats();
        }

        return [
            'total_invoices' => $this->subscriptionInvoices()->count(),
            'paid_invoices' => $this->subscriptionInvoices()->where('status', 'paid')->count(),
            'pending_invoices' => $this->subscriptionInvoices()->where('status', 'pending')->count(),
            'overdue_invoices' => $this->subscriptionInvoices()
                ->where('status', 'pending')
                ->where('due_date', '<', now())
                ->count(),
            'total_paid' => $this->getTotalRevenue(),
            'outstanding_balance' => $this->getOutstandingBalance(),
            'current_mrr' => $this->getCurrentMRR(),
            'is_payment_up_to_date' => $this->isPaymentUpToDate(),
            'next_billing_date' => $this->getNextBillingDate(),
        ];
    } catch (\Exception $e) {
        Log::info('Billing tables not ready, using fallback stats: ' . $e->getMessage());
        return $this->getFallbackStats();
    }
}

/**
 * Fallback stats when billing tables don't exist
 */
protected function getFallbackStats(): array
{
    return [
        'total_invoices' => 0,
        'paid_invoices' => 0,
        'pending_invoices' => 0,
        'overdue_invoices' => 0,
        'total_paid' => 0.00,
        'outstanding_balance' => 0.00,
        'current_mrr' => $this->monthly_fee ?? 0.00,
        'is_payment_up_to_date' => true,
        'next_billing_date' => $this->subscription_expires_at,
    ];
}

/**
 * Get monthly revenue (SAFE VERSION)
 */
public function getMonthlyRevenue(): float
{
    try {
        if (!Schema::hasTable('subscription_invoices')) {
            return $this->monthly_fee ?? 0.00;
        }

        return $this->subscriptionInvoices()
            ->where('status', 'paid')
            ->where('billing_period', 'monthly')
            ->whereMonth('invoice_date', now()->month)
            ->whereYear('invoice_date', now()->year)
            ->sum('amount') ?? 0.00;
    } catch (\Exception $e) {
        return $this->monthly_fee ?? 0.00;
    }
}

/**
 * Get total revenue (SAFE VERSION)
 */
public function getTotalRevenue(): float
{
    try {
        if (!Schema::hasTable('subscription_payments')) {
            // Estimate based on monthly fee and subscription duration
            $monthsActive = $this->subscription_started_at
                ? $this->subscription_started_at->diffInMonths(now()) + 1
                : 1;
            return ($this->monthly_fee ?? 0.00) * $monthsActive;
        }

        return $this->subscriptionPayments()
            ->where('status', 'completed')
            ->sum('amount') ?? 0.00;
    } catch (\Exception $e) {
        // Fallback calculation
        $monthsActive = $this->subscription_started_at
            ? $this->subscription_started_at->diffInMonths(now()) + 1
            : 1;
        return ($this->monthly_fee ?? 0.00) * $monthsActive;
    }
}

/**
 * Check if school has ever had a trial subscription
 */
public function hasUsedTrial(): bool
{
    // Check subscription_history for any trial records
    $trialHistory = DB::table('subscription_history')
        ->where('school_id', $this->id)
        ->where('action', 'trial_started')
        ->exists();
    
    if ($trialHistory) {
        return true;
    }
    
    // Check if trial_ends_at was ever set (indicates trial was used)
    // Even if currently expired, if trial_ends_at exists, trial was used
    if ($this->trial_ends_at) {
        return true;
    }
    
    return false;
}

/**
 * Check if school can start a trial
 */
public function canStartTrial(): bool
{
    // Trial can only be started if:
    // 1. Never had a trial before
    // 2. Current status is not 'active' (has no paid subscription)
    
    if ($this->hasUsedTrial()) {
        return false;
    }
    
    if ($this->subscription_status === 'active') {
        return false;
    }
    
    return true;
}

/**
 * Initialize trial subscription (PROTECTED - checks if allowed)
 */
public function initializeTrial()
{
    // CRITICAL: Check if trial can be started
    if (!$this->canStartTrial()) {
        throw new \Exception('Trial period has already been used for this school.');
    }
    
    $trialPackage = SubscriptionPackage::where('slug', 'trial')->first();
    
    if (!$trialPackage) {
        throw new \Exception('Trial package not found.');
    }
    
    $this->update([
        'subscription_status' => 'trial',
        'subscription_package_id' => $trialPackage->id,
        'trial_ends_at' => now()->addDays($trialPackage->trial_days),
        'subscription_started_at' => now(),
    ]);
    
    // Log in subscription history
    DB::table('subscription_history')->insert([
        'school_id' => $this->id,
        'action' => 'trial_started',
        'new_data' => json_encode([
            'package_id' => $trialPackage->id,
            'trial_days' => $trialPackage->trial_days,
            'trial_ends_at' => now()->addDays($trialPackage->trial_days)->toDateTimeString(),
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

/**
 * Admin-only: Reset trial (bypasses normal restrictions)
 */
public function adminResetTrial()
{
    $trialPackage = SubscriptionPackage::where('slug', 'trial')->first();
    
    if (!$trialPackage) {
        throw new \Exception('Trial package not found.');
    }
    
    $this->update([
        'subscription_status' => 'trial',
        'subscription_package_id' => $trialPackage->id,
        'trial_ends_at' => now()->addDays($trialPackage->trial_days),
        'subscription_expires_at' => null,
        'monthly_fee' => 0.00,
    ]);
    
    // Log in subscription history
    DB::table('subscription_history')->insert([
        'school_id' => $this->id,
        'action' => 'trial_reset',
        'reason' => 'Admin reset trial',
        'performed_by' => auth()->id(),
        'new_data' => json_encode([
            'package_id' => $trialPackage->id,
            'trial_days' => $trialPackage->trial_days,
            'trial_ends_at' => now()->addDays($trialPackage->trial_days)->toDateTimeString(),
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

}
