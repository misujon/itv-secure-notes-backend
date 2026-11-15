<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;

class PostRepository
{
    public function paginateAll(int $perPage = 20, bool $onlyTrashed = false): LengthAwarePaginator
    {
        $query = Post::query()->with('user');

        if ($onlyTrashed) {
            $query->onlyTrashed();
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('_id')
            ->paginate($perPage);
    }

    public function restoreById(string $id): bool
    {
        $post = Post::onlyTrashed()->where('_id', new ObjectId($id))->first();

        return $post !== null && (bool) $post->restore();
    }

    public function findById(string $id): ?Post
    {
        return Post::query()
            ->with('user')
            ->where('_id', new ObjectId($id))
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Post
    {
        return Post::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Post $post, array $data): Post
    {
        $post->fill($data);
        $post->save();

        return $post->fresh(['user']) ?? $post;
    }

    public function delete(Post $post): bool
    {
        return (bool) $post->delete();
    }

    public function countAll(): int
    {
        return Post::query()->count();
    }

    public function countForUser(string $userId): int
    {
        return Post::query()->where('user_id', new ObjectId($userId))->count();
    }

    /**
     * Retrieve a user and their paginated posts using a single $lookup aggregation pipeline.
     *
     * @return array{user: array<string, mixed>, posts: array<int, array<string, mixed>>, total: int}|null
     */
    public function getUserPostsWithLookup(string $userId, int $page = 1, int $perPage = 20): ?array
    {
        $skip = ($page - 1) * $perPage;

        $pipeline = [
            ['$match' => ['_id' => new ObjectId($userId), 'deleted_at' => null]],
            [
                '$lookup' => [
                    'from' => 'posts',
                    'localField' => '_id',
                    'foreignField' => 'user_id',
                    'pipeline' => [
                        ['$match' => ['deleted_at' => null]],
                        ['$sort' => ['created_at' => -1, '_id' => -1]],
                        [
                            '$facet' => [
                                'metadata' => [['$count' => 'total']],
                                'data' => [
                                    ['$skip' => $skip],
                                    ['$limit' => $perPage],
                                    [
                                        '$project' => [
                                            '_id' => 0,
                                            'id' => ['$toString' => '$_id'],
                                            'user_id' => ['$toString' => '$user_id'],
                                            'title' => 1,
                                            'content' => 1,
                                            'created_at' => [
                                                '$dateToString' => [
                                                    'format' => '%Y-%m-%dT%H:%M:%S.%LZ',
                                                    'date' => '$created_at',
                                                ],
                                            ],
                                            'updated_at' => [
                                                '$dateToString' => [
                                                    'format' => '%Y-%m-%dT%H:%M:%S.%LZ',
                                                    'date' => '$updated_at',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'as' => 'posts_facet',
                ],
            ],
            [
                '$project' => [
                    '_id' => 0,
                    'user' => [
                        'id' => ['$toString' => '$_id'],
                        'name' => '$name',
                        'email' => '$email',
                        'role' => '$role',
                        'interests' => ['$ifNull' => ['$interests', []]],
                    ],
                    'posts' => [
                        '$ifNull' => [
                            ['$arrayElemAt' => ['$posts_facet.data', 0]],
                            [],
                        ],
                    ],
                    'total' => [
                        '$ifNull' => [
                            ['$arrayElemAt' => [['$arrayElemAt' => ['$posts_facet.metadata.total', 0]], 0]],
                            0,
                        ],
                    ],
                ],
            ],
        ];

        /** @var array<int, array<string, mixed>> $result */
        $result = DB::connection('mongodb')
            ->getCollection('users')
            ->aggregate($pipeline, [
                'typeMap' => [
                    'root' => 'array',
                    'document' => 'array',
                    'array' => 'array',
                ],
            ])
            ->toArray();

        if ($result === []) {
            return null;
        }

        return [
            'user' => $result[0]['user'],
            'posts' => $result[0]['posts'] ?? [],
            'total' => (int) ($result[0]['total'] ?? 0),
        ];
    }
}
