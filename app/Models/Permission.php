<?php

declare(strict_types=1);

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Permission extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'permissions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'guard_name',
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
            ['name' => $name, 'guard_name' => $guardName]
        );
    }
}
