<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Note;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

    public function paginateAll(int $perPage = 20): LengthAwarePaginator
    {
        return Note::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('_id')
            ->paginate($perPage);
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
}
