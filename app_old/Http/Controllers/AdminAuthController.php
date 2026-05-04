<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\ResetPasswordMail;

class AdminAuthController extends Controller
{


    // Show Forgot Password View
    public function showForgotPassword()
    {
        return view('auth.forgot_password');
    }

    // Step 1: Send OTP
    public function sendResetOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['Email not found']);
        }

        $otp = rand(100000, 999999);
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $otp, 'created_at' => Carbon::now()]
        );

        Mail::to($request->email)->send(new ResetPasswordMail($otp));

        return redirect()->route('admin.verify.otp.page', ['email' => $request->email]);
    }

    // Show Verify OTP Page
    public function showVerifyOtp()
    {
        return view('auth.verify_otp');
    }

    // Step 2: Verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        $record = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->otp)
            ->first();

        if (!$record) {
            return back()->withErrors(['Invalid OTP']);
        }

        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            return back()->withErrors(['OTP expired']);
        }

        session([
            'reset_email' => $request->email,
            'reset_otp' => $request->otp
        ]);
        return redirect()->route('admin.reset.password.page');
    }

    // Show Reset Password View
    public function showResetPassword(Request $request)
    {
        $email = $request->query('email') ?? session('reset_email');
        $otp   = $request->query('otp') ?? session('reset_otp');

        if (!$email || !$otp) {
            return redirect()->route('admin.forgot.password')->withErrors(['Session expired, please restart the reset process.']);
        }

        return view('auth.reset_password', compact('email', 'otp'));
    }

    // Step 3: Reset Password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
            'password' => 'required|min:6'
        ]);

        $record = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->otp)
            ->first();

        if (!$record) {
            return back()->withErrors(['Invalid OTP']);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Password reset successful. Please login.');
    }
}
