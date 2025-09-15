<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        User::create([
            'fname' => 'System',
            'lname' => 'Administrator',
            'email' => 'admin@test.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'active',
            'gender' => 'male',
            'phone' => '1234567890',
            'address' => 'Admin Address',
            'idnumber' => 'ADMIN001',
            'date_of_birth' => '1990-01-01',
        ]);

        // Create instructor
        User::create([
            'fname' => 'John',
            'lname' => 'Instructor',
            'email' => 'instructor@test.com',
            'password' => Hash::make('admin123'),
            'role' => 'instructor',
            'status' => 'active',
            'gender' => 'male',
            'phone' => '1234567891',
            'address' => 'Instructor Address',
            'idnumber' => 'INST001',
            'date_of_birth' => '1985-01-01',
        ]);

        // Create student
        User::create([
            'fname' => 'Jane',
            'lname' => 'Student',
            'email' => 'student@test.com',
            'password' => Hash::make('admin123'),
            'role' => 'student',
            'status' => 'active',
            'gender' => 'female',
            'phone' => '1234567892',
            'address' => 'Student Address',
            'idnumber' => 'STUD001',
            'date_of_birth' => '2000-01-01',
        ]);
    }
}