<?php
// app/Http/Controllers/Api/SchoolController.php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\FacadesLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;
use App\Models\School; // You'll need to create this model

class SchoolController extends BaseController
{
    /**
     * Register a new school with admin user
     */
    public function register(Request $request)
    {
        try {
            Log::info('School registration attempt', $request->all());

            // Validate school and admin data
            $validator = Validator::make($request->all(), [
                // School validation
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:schools,email',
                'phone' => 'required|string|max:20',
                'address' => 'required|string|max:500',
                'city' => 'required|string|max:100',
                'country' => 'required|string|max:100',
                'website' => 'nullable|url|max:255',
                'start_time' => 'required|string',
                'end_time' => 'required|string',
                'operating_days' => 'required|array',

                // Admin user validation
                'admin_first_name' => 'required|string|max:255',
                'admin_last_name' => 'required|string|max:255',
                'admin_email' => 'required|email|max:255|unique:users,email',
                'admin_password' => 'required|string|min:8',
                'admin_password_confirmation' => 'required|same:admin_password',
                'admin_phone' => 'required|string|max:20',
            ]);

            if ($validator->fails()) {
                Log::error('School registration validation failed', $validator->errors()->toArray());
                return $this->sendError('Validation Error.', $validator->errors(), 422);
            }

            // Start database transaction
            DB::beginTransaction();

            try {
                // Create school
                $school = School::create([
                    'name' => $request->name,
                    'slug' => Str::slug($request->name),
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'city' => $request->city,
                    'country' => $request->country,
                    'website' => $request->website,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'operating_days' => json_encode($request->operating_days),
                    'invitation_code' => $this->generateInvitationCode($request->name),
                    'status' => 'active',
                ]);

                Log::info('School created successfully', ['school_id' => $school->id]);

                // Create admin user
                $admin = User::create([
                    'fname' => $request->admin_first_name,
                    'lname' => $request->admin_last_name,
                    'email' => $request->admin_email,
                    'password' => Hash::make($request->admin_password),
                    'role' => 'admin',
                    'phone' => $request->admin_phone,
                    'status' => 'active',
                    'date_of_birth' => now()->subYears(25)->format('Y-m-d'), // Default age
                    'gender' => 'other', // Default
                    'school_id' => $school->id, // Link to school if you have this field
                ]);

                Log::info('Admin user created successfully', ['user_id' => $admin->id]);

                // Create API token for the admin
                $token = $admin->createToken('school-registration')->plainTextToken;

                // Commit transaction
                DB::commit();

                $response = [
                    'school' => [
                        'id' => $school->id,
                        'name' => $school->name,
                        'email' => $school->email,
                        'phone' => $school->phone,
                        'address' => $school->address,
                        'city' => $school->city,
                        'country' => $school->country,
                        'website' => $school->website,
                        'start_time' => $school->start_time,
                        'end_time' => $school->end_time,
                        'operating_days' => json_decode($school->operating_days),
                        'invitation_code' => $school->invitation_code,
                        'status' => $school->status,
                    ],
                    'admin' => [
                        'id' => $admin->id,
                        'fname' => $admin->fname,
                        'lname' => $admin->lname,
                        'email' => $admin->email,
                        'role' => $admin->role,
                        'phone' => $admin->phone,
                        'status' => $admin->status,
                    ],
                    'token' => $token,
                    'trial_days_remaining' => 30, // Default trial period
                ];

                Log::info('School registration completed successfully', ['school_id' => $school->id, 'admin_id' => $admin->id]);

                return $this->sendResponse($response, 'School registered successfully.');

            } catch (\Exception $e) {
                // Rollback transaction
                DB::rollback();
                Log::error('Database transaction failed during school registration', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('School registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return $this->sendError('School registration failed: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Find school by name or invitation code
     */
    public function find(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $identifier = $request->identifier;

        // Search by name first (case insensitive)
        $school = School::where('name', 'ILIKE', "%{$identifier}%")
                       ->where('status', 'active')
                       ->first();

        // If not found by name, search by invitation code
        if (!$school) {
            $school = School::where('invitation_code', strtoupper($identifier))
                           ->where('status', 'active')
                           ->first();
        }

        if (!$school) {
            return $this->sendError('School not found.', [], 404);
        }

        $response = [
            'id' => $school->id,
            'name' => $school->name,
            'email' => $school->email,
            'phone' => $school->phone,
            'address' => $school->address,
            'city' => $school->city,
            'country' => $school->country,
            'website' => $school->website,
            'invitation_code' => $school->invitation_code,
            'status' => $school->status,
        ];

        return $this->sendResponse($response, 'School found.');
    }

    /**
     * Authenticate user for specific school
     */
    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'school_identifier' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        // Find school first
        $school = School::where('name', 'ILIKE', "%{$request->school_identifier}%")
                       ->orWhere('invitation_code', strtoupper($request->school_identifier))
                       ->where('status', 'active')
                       ->first();

        if (!$school) {
            return $this->sendError('School not found.', [], 404);
        }

        // Find user in that school
        $user = User::where('email', $request->email)
                   ->where('status', 'active')
                   ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->sendError('Invalid credentials.', [], 401);
        }

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = [
            'school' => [
                'id' => $school->id,
                'name' => $school->name,
                'email' => $school->email,
                'phone' => $school->phone,
                'address' => $school->address,
                'city' => $school->city,
                'country' => $school->country,
                'invitation_code' => $school->invitation_code,
            ],
            'user' => [
                'id' => $user->id,
                'fname' => $user->fname,
                'lname' => $user->lname,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
            ],
            'token' => $token,
            'trial_days_remaining' => 30,
        ];

        return $this->sendResponse($response, 'Authentication successful.');
    }

    /**
     * Generate unique invitation code
     */
    private function generateInvitationCode($schoolName)
    {
        // Get first 3 letters of each word in school name
        $words = explode(' ', strtoupper($schoolName));
        $prefix = '';

        foreach (array_slice($words, 0, 2) as $word) {
            $prefix .= substr($word, 0, 3);
        }

        // Add random number
        $suffix = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $code = $prefix . $suffix;

        // Ensure uniqueness
        while (School::where('invitation_code', $code)->exists()) {
            $suffix = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $code = $prefix . $suffix;
        }

        return $code;
    }
}
