<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class UserManagementController extends Controller
{
    /**
     * Get User Details
     */
    public function show($id)
    {
        $user = User::with(['district', 'roles'])->find($id);

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        // profile_image URL is now handled by User model toArray()

        return response()->json([
            'status' => true,
            'data' => $user
        ]);
    }

    /**
     * Update Profile / Upload Image
     */
    public function updateProfile(ProfileUpdateRequest $request)
    {
        try {
            /** @var \App\Models\User $user */
            $user = User::findOrFail($request->user_id);

            // Update allowed fields
            $user->fill($request->only(['name', 'address', 'email', 'gender', 'aadhaar_number']));

            // Handle Profile Image
            if ($request->hasFile('profile_image')) {
                if ($user->profile_image) {
                    // Original path for deletion - toArray doesn't affect raw attributes if accessed correctly
                    $rawPath = $user->getRawOriginal('profile_image');
                    if ($rawPath) {
                        Storage::disk('public')->delete($rawPath);
                    }
                }

                $filename = 'profile_' . Str::random(10) . '.' . $request->file('profile_image')->getClientOriginalExtension();
                $user->profile_image = $request->file('profile_image')->storeAs('profile_images', $filename, 'public');
            }

            $user->is_verified = 1;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully.',
                'data' => $user
            ]);
        } catch (Exception $e) {
            Log::error("Profile Update Error: " . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Update failed.'], 500);
        }
    }
}
