<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use App\Models\Role;
use App\Models\UserRating;
use App\Models\State;
use App\Models\Wallet;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    // =============================================================
    // 1. ADMIN / STAFF LOGIN (Formerly 'login', now 'extra_login')
    // =============================================================
    public function extra_login(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Credentials Check
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();

        // --- CONDITION 1: Role 1 Check (Invalid Credential Message) ---
        if ($user->role_id == 1) {
            return response()->json([
                'success' => false,
                'message' => 'This is not valid credential',
            ], 401);
        }

        // --- CONDITION 2: Role 3 Check (Customer - Return is_verified) ---
        if ($user->role_id == 3) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Customers must login using Mobile OTP.',
                'is_verified' => $user->is_verified,
            ], 403);
        }

        // --- CONDITION 3: Only Role 2 (Staff) Allowed Beyond This Point ---
        if ($user->role_id != 2) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized role.',
            ], 403);
        }

        // --- CONDITION 4: Verification Check for Role 2 ---
        // if ($user->is_verified == 0) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Your account is not verified yet.',
        //     ], 403);
        // }

        // Token Generation
        $token = $user->createToken('auth_token')->plainTextToken;

        if ($user->profile_image) {
            $user->profile_image = asset('storage/' . $user->profile_image);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 200);
    }

// public function send_otp(Request $request)
//     {
//         try {
//             // 1. Validation
//             $validator = Validator::make($request->all(), [
//                 'phone' => 'required|digits:10',
//             ]);

//             if ($validator->fails()) {
//                 return response()->json([
//                     'success' => false,
//                     'message' => 'Validation Error',
//                     'errors' => $validator->errors()
//                 ], 422);
//             }

//             $phone = $request->phone;

//             // 2Factor.in OTP Send
//             $apiKey = env('TWOFACTOR_API_KEY'); // Add in .env: TWOFACTOR_API_KEY=your_key_here
//             $otpResponse = file_get_contents("https://2factor.in/API/V1/{$apiKey}/SMS/{$phone}/AUTOGEN");
//             $otpData = json_decode($otpResponse, true);

//             if (!$otpData || $otpData['Status'] !== 'Success') {
//                 return response()->json([
//                     'success' => false,
//                     'message' => 'Failed to send OTP. Please try again.',
//                 ], 500);
//             }

//             $sessionId = $otpData['Details']; // Session ID to verify OTP later

//             // 2. Check if User Exists
//             $user = User::where('phone', $phone)->first();

//             // =================================================
//             // CASE 1: EXISTING USER (OLD USER) -> LOGIN FLOW
//             // =================================================
//             if ($user) {
//                 // Check for Staff Verification (Role 2)
//                 if ($user->role_id == 2 && $user->is_verified == 0) {
//                     return response()->json([
//                         'success' => false,
//                         'is_verified' => 0,
//                         'message' => 'Your account is not verified yet. Please contact Admin.',
//                     ], 403);
//                 }

//                 // Update OTP Session ID
//                 $user->otp = $sessionId;
//                 $user->save();

//                 return response()->json([
//                     'success' => true,
//                     'user_type' => 'existing', // 👈 KEY FLAG: OLD USER
//                     'message' => 'User exists. OTP sent successfully.',
//                 ], 200);
//             }

//             // =================================================
//             // CASE 2: NEW USER -> REGISTER + WALLET FLOW
//             // =================================================
//             else {
//                 // A. Create User
//                 $newUser = User::create([
//                     'name' => 'User ' . $phone,
//                     'email' => $phone . '@mobile.temp',
//                     'phone' => $phone,
//                     'password' => Hash::make(Str::random(10)),
//                     'role_id' => 3, // Default Customer
//                     'is_verified' => 0, // Keeping 0 for new user
//                     'otp' => $sessionId
//                 ]);

//                 // B. Create Free Wallet (Project=1, Tree=100)
//                 // if ($newUser) {
//                 //     Wallet::create([
//                 //         'user_id' => $newUser->id,
//                 //         'project_count' => 1,            // Free Project
//                 //         'tree_count' => 100,             // Free Trees
//                 //         'razorpay_signature' => 'free',  // Signature Free
//                 //         'amount' => 0,                   // Amount 0
//                 //         'status' => 'success',           // Status Success
//                 //     ]);
//                 // }

//                 return response()->json([
//                     'success' => true,
//                     'user_type' => 'new', // 👈 KEY FLAG: NEW USER
//                     'message' => 'New User registered OTP sent.',
//                 ], 200); // 200 OK or 201 Created
//             }
//         } catch (\Exception $e) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Something went wrong',
//                 'error' => $e->getMessage()
//             ], 500);
//         }
//     }

//     // =============================================================
//     // VERIFY OTP (Checks is_new_user logic correctly)
//     // =============================================================
//     public function verifyOtp(Request $request)
//     {
//         try {
//             $validator = Validator::make($request->all(), [
//                 'phone' => 'required|digits:10',
//                 'otp' => 'required'
//             ]);

//             if ($validator->fails()) {
//                 return response()->json([
//                     'success' => false,
//                     'message' => 'Validation Error',
//                     'errors' => $validator->errors()
//                 ], 422);
//             }

//             $user = User::where('phone', $request->phone)->first();

//             if (!$user) {
//                 return response()->json([
//                     'success' => false,
//                     'message' => 'User not found',
//                     'is_verified' => 0
//                 ], 404);
//             }

//             // 2Factor.in OTP Verify using stored Session ID
//             $apiKey = env('TWOFACTOR_API_KEY');
//             $sessionId = $user->otp; // Session ID stored during send_otp
//             $otpEntered = $request->otp;

//             $verifyResponse = file_get_contents("https://2factor.in/API/V1/{$apiKey}/SMS/VERIFY/{$sessionId}/{$otpEntered}");
//             $verifyData = json_decode($verifyResponse, true);

//             if ($verifyData && $verifyData['Status'] === 'Success' && $verifyData['Details'] === 'OTP Matched') {

//                 // Logic: If user was NOT verified (0), this is their first time verifying -> New User
//                 // If user was already verified (1), they are just logging in -> Old User
//                 $isNewUser = ($user->is_verified == 0) ? true : false;

//                 // IMPORTANT: We do NOT set is_verified = 1 here.
//                 // It will be set to 1 only when they update their profile.

//                 $user->otp = null; // Just clear OTP
//                 $user->save();

//                 // Generate Token
//                 $token = $user->createToken('auth_token')->plainTextToken;

//                 if ($user->profile_image) {
//                     $user->profile_image = asset('storage/' . $user->profile_image);
//                 }

//                 return response()->json([
//                     'success' => true,
//                     'message' => 'OTP Verified Successfully',
//                     'access_token' => $token,
//                     'token_type' => 'Bearer',
//                     'is_new_user' => $isNewUser, // Returns true if verified was 0, false if was 1
//                     'is_verified' => $user->is_verified, // Returns current status (0 for new, 1 for old)
//                     'user' => $user
//                 ], 200);
//             } else {
//                 return response()->json([
//                     'success' => false,
//                     'message' => 'Invalid OTP',
//                     'is_verified' => $user->is_verified
//                 ], 401);
//             }
//         } catch (\Exception $e) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Something went wrong',
//                 'error' => $e->getMessage()
//             ], 500);
//         }
//     }


    public function send_otp(Request $request)
    {
        try {
            // 1. Validation
            $validator = Validator::make($request->all(), [
                'phone' => 'required|digits:10',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $phone = $request->phone;
            $fixedOtp = '1234'; // Fixed OTP for testing

            // 2. Check if User Exists
            $user = User::where('phone', $phone)->first();

            // =================================================
            // CASE 1: EXISTING USER (OLD USER) -> LOGIN FLOW
            // =================================================
            if ($user) {
                // Check for Staff Verification (Role 2)
                if ($user->role_id == 2 && $user->is_verified == 0) {
                    return response()->json([
                        'success' => false,
                        'is_verified' => 0,
                        'message' => 'Your account is not verified yet. Please contact Admin.',
                    ], 403);
                }

                // Update OTP
                $user->otp = $fixedOtp;
                $user->save();

                return response()->json([
                    'success' => true,
                    'user_type' => 'existing', // 👈 KEY FLAG: OLD USER
                    'message' => 'User exists. OTP sent successfully.',
                    'otp' => $fixedOtp,
                ], 200);
            }

            // =================================================
            // CASE 2: NEW USER -> REGISTER + WALLET FLOW
            // =================================================
            else {
                // A. Create User
                $newUser = User::create([
                    'name' => 'User ' . $phone,
                    'email' => $phone . '@mobile.temp',
                    'phone' => $phone,
                    'password' => Hash::make(Str::random(10)),
                    'role_id' => 3, // Default Customer
                    'is_verified' => 0, // Keeping 0 for new user
                    'otp' => $fixedOtp
                ]);

                // B. Create Free Wallet (Project=1, Tree=100)
                // if ($newUser) {
                //     Wallet::create([
                //         'user_id' => $newUser->id,
                //         'project_count' => 1,            // Free Project
                //         'tree_count' => 100,             // Free Trees
                //         'razorpay_signature' => 'free',  // Signature Free
                //         'amount' => 0,                   // Amount 0
                //         'status' => 'success',           // Status Success
                //     ]);
                // }

                return response()->json([
                    'success' => true,
                    'user_type' => 'new', // 👈 KEY FLAG: NEW USER
                    'message' => 'New User registered OTP sent.',
                    'otp' => $fixedOtp,
                ], 200); // 200 OK or 201 Created
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =============================================================
    // VERIFY OTP (Checks is_new_user logic correctly)
    // =============================================================
    public function verifyOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|digits:10',
                'otp' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::where('phone', $request->phone)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'is_verified' => 0
                ], 404);
            }

            // OTP Match Logic
            if ($user->otp == $request->otp) {

                // Logic: If user was NOT verified (0), this is their first time verifying -> New User
                // If user was already verified (1), they are just logging in -> Old User
                $isNewUser = ($user->is_verified == 0) ? true : false;

                // IMPORTANT: We do NOT set is_verified = 1 here.
                // It will be set to 1 only when they update their profile.

                $user->otp = null; // Just clear OTP
                $user->save();

                // Generate Token
                $token = $user->createToken('auth_token')->plainTextToken;

                if ($user->profile_image) {
                    $user->profile_image = asset('storage/' . $user->profile_image);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'OTP Verified Successfully',
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'is_new_user' => $isNewUser, // Returns true if verified was 0, false if was 1
                    'is_verified' => $user->is_verified, // Returns current status (0 for new, 1 for old)
                    'user' => $user
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid OTP',
                    'is_verified' => $user->is_verified
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =============================================================
    // OTHER METHODS
    // =============================================================

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|confirmed|min:6',
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect.'], 422);
        }

        $request->user()->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['success' => true, 'message' => 'Password updated successfully']);
    }

    public function getUserDetails($id)
    {
        $user = User::with(['district', 'roles'])->find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
        if ($user->profile_image) {
            $user->profile_image = asset('storage/' . $user->profile_image);
        }
        return response()->json($user);
    }

    public function uploadProfileImage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'name' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $request->user_id,
                'gender' => 'nullable|string|max:50',
                'aadhaar_number' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::findOrFail($request->user_id);

            // Update text fields if they are present in request
            if ($request->has('name')) {
                $user->name = $request->name;
            }
            if ($request->has('address')) {
                $user->address = $request->address;
            }
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            if ($request->has('gender')) {
                $user->gender = $request->gender;
            }
            if ($request->has('aadhaar_number')) {
                $user->aadhaar_number = $request->aadhaar_number;
            }

            // Handle Profile Image Upload
            if ($request->hasFile('profile_image')) {
                // Delete old image if exists
                if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                    Storage::disk('public')->delete($user->profile_image);
                }

                $originalName = $request->file('profile_image')->getClientOriginalName();
                $random = Str::random(6);
                $filename = 'user_profile_' . $random . '_' . $originalName;

                $path = $request->file('profile_image')->storeAs('profile_images', $filename, 'public');
                $user->profile_image = $path;
            }

            // --- KEY CHANGE: Verify user only after profile update ---
            $user->is_verified = 1;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'data' => $user,
                'image_url' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function project_list()
    {
        try {
            $user = auth()->user();
            $projects = Project::where('extra_user', $user->id)
                ->with(['state', 'fieldOfficer'])
                ->withCount('trees')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Project list fetched successfully.',
                'count'   => $projects->count(),
                'data'    => $projects
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    // public function project_assign_officer(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'role_id' => 'required',
    //             'user_id' => 'required',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Validation Error',
    //                 'errors' => $validator->errors()
    //             ], 422);
    //         }

    //         $role_id = $request->role_id;
    //         $user_id = $request->user_id;

    //         $projects = collect();

    //         // --- CONDITION 1: Role 3 (Customer/Extra User) ---
    //         if ($role_id == 3) {
    //             $projects = Project::with(['state', 'fieldOfficer'])
    //                 ->where('extra_user', $user_id)
    //                 ->get();
    //         }
    //         // --- CONDITION 2: Role 2 (Staff/Field Officer - Existing Logic) ---
    //         elseif ($role_id == 2) {
    //             $projects = Project::with(['state', 'fieldOfficer'])
    //                 ->whereRaw("JSON_CONTAINS(field_officer_id, '\"$user_id\"')")
    //                 ->get();
    //         }

    //         // Check if projects found
    //         if ($projects->isEmpty()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Not assigned any project.',
    //                 'count'   => 0,
    //                 'data'    => []
    //             ], 200);
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Project list fetched successfully.',
    //             'count'   => $projects->count(),
    //             'data'    => $projects
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Something went wrong.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    
    // public function project_assign_officer(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'role_id' => 'required',
    //             'user_id' => 'required',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Validation Error',
    //                 'errors' => $validator->errors()
    //             ], 422);
    //         }

    //         $role_id = $request->role_id;
    //         $user_id = $request->user_id;

    //         $projects = collect();

    //         // --- CONDITION 1: Role 3 (Customer/Extra User) ---
    //         if ($role_id == 3) {
    //             $projects = Project::with(['state', 'fieldOfficer'])
    //                 ->withCount('trees') // 👈 Added tree count
    //                 ->where('extra_user', $user_id)
    //                 ->get();
    //         }
    //         // --- CONDITION 2: Role 2 (Staff/Field Officer - Existing Logic) ---
    //         elseif ($role_id == 2) {
    //             $projects = Project::with(['state', 'fieldOfficer'])
    //                 ->withCount('trees') // 👈 Added tree count
    //                 ->whereRaw("JSON_CONTAINS(field_officer_id, '\"$user_id\"')")
    //                 ->get();
    //         }

    //         // Check if projects found
    //         if ($projects->isEmpty()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Not assigned any project.',
    //                 'count'   => 0,
    //                 'data'    => []
    //             ], 200);
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Project list fetched successfully.',
    //             'count'   => $projects->count(),
    //             'data'    => $projects
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Something went wrong.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    
    public function project_assign_officer(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'role_id' => 'required',
                'user_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $role_id = $request->role_id;
            $user_id = $request->user_id;

            $projects = collect();

            // --- CONDITION 1: Role 3 (Customer/Extra User) ---
            if ($role_id == 3) {
                $projects = \App\Models\Project::with(['state', 'fieldOfficer'])
                    ->withCount('trees') // 👈 Added tree count
                    ->where('extra_user', $user_id)
                    ->get();
            }
            // --- CONDITION 2: Role 2 (Staff/Field Officer - Existing Logic) ---
            elseif ($role_id == 2) {
                $projects = \App\Models\Project::with(['state', 'fieldOfficer'])
                    ->withCount('trees') // 👈 Added tree count
                    ->whereRaw("JSON_CONTAINS(field_officer_id, '\"$user_id\"')")
                    ->get();
            }

            // Fetch Active Tree Price
            $activeTreePrice = \App\Models\TreePrice::where('is_active', 1)->value('price') ?? 0;

            // Check if projects found
            if ($projects->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not assigned any project.',
                    'active_tree_price' => $activeTreePrice, // Added here even if empty
                    'count'   => 0,
                    'data'    => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Project list fetched successfully.',
                'active_tree_price' => $activeTreePrice, // 👈 Added Active Price Here
                'count'   => $projects->count(),
                'data'    => $projects
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Admin/Staff Registration (Internal Use)
    public function user_register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'phone' => 'required|string|max:15|unique:users,phone',
                'password' => 'required|confirmed|min:8',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $role = Role::find(2);
            if (!$role) {
                return response()->json(['success' => false, 'message' => 'Role not found!'], 404);
            }

            $user = new User();
            $user->name = $validated['name'];
            $user->email = filter_var($validated['email'], FILTER_SANITIZE_EMAIL);
            $user->phone = $validated['phone'];
            $user->role_id = 2;
            $user->district_id = $validated['district_id'] ?? null;
            $user->designation = $validated['designation'] ?? null;
            $user->password = Hash::make($validated['password']);
            $user->save();

            if (method_exists($user, 'assignRole')) {
                $user->assignRole($role->name);
            }

            return response()->json([
                'success' => true,
                'message' => 'User created successfully!',
                'data' => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function user_rating(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $existingRating = UserRating::where('user_id', $validated['user_id'])->first();

        if ($existingRating) {
            $existingRating->update([
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]);
            $rating = $existingRating;
            $message = 'Rating updated successfully';
        } else {
            $rating = UserRating::create($validated);
            $message = 'Rating submitted successfully';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $rating,
        ], 200);
    }

    public function userRatings($user_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $ratings = UserRating::where('user_id', $user_id)->latest()->get();

        return response()->json([
            'success' => true,
            'user' => $user->only(['id', 'name', 'email']),
            'ratings' => $ratings,
            'average_rating' => round($ratings->avg('rating'), 1),
        ]);
    }

    public function faq_list()
    {
        $faqs = Faq::latest()->get();
        return response()->json([
            'status' => true,
            'faqs' => $faqs
        ]);
    }

    public function state_list()
    {
        try {
            $states = State::select('id', 'state_name')
                ->orderBy('state_name', 'ASC')
                ->get();

            if ($states->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No states found',
                    'data'    => []
                ], 404);
            }

            return response()->json([
                'status'  => true,
                'message' => 'State list fetched successfully',
                'data'    => $states
            ], 200);
        } catch (\Exception $e) {
            Log::error('State List API Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong while fetching states'
            ], 500);
        }
    }
  
    
    // public function get_tree_requirements(Request $request)
    // {
    //     try {
    //         // 1. Validation
    //         $validator = Validator::make($request->all(), [
    //             'role_id'    => 'required|integer',
    //             'project_id' => 'required|integer|exists:projects,id',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Validation Error',
    //                 'errors'  => $validator->errors()
    //             ], 422);
    //         }

    //         $role_id = $request->role_id;
    //         $project_id = $request->project_id;

    //         // Define Default List of Fields (Set all to false initially)
    //         // Keys match your database/form field names
    //         $requirements = [
    //             'all_captured_images' => false,
    //             'ward_plot_no'    => false,
    //             'tree_no'         => false,
    //             'tree_name'       => false,
    //             'scientific_name' => false,
    //             'family'          => false,
    //             'girth'           => false,
    //             'height'          => false,
    //             'canopy'          => false,
    //             'age'             => false,
    //             'condition'       => false,
    //             'address'         => false,
    //             'landmark'        => false,
    //             'ownership'       => false,
    //             'concern_person'  => false,
    //             'remark'          => false,
    //             'tree_images'     => false,
    //         ];

    //         // =========================================================
    //         // CASE 1: ROLE 2 (Company User/Staff) - Fetch from DB
    //         // =========================================================
    //         if ($role_id == 2) {
    //             $settings = \App\Models\ProjectSetting::where('project_id', $project_id)->get();

    //             // Create detailed requirements object with field metadata
    //             $detailedRequirements = [];

    //             foreach ($settings as $setting) {
    //                 // If the field exists in our list, update its status based on DB
    //                 // Assuming DB column 'is_required' is 1 for true
    //                 if (array_key_exists($setting->field_key, $requirements)) {
    //                     $requirements[$setting->field_key] = ($setting->is_required == 1);

    //                     // For fields that are required and have min/max values, add them
    //                     $fieldsWithMinMax = ['all_captured_images', 'girth', 'height', 'canopy', 'age'];

    //                     if (in_array($setting->field_key, $fieldsWithMinMax) && $setting->is_required == 1) {
    //                         $detailedRequirements[$setting->field_key] = [
    //                             'is_required' => true,
    //                             'min_value' => $setting->min_value ?? null,
    //                             'max_value' => $setting->max_value ?? null,
    //                         ];
    //                     } else {
    //                         // For non-min/max fields or optional fields
    //                         $detailedRequirements[$setting->field_key] = [
    //                             'is_required' => ($setting->is_required == 1),
    //                         ];
    //                     }
    //                 }
    //             }

    //             // If we have detailed requirements, use them; otherwise use simple format
    //             if (!empty($detailedRequirements)) {
    //                 $requirements = $detailedRequirements;
    //             }
                
    //             // Convert to detailed format if not already
    //             $detailedRequirements = [];
    //             foreach ($requirements as $field => $isRequired) {
    //                 $detailedRequirements[$field] = [
    //                     'is_required' => $isRequired,
    //                 ];
    //             }
    //             $requirements = $detailedRequirements;
                
    //             // Set ward_no to 10 for role_id 2
    //             $ward_no = 10;
    //         }

    //         // =========================================================
    //         // CASE 2: ROLE 3 (Customer) - Custom Manual Settings
    //         // =========================================================
    //         elseif ($role_id == 3) {
    //             // 👇 YAHAN AAP TRUE/FALSE SET KAR SAKTE HAIN CUSTOMERS KE LIYE
    //             $requirements = [
    //                 'ward_plot_no'    => false, // Optional
    //                 'tree_no'         => false, // Optional
    //                 'tree_name'       => true,  // Required
    //                 'scientific_name' => false, // Optional
    //                 'family'          => false, // Optional
    //                 'girth'           => true,  // Required
    //                 'height'          => true,  // Required
    //                 'canopy'          => false, // Optional
    //                 'age'             => false, // Optional
    //                 'condition'       => false, // Optional
    //                 'address'         => true,  // Required
    //                 'landmark'        => false, // Optional
    //                 'ownership'       => false, // Optional
    //                 'concern_person'  => false, // Optional
    //                 'remark'          => false, // Optional
    //                 'tree_images'     => true,  // Required
    //             ];
                
    //             // Convert to detailed format
    //             $detailedRequirements = [];
    //             foreach ($requirements as $field => $isRequired) {
    //                 $detailedRequirements[$field] = [
    //                     'is_required' => $isRequired,
    //                 ];
    //             }
    //             $requirements = $detailedRequirements;
                
    //             // Set ward_no to empty string for role_id 3
    //             $ward_no = "";
    //         }

    //         return response()->json([
    //             'success'      => true,
    //             'message'      => 'Field requirements fetched successfully.',
    //             'role_id'      => $role_id,
    //             'project_id'   => $project_id,
    //             'ward_no'      => $ward_no,
    //             'requirements' => $requirements
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Something went wrong',
    //             'error'   => $e->getMessage()
    //         ], 500);
    //     }
    // }
    
    public function get_tree_requirements(Request $request)
    {
        try {
            // 1. Validation
            $validator = Validator::make($request->all(), [
                'role_id'    => 'required|integer',
                'project_id' => 'required|integer|exists:projects,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors'  => $validator->errors()
                ], 422);
            }

            $role_id = $request->role_id;
            $project_id = $request->project_id;

            // Define Default List of Fields (Set all to false initially)
            // Keys match your database/form field names
            $requirements = [
                'all_captured_images' => false,
                'ward_plot_no'    => false,
                'tree_no'         => false,
                'tree_name'       => false,
                'scientific_name' => false,
                'family'          => false,
                'girth'           => false,
                'height'          => false,
                'canopy'          => false,
                'age'             => false,
                'condition'       => false,
                'address'         => false,
                'landmark'        => false,
                'ownership'       => false,
                'concern_person'  => false,
                'remark'          => false,
                'tree_images'     => false,
            ];

            // =========================================================
            // CASE 1: ROLE 2 (Company User/Staff) - Fetch from DB
            // =========================================================
            if ($role_id == 2) {
                $settings = \App\Models\ProjectSetting::where('project_id', $project_id)->get();

                // Create detailed requirements object with field metadata
                $detailedRequirements = [];

                foreach ($settings as $setting) {
                    // If the field exists in our list, update its status based on DB
                    // Assuming DB column 'is_required' is 1 for true
                    if (array_key_exists($setting->field_key, $requirements)) {
                        $requirements[$setting->field_key] = ($setting->is_required == 1);

                        // For fields that are required and have min/max values, add them
                        $fieldsWithMinMax = ['all_captured_images', 'girth', 'height', 'canopy', 'age'];

                        if (in_array($setting->field_key, $fieldsWithMinMax) && $setting->is_required == 1) {
                            $detailedRequirements[$setting->field_key] = [
                                'is_required' => true,
                                'min_value' => $setting->min_value ?? null,
                                'max_value' => $setting->max_value ?? null,
                            ];
                        } else {
                            // For non-min/max fields or optional fields
                            $detailedRequirements[$setting->field_key] = [
                                'is_required' => ($setting->is_required == 1),
                            ];
                        }
                    }
                }

                // If we have detailed requirements, use them; otherwise use simple format
                if (!empty($detailedRequirements)) {
                    $requirements = $detailedRequirements;
                }
                
                // Convert to detailed format if not already
                $detailedRequirements = [];
                foreach ($requirements as $field => $data) {
                    // Agar data pehle se array hai (min/max wala), to wahi rakho, warna array banao
                    $detailedRequirements[$field] = is_array($data) ? $data : ['is_required' => $data];
                }
                $requirements = $detailedRequirements;
                
                $project = \App\Models\Project::find($project_id);
                $ward_no = $project ? $project->ward_no : "";
            }

            // =========================================================
            // CASE 2: ROLE 3 (Customer) - Custom Manual Settings
            // =========================================================
            elseif ($role_id == 3) {
                // 👇 YAHAN AAP TRUE/FALSE SET KAR SAKTE HAIN CUSTOMERS KE LIYE
                $requirements = [
                    'ward_plot_no'    => false, // Optional
                    'tree_no'         => false, // Optional
                    'tree_name'       => false,  // Required
                    'scientific_name' => false, // Optional
                    'family'          => false, // Optional
                    'girth'           => true,  // Required
                    'height'          => false,  // Required
                    'canopy'          => false, // Optional
                    'age'             => false, // Optional
                    'condition'       => false, // Optional
                    'address'         => false,  // Required
                    'landmark'        => false, // Optional
                    'ownership'       => false, // Optional
                    'concern_person'  => false, // Optional
                    'remark'          => false, // Optional
                    'tree_images'     => false,  // Required
                ];
                
                // Convert to detailed format
                $detailedRequirements = [];
                foreach ($requirements as $field => $isRequired) {
                    $detailedRequirements[$field] = [
                        'is_required' => $isRequired,
                    ];
                }
                $requirements = $detailedRequirements;
                
                // Set ward_no to empty string for role_id 3
                $ward_no = "";
            }

            return response()->json([
                'success'      => true,
                'message'      => 'Field requirements fetched successfully.',
                'role_id'      => $role_id,
                'project_id'   => $project_id,
                'ward_no'      => $ward_no,
                'requirements' => $requirements
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // public function get_tree_requirements(Request $request)
    // {
    //     try {
    //         // 1. Validation
    //         $validator = Validator::make($request->all(), [
    //             'role_id'    => 'required|integer',
    //             'project_id' => 'required|integer|exists:projects,id',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Validation Error',
    //                 'errors'  => $validator->errors()
    //             ], 422);
    //         }

    //         $role_id = $request->role_id;
    //         $project_id = $request->project_id;

    //         // Define Default List of Fields (Set all to false initially)
    //         // Keys match your database/form field names
    //         $requirements = [
    //             'all_captured_images' => false,
    //             'ward_plot_no'    => false,
    //             'tree_no'         => false,
    //             'tree_name'       => false,
    //             'scientific_name' => false,
    //             'family'          => false,
    //             'girth'           => false,
    //             'height'          => false,
    //             'canopy'          => false,
    //             'age'             => false,
    //             'condition'       => false,
    //             'address'         => false,
    //             'landmark'        => false,
    //             'ownership'       => false,
    //             'concern_person'  => false,
    //             'remark'          => false,
    //             'tree_images'     => false,
    //         ];

    //         // =========================================================
    //         // CASE 1: ROLE 2 (Company User/Staff) - Fetch from DB
    //         // =========================================================
    //         if ($role_id == 2) {
    //             $settings = \App\Models\ProjectSetting::where('project_id', $project_id)->get();

    //             // Create detailed requirements object with field metadata
    //             $detailedRequirements = [];

    //             foreach ($settings as $setting) {
    //                 // If the field exists in our list, update its status based on DB
    //                 // Assuming DB column 'is_required' is 1 for true
    //                 if (array_key_exists($setting->field_key, $requirements)) {
    //                     $requirements[$setting->field_key] = ($setting->is_required == 1);

    //                     // For fields that are required and have min/max values, add them
    //                     $fieldsWithMinMax = ['all_captured_images', 'girth', 'height', 'canopy', 'age'];

    //                     if (in_array($setting->field_key, $fieldsWithMinMax) && $setting->is_required == 1) {
    //                         $detailedRequirements[$setting->field_key] = [
    //                             'is_required' => true,
    //                             'min_value' => $setting->min_value ?? null,
    //                             'max_value' => $setting->max_value ?? null,
    //                         ];
    //                     } else {
    //                         // For non-min/max fields or optional fields
    //                         $detailedRequirements[$setting->field_key] = [
    //                             'is_required' => ($setting->is_required == 1),
    //                         ];
    //                     }
    //                 }
    //             }

    //             // If we have detailed requirements, use them; otherwise use simple format
    //             if (!empty($detailedRequirements)) {
    //                 $requirements = $detailedRequirements;
    //             }
    //         }

    //         // =========================================================
    //         // CASE 2: ROLE 3 (Customer) - Custom Manual Settings
    //         // =========================================================
    //         elseif ($role_id == 3) {
    //             // 👇 YAHAN AAP TRUE/FALSE SET KAR SAKTE HAIN CUSTOMERS KE LIYE
    //             $requirements = [
    //                 'ward_plot_no'    => false, // Optional
    //                 'tree_no'         => false, // Optional
    //                 'tree_name'       => true,  // Required
    //                 'scientific_name' => false, // Optional
    //                 'family'          => false, // Optional
    //                 'girth'           => true,  // Required
    //                 'height'          => true,  // Required
    //                 'canopy'          => false, // Optional
    //                 'age'             => false, // Optional
    //                 'condition'       => false, // Optional
    //                 'address'         => true,  // Required
    //                 'landmark'        => false, // Optional
    //                 'ownership'       => false, // Optional
    //                 'concern_person'  => false, // Optional
    //                 'remark'          => false, // Optional
    //                 'tree_images'     => true,  // Required
    //             ];
    //         }

    //         return response()->json([
    //             'success'      => true,
    //             'message'      => 'Field requirements fetched successfully.',
    //             'role_id'      => $role_id,
    //             'project_id'   => $project_id,
    //             'requirements' => $requirements
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Something went wrong',
    //             'error'   => $e->getMessage()
    //         ], 500);
    //     }
    // }
}
