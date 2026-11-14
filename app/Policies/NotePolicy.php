<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Note;
use App\Models\User;

class NotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::NotesViewAny);
    }

    public function view(User $user, Note $note): bool
    {
        if ($user->hasPermission(Permission::NotesViewAny)) {
            return true;
        }

        return $user->hasPermission(Permission::NotesViewOwn)
            && (string) $note->user_id === (string) $user->_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::NotesCreate);
    }

    public function update(User $user, Note $note): bool
    {
        return $user->hasPermission(Permission::NotesUpdateOwn)
            && (string) $note->user_id === (string) $user->_id;
    }

    public function delete(User $user, Note $note): bool
    {
        return $user->hasPermission(Permission::NotesDeleteOwn)
            && (string) $note->user_id === (string) $user->_id;
    }
}
