<?php

namespace Tests\Feature;

use App\Mail\PasswordResetOtpMail;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MobilePasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_validates_email_input(): void
    {
        $response = $this->postJson('/api/password/forgot', []);
        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
            ]);

        $invalidResponse = $this->postJson('/api/password/forgot', ['email' => 'invalid-email-format']);
        $invalidResponse->assertStatus(422)
            ->assertJson([
                'status' => false,
            ]);
    }

    public function test_forgot_password_returns_404_if_customer_not_found(): void
    {
        $response = $this->postJson('/api/password/forgot', ['email' => 'notfound@example.com']);
        $response->assertStatus(404)
            ->assertJson([
                'status'  => false,
                'message' => 'No account found with this email address.',
            ]);
    }

    public function test_forgot_password_generates_otp_and_sends_email(): void
    {
        Mail::fake();

        $customer = Customer::create([
            'name'     => 'Alice Bob',
            'email'    => 'alice@example.com',
            'password' => 'oldpassword123',
            'status'   => 'active',
        ]);

        $response = $this->postJson('/api/password/forgot', [
            'email' => 'alice@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
            ]);

        // Check password_resets record exists
        $record = DB::table('password_resets')->where('email', 'alice@example.com')->first();
        $this->assertNotNull($record);

        // Verify Mail sent
        Mail::assertSent(PasswordResetOtpMail::class, function ($mail) {
            return $mail->hasTo('alice@example.com');
        });
    }

    public function test_forgot_password_is_throttled_within_cooldown(): void
    {
        Mail::fake();

        Customer::create([
            'name'     => 'Cooldown User',
            'email'    => 'cooldown@example.com',
            'password' => 'password123',
            'status'   => 'active',
        ]);

        // 1st request succeeds
        $this->postJson('/api/password/forgot', ['email' => 'cooldown@example.com'])
            ->assertStatus(200);

        // Immediate 2nd request is throttled
        $response = $this->postJson('/api/password/forgot', ['email' => 'cooldown@example.com']);
        $response->assertStatus(429)
            ->assertJson([
                'status' => false,
            ]);
    }

    public function test_verify_otp_validates_correct_and_incorrect_otp(): void
    {
        $email = 'verify@example.com';
        Customer::create([
            'name'     => 'Verify User',
            'email'    => $email,
            'password' => 'password123',
            'status'   => 'active',
        ]);

        $otp = '456789';
        DB::table('password_resets')->insert([
            'email'      => $email,
            'token'      => Hash::make($otp),
            'created_at' => now(),
        ]);

        // Wrong OTP
        $wrongResponse = $this->postJson('/api/password/verify-otp', [
            'email' => $email,
            'otp'   => '000000',
        ]);
        $wrongResponse->assertStatus(422)
            ->assertJson([
                'status' => false,
            ]);

        // Correct OTP
        $correctResponse = $this->postJson('/api/password/verify-otp', [
            'email' => $email,
            'otp'   => $otp,
        ]);
        $correctResponse->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data'   => [
                    'email' => $email,
                    'otp'   => $otp,
                ],
            ]);
    }

    public function test_verify_otp_fails_when_expired(): void
    {
        $email = 'expired@example.com';
        Customer::create([
            'name'     => 'Expired User',
            'email'    => $email,
            'password' => 'password123',
            'status'   => 'active',
        ]);

        $otp = '123456';
        DB::table('password_resets')->insert([
            'email'      => $email,
            'token'      => Hash::make($otp),
            'created_at' => Carbon::now()->subMinutes(20), // > 15 mins
        ]);

        $response = $this->postJson('/api/password/verify-otp', [
            'email' => $email,
            'otp'   => $otp,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'Verification code has expired. Please request a new one.',
            ]);
    }

    public function test_password_reset_success_flow_and_subsequent_login(): void
    {
        $email = 'resetflow@example.com';
        $customer = Customer::create([
            'name'     => 'Reset User',
            'email'    => $email,
            'password' => 'initialPassword',
            'status'   => 'active',
        ]);

        $otp = '789123';
        DB::table('password_resets')->insert([
            'email'      => $email,
            'token'      => Hash::make($otp),
            'created_at' => now(),
        ]);

        // Reset password
        $resetResponse = $this->postJson('/api/password/reset', [
            'email'                 => $email,
            'otp'                   => $otp,
            'password'              => 'brandNewPassword123',
            'password_confirmation' => 'brandNewPassword123',
        ]);

        $resetResponse->assertStatus(200)
            ->assertJson([
                'status' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'customer' => ['id', 'email', 'name'],
                ],
            ]);

        // Token in password_resets should now be deleted
        $this->assertNull(DB::table('password_resets')->where('email', $email)->first());

        // Customer can log in with new password
        $loginResponse = $this->postJson('/api/customer/login', [
            'email'    => $email,
            'password' => 'brandNewPassword123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJson([
                'status' => true,
            ]);
    }

    public function test_customer_route_aliases_work_identically(): void
    {
        Mail::fake();

        Customer::create([
            'name'     => 'Alias User',
            'email'    => 'alias@example.com',
            'password' => 'password123',
            'status'   => 'active',
        ]);

        $this->postJson('/api/customer/forgot-password', ['email' => 'alias@example.com'])
            ->assertStatus(200);

        Mail::assertSent(PasswordResetOtpMail::class);
    }
}
