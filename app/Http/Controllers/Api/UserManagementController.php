<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProfileUpdateRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class UserManagementController extends Controller
{
    use ApiResponse;

    /**
     * Get User Details
     */
    public function show($id)
    {
        $user = User::with(['district', 'roles'])->find($id);

        if (!$user) {
            return $this->error('User not found', 404);
        }

        if ($user->profile_image) {
            $user->profile_image = asset('storage/' . $user->profile_image);
        }

        return $this->success($user, 'User details fetched successfully.');
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
                    Storage::disk('public')->delete($user->profile_image);
                }

                $filename = 'profile_' . Str::random(10) . '.' . $request->file('profile_image')->getClientOriginalExtension();
                $user->profile_image = $request->file('profile_image')->storeAs('profile_images', $filename, 'public');
            }

            $user->is_verified = 1;
            $user->save();

            $user->image_url = $user->profile_image ? asset('storage/' . $user->profile_image) : null;

            return $this->success($user, 'Profile updated successfully.');
        } catch (Exception $e) {
            Log::error("Profile Update Error: " . $e->getMessage());
            return $this->error('Update failed.', 500);
        }
    }
}
