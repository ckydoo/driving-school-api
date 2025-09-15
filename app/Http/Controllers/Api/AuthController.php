<?php
// app/Http/Controllers/Api/AuthController.php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            // Check if user is active
            if ($user->status !== 'active') {
                return $this->sendError('Account is not active.', [], 403);
            }

            $success['token'] = $user->createToken('DrivingSchoolApp')->plainTextToken;
            $success['user'] = $user;

            return $this->sendResponse($success, 'User login successful.');
        } else {
            return $this->sendError('Invalid credentials.', [], 401);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'required|date',
            'role' => 'required|in:admin,instructor,student',
            'gender' => 'required|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'idnumber' => 'nullable|string|unique:users',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $user = User::create([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'date_of_birth' => $request->date_of_birth,
            'role' => $request->role,
            'status' => 'active',
            'gender' => $request->gender,
            'phone' => $request->phone,
            'address' => $request->address,
            'idnumber' => $request->idnumber,
        ]);

        $success['token'] = $user->createToken('DrivingSchoolApp')->plainTextToken;
        $success['user'] = $user;

        return $this->sendResponse($success, 'User registered successfully.');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse([], 'User logged out successfully.');
    }

    public function user(Request $request)
    {
        return $this->sendResponse($request->user(), 'User data retrieved successfully.');
    }
}
