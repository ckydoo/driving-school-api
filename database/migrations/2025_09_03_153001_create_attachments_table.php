<?php
// database/migrations/2025_09_15_000011_create_attachments_table.php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('original_name');
            $table->string('mime_type');
            $table->integer('file_size');
            $table->string('path');
            $table->unsignedBigInteger('uploaded_by');
            $table->string('entity_type'); // user, course, schedule, etc
            $table->unsignedBigInteger('entity_id');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            // Foreign key
            $table->foreign('uploaded_by')->references('id')->on('users');

            // Indexes
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
