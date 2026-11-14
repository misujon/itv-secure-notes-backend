<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\User;
use App\Services\AggregationService;
use App\Services\PostService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        private readonly PostService $postService,
        private readonly AggregationService $aggregationService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
        $posts = $this->postService->listPosts($perPage);

        return ApiResponse::paginated($posts, PostResource::class);
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $post = $this->postService->createForUser($user, $request->validated());

        return ApiResponse::success(
            PostResource::make($post)->resolve(),
            'Post created successfully.',
            201
        );
    }

    public function show(string $id): JsonResponse
    {
        $post = $this->postService->getPostById($id);

        return ApiResponse::success(
            PostResource::make($post)->resolve(),
            'Post retrieved successfully.'
        );
    }

    public function userPosts(Request $request, string $id): JsonResponse
    {
        $page = max((int) $request->integer('page', 1), 1);
        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);

        $result = $this->aggregationService->getUserPosts($id, $page, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'User posts retrieved successfully.',
            'data' => [
                'user' => $result['user'],
                'posts' => $result['posts'],
            ],
            'meta' => $result['meta'],
        ], 200);
    }
}
