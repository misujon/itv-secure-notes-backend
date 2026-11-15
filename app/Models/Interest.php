<?php

declare(strict_types=1);

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Interest extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';

    protected $table = 'interests';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    public static function normalizeName(string $name): string
    {
        return strtolower(trim($name));
    }
}
