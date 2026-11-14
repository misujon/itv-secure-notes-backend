<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

return new class extends Migration
{
    protected $connection = 'mongodb';

    public function up(): void
    {
        Schema::connection('mongodb')->create('roles', function (Blueprint $collection): void {
            $collection->unique(['name' => 1, 'guard_name' => 1], 'roles_name_guard_unique');
        });

        Schema::connection('mongodb')->create('permissions', function (Blueprint $collection): void {
            $collection->unique(['name' => 1, 'guard_name' => 1], 'permissions_name_guard_unique');
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->table('roles', function (Blueprint $collection): void {
            $collection->dropIndex('roles_name_guard_unique');
        });
        Schema::connection('mongodb')->dropIfExists('roles');

        Schema::connection('mongodb')->table('permissions', function (Blueprint $collection): void {
            $collection->dropIndex('permissions_name_guard_unique');
        });
        Schema::connection('mongodb')->dropIfExists('permissions');
    }
};
