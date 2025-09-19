<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, School};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope($request);

        $query = User::with('school')
                    ->when($schoolId, fn($q) => $q->where('school_id', $schoolId));

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                  ->orWhere('lname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Super admin can filter by school, regular admin cannot
        if ($request->filled('school_id') && $user->role === 'super_admin') {
            $query->where('school_id', $request->school_id);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Schools dropdown (only for super admin)
        $schools = $user->role === 'super_admin' ? School::orderBy('name')->get() : collect();

        return view('admin.users.index', compact('users', 'schools'));
    }

    public function create()
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope();
        
        // If regular admin, they can only create users for their school
        if ($schoolId) {
            $schools = School::where('id', $schoolId)->get();
        } else {
            // Super admin can create users for any school
            $schools = School::orderBy('name')->get();
        }
        
        return view('admin.users.create', compact('schools'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $schoolId = $this->getSchoolScope();

        $validator = Validator::make($request->all(), [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:15',
            'role' => 'required|in:student,instructor,admin',
            'status' => 'required|in:active,inactive,suspended',
            'school_id' => 'required|exists:schools,id',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Prevent regular admins from creating users outside their school
        if ($schoolId && $request->school_id != $schoolId) {
            return redirect()->back()
                           ->with('error', 'You can only create users for your school')
                           ->withInput();
        }

        User::create([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'status' => $request->status,
            'school_id' => $request->school_id,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.users.index')
                        ->with('success', 'User created successfully.');
    }

    /**
     * Get the school scope based on user role
     */
    private function getSchoolScope()
    {
        $user = auth()->user();
        
        // Super admin sees everything
        if ($user->role === 'super_admin') {
            return null;
        }
        
        // Regular admin sees only their school
        return $user->school_id;
    }
}
