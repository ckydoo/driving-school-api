<?php
// database/migrations/2025_09_15_000001_create_users_table.php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fname');
            $table->string('lname');
            $table->string('email')->unique();
            $table->string('gender')->nullable();
            $table->date('date_of_birth');
            $table->string('phone')->unique()->nullable();
            $table->string('idnumber')->unique()->nullable();
            $table->text('address')->nullable();
            $table->string('password');
            $table->string('course')->nullable();
            $table->string('role');
            $table->text('courseIds')->nullable(); // JSON stored as text in Flutter
            $table->string('status')->default('Active');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('last_login')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('last_login_method')->nullable();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

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
