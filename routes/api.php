<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Admin\AggregationController as AdminAggregationController;
use App\Http\Controllers\Api\V1\Admin\NoteController as AdminNoteController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\NoteController;
use App\Http\Controllers\Api\V1\PostController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

        Route::middleware('auth:api')->group(function (): void {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
        });
    });

    Route::middleware('auth:api')->prefix('notes')->group(function (): void {
        Route::get('/', [NoteController::class, 'index']);
        Route::post('/', [NoteController::class, 'store']);

        Route::middleware('object.id')->group(function (): void {
            Route::get('/{id}', [NoteController::class, 'show']);
            Route::patch('/{id}', [NoteController::class, 'update']);
            Route::delete('/{id}', [NoteController::class, 'destroy']);
        });
    });

    Route::middleware(['auth:api', 'role:admin'])->prefix('admin')->group(function (): void {
        Route::prefix('users')->group(function (): void {
            Route::get('/', [AdminUserController::class, 'index']);
            Route::post('/', [AdminUserController::class, 'store']);

            Route::middleware('object.id')->group(function (): void {
                Route::get('/{id}', [AdminUserController::class, 'show']);
                Route::patch('/{id}', [AdminUserController::class, 'update']);
                Route::delete('/{id}', [AdminUserController::class, 'destroy']);
            });
        });

        Route::prefix('notes')->group(function (): void {
            Route::get('/', [AdminNoteController::class, 'index']);

            Route::middleware('object.id')->group(function (): void {
                Route::get('/{id}', [AdminNoteController::class, 'show']);
            });
        });

        Route::prefix('aggregations')->group(function (): void {
            Route::get('/interests', [AdminAggregationController::class, 'interests']);
        });
    });

    Route::prefix('posts')->group(function (): void {
        Route::get('/', [PostController::class, 'index']);
        Route::post('/', [PostController::class, 'store'])->middleware('auth:api');

        Route::middleware('object.id')->group(function (): void {
            Route::get('/{id}', [PostController::class, 'show']);
            Route::patch('/{id}', [PostController::class, 'update'])->middleware('auth:api');
        });
    });

    Route::middleware('object.id')->get('/users/{id}/posts', [PostController::class, 'userPosts']);
});
