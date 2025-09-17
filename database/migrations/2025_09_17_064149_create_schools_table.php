<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->unique();
            $table->string('phone');
            $table->text('address');
            $table->string('city');
            $table->string('country')->default('Zimbabwe');
            $table->string('website')->nullable();
            $table->time('start_time')->default('08:00');
            $table->time('end_time')->default('17:00');
            $table->json('operating_days')->default('["Mon","Tue","Wed","Thu","Fri"]');
            $table->string('invitation_code', 10)->unique();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->enum('subscription_status', ['trial', 'active', 'suspended', 'cancelled'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->decimal('monthly_fee', 8, 2)->default(0.00);
            $table->integer('max_students')->default(100);
            $table->integer('max_instructors')->default(10);
            $table->json('features')->nullable(); // Store enabled features
            $table->timestamps();
            
            $table->index(['status', 'subscription_status']);
            $table->index('invitation_code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('schools');
    }
};