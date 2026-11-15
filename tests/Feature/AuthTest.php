<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Interest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_user_can_register_and_receives_jwt_with_user_role(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Alice Example',
            'email' => 'Alice@Example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'role' => 'admin',
            'interests' => [' Chess ', 'Reading', 'chess'],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.token_type', 'bearer')
            ->assertJsonPath('data.user.email', 'alice@example.com')
            ->assertJsonPath('data.user.role', User::ROLE_USER)
            ->assertJsonPath('data.user.interests', ['chess', 'reading'])
            ->assertJsonMissingPath('data.user.password');

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_duplicate_email_registration_is_rejected(): void
    {
        User::factory()->create(['email' => 'alice@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Alice Duplicate',
            'email' => 'alice@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['email']]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'bob@example.com',
            'password' => Hash::make('Secret123!'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'bob@example.com',
            'password' => 'Secret123!',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'bob@example.com')
            ->assertJsonMissingPath('data.user.password');

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'bob@example.com',
            'password' => Hash::make('Secret123!'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'bob@example.com',
            'password' => 'WrongPassword!',
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_login_is_rate_limited(): void
    {
        User::factory()->create([
            'email' => 'rate@example.com',
            'password' => Hash::make('Secret123!'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'rate@example.com',
                'password' => 'WrongPassword!',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'rate@example.com',
            'password' => 'WrongPassword!',
        ])->assertStatus(429)
            ->assertJsonPath('success', false);
    }

    public function test_me_endpoint_returns_authenticated_user(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com']);
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'me@example.com')
            ->assertJsonMissingPath('data.password');
    }

    public function test_me_endpoint_rejects_missing_or_invalid_jwt(): void
    {
        $this->getJson('/api/v1/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);

        $this->withHeader('Authorization', 'Bearer invalid.jwt.token')
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_expired_jwt_is_rejected(): void
    {
        $user = User::factory()->create();

        JWTAuth::factory()->setTTL(1);
        $token = auth('api')->login($user);
        JWTAuth::factory()->setTTL(60);

        $this->travel(2)->minutes();
        auth('api')->forgetUser();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_logout_invalidates_token(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('success', true);

        auth('api')->forgetUser();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_public_can_fetch_active_interests(): void
    {
        Interest::query()->create(['name' => 'chess']);
        $deleted = Interest::query()->create(['name' => 'archived']);
        $deleted->delete();

        $this->getJson('/api/v1/interests')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'chess');
    }

    public function test_authenticated_user_can_update_profile_and_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'password' => Hash::make('password123'),
            'interests' => ['chess'],
        ]);
        $token = auth('api')->login($user);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/auth/me', [
                'name' => 'New Name',
                'email' => 'new@example.com',
                'interests' => ['Chess', 'Reading'],
                'role' => 'admin',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.email', 'new@example.com')
            ->assertJsonPath('data.role', 'user')
            ->assertJsonPath('data.interests', ['chess', 'reading']);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/auth/password', [
                'current_password' => 'wrong-password',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertUnprocessable();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/auth/password', [
                'current_password' => 'password123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
