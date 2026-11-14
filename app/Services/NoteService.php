<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Note;
use App\Models\User;
use App\Repositories\NoteRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

class NoteService
{
    public function __construct(
        private readonly NoteRepository $noteRepository,
    ) {}

    public function listForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $this->noteRepository->paginateForUser((string) $user->_id, $perPage);
    }

    public function listAllForAdmin(int $perPage = 20): LengthAwarePaginator
    {
        return $this->noteRepository->paginateAll($perPage);
    }

    /**
     * @param  array{title: string, content: string}  $data
     */
    public function createForUser(User $user, array $data): Note
    {
        return $this->noteRepository->create([
            'user_id' => $user->_id,
            'title' => trim($data['title']),
            'content' => $data['content'],
        ]);
    }

    public function getForUser(User $user, string $id): Note
    {
        $note = $this->findOrFail($id);
        Gate::forUser($user)->authorize('view', $note);

        return $note;
    }

    public function getForAdmin(string $id): Note
    {
        $note = $this->noteRepository->findById($id, withUser: true);

        if ($note === null) {
            throw (new ModelNotFoundException)->setModel(Note::class, [$id]);
        }

        return $note;
    }

    /**
     * @param  array{title?: string, content?: string}  $data
     */
    public function updateForUser(User $user, string $id, array $data): Note
    {
        $note = $this->findOrFail($id);
        Gate::forUser($user)->authorize('update', $note);

        $attributes = [];

        if (array_key_exists('title', $data)) {
            $attributes['title'] = trim($data['title']);
        }

        if (array_key_exists('content', $data)) {
            $attributes['content'] = $data['content'];
        }

        return $this->noteRepository->update($note, $attributes);
    }

    public function deleteForUser(User $user, string $id): void
    {
        $note = $this->findOrFail($id);
        Gate::forUser($user)->authorize('delete', $note);

        $this->noteRepository->delete($note);
    }

    private function findOrFail(string $id): Note
    {
        $note = $this->noteRepository->findById($id);

        if ($note === null) {
            throw (new ModelNotFoundException)->setModel(Note::class, [$id]);
        }

        return $note;
    }
}
