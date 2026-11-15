<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Repositories\PostRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

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

    /**
     * @param  array{title?: string, content?: string}  $data
     */
    public function updateForUser(User $user, string $id, array $data): Post
    {
        $post = $this->getPostById($id);
        Gate::forUser($user)->authorize('update', $post);

        $attributes = [];

        if (array_key_exists('title', $data)) {
            $attributes['title'] = trim($data['title']);
        }

        if (array_key_exists('content', $data)) {
            $attributes['content'] = $data['content'];
        }

        return $this->postRepository->update($post, $attributes);
    }

    public function deleteForUser(User $user, string $id): void
    {
        $post = $this->getPostById($id);
        Gate::forUser($user)->authorize('delete', $post);

        $this->postRepository->delete($post);
    }
}
