<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    /**
     * Paginate active users for the REST API.
     */
    public function listUsers(int $perPage = 20): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage);
    }

    /**
     * Paginate users for the admin portal with optional search, role filter, and trash toggle.
     */
    public function listUsersForAdmin(?string $search = null, ?string $role = null, int $perPage = 15, bool $onlyTrashed = false): LengthAwarePaginator
    {
        return $this->userRepository->paginateForAdmin($search, $role, $perPage, $onlyTrashed);
    }

    /**
     * Restore a soft-deleted user account by ID.
     */
    public function restoreUser(string $id): void
    {
        if (! $this->userRepository->restoreById($id)) {
            throw (new ModelNotFoundException)->setModel(User::class, [$id]);
        }
    }

    /**
     * Fetch a user by ID or throw ModelNotFoundException.
     */
    public function getUserById(string $id): User
    {
        $user = $this->userRepository->findById($id);

        if ($user === null) {
            throw (new ModelNotFoundException)->setModel(User::class, [$id]);
        }

        return $user;
    }

    /**
     * Create a new user account as an administrator with explicit role assignment.
     *
     * @param  array{name: string, email: string, password: string, role: string, interests?: array<int, string>}  $data
     */
    public function createUser(array $data): User
    {
        $email = strtolower(trim($data['email']));

        if ($this->userRepository->findByEmail($email) !== null) {
            throw new BusinessException('Email is already registered.', 422);
        }

        return $this->userRepository->create([
            'name' => trim($data['name']),
            'email' => $email,
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'roles' => [$data['role']],
            'permissions' => [],
            'interests' => User::normalizeInterests($data['interests'] ?? []),
        ]);
    }

    /**
     * Update a user's details or role as an administrator, preventing demotion of the last admin.
     *
     * @param  array{name?: string, email?: string, password?: string, role?: string, interests?: array<int, string>}  $data
     */
    public function updateUser(string $id, array $data): User
    {
        $user = $this->getUserById($id);
        $attributes = [];

        if (array_key_exists('name', $data)) {
            $attributes['name'] = trim($data['name']);
        }

        if (array_key_exists('email', $data)) {
            $email = strtolower(trim($data['email']));
            $existing = $this->userRepository->findByEmail($email);

            if ($existing !== null && (string) $existing->_id !== (string) $user->_id) {
                throw new BusinessException('Email is already taken.', 422);
            }

            $attributes['email'] = $email;
        }

        if (array_key_exists('password', $data)) {
            $attributes['password'] = Hash::make($data['password']);
        }

        if (array_key_exists('role', $data)) {
            if ($user->isAdmin() && $data['role'] !== User::ROLE_ADMIN && $this->userRepository->countAdmins() <= 1) {
                throw new BusinessException('Cannot demote the last admin account.', 422);
            }

            $attributes['role'] = $data['role'];
            $attributes['roles'] = [$data['role']];
        }

        if (array_key_exists('interests', $data)) {
            $attributes['interests'] = User::normalizeInterests($data['interests']);
        }

        return $this->userRepository->update($user, $attributes);
    }

    /**
     * Soft-delete a user account, preventing deletion of the last remaining admin.
     */
    public function deleteUser(string $id): void
    {
        $user = $this->getUserById($id);

        if ($user->isAdmin() && $this->userRepository->countAdmins() <= 1) {
            throw new BusinessException('Cannot delete the last admin account.', 422);
        }

        $this->userRepository->delete($user);
    }

    /**
     * Update the authenticated user's own profile without allowing role changes.
     *
     * @param  array{name?: string, email?: string, password?: string|null, interests?: array<int, string>}  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        $attributes = [];

        if (array_key_exists('name', $data)) {
            $attributes['name'] = trim($data['name']);
        }

        if (array_key_exists('email', $data)) {
            $email = strtolower(trim($data['email']));
            $existing = $this->userRepository->findByEmail($email);

            if ($existing !== null && (string) $existing->_id !== (string) $user->_id) {
                throw new BusinessException('Email is already taken.', 422);
            }

            $attributes['email'] = $email;
        }

        if (isset($data['password']) && $data['password'] !== '') {
            $attributes['password'] = Hash::make($data['password']);
        }

        if (array_key_exists('interests', $data)) {
            $attributes['interests'] = User::normalizeInterests($data['interests']);
        }

        return $this->userRepository->update($user, $attributes);
    }
}
