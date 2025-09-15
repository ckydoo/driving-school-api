<?php
// database/migrations/2025_09_03_152735_create_courses_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('lessons')->default(1);
            $table->enum('type', ['practical', 'theory', 'combined'])->default('practical');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->json('requirements')->nullable();
            $table->integer('duration_minutes')->default(60);
            $table->timestamps();

            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
