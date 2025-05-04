<?php

namespace App\Http\Requests\User\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // only signed-in users may update their own profile
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'firstName'   => 'required|string|min:3|max:255',
            'lastName'    => 'required|string|min:3|max:255',
            'password'    => 'nullable|string|min:6',
            'avatar_img'  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'firstName.required' => 'First name is required.',
            'firstName.min'      => 'First name must be at least :min characters.',
            'lastName.required'  => 'Last name is required.',
            'lastName.min'       => 'Last name must be at least :min characters.',
            'password.min'       => 'Password must be at least :min characters.',
            'avatar_img.image'   => 'Avatar must be a valid image.',
            'avatar_img.mimes'   => 'Avatar must be jpeg, png, jpg or gif.',
            'avatar_img.max'     => 'Avatar may not be larger than :max kilobytes.',
        ];
    }
}
