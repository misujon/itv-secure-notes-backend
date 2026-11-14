<?php

declare(strict_types=1);

namespace App\Traits;

use App\Auth\RolePermissionRegistry;
use App\Enums\Permission as PermissionEnum;
use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Collection;

trait HasMongoRoles
{
    /**
     * @var list<string>|null
     */
    private ?array $cachedAllPermissions = null;

    public function assignRole(string|UserRole|Role ...$roles): static
    {
        $current = $this->getRoleNames()->all();

        foreach ($roles as $role) {
            $name = $this->resolveRoleName($role);
            if (! in_array($name, $current, true)) {
                $current[] = $name;
            }
        }

        $this->roles = array_values($current);

        if ($this->role === null || $this->role === '') {
            $this->role = $current[0] ?? UserRole::User->value;
        }

        $this->save();
        $this->forgetCachedPermissions();

        return $this;
    }

    public function syncRoles(string|UserRole|Role ...$roles): static
    {
        $normalized = [];

        foreach ($roles as $role) {
            $name = $this->resolveRoleName($role);
            if (! in_array($name, $normalized, true)) {
                $normalized[] = $name;
            }
        }

        $this->roles = $normalized;
        $this->role = $normalized[0] ?? UserRole::User->value;
        $this->save();
        $this->forgetCachedPermissions();

        return $this;
    }

    public function removeRole(string|UserRole|Role $role): static
    {
        $name = $this->resolveRoleName($role);
        $remaining = array_values(array_filter(
            $this->getRoleNames()->all(),
            fn (string $item): bool => $item !== $name
        ));

        $this->roles = $remaining;
        if ($this->role === $name) {
            $this->role = $remaining[0] ?? UserRole::User->value;
        }

        $this->save();
        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * @param  string|UserRole|array<int, string|UserRole>  $roles
     */
    public function hasRole(string|UserRole|array $roles): bool
    {
        $targets = is_array($roles) ? $roles : [$roles];
        $assigned = $this->getRoleNames()->all();

        foreach ($targets as $target) {
            if (in_array($this->resolveRoleName($target), $assigned, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  string|UserRole|array<int, string|UserRole>  ...$roles
     */
    public function hasAnyRole(string|UserRole|array ...$roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection<int, string>
     */
    public function getRoleNames(): Collection
    {
        $names = is_array($this->roles) ? $this->roles : [];

        if ($this->role !== null && $this->role !== '' && ! in_array($this->role, $names, true)) {
            $names[] = $this->role;
        }

        return collect(array_values(array_unique(array_filter($names))));
    }

    public function givePermissionTo(string|PermissionEnum|Permission ...$permissions): static
    {
        $current = is_array($this->permissions) ? $this->permissions : [];

        foreach ($permissions as $permission) {
            $name = $this->resolvePermissionName($permission);
            if (! in_array($name, $current, true)) {
                $current[] = $name;
            }
        }

        $this->permissions = array_values($current);
        $this->save();
        $this->forgetCachedPermissions();

        return $this;
    }

    public function revokePermissionTo(string|PermissionEnum|Permission $permission): static
    {
        $name = $this->resolvePermissionName($permission);
        $this->permissions = array_values(array_filter(
            is_array($this->permissions) ? $this->permissions : [],
            fn (string $item): bool => $item !== $name
        ));
        $this->save();
        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * @param  array<int, string|PermissionEnum|Permission>  $permissions
     */
    public function syncPermissions(array $permissions): static
    {
        $normalized = [];

        foreach ($permissions as $permission) {
            $name = $this->resolvePermissionName($permission);
            if (! in_array($name, $normalized, true)) {
                $normalized[] = $name;
            }
        }

        $this->permissions = $normalized;
        $this->save();
        $this->forgetCachedPermissions();

        return $this;
    }

    public function hasPermissionTo(string|PermissionEnum|Permission $permission): bool
    {
        $name = $this->resolvePermissionName($permission);

        return in_array($name, $this->getAllPermissions()->all(), true);
    }

    /**
     * @return Collection<int, string>
     */
    public function getAllPermissions(): Collection
    {
        if ($this->cachedAllPermissions !== null) {
            return collect($this->cachedAllPermissions);
        }

        $direct = is_array($this->permissions) ? $this->permissions : [];
        $roleNames = $this->getRoleNames()->all();
        $fromRoles = [];

        if ($roleNames !== []) {
            $roles = Role::query()->whereIn('name', $roleNames)->get();

            if ($roles->isNotEmpty()) {
                foreach ($roles as $role) {
                    foreach ($role->permissions ?? [] as $perm) {
                        $fromRoles[] = (string) $perm;
                    }
                }
            } else {
                foreach ($roleNames as $roleName) {
                    $enum = UserRole::tryFrom($roleName);
                    if ($enum !== null) {
                        foreach (RolePermissionRegistry::permissionsFor($enum) as $perm) {
                            $fromRoles[] = $perm->value;
                        }
                    }
                }
            }
        }

        $this->cachedAllPermissions = array_values(array_unique([...$direct, ...$fromRoles]));

        return collect($this->cachedAllPermissions);
    }

    public function forgetCachedPermissions(): void
    {
        $this->cachedAllPermissions = null;
    }

    private function resolveRoleName(string|UserRole|Role $role): string
    {
        if ($role instanceof UserRole) {
            return $role->value;
        }

        if ($role instanceof Role) {
            return (string) $role->name;
        }

        return $role;
    }

    private function resolvePermissionName(string|PermissionEnum|Permission $permission): string
    {
        if ($permission instanceof PermissionEnum) {
            return $permission->value;
        }

        if ($permission instanceof Permission) {
            return (string) $permission->name;
        }

        return $permission;
    }
}
