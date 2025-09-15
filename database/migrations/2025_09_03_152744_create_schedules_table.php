
<?php
// database/migrations/2025_09_15_000003_create_schedules_table.php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student');
            $table->unsignedBigInteger('instructor');
            $table->unsignedBigInteger('course');
            $table->unsignedBigInteger('vehicle')->nullable();
            $table->dateTime('lesson_date');
            $table->time('start');
            $table->time('end');
            $table->string('class_type'); // practical, theory
            $table->string('status')->default('scheduled');
            $table->boolean('attended')->default(false);
            $table->integer('lessons_deducted')->default(1);
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_pattern')->nullable();
            $table->date('recurring_end_date')->nullable();
            $table->text('notes')->nullable();
            $table->text('instructor_notes')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            // Foreign keys
            $table->foreign('student')->references('id')->on('users');
            $table->foreign('instructor')->references('id')->on('users');
            $table->foreign('course')->references('id')->on('courses');
            $table->foreign('vehicle')->references('id')->on('fleet');

            // Indexes
            $table->index(['lesson_date', 'status']);
            $table->index(['student', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
