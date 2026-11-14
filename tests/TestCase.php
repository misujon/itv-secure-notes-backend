<?php

declare(strict_types=1);

namespace Tests;

use App\Models\Note;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! static::$migrated) {
            $this->artisan('migrate:fresh', ['--no-interaction' => true]);
            Role::query()->delete();
            Permission::query()->delete();
            $this->seed(RolePermissionSeeder::class);
            static::$migrated = true;
        }

        User::query()->delete();
        Note::query()->delete();
        Post::query()->delete();
    }
}
