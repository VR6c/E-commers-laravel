<?php

namespace App\Http\Controllers\Vendor\Auth;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected $vendorService;

    public function showLoginForm()
    {
        return view('vendor.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        try {
            if (Auth::guard('vendor')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
                $vendor = Auth::guard('vendor')->user();
                if ($vendor->status === 'pending') {
                    Auth::guard('vendor')->logout();

                    return redirect()->route('vendor.login')
                        ->with('warning', 'Your account is pending administrative approval. You will receive access once approved.');
                }
                if ($vendor->status !== 'active') {
                    Auth::guard('vendor')->logout();

                    return redirect()->route('vendor.login')
                        ->with('error', 'Your vendor account has been deactivated or suspended. Please contact support.');
                }

                $request->session()->regenerate();

                return redirect()->route('vendor.dashboard');
            }
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Invalid credentials');
        }

        return back()->with('error', 'Invalid credentials');
    }

    public function logout()
    {
        Auth::guard('vendor')->logout();

        return redirect()->route('vendor.login');
    }

    public function showRegisterForm()
    {
        return view('vendor.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:vendors,email',
            'phone'    => 'nullable|string|max:30',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $vendor = Vendor::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'status'   => 'pending',
        ]);

        return redirect()->route('vendor.login')
            ->with('success', 'Your vendor account has been created and is pending approval. You will be notified once it is approved.');
    }

    public function dashboard()
    {
        return view('vendor.dashboard');
    }
}
