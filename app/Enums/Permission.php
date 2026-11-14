<?php

declare(strict_types=1);

namespace App\Enums;

enum Permission: string
{
    case NotesViewOwn = 'notes.view-own';
    case NotesCreate = 'notes.create';
    case NotesUpdateOwn = 'notes.update-own';
    case NotesDeleteOwn = 'notes.delete-own';
    case PostsViewPublic = 'posts.view-public';
    case PostsCreateOwn = 'posts.create-own';
    case ProfileViewOwn = 'profile.view-own';
    case ProfileUpdateOwn = 'profile.update-own';

    case UsersViewAny = 'users.view-any';
    case UsersView = 'users.view';
    case UsersCreate = 'users.create';
    case UsersUpdate = 'users.update';
    case UsersDelete = 'users.delete';
    case NotesViewAny = 'notes.view-any';
    case InterestsViewGroups = 'interests.view-groups';
}
