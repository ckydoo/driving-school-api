<?php
// app/Http/Controllers/Api/SchoolController.php

namespace App\Http\Controllers\Api;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SchoolController extends Controller
{
    /**
     * Register a new school
     * POST /api/schools/register
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:schools,name',
                'email' => 'required|email|unique:schools,email',
                'phone' => 'required|string',
                'address' => 'required|string',
                'city' => 'required|string|max:255', // ✅ Added city validation
                'country' => 'nullable|string|max:255',
                'website' => 'nullable|url',
                'start_time' => 'nullable|string',
                'end_time' => 'nullable|string',
                'operating_days' => 'nullable|array',
                'admin_fname' => 'required|string',
                'admin_lname' => 'required|string',
                'admin_email' => 'required|email|unique:users,email',
                'admin_password' => 'required|string|min:8',
                'admin_phone' => 'nullable|string',
                'admin_date_of_birth' => 'nullable|date',
                'admin_gender' => 'nullable|in:male,female,other,not_specified',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create school
            $school = School::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'address' => $request->input('address'),
                'city' => $request->input('city', 'Harare'), // ✅ Added missing city field
                'country' => $request->input('country', 'Zimbabwe'), // ✅ Added country field
                'website' => $request->input('website'),
                'start_time' => $request->input('start_time', '08:00'),
                'end_time' => $request->input('end_time', '17:00'),
                'operating_days' => json_encode($request->input('operating_days', ['Mon','Tue','Wed','Thu','Fri'])),
                'invitation_code' => $this->generateUniqueInvitationCode($request->input('name')), // ✅ Fixed method name
                'status' => 'active',
                'trial_expires_at' => now()->addDays(30), // 30-day trial
            ]);

            // Create admin user for the school
            $adminUser = User::create([
                'school_id' => $school->id,
                'fname' => $request->input('admin_fname'),
                'lname' => $request->input('admin_lname'),
                'email' => $request->input('admin_email'),
                'password' => Hash::make($request->input('admin_password')),
                'role' => 'admin',
                'status' => 'active',
                'date_of_birth' => $request->input('admin_date_of_birth', '1990-01-01'), // ✅ Added required field with default
                'phone' => $request->input('admin_phone'),
                'gender' => $request->input('admin_gender', 'Male'), // ✅ Added optional field
            ]);

            return response()->json([
                'success' => true,
                'message' => 'School registered successfully',
                'data' => [
                    'school' => [
                        'id' => $school->id,
                        'name' => $school->name,
                        'invitation_code' => $school->invitation_code,
                    ],
                    'admin_user' => [
                        'id' => $adminUser->id,
                        'email' => $adminUser->email,
                        'name' => $adminUser->fname . ' ' . $adminUser->lname,
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('School registration error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Generate a unique school invitation code
     */
    private function generateUniqueInvitationCode(string $schoolName): string
    {
        // Get first 3 letters of each word in school name
        $words = explode(' ', strtoupper($schoolName));
        $prefix = '';

        foreach (array_slice($words, 0, 2) as $word) {
            $prefix .= substr(preg_replace('/[^A-Z]/', '', $word), 0, 3);
        }

        // Ensure we have at least 3 characters
        if (strlen($prefix) < 3) {
            $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $schoolName), 0, 3));
        }

        // Add random number to ensure uniqueness
        do {
            $suffix = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $code = $prefix . $suffix;
        } while (School::where('invitation_code', $code)->exists());

        return $code;
    }

    /**
     * Find school by name or code
     * POST /api/schools/find
     */
    public function find(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'identifier' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $identifier = $request->input('identifier');

            $school = School::where('name', 'like', "%{$identifier}%")
                          ->orWhere('invitation_code', $identifier)
                          ->orWhere('slug', $identifier)
                          ->first();

            if (!$school) {
                return response()->json([
                    'success' => false,
                    'message' => 'School not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $school->id,
                    'name' => $school->name,
                    'invitation_code' => $school->invitation_code,
                    'address' => $school->address,
                    'phone' => $school->phone,
                    'email' => $school->email,
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('School find error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Authenticate user with school
     * POST /api/schools/authenticate
     */
    public function authenticateUser(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
                'school_identifier' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find school first
            $school = School::where('name', 'like', "%{$request->school_identifier}%")
                          ->orWhere('invitation_code', $request->school_identifier)
                          ->first();

            if (!$school) {
                return response()->json([
                    'success' => false,
                    'message' => 'School not found'
                ], 404);
            }

            // Find and authenticate user
            $user = User::where('email', $request->email)
                       ->where('school_id', $school->id)
                       ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Generate token
            $token = $user->createToken('school-api')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Authentication successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->fname . ' ' . $user->lname,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                    'school' => [
                        'id' => $school->id,
                        'name' => $school->name,
                        'invitation_code' => $school->invitation_code,
                    ],
                    'token' => $token
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Authentication error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}