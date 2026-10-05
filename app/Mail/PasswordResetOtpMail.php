<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $otp,
        public readonly int $expireMinutes = 15
    ) {}

    public function envelope(): Envelope
    {
        $appName = config('app.name', 'TVR Store');

        return new Envelope(
            subject: "{$this->otp} is your {$appName} verification code",
        );
    }

    public function content(): Content
    {
        $appName = config('app.name', 'TVR Store');

        return new Content(
            htmlString: "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            </head>
            <body style='margin: 0; padding: 0; background-color: #f4f6f9; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
                <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f4f6f9; padding: 40px 0;'>
                    <tr>
                        <td align='center'>
                            <table width='100%' cellpadding='0' cellspacing='0' style='max-width: 520px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08);'>
                                <!-- Header -->
                                <tr>
                                    <td style='background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 28px 32px; text-align: center;'>
                                        <h1 style='margin: 0; color: #ffffff; font-size: 24px; font-weight: 700; letter-spacing: 0.5px;'>
                                            {$appName}
                                        </h1>
                                        <p style='margin: 6px 0 0; color: #94a3b8; font-size: 13px;'>Password Reset Verification</p>
                                    </td>
                                </tr>
                                <!-- Content -->
                                <tr>
                                    <td style='padding: 36px 32px;'>
                                        <p style='margin: 0 0 16px; color: #334155; font-size: 15px; line-height: 1.6;'>
                                            Hello,
                                        </p>
                                        <p style='margin: 0 0 24px; color: #475569; font-size: 15px; line-height: 1.6;'>
                                            We received a request to reset your password. Use the 6-digit verification code below to complete the process on your mobile device:
                                        </p>
                                        <!-- OTP Display Box -->
                                        <div style='background-color: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 10px; padding: 20px; text-align: center; margin: 24px 0;'>
                                            <span style='font-family: monospace; font-size: 32px; font-weight: 800; letter-spacing: 8px; color: #0284c7; display: inline-block;'>
                                                {$this->otp}
                                            </span>
                                            <p style='margin: 8px 0 0; color: #64748b; font-size: 12px;'>
                                                ⏱ This code expires in {$this->expireMinutes} minutes
                                            </p>
                                        </div>
                                        <p style='margin: 24px 0 0; color: #64748b; font-size: 13px; line-height: 1.6;'>
                                            If you did not request a password reset, please ignore this email. Your password will remain unchanged and your account stays safe.
                                        </p>
                                    </td>
                                </tr>
                                <!-- Footer -->
                                <tr>
                                    <td style='background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 32px; text-align: center;'>
                                        <p style='margin: 0; color: #94a3b8; font-size: 12px;'>
                                            &copy; " . date('Y') . " {$appName}. All rights reserved.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>
            ",
        );
    }
}
