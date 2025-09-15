<?php
// database/migrations/2025_09_15_000008_create_notes_table.php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('note_by');
            $table->unsignedBigInteger('note_for');
            $table->text('note');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            // Foreign keys
            $table->foreign('note_by')->references('id')->on('users');
            $table->foreign('note_for')->references('id')->on('users');

            // Indexes
            $table->index(['note_for', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
