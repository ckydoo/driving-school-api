<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'subscription_status',
        'subscription_expires_at',
        'settings',
        'status',
    ];

    protected $casts = [
        'subscription_expires_at' => 'datetime',
        'settings' => 'array',
    ];

    // === RELATIONSHIPS ===

    /**
     * Users belonging to this school
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Students belonging to this school
     */
    public function students()
    {
        return $this->hasMany(User::class)->where('role', 'student');
    }

    /**
     * Instructors belonging to this school
     */
    public function instructors()
    {
        return $this->hasMany(User::class)->where('role', 'instructor');
    }

    /**
     * Admins belonging to this school
     */
    public function admins()
    {
        return $this->hasMany(User::class)->where('role', 'admin');
    }

    /**
     * Fleet vehicles belonging to this school
     */
    public function fleet()
    {
        return $this->hasMany(Fleet::class);
    }

    /**
     * Courses offered by this school
     */
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Schedules for this school (through users)
     */
    public function schedules()
    {
        return $this->hasManyThrough(Schedule::class, User::class, 'school_id', 'student');
    }

    /**
     * Invoices for this school (through students)
     */
    public function invoices()
    {
        return $this->hasManyThrough(Invoice::class, User::class, 'school_id', 'student');
    }

    /**
     * Payments for this school (through invoices)
     */
    public function payments()
    {
        return $this->hasManyThrough(
            Payment::class, 
            Invoice::class, 
            'student', // Foreign key on invoices table (student_id)
            'invoiceId', // Foreign key on payments table
            'id', // Local key on schools table
            'id' // Local key on invoices table
        )->whereHas('invoice', function($query) {
            $query->whereHas('student', function($q) {
                $q->where('school_id', $this->id);
            });
        });
    }

    // === HELPER METHODS ===

    /**
     * Check if school has active subscription
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription_status === 'active' && 
               (!$this->subscription_expires_at || $this->subscription_expires_at->isFuture());
    }

    /**
     * Check if subscription is expired
     */
    public function isSubscriptionExpired(): bool
    {
        return $this->subscription_expires_at && $this->subscription_expires_at->isPast();
    }

    /**
     * Get subscription status with expiry check
     */
    public function getSubscriptionStatusAttribute($value)
    {
        if ($value === 'active' && $this->isSubscriptionExpired()) {
            return 'expired';
        }
        return $value;
    }

    /**
     * Get monthly revenue for this school
     */
    public function getMonthlyRevenue($month = null, $year = null)
    {
        $month = $month ?: now()->month;
        $year = $year ?: now()->year;

        return Payment::whereHas('invoice', function($query) {
            $query->whereHas('student', function($q) {
                $q->where('school_id', $this->id);
            });
        })
        ->whereMonth('created_at', $month)
        ->whereYear('created_at', $year)
        ->sum('amount');
    }

    /**
     * Get total revenue for this school
     */
    public function getTotalRevenue()
    {
        return Payment::whereHas('invoice', function($query) {
            $query->whereHas('student', function($q) {
                $q->where('school_id', $this->id);
            });
        })->sum('amount');
    }

    // === SCOPES ===

    /**
     * Scope for active schools
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for schools with active subscriptions
     */
    public function scopeActiveSubscription($query)
    {
        return $query->where('subscription_status', 'active')
                    ->where(function($q) {
                        $q->whereNull('subscription_expires_at')
                          ->orWhere('subscription_expires_at', '>', now());
                    });
    }

    /**
     * Scope for trial subscriptions
     */
    public function scopeTrial($query)
    {
        return $query->where('subscription_status', 'trial');
    }

    /**
     * Scope for expired subscriptions
     */
    public function scopeExpired($query)
    {
        return $query->where('subscription_status', 'expired')
                    ->orWhere(function($q) {
                        $q->where('subscription_status', 'active')
                          ->where('subscription_expires_at', '<=', now());
                    });
    }
}