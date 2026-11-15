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

    /**
     * Paginate public posts (or soft-deleted posts for admin trash).
     */
    public function listPosts(int $perPage = 20, bool $onlyTrashed = false): LengthAwarePaginator
    {
        return $this->postRepository->paginateAll($perPage, $onlyTrashed);
    }

    /**
     * Create a new public post authored by the given user.
     *
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

    /**
     * Fetch a public post by ID or throw ModelNotFoundException.
     */
    public function getPostById(string $id): Post
    {
        $post = $this->postRepository->findById($id);

        if ($post === null) {
            throw (new ModelNotFoundException)->setModel(Post::class, [$id]);
        }

        return $post;
    }

    /**
     * Update a post after verifying author/admin authorization via policy.
     *
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

    /**
     * Soft-delete a post after verifying author/admin authorization via policy.
     */
    public function deleteForUser(User $user, string $id): void
    {
        $post = $this->getPostById($id);
        Gate::forUser($user)->authorize('delete', $post);

        $this->postRepository->delete($post);
    }

    /**
     * Restore a soft-deleted post by ID from the admin trash.
     */
    public function restoreForAdmin(string $id): void
    {
        if (! $this->postRepository->restoreById($id)) {
            throw (new ModelNotFoundException)->setModel(Post::class, [$id]);
        }
    }
}
