<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    /**
     * Register a new regular user account, normalize interests, and issue a JWT token.
     *
     * @param  array{name: string, email: string, password: string, interests?: array<int, string>}  $data
     * @return array{token: string, token_type: string, expires_in: int, user: User}
     */
    public function register(array $data): array
    {
        $email = strtolower(trim($data['email']));

        if ($this->userRepository->findByEmail($email) !== null) {
            throw new BusinessException('Email is already registered.', 422);
        }

        $user = $this->userRepository->create([
            'name' => trim($data['name']),
            'email' => $email,
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_USER,
            'roles' => [User::ROLE_USER],
            'permissions' => [],
            'interests' => User::normalizeInterests($data['interests'] ?? []),
        ]);

        $token = $this->guard()->login($user);

        return $this->tokenPayload((string) $token, $user);
    }

    /**
     * Authenticate a user by email and password and issue a JWT token.
     *
     * @param  array{email: string, password: string}  $credentials
     * @return array{token: string, token_type: string, expires_in: int, user: User}
     */
    public function login(array $credentials): array
    {
        $email = strtolower(trim($credentials['email']));
        $user = $this->userRepository->findByEmail($email);

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            throw new BusinessException('Invalid email or password.', 401);
        }

        $token = $this->guard()->login($user);

        return $this->tokenPayload((string) $token, $user);
    }

    /**
     * Return the currently authenticated JWT user or throw 401.
     */
    public function me(): User
    {
        /** @var User|null $user */
        $user = $this->guard()->user();

        if ($user === null) {
            throw new BusinessException('Unauthenticated.', 401);
        }

        return $user;
    }

    /**
     * Invalidate the current JWT token.
     */
    public function logout(): void
    {
        $this->guard()->logout();
    }

    /**
     * Refresh the current JWT token and return a new token payload.
     *
     * @return array{token: string, token_type: string, expires_in: int, user: User}
     */
    public function refresh(): array
    {
        $token = $this->guard()->refresh();

        return $this->tokenPayload((string) $token, $this->me());
    }

    /**
     * Update the authenticated user's profile (name, email, interests) without allowing role changes.
     *
     * @param  array{name: string, email: string, interests?: array<int, string>}  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        $email = strtolower(trim($data['email']));
        $existing = $this->userRepository->findByEmail($email);

        if ($existing !== null && (string) $existing->_id !== (string) $user->_id) {
            throw ValidationException::withMessages([
                'email' => ['The email has already been taken.'],
            ]);
        }

        $attributes = [
            'name' => trim($data['name']),
            'email' => $email,
        ];

        if (array_key_exists('interests', $data)) {
            $attributes['interests'] = User::normalizeInterests($data['interests'] ?? []);
        }

        return $this->userRepository->update($user, $attributes);
    }

    /**
     * Verify the user's current password and update it to a new hashed password.
     */
    public function updatePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $this->userRepository->update($user, [
            'password' => Hash::make($newPassword),
        ]);
    }

    /**
     * @return array{token: string, token_type: string, expires_in: int, user: User}
     */
    private function tokenPayload(string $token, User $user): array
    {
        return [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60,
            'user' => $user,
        ];
    }

    private function guard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return $guard;
    }
}
