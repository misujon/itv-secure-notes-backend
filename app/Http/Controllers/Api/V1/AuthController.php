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

    /**
     * Register a new user account and return a JWT token payload.
     */
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

    /**
     * Authenticate a user with email and password and return a JWT token payload.
     */
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

    /**
     * Retrieve the currently authenticated user's profile.
     */
    public function me(): JsonResponse
    {
        $user = $this->authService->me();

        return ApiResponse::success(
            UserResource::make($user)->resolve(),
            'Authenticated user retrieved successfully.'
        );
    }

    /**
     * Update the authenticated user's name, email, and interests.
     */
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

    /**
     * Change the authenticated user's password after verifying the current password.
     */
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

    /**
     * Invalidate the authenticated user's current JWT token.
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return ApiResponse::success(null, 'Logged out successfully.');
    }

    /**
     * Issue a refreshed JWT token for the authenticated user.
     */
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
