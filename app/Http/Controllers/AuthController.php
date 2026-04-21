<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {
            return back()->withErrors(['email' => 'User not found']);
        }

        if ($user->status != 1) {
            return back()->withErrors(['email' => 'You are blocked. Please contact the admin.']);
        }

        // Check password first
        if (! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['email' => 'Invalid email or password']);
        }

        // Check if Global OTP is enabled
        $otpEnabled = Setting::get('global_otp_login_enabled', '0') === '1';

        // Skip OTP for Admin and Super Admin (role_id 1 and 2)
        $isAdmin = in_array($user->role_id, [1, 2]);

        if ($otpEnabled && ! $isAdmin) {
            // Generate OTP
            $otp = rand(100000, 999999);

            // For now, if phone is missing, we might need a fallback or error
            if (empty($user->phone)) {

                // Let's proceed with normal login if phone is missing to avoid lockout, but log it.
                Log::warning("OTP enabled but user {$user->email} has no phone number.");
            } else {
                // Store in session
                Session::put('otp_login_user_id', $user->id);
                Session::put('otp_code', $otp);
                Session::put('otp_expires_at', now()->addMinutes(10));

                // Send OTP
                $this->otpService->sendOtp($user->phone, $otp);

                return redirect()->route('login.otp.verify');
            }
        }

        // Standard Login
        if (Auth::attempt($credentials)) {
            return redirect()->route('home');
        }

        return back()->withErrors(['email' => 'Invalid email or password']);
    }

    /**
     * Show OTP verification form.
     */
    public function showOtpVerify()
    {
        if (! Session::has('otp_login_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.otp-verify');
    }

    /**
     * Verify OTP and log user in.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric',
        ]);

        if (! Session::has('otp_login_user_id')) {
            return redirect()->route('login');
        }

        $sessionOtp = Session::get('otp_code');
        $expiresAt = Session::get('otp_expires_at');

        if (now()->gt($expiresAt)) {
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
        }

        if ($request->otp == $sessionOtp) {
            $user = User::find(Session::get('otp_login_user_id'));

            if ($user) {
                Auth::login($user);

                // Clear session
                Session::forget(['otp_login_user_id', 'otp_code', 'otp_expires_at']);

                return redirect()->route('home');
            }
        }

        return back()->withErrors(['otp' => 'Invalid OTP code.']);
    }

    /**
     * Resend OTP.
     */
    public function resendOtp()
    {
        if (! Session::has('otp_login_user_id')) {
            return response()->json(['status' => 'error', 'message' => 'Session expired.'], 403);
        }

        $user = User::find(Session::get('otp_login_user_id'));
        if (! $user || empty($user->phone)) {
            return response()->json(['status' => 'error', 'message' => 'User or phone not found.'], 404);
        }

        $otp = rand(100000, 999999);
        Session::put('otp_code', $otp);
        Session::put('otp_expires_at', now()->addMinutes(10));

        $this->otpService->sendOtp($user->phone, $otp);

        return response()->json(['status' => 'success', 'message' => 'OTP resent successfully.']);
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('login');
    }
}
