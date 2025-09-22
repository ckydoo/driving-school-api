<?php
// app/Http/Controllers/SchoolRegistrationController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\School;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SchoolRegistrationController extends Controller
{
    /**
     * Show the registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.school-register');
    }

    /**
     * Handle school registration
     */
    public function register(Request $request)
    {
        // Validate the form data
        $validator = Validator::make($request->all(), [
            'school_name' => 'required|string|max:255',
            'school_address' => 'required|string|max:500',
            'admin_email' => 'required|email|max:255|unique:users,email|unique:schools,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors below.');
        }

        DB::beginTransaction();

        try {
            // Generate unique invitation code
            $invitationCode = $this->generateInvitationCode();

            // Create the school
            $school = School::create([
                'name' => $request->school_name,
                'slug' => Str::slug($request->school_name),
                'email' => $request->admin_email,
                'phone' => '', // Will be updated later
                'address' => $request->school_address,
                'city' => '', // Will be updated later
                'country' => 'Zimbabwe', // Default from your migration
                'invitation_code' => $invitationCode,
                'status' => 'active',
                'subscription_status' => 'trial',
                'trial_ends_at' => now()->addDays(30), // 30-day free trial
                'monthly_fee' => 0.00,
                'max_students' => 100,
                'max_instructors' => 10,
            ]);

            // Create the admin user
            $admin = User::create([
                'fname' => 'Admin', // Default - can be updated later
                'lname' => 'User',  // Default - can be updated later
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role' => 'admin',
                'status' => 'active',
                'school_id' => $school->id,
                'gender' => 'other', // Default
                'date_of_birth' => now()->subYears(30), // Default
            ]);

            DB::commit();

            // Auto-login the newly created admin
            auth()->login($admin);

            return redirect()->route('admin.dashboard')->with('success',
                "Welcome to " . config('app.name') . "! Your driving school '{$school->name}' has been registered successfully. You have a 30-day free trial."
            );

        } catch (\Exception $e) {
            DB::rollback();

            return back()
                ->withInput()
                ->with('error', 'Registration failed. Please try again. If the problem persists, contact support.');
        }
    }

    /**
     * Generate a unique invitation code
     */
    private function generateInvitationCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (School::where('invitation_code', $code)->exists());

        return $code;
    }
}
