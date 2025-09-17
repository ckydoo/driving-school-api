<?php
// database/migrations/2024_01_02_000000_add_school_id_to_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add school_id to users table if it doesn't exist
        if (!Schema::hasColumn('users', 'school_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('school_id')->nullable()->after('id');
                $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
                $table->index(['school_id', 'role']);
                $table->index(['school_id', 'status']);
            });
        }

        // Add school_id to courses table if it doesn't exist
        if (!Schema::hasColumn('courses', 'school_id')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->unsignedBigInteger('school_id')->nullable()->after('id');
                $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
                $table->index(['school_id', 'status']);
            });
        }

        // Add school_id to fleet table if it doesn't exist
        if (!Schema::hasColumn('fleet', 'school_id')) {
            Schema::table('fleet', function (Blueprint $table) {
                $table->unsignedBigInteger('school_id')->nullable()->after('id');
                $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
                $table->index(['school_id', 'status']);
            });
        }

        // Add school_id to schedules table if it doesn't exist
        if (!Schema::hasColumn('schedules', 'school_id')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->unsignedBigInteger('school_id')->nullable()->after('id');
                $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
                $table->index(['school_id', 'status']);
            });
        }

        // Add school_id to invoices table if it doesn't exist
        if (!Schema::hasColumn('invoices', 'school_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->unsignedBigInteger('school_id')->nullable()->after('id');
                $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
                $table->index(['school_id', 'status']);
            });
        }
    }

    public function down()
    {
        // Remove school_id and foreign keys
        $tables = ['users', 'courses', 'fleet', 'schedules', 'invoices'];
        
        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'school_id')) {
                Schema::table($table, function (Blueprint $table_blueprint) use ($table) {
                    $table_blueprint->dropForeign(["{$table}_school_id_foreign"]);
                    $table_blueprint->dropColumn('school_id');
                });
            }
        }
    }
};