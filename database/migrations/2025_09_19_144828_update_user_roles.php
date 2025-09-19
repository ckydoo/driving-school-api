<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Update the role enum to include super_admin
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('student', 'instructor', 'admin', 'super_admin') DEFAULT 'student'");
    }

    public function down()
    {
        // Revert super_admin users to admin before removing the enum value
        DB::table('users')->where('role', 'super_admin')->update(['role' => 'admin']);
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('student', 'instructor', 'admin') DEFAULT 'student'");
    }
};