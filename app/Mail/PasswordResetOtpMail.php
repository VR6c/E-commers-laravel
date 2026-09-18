<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetOtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(public readonly string $otp) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password Reset Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: "
                <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                    <h2 style='color: #1a202c; margin-bottom: 12px;'>Password Reset Request</h2>
                    <p style='color: #4a5568; font-size: 15px;'>Your 6-digit verification code is:</p>
                    <div style='background: #f7fafc; border: 1px dashed #cbd5e0; padding: 16px; text-align: center; font-size: 28px; font-weight: bold; letter-spacing: 4px; color: #2b6cb0; margin: 16px 0;'>
                        {$this->otp}
                    </div>
                    <p style='color: #718096; font-size: 13px;'>This code expires in 60 minutes. If you did not request a password reset, please ignore this email.</p>
                </div>
            ",
        );
    }
}
