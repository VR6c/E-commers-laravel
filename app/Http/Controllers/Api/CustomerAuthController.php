<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ThrottleSensitiveRequests;
use App\Models\Customer;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class CustomerAuthController extends Controller
{
    use ApiResponse;

    /**
     * Failed logins allowed per account within LOGIN_LOCK_SECONDS before it is locked.
     */
    public const MAX_LOGIN_FAILURES = 5;

    public const LOGIN_LOCK_SECONDS = 900;

    public static function loginLockKey(string $email): string
    {
        return 'login-lock:'.sha1(strtolower(trim($email)));
    }

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
            'password' => $request->password,
            'phone' => $request->phone,
            'status' => 'active',
        ]);

        $token = $customer->createToken('CustomerToken')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'customer' => $customer,
        ], 'Registration successful', 201);
    }

    public function login(Request $request)
    {
        // Per-account lockout (counted whether or not the account exists, so it reveals nothing)
        $limiter = ThrottleSensitiveRequests::limiter();
        $lockKey = self::loginLockKey((string) $request->input('email'));
        $failures = $limiter->attempts($lockKey);

        if ($failures >= self::MAX_LOGIN_FAILURES) {
            $wait = $limiter->availableIn($lockKey);

            return $this->problemResponse(
                'https://api.example.com/errors/too-many-attempts',
                'Too Many Attempts',
                429,
                "Too many failed login attempts. Try again in {$wait} seconds or reset your password.",
                $request->path()
            )->header('Retry-After', (string) $wait);
        }

        $customer = Customer::where('email', $request->email)->first();

        if (! $customer) {
            $limiter->hit($lockKey, self::LOGIN_LOCK_SECONDS);

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
            $limiter->hit($lockKey, self::LOGIN_LOCK_SECONDS);

            return $this->problemResponse(
                'https://api.example.com/errors/unauthorized',
                'Unauthorized',
                401,
                'Invalid login credentials provided.',
                $request->path()
            );
        }

        if ($failures > 0) {
            $limiter->clear($lockKey);
        }

        $token = $customer->createToken('CustomerToken')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
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
            'token' => $newToken,
            'customer' => $customer,
        ], 'Token refreshed successfully');
    }
}
