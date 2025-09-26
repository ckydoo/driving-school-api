<?php
// database/migrations/create_subscription_packages_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subscription_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Starter", "Professional", "Enterprise"
            $table->string('slug')->unique(); // e.g., "starter", "professional"
            $table->decimal('monthly_price', 8, 2); // Monthly price
            $table->decimal('yearly_price', 8, 2)->nullable(); // Yearly price (with discount)
            $table->text('description')->nullable();
            $table->json('features'); // Array of features
            $table->json('limits'); // Limits like max_students, max_instructors
            $table->integer('trial_days')->default(30);
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('stripe_monthly_price_id')->nullable();
            $table->string('stripe_yearly_price_id')->nullable();
            $table->timestamps();
        });

        // Insert default packages
        DB::table('subscription_packages')->insert([
            [
                'name' => 'Trial',
                'slug' => 'trial',
                'monthly_price' => 0.00,
                'yearly_price' => 0.00,
                'description' => '30-day free trial with all features',
                'features' => json_encode([
                    'All features included',
                    '30-day trial period',
                    'Email support',
                    'Mobile app access'
                ]),
                'limits' => json_encode([
                    'max_students' => 50,
                    'max_instructors' => 5,
                    'max_vehicles' => 10
                ]),
                'trial_days' => 30,
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'monthly_price' => 20.00,
                'yearly_price' => 200.00, // 2 months free
                'description' => 'Perfect for growing driving schools',
                'features' => json_encode([
                    'Unlimited students',
                    'Unlimited instructors',
                    'Advanced scheduling',
                    'Complete billing system',
                    'Receipt generation',
                    'Progress tracking',
                    'Cloud backup',
                    'Priority support',
                    'Mobile app access',
                    'Custom branding'
                ]),
                'limits' => json_encode([
                    'max_students' => -1, // -1 = unlimited
                    'max_instructors' => -1,
                    'max_vehicles' => -1
                ]),
                'trial_days' => 30,
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('subscription_packages');
    }
};