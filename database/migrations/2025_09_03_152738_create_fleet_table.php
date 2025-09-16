<?php
// database/migrations/2025_09_03_152738_create_fleet_table.php - FIXED VERSION

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet', function (Blueprint $table) {
            $table->id();
            $table->string('carplate');
            $table->string('make');
            $table->string('model');
            $table->string('modelyear');
            $table->unsignedBigInteger('instructor')->nullable(); // Allow NULL
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            // Foreign key - allows NULL values
            $table->foreign('instructor')->references('id')->on('users');

            // Indexes
            $table->index('carplate');
            $table->index('instructor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet');
    }
};