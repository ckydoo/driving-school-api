
<?php
// database/migrations/2025_09_15_000006_create_payments_table.php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoiceId');
            $table->decimal('amount', 10, 2);
            $table->string('method');
            $table->string('status')->default('Paid');
            $table->timestamp('paymentDate')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->text('notes')->nullable();
            $table->string('reference')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('receipt_generated_at')->nullable();
            $table->string('cloud_storage_path')->nullable();
            $table->integer('receipt_file_size')->nullable();
            $table->string('receipt_type')->nullable();
            $table->boolean('receipt_generated')->default(false);
            $table->unsignedBigInteger('userId')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            // Foreign keys
            $table->foreign('invoiceId')->references('id')->on('invoices');
            $table->foreign('userId')->references('id')->on('users');

            // Indexes
            $table->index(['invoiceId', 'status']);
            $table->index('paymentDate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
