<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;

class UserRepository
{
    public function findById(string $id): ?User
    {
        return User::query()->where('_id', new ObjectId($id))->first();
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', strtolower(trim($email)))->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User
    {
        $user->fill($data);
        $user->save();

        return $user->fresh() ?? $user;
    }

    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }

    public function restoreById(string $id): bool
    {
        $user = User::onlyTrashed()->where('_id', new ObjectId($id))->first();

        return $user !== null && (bool) $user->restore();
    }

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return User::query()
            ->orderByDesc('created_at')
            ->orderByDesc('_id')
            ->paginate($perPage);
    }

    public function countAdmins(): int
    {
        return User::query()->where('role', User::ROLE_ADMIN)->count();
    }

    public function countAll(): int
    {
        return User::query()->count();
    }

    /**
     * @return Collection<int, User>
     */
    public function recent(int $limit = 5): Collection
    {
        return User::query()
            ->orderByDesc('created_at')
            ->orderByDesc('_id')
            ->limit($limit)
            ->get();
    }

    public function paginateForAdmin(?string $search = null, ?string $role = null, int $perPage = 15, bool $onlyTrashed = false): LengthAwarePaginator
    {
        $query = User::query();

        if ($onlyTrashed) {
            $query->onlyTrashed();
        }

        if ($role !== null && $role !== '') {
            $query->where('role', $role);
        }

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($subQuery) use ($term): void {
                $subQuery->where('name', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%');
            });
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('_id')
            ->paginate($perPage);
    }

    /**
     * Group users by their normalized interests using a single native MongoDB aggregation pipeline.
     *
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function groupUsersByInterests(int $page = 1, int $perPage = 20): array
    {
        $skip = ($page - 1) * $perPage;

        $pipeline = [
            ['$match' => ['deleted_at' => null, 'interests' => ['$exists' => true, '$ne' => []]]],
            ['$unwind' => '$interests'],
            [
                '$group' => [
                    '_id' => '$interests',
                    'user_count' => ['$sum' => 1],
                    'users' => [
                        '$push' => [
                            'id' => ['$toString' => '$_id'],
                            'name' => '$name',
                            'email' => '$email',
                            'role' => '$role',
                        ],
                    ],
                ],
            ],
            [
                '$project' => [
                    '_id' => 0,
                    'interest' => '$_id',
                    'user_count' => 1,
                    'users' => 1,
                ],
            ],
            ['$sort' => ['user_count' => -1, 'interest' => 1]],
            [
                '$facet' => [
                    'metadata' => [['$count' => 'total']],
                    'data' => [
                        ['$skip' => $skip],
                        ['$limit' => $perPage],
                    ],
                ],
            ],
        ];

        /** @var array<int, array<string, mixed>> $result */
        $result = DB::connection('mongodb')
            ->getCollection('users')
            ->aggregate($pipeline, [
                'typeMap' => [
                    'root' => 'array',
                    'document' => 'array',
                    'array' => 'array',
                ],
            ])
            ->toArray();

        $facet = $result[0] ?? ['metadata' => [], 'data' => []];
        $total = (int) ($facet['metadata'][0]['total'] ?? 0);

        return [
            'items' => $facet['data'] ?? [],
            'total' => $total,
        ];
    }
}
