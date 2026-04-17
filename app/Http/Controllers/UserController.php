<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use App\Models\District;
use App\Mail\UserCreatedMail;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function index()
    {
        $page_title = 'Create User';
        $roles = Role::all();
        $districts = District::all();
        $user = null;
        return view('admin.user_management.new_user', compact('page_title', 'user', 'districts', 'roles'));
    }

    public function edit($id)
    {
        $page_title = 'Update User';
        $roles = Role::all();
        $districts = District::all();
        $user = User::where('id', $id)->first();

        return view('admin.user_management.new_user', compact('page_title', 'user', 'districts', 'roles'));
    }

    // public function store(Request $request)
    // {
    //     // Validation Rules
    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|email:rfc,dns|unique:users,email|max:255',
    //         'phone' => 'required|numeric|digits:10|unique:users,phone', // Mobile Number (Required)
    //         'role_id' => 'required|exists:roles,id',
    //         'district_id' => 'required|exists:districts_master,id',
    //         'designation' => 'nullable|string|max:255',
    //         // New Optional Fields
    //         'aadhaar_number' => 'nullable|string|max:20',
    //         'address' => 'nullable|string|max:500',
    //         'projects' => 'nullable|string|max:255',
    //         'ward_number' => 'nullable|string|max:50',
    //         'gender' => 'nullable|string|in:Male,Female,Other',
    //         'password' => [
    //             'required',
    //             'confirmed',
    //             'min:8',
    //             'regex:/[a-z]/',
    //             'regex:/[A-Z]/',
    //             'regex:/[0-9]/',
    //             'regex:/[@$!%*#?&]/',
    //         ],
    //     ]);

    //     $user = new User();
    //     $user->name = $request->name;
    //     $user->email = filter_var($request->email, FILTER_SANITIZE_EMAIL);
    //     $user->phone = $request->phone; // Saving Mobile Number
    //     $user->role_id = $request->role_id;
    //     $user->district_id = $request->district_id;
    //     $user->designation = $request->designation;

    //     // Saving New Fields
    //     $user->aadhaar_number = $request->aadhaar_number;
    //     $user->address = $request->address;
    //     $user->projects = $request->projects;
    //     $user->ward_number = $request->ward_number;
    //     $user->gender = $request->gender;

    //     $user->password = bcrypt($request->password);

    //     $roleName = Role::find($request->role_id)->name ?? null;

    //     $user->save(); // Save User First

    //     if ($roleName) {
    //         $user->assignRole($roleName);
    //     }

    //     return redirect()->back()->with('success', 'User created successfully!');
    // }
    public function store(Request $request)
    {
        // Validation Rules
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns|unique:users,email|max:255',
            'phone' => 'required|numeric|digits:10|unique:users,phone',
            'role_id' => 'required|exists:roles,id',
            'district_id' => 'required|exists:districts_master,id',
            'designation' => 'nullable|string|max:255',
            'aadhaar_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'projects' => 'nullable|string|max:255',
            'ward_number' => 'nullable|string|max:50',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
        ]);

        // Plain password ko save karein (mail ke liye)
        $plainPassword = $request->password;

        $user = new User();
        $user->name = $request->name;
        $user->email = filter_var($request->email, FILTER_SANITIZE_EMAIL);
        $user->phone = $request->phone;
        $user->role_id = $request->role_id;
        $user->district_id = $request->district_id;
        $user->designation = $request->designation;
        $user->aadhaar_number = $request->aadhaar_number;
        $user->address = $request->address;
        $user->projects = $request->projects;
        $user->ward_number = $request->ward_number;
        $user->gender = $request->gender;
        $user->password = bcrypt($request->password);

        $roleName = Role::find($request->role_id)->name ?? null;

        $user->save();

        if ($roleName) {
            $user->assignRole($roleName);
        }

        // Send Email
        try {
            Mail::to($user->email)->send(new UserCreatedMail($user, $plainPassword, $roleName));
        } catch (\Exception $e) {
            // Log error but don't stop execution
            \Log::error('Email sending failed: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'User created successfully! Login credentials sent to email.');
    }

    public function update(Request $request)
    {
        $id = $request->id ?? null;

        $request->validate([
            'id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'required|numeric|digits:10|unique:users,phone,' . $id, // Mobile Required & Unique except current user
            'role_id' => 'required|exists:roles,id',
            'district_id' => 'required|exists:districts_master,id',
            'designation' => 'nullable|string|max:255', // Changed to nullable based on typical logic, set to required if needed
            // New Optional Fields
            'aadhaar_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'projects' => 'nullable|string|max:255',
            'ward_number' => 'nullable|string|max:50',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'password' => [
                'nullable',
                'confirmed',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
        ]);

        $user = User::where('id', $id)->first();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone; // Updating Mobile
        $user->role_id = $request->role_id;
        $user->district_id = $request->district_id;
        $user->designation = $request->designation;

        // Updating New Fields
        $user->aadhaar_number = $request->aadhaar_number;
        $user->address = $request->address;
        $user->projects = $request->projects;
        $user->ward_number = $request->ward_number;
        $user->gender = $request->gender;

        $roleName = Role::find($request->role_id)->name ?? null;
        if ($roleName) {
            $user->syncRoles([$roleName]);
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('user.list')->with('success', 'User updated successfully.');
    }

    public function show()
    {
        $page_title = 'User List';
        $users = User::with(['roles', 'district'])->get();

        return view('admin.user_management.user_list', compact('page_title', 'users'));
    }

    public function updateStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->status = $request->status;
        $user->save();
        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully.'
        ]);
    }

    public function delete($id)
    {
        $role = User::findOrFail($id);
        $role->delete();
        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}
