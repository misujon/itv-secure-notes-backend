<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return ApiResponse::success([
            'token' => $result['token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
            'user' => UserResource::make($result['user'])->resolve(),
        ], 'User registered successfully.', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return ApiResponse::success([
            'token' => $result['token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
            'user' => UserResource::make($result['user'])->resolve(),
        ], 'Logged in successfully.');
    }

    public function me(): JsonResponse
    {
        $user = $this->authService->me();

        return ApiResponse::success(
            UserResource::make($user)->resolve(),
            'Authenticated user retrieved successfully.'
        );
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->authService->updateProfile(
            $this->authService->me(),
            $request->validated()
        );

        return ApiResponse::success(
            UserResource::make($user)->resolve(),
            'Profile updated successfully.'
        );
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $this->authService->updatePassword(
            $this->authService->me(),
            $validated['current_password'],
            $validated['password']
        );

        return ApiResponse::success(null, 'Password updated successfully.');
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return ApiResponse::success(null, 'Logged out successfully.');
    }

    public function refresh(): JsonResponse
    {
        $result = $this->authService->refresh();

        return ApiResponse::success([
            'token' => $result['token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
            'user' => UserResource::make($result['user'])->resolve(),
        ], 'Token refreshed successfully.');
    }
}
