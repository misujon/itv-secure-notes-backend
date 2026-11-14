<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
        $users = $this->userService->listUsers($perPage);

        return ApiResponse::paginated($users, UserResource::class);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return ApiResponse::success(
            UserResource::make($user)->resolve(),
            'User created successfully.',
            201
        );
    }

    public function show(string $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        return ApiResponse::success(
            UserResource::make($user)->resolve(),
            'User retrieved successfully.'
        );
    }

    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $user = $this->userService->updateUser($id, $request->validated());

        return ApiResponse::success(
            UserResource::make($user)->resolve(),
            'User updated successfully.'
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $this->userService->deleteUser($id);

        return ApiResponse::success(null, 'User deleted successfully.');
    }
}
