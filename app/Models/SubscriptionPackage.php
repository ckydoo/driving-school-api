<?php
// app/Models/SubscriptionPackage.php - FIXED VERSION

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
        // FIXED: Add proper validation to prevent division by zero
        if (!$this->yearly_price || !$this->monthly_price || $this->monthly_price <= 0) {
            return 0;
        }
        
        $yearlyMonthlyEquivalent = $this->monthly_price * 12;
        
        // Additional safety check
        if ($yearlyMonthlyEquivalent <= 0) {
            return 0;
        }
        
        // Make sure yearly price is less than monthly equivalent for a valid discount
        if ($this->yearly_price >= $yearlyMonthlyEquivalent) {
            return 0;
        }
        
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

    // ADDITIONAL HELPER METHODS

    /**
     * Get formatted monthly price
     */
    public function getFormattedMonthlyPrice()
    {
        return '$' . number_format($this->monthly_price, 2);
    }

    /**
     * Get formatted yearly price
     */
    public function getFormattedYearlyPrice()
    {
        if (!$this->yearly_price) {
            return null;
        }
        return '$' . number_format($this->yearly_price, 2);
    }

    /**
     * Get yearly savings amount
     */
    public function getYearlySavings()
    {
        if (!$this->yearly_price || !$this->monthly_price || $this->monthly_price <= 0) {
            return 0;
        }
        
        $yearlyMonthlyEquivalent = $this->monthly_price * 12;
        
        if ($yearlyMonthlyEquivalent <= 0 || $this->yearly_price >= $yearlyMonthlyEquivalent) {
            return 0;
        }
        
        return $yearlyMonthlyEquivalent - $this->yearly_price;
    }

    /**
     * Get formatted yearly savings
     */
    public function getFormattedYearlySavings()
    {
        $savings = $this->getYearlySavings();
        return $savings > 0 ? '$' . number_format($savings, 2) : '$0.00';
    }

    /**
     * Check if package has yearly pricing
     */
    public function hasYearlyPricing()
    {
        return $this->yearly_price && $this->yearly_price > 0;
    }

    /**
     * Get price for specific billing period
     */
    public function getPriceForPeriod($billingPeriod = 'monthly')
    {
        return $billingPeriod === 'yearly' && $this->hasYearlyPricing() 
            ? $this->yearly_price 
            : $this->monthly_price;
    }

    /**
     * Get formatted price for specific billing period
     */
    public function getFormattedPriceForPeriod($billingPeriod = 'monthly')
    {
        $price = $this->getPriceForPeriod($billingPeriod);
        return '$' . number_format($price, 2);
    }

    /**
     * Check if this is a free/trial package
     */
    public function isFree()
    {
        return $this->monthly_price == 0;
    }

    /**
     * Get limit description for display
     */
    public function getLimitDescription($limitType)
    {
        $limit = $this->getLimit($limitType);
        
        if ($limit === -1) {
            return 'Unlimited';
        }
        
        return number_format($limit);
    }

    /**
     * Get all features as formatted list
     */
    public function getFormattedFeatures()
    {
        return $this->features ?? [];
    }

    /**
     * Check if package can accommodate given usage
     */
    public function canAccommodate($students = 0, $instructors = 0, $vehicles = 0)
    {
        if (!$this->isUnlimited('max_students') && $students > $this->getLimit('max_students')) {
            return false;
        }
        
        if (!$this->isUnlimited('max_instructors') && $instructors > $this->getLimit('max_instructors')) {
            return false;
        }
        
        if (!$this->isUnlimited('max_vehicles') && $vehicles > $this->getLimit('max_vehicles')) {
            return false;
        }
        
        return true;
    }
}