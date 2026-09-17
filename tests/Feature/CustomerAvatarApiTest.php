<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerAvatarApiTest extends TestCase
{
    use RefreshDatabase;

    private Customer $customer;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::create([
            'name' => 'Avatar Tester',
            'email' => 'avatar.tester@example.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $this->token = $this->customer->createToken('TestToken')->plainTextToken;
    }

    public function test_avatar_endpoints_require_authentication(): void
    {
        $this->postJson('/api/customer/avatar', [])
            ->assertStatus(401);

        $this->deleteJson('/api/customer/avatar')
            ->assertStatus(401);
    }

    public function test_upload_avatar_validates_image_file(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/customer/avatar', [
                'avatar' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_upload_avatar_successfully_updates_customer_profile(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('my_avatar.png', 400, 400);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/customer/avatar', [
                'avatar' => $file,
                'avatar_type' => 'photo',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Avatar updated successfully',
                'data' => [
                    'id' => $this->customer->id,
                    'name' => 'Avatar Tester',
                    'email' => 'avatar.tester@example.com',
                    'avatar_type' => 'photo',
                ],
            ]);

        $this->assertNotNull($response->json('data.profile_image'));
        $this->assertNotNull($response->json('data.avatar_url'));

        // Refresh model from DB
        $this->customer->refresh();
        $this->assertNotNull($this->customer->profile_image);
        $this->assertEquals('photo', $this->customer->avatar_type);
    }

    public function test_delete_avatar_resets_to_default(): void
    {
        // Set initial avatar
        $this->customer->update([
            'profile_image' => 'https://example.com/avatar.jpg',
            'avatar_type' => 'photo',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson('/api/customer/avatar');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Avatar removed successfully',
                'data' => [
                    'id' => $this->customer->id,
                    'name' => 'Avatar Tester',
                    'email' => 'avatar.tester@example.com',
                    'profile_image' => null,
                    'avatar_type' => 'default',
                ],
            ]);

        $this->customer->refresh();
        $this->assertNull($this->customer->profile_image);
        $this->assertEquals('default', $this->customer->avatar_type);
    }

    public function test_get_profile_returns_avatar_fields(): void
    {
        $this->customer->update([
            'profile_image' => 'https://example.com/custom-avatar.jpg',
            'avatar_type' => 'photo',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/customer/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'address',
                    'status',
                    'profile_image',
                    'avatar_url',
                    'avatar_type',
                ],
            ])
            ->assertJsonPath('data.profile_image', 'https://example.com/custom-avatar.jpg')
            ->assertJsonPath('data.avatar_type', 'photo');
    }
}
