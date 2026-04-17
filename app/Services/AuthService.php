<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Exception;

class AuthService
{
    /**
     * Register a new user or return existing one.
     */
    public function registerOrRetrieveUser(string $phone, string $otp): array
    {
        $user = User::where('phone', $phone)->first();
        $isNew = false;

        if (!$user) {
            $user = User::create([
                'name' => 'User ' . $phone,
                'email' => $phone . '@mobile.temp',
                'phone' => $phone,
                'password' => Hash::make(Str::random(16)),
                'role_id' => 3,
                'is_verified' => 0,
                'otp' => $otp
            ]);
            $isNew = true;
        } else {
            $user->update(['otp' => $otp]);
        }

        return ['user' => $user, 'is_new' => $isNew];
    }

    /**
     * Verify User OTP
     */
    public function verifyUserOtp(User $user, string $otp): bool
    {
        if ($user->otp === $otp) {
            $user->otp = null;
            $user->save();
            return true;
        }

        return false;
    }
}
