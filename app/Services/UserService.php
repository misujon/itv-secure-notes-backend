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

    public function listUsers(int $perPage = 20): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage);
    }

    public function listUsersForAdmin(?string $search = null, ?string $role = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginateForAdmin($search, $role, $perPage);
    }

    public function getUserById(string $id): User
    {
        $user = $this->userRepository->findById($id);

        if ($user === null) {
            throw (new ModelNotFoundException)->setModel(User::class, [$id]);
        }

        return $user;
    }

    /**
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
            'interests' => User::normalizeInterests($data['interests'] ?? []),
        ]);
    }

    /**
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
        }

        if (array_key_exists('interests', $data)) {
            $attributes['interests'] = User::normalizeInterests($data['interests']);
        }

        return $this->userRepository->update($user, $attributes);
    }

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
