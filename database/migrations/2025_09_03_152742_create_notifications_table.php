<?php
// database/migrations/2025_09_15_000009_create_notifications_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user');
            $table->string('title');
            $table->text('message');
            $table->string('type')->nullable();
            $table->boolean('read')->default(false);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('status')->default('Sent');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            // Foreign key
            $table->foreign('user')->references('id')->on('users');

            // Indexes
            $table->index(['user', 'read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
