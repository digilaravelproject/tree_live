<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class CustomerAuthController extends Controller
{
    // Send OTP
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric|digits:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Create User with Role 3 if not exists
        $user = User::firstOrCreate(
            ['phone' => $request->phone],
            [
                'role_id' => 3,
                'status' => 'inactive'
            ]
        );

        // SMS Gateway Code yahan aayega
        $otp = 1234; // Demo

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully',
            'action' => 'redirect_to_verify'
        ]);
    }

    // Verify OTP
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'otp' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        if ($request->otp == 1234) {
            $user = User::where('phone', $request->phone)->first();

            if (!$user) return response()->json(['status' => false, 'message' => 'User not found'], 404);

            $user->status = 'active';
            $user->save();

            $token = $user->createToken('CustomerApp')->plainTextToken;

            // Check if Profile (Name/Email) is incomplete
            $isProfileIncomplete = empty($user->name) || empty($user->email);

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'token' => $token,
                'action' => $isProfileIncomplete ? 'redirect_to_profile_update' : 'redirect_to_dashboard'
            ]);
        }

        return response()->json(['status' => false, 'message' => 'Invalid OTP'], 401);
    }

    // Update Profile
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address ?? $user->address,
        ]);

        return response()->json(['status' => true, 'message' => 'Profile Updated', 'action' => 'redirect_to_dashboard']);
    }
}
