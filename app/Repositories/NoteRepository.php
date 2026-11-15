<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Note;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use MongoDB\BSON\ObjectId;

class NoteRepository
{
    public function paginateForUser(string $userId, int $perPage = 20): LengthAwarePaginator
    {
        return Note::query()
            ->where('user_id', new ObjectId($userId))
            ->orderByDesc('created_at')
            ->orderByDesc('_id')
            ->paginate($perPage);
    }

    public function paginateAll(int $perPage = 20, bool $onlyTrashed = false): LengthAwarePaginator
    {
        $query = Note::query()->with('user');

        if ($onlyTrashed) {
            $query->onlyTrashed();
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('_id')
            ->paginate($perPage);
    }

    public function restoreById(string $id): bool
    {
        $note = Note::onlyTrashed()->where('_id', new ObjectId($id))->first();

        return $note !== null && (bool) $note->restore();
    }

    public function findById(string $id, bool $withUser = false): ?Note
    {
        $query = Note::query()->where('_id', new ObjectId($id));

        if ($withUser) {
            $query->with('user');
        }

        return $query->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Note
    {
        return Note::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Note $note, array $data): Note
    {
        $note->fill($data);
        $note->save();

        return $note->fresh() ?? $note;
    }

    public function delete(Note $note): bool
    {
        return (bool) $note->delete();
    }

    public function countAll(): int
    {
        return Note::query()->count();
    }

    public function countForUser(string $userId): int
    {
        return Note::query()->where('user_id', new ObjectId($userId))->count();
    }

    /**
     * @return Collection<int, Note>
     */
    public function recent(int $limit = 5): Collection
    {
        return Note::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('_id')
            ->limit($limit)
            ->get();
    }
}
