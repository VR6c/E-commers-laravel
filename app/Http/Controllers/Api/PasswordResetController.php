<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetOtpMail;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    /**
     * OTP validity duration in minutes.
     */
    protected const OTP_EXPIRY_MINUTES = 15;

    /**
     * Minimum cooldown between OTP requests in seconds.
     */
    protected const RESEND_COOLDOWN_SECONDS = 60;

    /**
     * POST /api/password/forgot or POST /api/customer/forgot-password
     * Generate and send a 6-digit OTP code to the customer's email.
     *
     * Request body: { "email": "customer@gmail.com" }
     */
    public function forgot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Please provide a valid email address.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->email));
        $customer = Customer::where('email', $email)->first();

        if (! $customer) {
            return response()->json([
                'status'  => false,
                'message' => 'No account found with this email address.',
            ], 404);
        }

        // Throttle rapid requests (60 seconds cooldown)
        $existing = DB::table('password_resets')->where('email', $email)->first();
        if ($existing && $existing->created_at) {
            $secondsSinceLast = now()->diffInSeconds(Carbon::parse($existing->created_at));
            if ($secondsSinceLast < self::RESEND_COOLDOWN_SECONDS) {
                $wait = self::RESEND_COOLDOWN_SECONDS - $secondsSinceLast;

                return response()->json([
                    'status'  => false,
                    'message' => "Please wait {$wait} seconds before requesting a new code.",
                    'data'    => [
                        'retry_after' => $wait,
                    ],
                ], 429);
            }
        }

        // Generate 6-digit OTP
        $otp = (string) random_int(100000, 999999);
        $token = Hash::make($otp);

        // Store in password_resets
        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            [
                'token'      => $token,
                'created_at' => now(),
            ]
        );

        // Send OTP email
        $mailSent = false;
        $smtpError = null;

        try {
            Mail::to($email)->send(new PasswordResetOtpMail($otp, self::OTP_EXPIRY_MINUTES));
            $mailSent = true;
        } catch (\Throwable $e) {
            $smtpError = $e->getMessage();
            Log::error("Failed to send OTP to {$email}: " . $smtpError);
        }

        // If mail failed and in production, return 500 error
        if (! $mailSent && ! config('app.debug')) {
            return response()->json([
                'status'  => false,
                'message' => 'Unable to send verification email. Please check your email configuration or try again later.',
                'error'   => $smtpError,
            ], 500);
        }

        $response = [
            'status'  => true,
            'message' => "A 6-digit verification code has been sent to {$email}.",
            'data'    => [
                'email'              => $email,
                'expires_in_minutes' => self::OTP_EXPIRY_MINUTES,
            ],
        ];

        // Developer helper in debug mode
        if (config('app.debug')) {
            $response['debug_otp'] = $otp;
            if ($smtpError) {
                $response['smtp_warning'] = "SMTP error: {$smtpError}. Note: debug_otp is available for local testing.";
            }
        }

        return response()->json($response);
    }

    /**
     * POST /api/password/verify-otp or POST /api/customer/verify-otp
     * Validate the 6-digit OTP entered by the user.
     *
     * Request body: { "email": "customer@gmail.com", "otp": "123456" }
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Please provide a valid email and 6-digit code.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->email));
        $record = DB::table('password_resets')->where('email', $email)->first();

        if (! $record) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or expired verification code.',
            ], 422);
        }

        // Check expiration
        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->diffInMinutes(now()) > self::OTP_EXPIRY_MINUTES) {
            DB::table('password_resets')->where('email', $email)->delete();

            return response()->json([
                'status'  => false,
                'message' => 'Verification code has expired. Please request a new one.',
            ], 422);
        }

        // Check OTP match
        if (! Hash::check($request->otp, $record->token)) {
            return response()->json([
                'status'  => false,
                'message' => 'Incorrect verification code. Please check and try again.',
            ], 422);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Verification code confirmed successfully.',
            'data'    => [
                'email' => $email,
                'otp'   => $request->otp,
            ],
        ]);
    }

    /**
     * POST /api/password/resend-otp or POST /api/customer/resend-otp
     * Re-send a new OTP code to the customer's email.
     *
     * Request body: { "email": "customer@gmail.com" }
     */
    public function resendOtp(Request $request)
    {
        return $this->forgot($request);
    }

    /**
     * POST /api/password/reset or POST /api/customer/reset-password
     * Verify OTP and update the customer's password.
     *
     * Request body: { "email": "...", "otp": "123456", "password": "...", "password_confirmation": "..." }
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'otp'      => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->email));
        $record = DB::table('password_resets')->where('email', $email)->first();

        if (! $record) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or expired verification code.',
            ], 422);
        }

        // Check expiration
        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->diffInMinutes(now()) > self::OTP_EXPIRY_MINUTES) {
            DB::table('password_resets')->where('email', $email)->delete();

            return response()->json([
                'status'  => false,
                'message' => 'Verification code has expired. Please request a new one.',
            ], 422);
        }

        // Verify OTP
        if (! Hash::check($request->otp, $record->token)) {
            return response()->json([
                'status'  => false,
                'message' => 'Incorrect verification code.',
            ], 422);
        }

        $customer = Customer::where('email', $email)->first();
        if (! $customer) {
            return response()->json([
                'status'  => false,
                'message' => 'Customer account not found.',
            ], 404);
        }

        // Update password
        $customer->password = $request->password;
        $customer->save();

        // Delete reset record to prevent reuse
        DB::table('password_resets')->where('email', $email)->delete();

        // Invalidate old tokens
        $customer->tokens()->delete();

        // Issue new token so mobile app can automatically log in
        $token = $customer->createToken('CustomerToken')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Password has been reset successfully.',
            'data'    => [
                'token'    => $token,
                'customer' => $customer,
            ],
        ]);
    }
}
