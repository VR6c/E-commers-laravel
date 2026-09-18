<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetOtpMail;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    /**
     * POST /api/password/forgot
     * Send a password reset OTP/token to the customer's email.
     *
     * Body: { "email": "customer@example.com" }
     */
    public function forgot(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $customer = Customer::where('email', $request->email)->first();

        // Always return success to avoid email enumeration
        if (! $customer) {
            return response()->json([
                'status' => true,
                'message' => 'If that email is registered, a reset code has been sent.',
            ]);
        }

        // Generate a 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = Hash::make($otp);

        // Store in password_resets (replaces any existing entry)
        DB::table('password_resets')->updateOrInsert(
            ['email' => $customer->email],
            [
                'token' => $token,
                'created_at' => now(),
            ]
        );

        // Send OTP via queued email (non-blocking)
        Mail::to($customer->email)->queue(new PasswordResetOtpMail($otp));

        return response()->json([
            'status' => true,
            'message' => 'If that email is registered, a reset code has been sent.',
        ]);
    }

    /**
     * POST /api/password/reset
     * Verify the OTP and set a new password.
     *
     * Body: { "email": "...", "otp": "123456", "password": "...", "password_confirmation": "..." }
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $record = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (! $record) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired reset code.',
            ], 422);
        }

        // Check expiry (60 minutes)
        $createdAt = \Carbon\Carbon::parse($record->created_at);
        if ($createdAt->diffInMinutes(now()) > 60) {
            DB::table('password_resets')->where('email', $request->email)->delete();

            return response()->json([
                'status' => false,
                'message' => 'Reset code has expired. Please request a new one.',
            ], 422);
        }

        // Verify OTP
        if (! Hash::check($request->otp, $record->token)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid reset code.',
            ], 422);
        }

        // Update password
        $customer = Customer::where('email', $request->email)->first();
        if (! $customer) {
            return response()->json([
                'status' => false,
                'message' => 'Customer not found.',
            ], 404);
        }

        $customer->password = bcrypt($request->password);
        $customer->save();

        // Clean up the token
        DB::table('password_resets')->where('email', $request->email)->delete();

        // Revoke all existing tokens so old sessions are invalidated
        $customer->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Password has been reset successfully. Please log in again.',
        ]);
    }
}
