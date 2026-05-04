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
    public function registerOrRetrieveUser(string $identifier, string $otp, string $channel = 'phone'): array
    {
        $isNew = false;

        if ($channel === 'email') {
            $user = User::where('email', $identifier)->first();

            if (!$user) {
                $user = User::create([
                    'name' => 'User ' . $identifier,
                    'email' => $identifier,
                    'phone' => null,
                    'password' => Hash::make(Str::random(16)),
                    'role_id' => 3,
                    'is_verified' => 0,
                    'otp' => $otp
                ]);
                $isNew = true;
            } else {
                $user->update(['otp' => $otp]);
            }
        } else {
            $user = User::where('phone', $identifier)->first();

            if (!$user) {
                $user = User::create([
                    'name' => 'User ' . $identifier,
                    'email' => $identifier . '@mobile.temp',
                    'phone' => $identifier,
                    'password' => Hash::make(Str::random(16)),
                    'role_id' => 3,
                    'is_verified' => 0,
                    'otp' => $otp
                ]);
                $isNew = true;
            } else {
                $user->update(['otp' => $otp]);
            }
        }

        return ['user' => $user, 'is_new' => $isNew];
    }

    /**
     * Verify User OTP
     */
    public function verifyUserOtp(User $user, string $otp, string $channel = 'phone'): bool
    {
        if ($user->otp === $otp) {
            $user->otp = null;
            $user->save();
            return true;
        }

        return false;
    }
}
