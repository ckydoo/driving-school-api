<?php
// Create this migration: php artisan make:migration setup_existing_schools_with_trial_package

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\School;
use App\Models\SubscriptionPackage;

return new class extends Migration
{
    public function up()
    {
        // First, ensure we have a trial package
        $trialPackage = SubscriptionPackage::where('slug', 'trial')->first();
        
        if (!$trialPackage) {
            $trialPackage = SubscriptionPackage::create([
                'name' => 'Trial',
                'slug' => 'trial',
                'monthly_price' => 0.00,
                'yearly_price' => 0.00,
                'description' => 'Free 30-day trial with all features',
                'features' => [
                    'All features included',
                    '30-day trial period',
                    'Email support',
                    'Mobile app access'
                ],
                'limits' => [
                    'max_students' => 50,
                    'max_instructors' => 5,
                    'max_vehicles' => 10
                ],
                'trial_days' => 30,
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 1
            ]);
        }

        // Update all existing schools to have the trial package
        School::whereNull('subscription_package_id')->update([
            'subscription_package_id' => $trialPackage->id,
            'subscription_status' => 'trial',
            'trial_ends_at' => now()->addDays(30),
            'subscription_started_at' => now(),
        ]);

        // Set trial_ends_at for schools that have null trial_ends_at
        School::whereNull('trial_ends_at')
            ->where('subscription_status', 'trial')
            ->update([
                'trial_ends_at' => now()->addDays(30)
            ]);
    }

    public function down()
    {
        // Remove subscription package references from schools
        School::whereNotNull('subscription_package_id')->update([
            'subscription_package_id' => null,
            'subscription_started_at' => null,
        ]);
    }
};