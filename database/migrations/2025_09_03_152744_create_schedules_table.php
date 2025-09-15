<?php
// database/migrations/2025_09_03_152739_create_schedules_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('car_id')->nullable()->constrained('fleet')->onDelete('set null');
            $table->datetime('start');
            $table->datetime('end');
            $table->enum('class_type', ['practical', 'theory'])->default('practical');
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->boolean('attended')->default(false);
            $table->integer('lessons_deducted')->default(1);
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_pattern')->nullable();
            $table->date('recurring_end_date')->nullable();
            $table->text('notes')->nullable();
            $table->text('instructor_notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'start']);
            $table->index(['instructor_id', 'start']);
            $table->index(['status', 'start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
