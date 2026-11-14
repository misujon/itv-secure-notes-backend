<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

class NotePolicy
{
    public function view(User $user, Note $note): bool
    {
        return $user->isAdmin() || (string) $note->user_id === (string) $user->_id;
    }

    public function update(User $user, Note $note): bool
    {
        return (string) $note->user_id === (string) $user->_id;
    }

    public function delete(User $user, Note $note): bool
    {
        return (string) $note->user_id === (string) $user->_id;
    }
}
