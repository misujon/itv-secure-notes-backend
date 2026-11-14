<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Repositories\PostRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PostService
{
    public function __construct(
        private readonly PostRepository $postRepository,
    ) {}

    public function listPosts(int $perPage = 20): LengthAwarePaginator
    {
        return $this->postRepository->paginateAll($perPage);
    }

    /**
     * @param  array{title: string, content: string}  $data
     */
    public function createForUser(User $user, array $data): Post
    {
        return $this->postRepository->create([
            'user_id' => $user->_id,
            'title' => trim($data['title']),
            'content' => $data['content'],
        ]);
    }

    public function getPostById(string $id): Post
    {
        $post = $this->postRepository->findById($id);

        if ($post === null) {
            throw (new ModelNotFoundException)->setModel(Post::class, [$id]);
        }

        return $post;
    }
}
