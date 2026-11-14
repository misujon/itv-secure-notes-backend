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
        Schema::connection('mongodb')->create('notes', function (Blueprint $collection): void {
            $collection->index(['user_id' => 1, 'created_at' => -1, '_id' => -1], 'notes_user_created_id_idx');
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->table('notes', function (Blueprint $collection): void {
            $collection->dropIndex('notes_user_created_id_idx');
        });
        Schema::connection('mongodb')->dropIfExists('notes');
    }
};
