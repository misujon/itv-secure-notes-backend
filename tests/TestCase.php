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
            Role::withTrashed()->forceDelete();
            Permission::withTrashed()->forceDelete();
            $this->seed(RolePermissionSeeder::class);
            static::$migrated = true;
        }

        User::withTrashed()->forceDelete();
        Note::withTrashed()->forceDelete();
        Post::withTrashed()->forceDelete();
    }
}
