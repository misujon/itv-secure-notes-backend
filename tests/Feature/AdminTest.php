<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Tests\TestCase;

class AdminTest extends TestCase
{
    public function test_regular_user_cannot_access_admin_endpoints(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $token = auth('api')->login($user);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/users')
            ->assertForbidden();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/notes')
            ->assertForbidden();
    }

    public function test_admin_can_list_create_update_and_delete_users(): void
    {
        $admin = User::factory()->admin()->create();
        $token = auth('api')->login($admin);

        $createResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/admin/users', [
                'name' => 'Managed User',
                'email' => 'managed@example.com',
                'password' => 'Secret123!',
                'role' => User::ROLE_USER,
                'interests' => ['Chess', 'Music'],
            ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.email', 'managed@example.com')
            ->assertJsonPath('data.role', User::ROLE_USER)
            ->assertJsonMissingPath('data.password');

        $userId = $createResponse->json('data.id');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/users?per_page=10')
            ->assertOk()
            ->assertJsonPath('meta.total', 2);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/users/'.$userId)
            ->assertOk()
            ->assertJsonPath('data.id', $userId);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/admin/users/'.$userId, [
                'name' => 'Updated Name',
                'interests' => ['Coding'],
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.interests', ['coding']);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/admin/users/'.$userId)
            ->assertOk();

        $this->assertNull(User::query()->find($userId));
    }

    public function test_admin_cannot_delete_or_demote_last_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $token = auth('api')->login($admin);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/admin/users/'.$admin->_id, ['role' => User::ROLE_USER])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/admin/users/'.$admin->_id)
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_admin_can_view_all_users_notes_with_pagination(): void
    {
        $admin = User::factory()->admin()->create();
        $userA = User::factory()->create(['name' => 'User A']);
        $userB = User::factory()->create(['name' => 'User B']);

        $noteA = Note::factory()->create(['user_id' => $userA->_id, 'title' => 'Note A']);
        Note::factory()->create(['user_id' => $userB->_id, 'title' => 'Note B']);

        $token = auth('api')->login($admin);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/notes?per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 2);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/notes/'.$noteA->_id)
            ->assertOk()
            ->assertJsonPath('data.title', 'Note A')
            ->assertJsonPath('data.user.name', 'User A');
    }

    public function test_create_admin_command_creates_admin_user(): void
    {
        $this->artisan('app:create-admin', [
            '--name' => 'CLI Admin',
            '--email' => 'cliadmin@example.com',
            '--password' => 'Secret123!',
        ])->assertSuccessful();

        $admin = User::query()->where('email', 'cliadmin@example.com')->first();
        $this->assertNotNull($admin);
        $this->assertSame(User::ROLE_ADMIN, $admin->role);
    }
}
