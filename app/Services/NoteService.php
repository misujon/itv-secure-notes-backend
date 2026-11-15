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

    /**
     * Paginate active notes belonging to the given user.
     */
    public function listForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $this->noteRepository->paginateForUser((string) $user->_id, $perPage);
    }

    /**
     * Paginate all notes (or trashed notes) for the admin portal.
     */
    public function listAllForAdmin(int $perPage = 20, bool $onlyTrashed = false): LengthAwarePaginator
    {
        return $this->noteRepository->paginateAll($perPage, $onlyTrashed);
    }

    /**
     * Create a new private note owned by the given user.
     *
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

    /**
     * Fetch a single note and authorize ownership/admin access via policy.
     */
    public function getForUser(User $user, string $id): Note
    {
        $note = $this->findOrFail($id);
        Gate::forUser($user)->authorize('view', $note);

        return $note;
    }

    /**
     * Fetch any note with its author relation for the admin portal.
     */
    public function getForAdmin(string $id): Note
    {
        $note = $this->noteRepository->findById($id, withUser: true);

        if ($note === null) {
            throw (new ModelNotFoundException)->setModel(Note::class, [$id]);
        }

        return $note;
    }

    /**
     * Update a note after verifying user ownership via policy.
     *
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

    /**
     * Update any note as an administrator without user-ownership restriction.
     *
     * @param  array{title?: string, content?: string}  $data
     */
    public function updateForAdmin(string $id, array $data): Note
    {
        $note = $this->findOrFail($id);

        $attributes = [];

        if (array_key_exists('title', $data)) {
            $attributes['title'] = trim($data['title']);
        }

        if (array_key_exists('content', $data)) {
            $attributes['content'] = $data['content'];
        }

        return $this->noteRepository->update($note, $attributes);
    }

    /**
     * Soft-delete a note after verifying user ownership via policy.
     */
    public function deleteForUser(User $user, string $id): void
    {
        $note = $this->findOrFail($id);
        Gate::forUser($user)->authorize('delete', $note);

        $this->noteRepository->delete($note);
    }

    /**
     * Soft-delete any note as an administrator.
     */
    public function deleteForAdmin(string $id): void
    {
        $note = $this->findOrFail($id);
        $this->noteRepository->delete($note);
    }

    /**
     * Restore a soft-deleted note by ID from the admin trash.
     */
    public function restoreForAdmin(string $id): void
    {
        if (! $this->noteRepository->restoreById($id)) {
            throw (new ModelNotFoundException)->setModel(Note::class, [$id]);
        }
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
