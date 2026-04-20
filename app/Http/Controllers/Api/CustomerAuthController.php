<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerAuthController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Send OTP to Customer
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric|digits:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $phone = $request->phone;
            $otp = (string) rand(1000, 9999);

            // Send via Service
            $this->otpService->sendOtp($phone, $otp);

            // Create or update user
            $user = User::updateOrCreate(
                ['phone' => $phone],
                [
                    'role_id' => 3, // Customer
                    'otp' => $otp,
                    'status' => 'inactive',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully',
                'action' => 'redirect_to_verify',
                'otp' => config('app.debug') ? $otp : null, // Only for debug
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while sending OTP.',
            ], 500);
        }
    }

    /**
     * Verify OTP and Login
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric|digits:10',
            'otp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        if ($user->otp === $request->otp) {
            $user->status = 'active';
            $user->otp = null;
            $user->save();

            $token = $user->createToken('CustomerApp')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'token' => $token,
                'action' => (empty($user->name) || empty($user->email)) ? 'redirect_to_profile_update' : 'redirect_to_dashboard',
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid OTP'], 401);
    }

    /**
     * Update Profile
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address ?? $user->address,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile Updated',
            'action' => 'redirect_to_dashboard',
        ]);
    }
}
