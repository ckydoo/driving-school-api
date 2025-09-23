<?php
// Create this migration: php artisan make:migration create_device_registrations_table

// database/migrations/create_device_registrations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('device_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('user_id');
            $table->string('device_id')->unique();
            $table->string('platform')->nullable(); // 'android', 'ios', 'web', etc.
            $table->string('app_version')->nullable();
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->timestamp('last_seen')->nullable();
            $table->timestamp('last_successful_sync')->nullable();
            $table->boolean('requires_full_sync')->default(false);
            $table->json('sync_metadata')->nullable(); // Additional sync-related data
            $table->timestamps();

            // Indexes
            $table->index(['school_id', 'status']);
            $table->index(['device_id', 'school_id']);
            $table->index('last_seen');

            // Foreign keys
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('device_registrations');
    }
};
