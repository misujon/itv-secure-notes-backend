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
        Schema::connection('mongodb')->create('users', function (Blueprint $collection): void {
            $collection->unique('email', 'users_email_unique');
            $collection->index(['deleted_at' => 1, 'created_at' => -1, '_id' => -1], 'users_deleted_created_id_idx');
            $collection->index(['deleted_at' => 1, 'interests' => 1], 'users_deleted_interests_idx');
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->table('users', function (Blueprint $collection): void {
            $collection->dropIndex('users_email_unique');
            $collection->dropIndex('users_deleted_created_id_idx');
            $collection->dropIndex('users_deleted_interests_idx');
        });
        Schema::connection('mongodb')->dropIfExists('users');
    }
};
