<?php
// database/migrations/2025_09_19_add_super_admin_role.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Add super_admin to role enum in users table
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'instructor', 'student') NOT NULL");
        
        // Add is_super_admin boolean column for easier queries
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_super_admin')->default(false)->after('role');
            $table->index(['is_super_admin', 'role']);
        });

        // Update existing admin users who have no school_id to be super admins
        DB::table('users')
            ->where('role', 'admin')
            ->whereNull('school_id')
            ->update([
                'role' => 'super_admin',
                'is_super_admin' => true
            ]);
    }

    public function down()
    {
        // Remove super_admin role and column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_super_admin');
        });
        
        // Revert role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'instructor', 'student') NOT NULL");
    }
};