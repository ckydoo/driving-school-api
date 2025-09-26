<?php
// Create this migration: php artisan make:migration create_subscription_billing_tables

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Subscription Invoices - separate from driving school invoices
        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('subscription_package_id');
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);
            $table->enum('billing_period', ['monthly', 'yearly']);
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded', 'cancelled'])->default('pending');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->date('period_start');
            $table->date('period_end');
            $table->json('invoice_data')->nullable(); // Store package details at time of invoice
            $table->string('stripe_invoice_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys and indexes
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('subscription_package_id')->references('id')->on('subscription_packages');
            $table->index(['school_id', 'status']);
            $table->index(['status', 'due_date']);
            $table->index('invoice_date');
        });

        // Subscription Payments - separate payment tracking
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('subscription_invoice_id');
            $table->string('payment_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['stripe', 'paypal', 'bank_transfer', 'manual', 'credit_card']);
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled', 'refunded']);
            $table->datetime('payment_date');
            $table->string('transaction_id')->nullable(); // Stripe payment intent ID, etc.
            $table->string('reference_number')->nullable();
            $table->json('gateway_response')->nullable(); // Store full payment gateway response
            $table->decimal('fee_amount', 10, 2)->default(0.00); // Payment processing fees
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys and indexes
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('subscription_invoice_id')->references('id')->on('subscription_invoices');
            $table->index(['school_id', 'status']);
            $table->index(['status', 'payment_date']);
            $table->index('transaction_id');
        });

        // Subscription Usage Tracking (optional - for usage-based billing)
        Schema::create('subscription_usage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('usage_type'); // students, instructors, vehicles, api_calls, etc.
            $table->integer('usage_count');
            $table->date('usage_date');
            $table->json('usage_data')->nullable(); // Additional usage details
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->index(['school_id', 'usage_type', 'usage_date']);
        });

        // Subscription History - track all subscription changes
        Schema::create('subscription_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->enum('action', ['created', 'upgraded', 'downgraded', 'cancelled', 'reactivated', 'trial_started', 'trial_extended', 'package_changed']);
            $table->json('old_data')->nullable(); // Previous subscription state
            $table->json('new_data')->nullable(); // New subscription state
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable(); // Admin user who made the change
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['school_id', 'action']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_history');
        Schema::dropIfExists('subscription_usage');
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('subscription_invoices');
    }
};