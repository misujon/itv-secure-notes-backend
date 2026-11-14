<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Tests\TestCase;

class PostAndAggregationTest extends TestCase
{
    public function test_anyone_can_list_and_view_posts_and_authenticated_user_can_create(): void
    {
        $user = User::factory()->create(['name' => 'Author One']);
        $post = Post::factory()->create([
            'user_id' => $user->_id,
            'title' => 'Public Post',
            'content' => 'Visible to everyone.',
        ]);

        $this->getJson('/api/v1/posts?per_page=10')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);

        $this->getJson('/api/v1/posts/'.$post->_id)
            ->assertOk()
            ->assertJsonPath('data.title', 'Public Post')
            ->assertJsonPath('data.user.name', 'Author One');

        $this->postJson('/api/v1/posts', [
            'title' => 'Unauth Post',
            'content' => 'Nope',
        ])->assertUnauthorized();

        $token = auth('api')->login($user);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/posts', [
                'title' => 'New Public Post',
                'content' => 'Created by authenticated user.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'New Public Post')
            ->assertJsonPath('data.user_id', (string) $user->_id);
    }

    public function test_admin_can_group_users_by_interests_with_pagination(): void
    {
        $admin = User::factory()->admin()->create(['interests' => ['chess']]);
        User::factory()->create(['name' => 'Alice', 'interests' => ['chess', 'reading']]);
        User::factory()->create(['name' => 'Bob', 'interests' => ['reading', 'music']]);
        User::factory()->create(['name' => 'No Interests', 'interests' => []]);

        $token = auth('api')->login($admin);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/aggregations/interests?per_page=2&page=1');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('data.0.interest', 'chess')
            ->assertJsonPath('data.0.user_count', 2)
            ->assertJsonPath('data.1.interest', 'reading')
            ->assertJsonPath('data.1.user_count', 2)
            ->assertJsonMissingPath('data.0.users.0.password');

        $pageTwo = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/aggregations/interests?per_page=2&page=2');

        $pageTwo->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.interest', 'music')
            ->assertJsonPath('data.0.user_count', 1);
    }

    public function test_non_admin_cannot_access_interest_aggregation(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $token = auth('api')->login($user);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/aggregations/interests')
            ->assertForbidden();
    }

    public function test_user_posts_lookup_aggregation_returns_user_and_paginated_posts(): void
    {
        $user = User::factory()->create(['name' => 'Lookup Author']);
        $otherUser = User::factory()->create();

        Post::factory()->count(3)->create(['user_id' => $user->_id]);
        Post::factory()->count(2)->create(['user_id' => $otherUser->_id]);

        $response = $this->getJson('/api/v1/users/'.$user->_id.'/posts?per_page=2&page=1');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', (string) $user->_id)
            ->assertJsonPath('data.user.name', 'Lookup Author')
            ->assertJsonMissingPath('data.user.password')
            ->assertJsonCount(2, 'data.posts')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_user_posts_lookup_handles_user_without_posts_and_missing_user(): void
    {
        $user = User::factory()->create(['name' => 'Silent User']);

        $this->getJson('/api/v1/users/'.$user->_id.'/posts')
            ->assertOk()
            ->assertJsonPath('data.user.id', (string) $user->_id)
            ->assertJsonCount(0, 'data.posts')
            ->assertJsonPath('meta.total', 0)
            ->assertJsonPath('meta.last_page', 1);

        $this->getJson('/api/v1/users/507f1f77bcf86cd799439011/posts')
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }
}
