<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $admin = auth()->user();

        return view('admin.profile.edit', compact('admin'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $admin = auth()->user();
        $data = $request->only(['name', 'email', 'phone']);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            if ($admin->profile_image && !\Illuminate\Support\Str::startsWith($admin->profile_image, ['http://', 'https://']) && Storage::exists('public/'.$admin->profile_image)) {
                Storage::delete('public/'.$admin->profile_image);
            }
            $path = \App\Services\ImageUploadService::upload($request->file('profile_image'), 'admin_profiles');
            $data['profile_image'] = $path;
        }

        // Handle password update
        if ($request->filled('current_password') && $request->filled('password')) {
            if (! Hash::check($request->current_password, $admin->password)) {
                return back()->withErrors(['current_password' => 'Current password does not match'])->withInput();
            }
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function destroy()
    {
        $admin = auth()->user();

        // Handle profile image deletion
        if ($admin->profile_image && Storage::exists('public/' . $admin->profile_image)) {
            Storage::delete('public/' . $admin->profile_image);
        }

        $admin->delete();

        auth()->logout();

        return redirect()->route('login')->with('success', 'Your administrator account has been deleted successfully.');
    }
}
