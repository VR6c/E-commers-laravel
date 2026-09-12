<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\UpdateProfileRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $vendor = auth()->guard('vendor')->user();

        return view('vendor.profile.edit', compact('vendor'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $vendor = auth()->guard('vendor')->user();
        $data = $request->only(['name', 'email', 'phone']);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            if ($vendor->profile_image && !\Illuminate\Support\Str::startsWith($vendor->profile_image, ['http://', 'https://']) && Storage::exists('public/'.$vendor->profile_image)) {
                Storage::delete('public/'.$vendor->profile_image);
            }
            $path = \App\Services\ImageUploadService::upload($request->file('profile_image'), 'vendor_profiles');
            $data['profile_image'] = $path;
        }

        // Handle password update
        if ($request->filled('current_password') && $request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $vendor->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function destroy()
    {
        $vendor = auth()->guard('vendor')->user();

        // Handle profile image deletion
        if ($vendor->profile_image && Storage::exists('public/' . $vendor->profile_image)) {
            Storage::delete('public/' . $vendor->profile_image);
        }

        $vendor->delete();

        auth()->guard('vendor')->logout();

        return redirect()->route('vendor.login')->with('success', 'Your vendor account has been deleted successfully.');
    }
}
