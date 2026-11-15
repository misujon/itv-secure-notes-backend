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
        Schema::connection('mongodb')->create('interests', function (Blueprint $collection): void {
            $collection->unique('name', 'interests_name_unique');
            $collection->index(['deleted_at' => 1, 'name' => 1], 'interests_deleted_name_idx');
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->table('interests', function (Blueprint $collection): void {
            $collection->dropIndex('interests_name_unique');
            $collection->dropIndex('interests_deleted_name_idx');
        });
        Schema::connection('mongodb')->dropIfExists('interests');
    }
};
