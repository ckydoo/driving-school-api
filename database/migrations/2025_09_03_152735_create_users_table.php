<?php
// database/migrations/2025_09_03_152735_create_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fname');
            $table->string('lname');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->date('date_of_birth');
            $table->enum('role', ['admin', 'instructor', 'student']);
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('idnumber')->unique()->nullable();
            $table->string('profile_picture')->nullable();
            $table->json('emergency_contact')->nullable();
            $table->rememberToken();
            $table->timestamps();

            // Indexes
            $table->index(['role', 'status']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
