<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Tests\TestCase;

class NoteTest extends TestCase
{
    public function test_user_can_create_and_retrieve_own_note(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $token = auth('api')->login($user);

        $createResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/notes', [
                'title' => 'My Private Note',
                'content' => 'Secret content here.',
                'user_id' => (string) $otherUser->_id,
            ]);

        $createResponse->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'My Private Note')
            ->assertJsonPath('data.user_id', (string) $user->_id);

        $noteId = $createResponse->json('data.id');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/notes/'.$noteId)
            ->assertOk()
            ->assertJsonPath('data.id', $noteId)
            ->assertJsonPath('data.title', 'My Private Note');
    }

    public function test_user_can_list_own_notes_with_pagination(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Note::factory()->count(5)->create(['user_id' => $user->_id]);
        Note::factory()->count(3)->create(['user_id' => $otherUser->_id]);

        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/notes?per_page=2&page=1');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 5)
            ->assertJsonPath('meta.last_page', 3);
    }

    public function test_user_can_update_and_delete_own_note(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $user->_id]);
        $token = auth('api')->login($user);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/notes/'.$note->_id, [
                'title' => 'Updated Title',
            ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated Title');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/notes/'.$note->_id)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNull(Note::query()->find($note->_id));
    }

    public function test_user_cannot_read_update_or_delete_another_users_note(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $owner->_id]);

        $token = auth('api')->login($intruder);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/notes/'.$note->_id)
            ->assertForbidden();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/notes/'.$note->_id, ['title' => 'Hacked'])
            ->assertForbidden();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/notes/'.$note->_id)
            ->assertForbidden();
    }

    public function test_invalid_object_id_returns_422(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/notes/not-an-object-id')
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }
}
