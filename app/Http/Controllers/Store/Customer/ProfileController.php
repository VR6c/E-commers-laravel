<?php

namespace App\Http\Controllers\Store\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\UpdateProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $customer = Auth::guard('customer')->user();

        return view('themes.xylo.customer.profile.edit', compact('customer'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $customer = Auth::guard('customer')->user();

        $data = $request->only(['name', 'email', 'phone', 'address']);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            if ($customer->profile_image && !\Illuminate\Support\Str::startsWith($customer->profile_image, ['http://', 'https://']) && Storage::disk('public')->exists($customer->profile_image)) {
                Storage::disk('public')->delete($customer->profile_image);
            }

            $path = \App\Services\ImageUploadService::upload($request->file('profile_image'), 'customer_profiles');
            $data['profile_image'] = $path;
        }

        // Handle password update (mutator in model will hash automatically if present)
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $customer->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function destroy()
    {
        $customer = Auth::guard('customer')->user();

        // Handle profile image deletion
        if ($customer->profile_image && Storage::disk('public')->exists($customer->profile_image)) {
            Storage::disk('public')->delete($customer->profile_image);
        }

        $customer->delete();

        Auth::guard('customer')->logout();

        return redirect()->route('xylo.home')->with('success', 'Your account has been deleted successfully.');
    }
}
