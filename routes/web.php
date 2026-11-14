<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AllNoteController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InterestController;
use App\Http\Controllers\Admin\MyNoteController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserPostController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin']);

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login.store');

    Route::middleware(['auth:web', 'role:admin'])->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::prefix('users')->name('users.')->group(function (): void {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');

            Route::middleware('object.id:user')->group(function (): void {
                Route::get('/{user}', [UserController::class, 'show'])->name('show');
                Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
                Route::match(['put', 'patch'], '/{user}', [UserController::class, 'update'])->name('update');
                Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
                Route::get('/{user}/posts', UserPostController::class)->name('posts');
            });
        });

        Route::prefix('notes')->name('notes.')->group(function (): void {
            Route::get('/', [AllNoteController::class, 'index'])->name('index');

            Route::middleware('object.id:note')->group(function (): void {
                Route::get('/{note}', [AllNoteController::class, 'show'])->name('show');
            });
        });

        Route::get('/interests', InterestController::class)->name('interests.index');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::prefix('my-notes')->name('my-notes.')->group(function (): void {
            Route::get('/', [MyNoteController::class, 'index'])->name('index');
            Route::get('/create', [MyNoteController::class, 'create'])->name('create');
            Route::post('/', [MyNoteController::class, 'store'])->name('store');

            Route::middleware('object.id:note')->group(function (): void {
                Route::get('/{note}/edit', [MyNoteController::class, 'edit'])->name('edit');
                Route::match(['put', 'patch'], '/{note}', [MyNoteController::class, 'update'])->name('update');
                Route::delete('/{note}', [MyNoteController::class, 'destroy'])->name('destroy');
            });
        });
    });
});
