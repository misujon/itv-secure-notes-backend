<?php

declare(strict_types=1);

namespace Tests;

use App\Models\Note;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! static::$migrated) {
            $this->artisan('migrate:fresh', ['--no-interaction' => true]);
            static::$migrated = true;
        }

        User::query()->delete();
        Note::query()->delete();
        Post::query()->delete();
    }
}
