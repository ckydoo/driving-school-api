<?php
// app/Http/Controllers/Api/UserController.php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        $query = User::query();

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                  ->orWhere('lname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        return $this->sendResponse($users, 'Users retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
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
            'status' => $request->status ?? 'active',
            'gender' => $request->gender,
            'phone' => $request->phone,
            'address' => $request->address,
            'idnumber' => $request->idnumber,
            'emergency_contact' => $request->emergency_contact,
        ]);

        return $this->sendResponse($user, 'User created successfully.');
    }

    public function show($id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return $this->sendError('User not found.');
        }

        return $this->sendResponse($user, 'User retrieved successfully.');
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return $this->sendError('User not found.');
        }

        $validator = Validator::make($request->all(), [
            'fname' => 'sometimes|required|string|max:255',
            'lname' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'date_of_birth' => 'sometimes|required|date',
            'role' => 'sometimes|required|in:admin,instructor,student',
            'status' => 'sometimes|required|in:active,inactive,suspended',
            'gender' => 'sometimes|required|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'idnumber' => 'nullable|string|unique:users,idnumber,' . $id,
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $user->update($request->only([
            'fname', 'lname', 'email', 'date_of_birth', 'role', 'status',
            'gender', 'phone', 'address', 'idnumber', 'emergency_contact'
        ]));

        return $this->sendResponse($user, 'User updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return $this->sendError('User not found.');
        }

        $user->delete();

        return $this->sendResponse([], 'User deleted successfully.');
    }

    public function students()
    {
        $students = User::students()->active()->get();
        return $this->sendResponse($students, 'Students retrieved successfully.');
    }

    public function instructors()
    {
        $instructors = User::instructors()->active()->get();
        return $this->sendResponse($instructors, 'Instructors retrieved successfully.');
    }
}
