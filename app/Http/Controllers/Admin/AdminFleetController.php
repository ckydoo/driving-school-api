<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Fleet, User, School};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminFleetController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope();

        $query = Fleet::with(['assignedInstructor', 'assignedInstructor.school'])
                     ->when($schoolId, fn($q) => $q->where('school_id', $schoolId));

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('carplate', 'like', "%{$search}%")
                  ->orWhere('make', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $vehicles = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.fleet.index', compact('vehicles'));
    }

    public function create()
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope();
        
        // Get instructors for the current school scope
        $instructors = User::where('role', 'instructor')
                          ->where('status', 'active')
                          ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                          ->get();
        
        return view('admin.fleet.create', compact('instructors'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope() ?: $user->school_id;

        $validator = Validator::make($request->all(), [
            'carplate' => 'required|string|max:20|unique:fleet',
            'make' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'modelyear' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'status' => 'required|in:available,maintenance,retired',
            'instructor' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        Fleet::create([
            'carplate' => $request->carplate,
            'make' => $request->make,
            'model' => $request->model,
            'modelyear' => $request->modelyear,
            'status' => $request->status,
            'instructor' => $request->instructor,
            'school_id' => $schoolId, // Assign to current school
        ]);

        return redirect()->route('admin.fleet.index')
                        ->with('success', 'Vehicle added successfully.');
    }

    private function getSchoolScope()
    {
        $user = auth()->user();
        return $user->role === 'super_admin' ? null : $user->school_id;
    }
}
