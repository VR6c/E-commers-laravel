<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

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

        if (! $customer) {
            return $this->problemResponse(
                'https://api.example.com/errors/unauthorized',
                'Unauthorized',
                401,
                'Invalid login credentials provided.',
                $request->path()
            );
        }

        $passwordValid = false;
        try {
            $info = password_get_info($customer->password);
            if ($info['algo'] === 0) {
                if ($request->password === $customer->password) {
                    $customer->password = $request->password;
                    $customer->save();
                    $passwordValid = true;
                }
            } else {
                $passwordValid = Hash::check($request->password, $customer->password);
            }
        } catch (\Throwable $e) {
            $passwordValid = false;
        }

        if (! $passwordValid) {
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

    /**
     * POST /api/customer/refresh
     * Refresh / rotate Sanctum access token.
     * Accepts token in Authorization header (Bearer token) or in request body (refresh_token / token).
     */
    public function refresh(Request $request)
    {
        $customer = null;
        $accessToken = null;

        // 1. Check if authenticated via sanctum guard
        $authenticatedUser = $request->user('sanctum') ?? $request->user();
        if ($authenticatedUser instanceof Customer) {
            $customer = $authenticatedUser;
            $accessToken = $customer->currentAccessToken();
        }

        // 2. If not resolved yet, extract token from body or header
        if (! $customer || ! $accessToken) {
            $plainToken = $request->input('refresh_token') ?? $request->bearerToken() ?? $request->input('token');

            if (! $plainToken) {
                return $this->problemResponse(
                    'https://api.example.com/errors/unauthorized',
                    'Unauthorized',
                    401,
                    'Token not provided.',
                    $request->path()
                );
            }

            $accessToken = PersonalAccessToken::findToken($plainToken);

            if (! $accessToken) {
                return $this->problemResponse(
                    'https://api.example.com/errors/unauthorized',
                    'Unauthorized',
                    401,
                    'Invalid or expired token.',
                    $request->path()
                );
            }

            $tokenable = $accessToken->tokenable;
            if ($tokenable instanceof Customer) {
                $customer = $tokenable;
            }
        }

        if (! $customer || ! $accessToken) {
            return $this->problemResponse(
                'https://api.example.com/errors/unauthorized',
                'Unauthorized',
                401,
                'Unable to authenticate customer from token.',
                $request->path()
            );
        }

        if ($customer->status !== 'active') {
            return $this->problemResponse(
                'https://api.example.com/errors/unauthorized',
                'Unauthorized',
                401,
                'Customer account is not active.',
                $request->path()
            );
        }

        // Token rotation: delete the current token
        $accessToken->delete();

        // Issue new token
        $newToken = $customer->createToken('CustomerToken')->plainTextToken;

        return $this->successResponse([
            'token'    => $newToken,
            'customer' => $customer,
        ], 'Token refreshed successfully');
    }
}
