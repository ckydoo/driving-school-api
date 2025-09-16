<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            
            // Foreign key fields - must match the field names used in upsertSchedule
            $table->unsignedBigInteger('student');
            $table->unsignedBigInteger('instructor'); 
            $table->unsignedBigInteger('course');
            $table->unsignedBigInteger('car')->nullable();
            
            // Schedule details
            $table->datetime('start');
            $table->datetime('end');
            $table->string('class_type')->default('Practical');
            $table->string('status')->default('scheduled');
            
            // Lesson tracking
            $table->boolean('attended')->default(false);
            $table->integer('lessons_completed')->default(0);
            $table->integer('lessons_deducted')->default(1);
            
            
            // Recurring schedule options
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_pattern')->nullable();
            $table->date('recurring_end_date')->nullable();
            
            // Additional notes
            $table->text('notes')->nullable();
            $table->text('instructor_notes')->nullable();
            
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('student')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('instructor')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('course')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('vehicle')->references('id')->on('fleet')->onDelete('set null');

            // Indexes for performance
            $table->index(['student', 'status']);
            $table->index(['instructor', 'start']);
            $table->index(['start', 'end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};