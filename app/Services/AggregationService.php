<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AggregationService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PostRepository $postRepository,
    ) {}

    /**
     * Group users by their profile interests using a single MongoDB aggregation pipeline.
     *
     * @return array{data: array<int, array<string, mixed>>, meta: array{current_page: int, per_page: int, total: int, last_page: int}}
     */
    public function groupUsersByInterests(int $page = 1, int $perPage = 20): array
    {
        $result = $this->userRepository->groupUsersByInterests($page, $perPage);
        $total = $result['total'];

        return [
            'data' => $result['items'],
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
        ];
    }

    /**
     * Retrieve paginated posts for a specific user via a MongoDB $lookup aggregation pipeline.
     *
     * @return array{user: array<string, mixed>, posts: array<int, array<string, mixed>>, meta: array{current_page: int, per_page: int, total: int, last_page: int}}
     */
    public function getUserPosts(string $userId, int $page = 1, int $perPage = 20): array
    {
        $result = $this->postRepository->getUserPostsWithLookup($userId, $page, $perPage);

        if ($result === null) {
            throw (new ModelNotFoundException)->setModel(User::class, [$userId]);
        }

        $total = $result['total'];

        return [
            'user' => $result['user'],
            'posts' => $result['posts'],
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
        ];
    }
}
