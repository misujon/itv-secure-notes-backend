<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Note;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;
use Tests\TestCase;

class IndexVerificationTest extends TestCase
{
    public function test_expected_indexes_exist_without_extra_indexes(): void
    {
        $database = DB::connection('mongodb')->getDatabase();

        $userIndexes = array_map(
            fn ($index): string => (string) $index['name'],
            iterator_to_array($database->selectCollection('users')->listIndexes())
        );
        $noteIndexes = array_map(
            fn ($index): string => (string) $index['name'],
            iterator_to_array($database->selectCollection('notes')->listIndexes())
        );
        $postIndexes = array_map(
            fn ($index): string => (string) $index['name'],
            iterator_to_array($database->selectCollection('posts')->listIndexes())
        );

        $this->assertEqualsCanonicalizing(
            ['_id_', 'users_email_unique', 'users_deleted_created_id_idx', 'users_deleted_interests_idx'],
            $userIndexes
        );
        $this->assertEqualsCanonicalizing(
            ['_id_', 'notes_user_created_id_idx', 'notes_deleted_created_id_idx'],
            $noteIndexes
        );
        $this->assertEqualsCanonicalizing(
            ['_id_', 'posts_user_created_id_idx', 'posts_deleted_created_id_idx'],
            $postIndexes
        );
    }

    public function test_notes_and_posts_queries_use_compound_indexes(): void
    {
        $user = User::factory()->create();
        Note::factory()->create(['user_id' => $user->_id]);
        Post::factory()->create(['user_id' => $user->_id]);

        $database = DB::connection('mongodb')->getDatabase();
        $userObjectId = new ObjectId((string) $user->_id);

        $notesExplain = $database->command([
            'explain' => [
                'find' => 'notes',
                'filter' => ['user_id' => $userObjectId],
                'sort' => ['created_at' => -1, '_id' => -1],
            ],
            'verbosity' => 'queryPlanner',
        ])->toArray()[0];

        $notesPlan = json_encode($notesExplain);
        $this->assertIsString($notesPlan);
        $this->assertStringContainsString('IXSCAN', $notesPlan);
        $this->assertStringContainsString('notes_user_created_id_idx', $notesPlan);

        $postsExplain = $database->command([
            'explain' => [
                'find' => 'posts',
                'filter' => ['user_id' => $userObjectId],
                'sort' => ['created_at' => -1, '_id' => -1],
            ],
            'verbosity' => 'queryPlanner',
        ])->toArray()[0];

        $postsPlan = json_encode($postsExplain);
        $this->assertIsString($postsPlan);
        $this->assertStringContainsString('IXSCAN', $postsPlan);
        $this->assertStringContainsString('posts_user_created_id_idx', $postsPlan);
    }
}
