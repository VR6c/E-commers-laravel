<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CustomerProfileController extends Controller
{
    /**
     * GET /api/customer/profile
     * Retrieve the authenticated customer's profile details including avatar fields.
     */
    public function getProfile(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->user();

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'status' => $customer->status ?? 'active',
                'profile_image' => $customer->profile_image,
                'avatar_url' => $customer->avatar_url,
                'avatar_type' => $customer->avatar_type ?? 'default',
            ],
        ], 200);
    }

    /**
     * PUT /api/customer/profile
     * Update the authenticated customer's name, email, phone, address, or password.
     * Only fields that are present in the request body are updated.
     */
    public function updateProfile(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes', 'required', 'email', 'max:255',
                Rule::unique('customers')->ignore($customer->id),
            ],
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:500',
            'avatar_type' => 'sometimes|nullable|string|in:photo,fluttermoji,default',
            'current_password' => 'required_with:new_password|string',
            'new_password' => [
                'sometimes', 'required', 'confirmed',
                Password::min(6),
            ],
        ]);

        // If changing password, verify the current one first
        if ($request->filled('new_password')) {
            if (! Hash::check($request->current_password, $customer->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Current password is incorrect.',
                    'errors' => ['current_password' => ['Current password is incorrect.']],
                ], 422);
            }
            $customer->password = bcrypt($request->new_password);
        }

        // Update only the fields that were sent
        if ($request->has('name')) {
            $customer->name = $request->name;
        }
        if ($request->has('email')) {
            $customer->email = $request->email;
        }
        if ($request->has('phone')) {
            $customer->phone = $request->phone;
        }
        if ($request->has('address')) {
            $customer->address = $request->address;
        }
        if ($request->has('avatar_type')) {
            $customer->avatar_type = $request->avatar_type;
        }

        $customer->save();

        $profileData = [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'address' => $customer->address,
            'status' => $customer->status ?? 'active',
            'profile_image' => $customer->profile_image,
            'avatar_url' => $customer->avatar_url,
            'avatar_type' => $customer->avatar_type ?? 'default',
        ];

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully.',
            'data' => $profileData,
            'customer' => $profileData,
        ], 200);
    }

    /**
     * Backward-compatible alias for updateProfile.
     */
    public function update(Request $request)
    {
        return $this->updateProfile($request);
    }

    /**
     * POST /api/customer/avatar
     * Upload or update customer avatar.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096', // Max 4MB
            'avatar_type' => 'nullable|string|in:photo,fluttermoji,default',
        ]);

        /** @var Customer $customer */
        $customer = $request->user();

        // Upload using ImageUploadService (Cloudinary -> ImgBB -> storage fallback)
        $avatarUrl = ImageUploadService::uploadAvatar($request->file('avatar'));

        $customer->update([
            'profile_image' => $avatarUrl,
            'avatar_type' => $request->input('avatar_type', 'photo'),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Avatar updated successfully',
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'status' => $customer->status ?? 'active',
                'profile_image' => $customer->profile_image,
                'avatar_url' => $customer->avatar_url,
                'avatar_type' => $customer->avatar_type,
            ],
        ], 200);
    }

    /**
     * DELETE /api/customer/avatar
     * Delete avatar and revert to default initials.
     */
    public function deleteAvatar(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $customer->update([
            'profile_image' => null,
            'avatar_type' => 'default',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Avatar removed successfully',
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'status' => $customer->status ?? 'active',
                'profile_image' => null,
                'avatar_url' => $customer->avatar_url,
                'avatar_type' => 'default',
            ],
        ], 200);
    }
}
