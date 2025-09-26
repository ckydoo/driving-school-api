<?php
// Create this seeder: php artisan make:seeder SubscriptionPackageSeeder

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPackage;

class SubscriptionPackageSeeder extends Seeder
{
    public function run()
    {
        $packages = [
            [
                'name' => 'Trial',
                'slug' => 'trial',
                'monthly_price' => 0.00,
                'yearly_price' => 0.00,
                'description' => 'Free trial package with limited features',
                'features' => [
                    'Basic student management',
                    'Basic scheduling',
                    'Basic reporting'
                ],
                'limits' => [
                    'max_students' => 10,
                    'max_instructors' => 2,
                    'max_vehicles' => 2
                ],
                'trial_days' => 30,
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'monthly_price' => 29.99,
                'yearly_price' => 299.99,
                'description' => 'Perfect for small driving schools',
                'features' => [
                    'Student management',
                    'Instructor management',
                    'Basic scheduling',
                    'Invoice generation',
                    'Basic reporting',
                    'Email notifications'
                ],
                'limits' => [
                    'max_students' => 50,
                    'max_instructors' => 5,
                    'max_vehicles' => 5
                ],
                'trial_days' => 14,
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'monthly_price' => 59.99,
                'yearly_price' => 599.99,
                'description' => 'Most popular plan for growing schools',
                'features' => [
                    'Everything in Basic',
                    'Advanced scheduling',
                    'Fleet management',
                    'Advanced reporting',
                    'SMS notifications',
                    'API access',
                    'Custom branding',
                    'Priority support'
                ],
                'limits' => [
                    'max_students' => 200,
                    'max_instructors' => 15,
                    'max_vehicles' => 15
                ],
                'trial_days' => 14,
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 3
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'monthly_price' => 99.99,
                'yearly_price' => 999.99,
                'description' => 'For large driving schools with advanced needs',
                'features' => [
                    'Everything in Professional',
                    'Unlimited students',
                    'Unlimited instructors',
                    'Unlimited vehicles',
                    'Advanced analytics',
                    'Multi-location support',
                    'Custom integrations',
                    'Dedicated account manager'
                ],
                'limits' => [
                    'max_students' => -1, // Unlimited
                    'max_instructors' => -1, // Unlimited
                    'max_vehicles' => -1 // Unlimited
                ],
                'trial_days' => 30,
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 4
            ]
        ];

        foreach ($packages as $package) {
            SubscriptionPackage::create($package);
        }
    }
}

// Don't forget to add this to DatabaseSeeder.php:
// $this->call(SubscriptionPackageSeeder::class);