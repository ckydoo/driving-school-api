
<?php
// database/migrations/2025_09_15_000005_create_invoices_table.php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student');
            $table->unsignedBigInteger('course');
            $table->integer('lessons');
            $table->decimal('price_per_lesson', 10, 2);
            $table->decimal('amountpaid', 10, 2)->default(0);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('due_date')->nullable();
            $table->string('courseName')->nullable();
            $table->string('status')->default('unpaid');
            $table->decimal('total_amount', 10, 2);
            $table->integer('used_lessons')->default(0);
            $table->string('invoice_number')->unique();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            // Foreign keys
            $table->foreign('student')->references('id')->on('users');
            $table->foreign('course')->references('id')->on('courses');

            // Indexes
            $table->index(['student', 'status']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
