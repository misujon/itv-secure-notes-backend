<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\User;
use App\Services\PostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct(
        private readonly PostService $postService,
    ) {}

    public function index(Request $request): View
    {
        $onlyTrashed = $request->boolean('trashed');
        $posts = $this->postService->listPosts(15, $onlyTrashed);

        return view('admin.posts.index', [
            'posts' => $posts,
            'onlyTrashed' => $onlyTrashed,
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.create');
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $post = $this->postService->createForUser($user, $request->validated());

        return redirect()
            ->route('admin.posts.show', (string) $post->_id)
            ->with('status', 'Post published successfully.');
    }

    public function show(string $post): View
    {
        $record = $this->postService->getPostById($post);

        return view('admin.posts.show', [
            'post' => $record,
        ]);
    }

    public function edit(string $post): View
    {
        $record = $this->postService->getPostById($post);

        return view('admin.posts.edit', [
            'post' => $record,
        ]);
    }

    public function update(UpdatePostRequest $request, string $post): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->postService->updateForUser($user, $post, $request->validated());

        return redirect()
            ->route('admin.posts.show', $post)
            ->with('status', 'Post updated successfully.');
    }

    public function destroy(Request $request, string $post): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->postService->deleteForUser($user, $post);

        return redirect()
            ->route('admin.posts.index')
            ->with('status', 'Post moved to trash.');
    }

    public function restore(string $post): RedirectResponse
    {
        $this->postService->restoreForAdmin($post);

        return redirect()
            ->route('admin.posts.index', ['trashed' => 1])
            ->with('status', 'Post restored successfully.');
    }
}
