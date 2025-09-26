<?php
// Create this migration: php artisan make:migration create_subscription_packages_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subscription_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('monthly_price', 8, 2);
            $table->decimal('yearly_price', 8, 2)->nullable();
            $table->text('description')->nullable();
            $table->json('features'); // Array of features
            $table->json('limits'); // max_students, max_instructors, max_vehicles
            $table->integer('trial_days')->default(30);
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('stripe_monthly_price_id')->nullable();
            $table->string('stripe_yearly_price_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_packages');
    }
};