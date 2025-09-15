<?php
// database/migrations/2025_09_03_152738_create_fleet_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet', function (Blueprint $table) {
            $table->id();
            $table->string('make');
            $table->string('model');
            $table->string('registration');
            $table->integer('year');
            $table->enum('transmission', ['manual', 'automatic']);
            $table->enum('status', ['available', 'in_use', 'maintenance', 'out_of_service'])->default('available');
            $table->foreignId('assigned_instructor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('insurance_expiry')->nullable();
            $table->date('mot_expiry')->nullable();
            $table->integer('mileage')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('registration');
            $table->index(['status', 'transmission']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet');
    }
};
