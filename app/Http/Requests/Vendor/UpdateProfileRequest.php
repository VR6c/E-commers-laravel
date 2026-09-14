<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('vendor')->check(); // Vendor guard
    }

    public function rules(): array
    {
        $vendor = Auth::guard('vendor')->user();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('vendors', 'email')->ignore($vendor?->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],

            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.unique' => 'This email is already in use.',
            'current_password.required' => 'The current password field is required to set a new password.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'profile_image.image' => 'The profile image must be an image file.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('password')) {
                $vendor = Auth::guard('vendor')->user();

                if (! $this->filled('current_password')) {
                    $validator->errors()->add('current_password', 'The current password field is required to change your password.');
                    return;
                }

                $match = false;
                try {
                    $info = password_get_info($vendor->password);
                    if ($info['algo'] === 0) {
                        $match = ($this->current_password === $vendor->password);
                    } else {
                        $match = Hash::check($this->current_password, $vendor->password);
                    }
                } catch (\Throwable $e) {
                    $match = false;
                }

                if (! $match) {
                    $validator->errors()->add('current_password', 'The current password is incorrect.');
                }
            }
        });
    }
}
