<?php

declare(strict_types=1);

namespace App\Auth;

use App\Enums\Permission;
use App\Enums\UserRole;

final class RolePermissionRegistry
{
    /**
     * @var list<Permission>
     */
    private const USER_PERMISSIONS = [
        Permission::NotesViewOwn,
        Permission::NotesCreate,
        Permission::NotesUpdateOwn,
        Permission::NotesDeleteOwn,
        Permission::PostsViewPublic,
        Permission::PostsCreateOwn,
        Permission::ProfileViewOwn,
        Permission::ProfileUpdateOwn,
    ];

    /**
     * @var list<Permission>
     */
    private const ADMIN_ONLY_PERMISSIONS = [
        Permission::UsersViewAny,
        Permission::UsersView,
        Permission::UsersCreate,
        Permission::UsersUpdate,
        Permission::UsersDelete,
        Permission::NotesViewAny,
        Permission::InterestsViewGroups,
    ];

    /**
     * @return list<Permission>
     */
    public static function permissionsFor(UserRole $role): array
    {
        return match ($role) {
            UserRole::User => self::USER_PERMISSIONS,
            UserRole::Admin => [...self::USER_PERMISSIONS, ...self::ADMIN_ONLY_PERMISSIONS],
        };
    }

    public static function hasPermission(UserRole $role, Permission $permission): bool
    {
        return in_array($permission, self::permissionsFor($role), true);
    }
}
