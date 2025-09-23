<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\School;
use App\Models\User;

class SchoolController extends BaseController
{
    /**
     * Authenticate user for a specific school
     * POST /api/schools/authenticate
     */
    public function authenticateUser(Request $request): JsonResponse
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'school_identifier' => 'required|string',
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $schoolIdentifier = $request->input('school_identifier');
            $email = $request->input('email');
            $password = $request->input('password');

            // Find school by name or code
            $school = School::where('name', $schoolIdentifier)
                          ->orWhere('invitation_code', $schoolIdentifier)
                          ->orWhere('slug', $schoolIdentifier)
                          ->first();

            if (!$school) {
                return response()->json([
                    'success' => false,
                    'message' => 'School not found'
                ], 404);
            }

            // Find user in this school
            $user = User::where('email', $email)
                       ->where('school_id', $school->id)
                       ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found in this school'
                ], 404);
            }

            // Verify password
            if (!Hash::check($password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Check if user is active
            if ($user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'User account is not active'
                ], 403);
            }

            // Create API token
            $token = $user->createToken('school-auth')->plainTextToken;

            // Calculate trial days remaining (if applicable)
            $trialDaysRemaining = null;
            if ($school->trial_expires_at && $school->trial_expires_at->isFuture()) {
                $trialDaysRemaining = now()->diffInDays($school->trial_expires_at);
            }

            // Prepare response data
            $responseData = [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'fname' => $user->fname,
                    'lname' => $user->lname,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'status' => $user->status,
                ],
                'school' => [
                    'id' => $school->id,
                    'name' => $school->name,
                    'invitation_code' => $school->invitation_code,
                    'address' => $school->address,
                    'phone' => $school->phone,
                    'email' => $school->email,
                    'status' => $school->status,
                ],
                'token' => $token,
                'trial_days_remaining' => $trialDaysRemaining,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Authentication successful',
                'data' => $responseData
            ], 200);

        } catch (\Exception $e) {
            \Log::error('School authentication error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
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
                'admin_fname' => 'required|string',
                'admin_lname' => 'required|string',
                'admin_email' => 'required|email|unique:users,email',
                'admin_password' => 'required|string|min:8',
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
                'invitation_code' => $this->generateSchoolinvitation_code($request->input('name')),
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
     * Generate a unique school code
     */
    private function generateSchoolCode(string $schoolName): string
    {
        // Create base code from school name
        $baseCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $schoolName), 0, 6));
        
        // Add random numbers to ensure uniqueness
        do {
            $code = $baseCode . rand(100, 999);
        } while (School::where('invitation_code', $invitation_code)->exists());

        return $invitation_code;
    }
}