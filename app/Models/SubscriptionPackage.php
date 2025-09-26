<?php
// app/Models/SubscriptionPackage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPackage extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'monthly_price',
        'yearly_price',
        'description',
        'features',
        'limits',
        'trial_days',
        'is_popular',
        'is_active',
        'sort_order',
        'stripe_monthly_price_id',
        'stripe_yearly_price_id'
    ];

    protected $casts = [
        'features' => 'array',
        'limits' => 'array',
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function schools()
    {
        return $this->hasMany(School::class, 'subscription_package_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Helper methods
    public function getYearlyDiscount()
    {
        if (!$this->yearly_price || !$this->monthly_price) {
            return 0;
        }
        
        $yearlyMonthlyEquivalent = $this->monthly_price * 12;
        return round((($yearlyMonthlyEquivalent - $this->yearly_price) / $yearlyMonthlyEquivalent) * 100);
    }

    public function hasFeature($feature)
    {
        return in_array($feature, $this->features ?? []);
    }

    public function getLimit($limit)
    {
        return $this->limits[$limit] ?? 0;
    }

    public function isUnlimited($limit)
    {
        return ($this->limits[$limit] ?? 0) === -1;
    }
}