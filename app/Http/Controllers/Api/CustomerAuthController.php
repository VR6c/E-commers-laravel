<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CustomerAuthController extends Controller
{
    use ApiResponse;

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:customers',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->problemResponse(
                'https://api.example.com/errors/validation-error',
                'Validation Error',
                422,
                'The given data was invalid.',
                $request->path(),
                $validator->errors()->toArray()
            );
        }

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'status' => 'active',
        ]);

        $token = $customer->createToken('CustomerToken')->plainTextToken;

        return $this->successResponse([
            'token'    => $token,
            'customer' => $customer,
        ], 'Registration successful', 201);
    }

    public function login(Request $request)
    {
        $customer = Customer::where('email', $request->email)->first();

        if (! $customer || ! Hash::check($request->password, $customer->password)) {
            return $this->problemResponse(
                'https://api.example.com/errors/unauthorized',
                'Unauthorized',
                401,
                'Invalid login credentials provided.',
                $request->path()
            );
        }

        $token = $customer->createToken('CustomerToken')->plainTextToken;

        return $this->successResponse([
            'token'    => $token,
            'customer' => $customer,
        ], 'Login successful');
    }

    public function profile(Request $request)
    {
        return $this->successResponse($request->user(), 'Customer profile retrieved');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }
}
