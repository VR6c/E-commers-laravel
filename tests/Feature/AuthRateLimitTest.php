<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthRateLimitTest extends TestCase
{
    use RefreshDatabase;

    private function customerWithOtp(string $otp = '456789'): string
    {
        $email = 'rate@example.com';
        Customer::create(['name' => 'Rate', 'email' => $email, 'password' => 'password123', 'status' => 'active']);
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => Hash::make($otp),
            'created_at' => now(),
        ]);

        return $email;
    }

    public function test_otp_is_locked_after_five_wrong_guesses_even_if_the_next_guess_is_correct(): void
    {
        $email = $this->customerWithOtp();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/password/verify-otp', ['email' => $email, 'otp' => '000000'])->assertStatus(422);
        }

        $this->postJson('/api/password/verify-otp', ['email' => $email, 'otp' => '456789'])
            ->assertStatus(429)
            ->assertJson(['status' => false]);

        $this->assertDatabaseMissing('password_resets', ['email' => $email]);
    }

    public function test_reset_step_counts_wrong_otp_guesses_too(): void
    {
        $email = $this->customerWithOtp();
        $payload = ['email' => $email, 'password' => 'newpassword1', 'password_confirmation' => 'newpassword1'];

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/password/reset', $payload + ['otp' => '111111'])->assertStatus(422);
        }

        $this->postJson('/api/password/reset', $payload + ['otp' => '456789'])->assertStatus(429);
        $this->assertTrue(Hash::check('password123', Customer::where('email', $email)->value('password')));
    }

    public function test_correct_otp_within_the_limit_still_works(): void
    {
        $email = $this->customerWithOtp();

        $this->postJson('/api/password/verify-otp', ['email' => $email, 'otp' => '000000'])->assertStatus(422);
        $this->postJson('/api/password/verify-otp', ['email' => $email, 'otp' => '456789'])->assertOk();
    }

    public function test_login_is_throttled_per_ip_after_ten_attempts_per_minute(): void
    {
        // Different emails each time so the per-account lockout does not trigger first
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/customer/login', ['email' => "nobody{$i}@example.com", 'password' => 'x'])->assertStatus(401);
        }

        $this->postJson('/api/customer/login', ['email' => 'nobody11@example.com', 'password' => 'x'])->assertStatus(429);
    }

    private function loginCustomer(): Customer
    {
        return Customer::create(['name' => 'Lock', 'email' => 'lock@example.com', 'password' => 'password123', 'status' => 'active']);
    }

    private function failLogins(int $times, string $email = 'lock@example.com'): void
    {
        for ($i = 0; $i < $times; $i++) {
            $this->postJson('/api/customer/login', ['email' => $email, 'password' => 'wrong'])->assertStatus(401);
        }
    }

    public function test_account_is_locked_after_five_failed_logins_even_with_the_right_password(): void
    {
        $this->loginCustomer();
        $this->failLogins(5);

        $response = $this->postJson('/api/customer/login', ['email' => 'lock@example.com', 'password' => 'password123']);

        $response->assertStatus(429);
        $this->assertGreaterThan(0, (int) $response->headers->get('Retry-After'));
    }

    public function test_lockout_applies_to_unknown_emails_too_and_does_not_affect_other_accounts(): void
    {
        $this->loginCustomer();
        $this->failLogins(5, 'ghost@example.com');

        $this->postJson('/api/customer/login', ['email' => 'ghost@example.com', 'password' => 'x'])->assertStatus(429);
        $this->postJson('/api/customer/login', ['email' => 'lock@example.com', 'password' => 'password123'])->assertOk();
    }

    public function test_successful_login_clears_the_failure_count(): void
    {
        $this->loginCustomer();
        $this->failLogins(3);
        $this->postJson('/api/customer/login', ['email' => 'lock@example.com', 'password' => 'password123'])->assertOk();

        $this->failLogins(4);
        $this->postJson('/api/customer/login', ['email' => 'lock@example.com', 'password' => 'password123'])->assertOk();
    }

    public function test_completed_password_reset_lifts_the_lockout(): void
    {
        $this->loginCustomer();
        $this->failLogins(5);
        $this->postJson('/api/customer/login', ['email' => 'lock@example.com', 'password' => 'password123'])->assertStatus(429);

        DB::table('password_resets')->insert([
            'email' => 'lock@example.com',
            'token' => Hash::make('654321'),
            'created_at' => now(),
        ]);
        $this->postJson('/api/password/reset', [
            'email' => 'lock@example.com',
            'otp' => '654321',
            'password' => 'brandnew123',
            'password_confirmation' => 'brandnew123',
        ])->assertOk();

        $this->postJson('/api/customer/login', ['email' => 'lock@example.com', 'password' => 'brandnew123'])->assertOk();
    }
}
