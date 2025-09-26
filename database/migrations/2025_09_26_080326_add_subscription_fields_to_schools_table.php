<?php
// Create this migration: php artisan make:migration add_subscription_fields_to_schools_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('schools', function (Blueprint $table) {
            // Add subscription package relationship
            $table->unsignedBigInteger('subscription_package_id')->nullable()->after('id');
            $table->foreign('subscription_package_id')->references('id')->on('subscription_packages')->onDelete('set null');
            
            // Add Stripe customer ID for payments
            $table->string('stripe_customer_id')->nullable()->after('subscription_package_id');
            
            // Update subscription status enum to include more states
            $table->enum('subscription_status', ['trial', 'active', 'suspended', 'cancelled', 'expired'])->default('trial')->change();
            
            // Add subscription expiration date
            $table->timestamp('subscription_expires_at')->nullable()->after('trial_ends_at');
            
            // Add subscription started date for tracking
            $table->timestamp('subscription_started_at')->nullable()->after('subscription_expires_at');
            
            // Add remaining trial days calculation helper (optional - can be computed)
            // This is optional since it can be calculated from trial_ends_at
            
            // Add indexes for better performance
            $table->index(['subscription_status', 'subscription_expires_at']);
            $table->index(['subscription_package_id', 'subscription_status']);
        });
    }

    public function down()
    {
        Schema::table('schools', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['subscription_package_id']);
            
            // Drop added columns
            $table->dropColumn([
                'subscription_package_id',
                'stripe_customer_id',
                'subscription_expires_at',
                'subscription_started_at'
            ]);
            
            // Revert subscription_status enum to original values
            $table->enum('subscription_status', ['trial', 'active', 'suspended', 'cancelled'])->default('trial')->change();
        });
    }
};