<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'email' => 'required|email|unique:users,email,' . $this->user_id,
            'phone' => 'nullable|numeric|digits:10|unique:users,phone,' . $this->user_id,
            'gender' => 'nullable|string|max:20',
            'aadhaar_number' => 'nullable|string|max:20',
        ];
    }
}
