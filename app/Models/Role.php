<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Permission as PermissionEnum;
use MongoDB\Laravel\Eloquent\Model;

class Role extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'roles';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'permissions',
    ];

    public static function findByName(string $name, string $guardName = 'web'): ?self
    {
        return static::query()
            ->where('name', $name)
            ->where('guard_name', $guardName)
            ->first();
    }

    public static function findOrCreate(string $name, string $guardName = 'web'): self
    {
        return static::query()->firstOrCreate(
            ['name' => $name, 'guard_name' => $guardName],
            ['permissions' => []]
        );
    }

    public function givePermissionTo(string|PermissionEnum|Permission ...$permissions): self
    {
        $current = $this->permissions ?? [];

        foreach ($permissions as $permission) {
            $name = $this->resolvePermissionName($permission);
            if (! in_array($name, $current, true)) {
                $current[] = $name;
            }
        }

        $this->permissions = array_values($current);
        $this->save();

        return $this;
    }

    /**
     * @param  array<int, string|PermissionEnum|Permission>  $permissions
     */
    public function syncPermissions(array $permissions): self
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

        return $this;
    }

    public function revokePermissionTo(string|PermissionEnum|Permission $permission): self
    {
        $name = $this->resolvePermissionName($permission);
        $this->permissions = array_values(array_filter(
            $this->permissions ?? [],
            fn (string $item): bool => $item !== $name
        ));
        $this->save();

        return $this;
    }

    public function hasPermissionTo(string|PermissionEnum|Permission $permission): bool
    {
        $name = $this->resolvePermissionName($permission);

        return in_array($name, $this->permissions ?? [], true);
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
