<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Interest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with roles, default interests,
     * an administrator account, and a demo regular user account.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        foreach (['chess', 'reading', 'coding', 'photography', 'music'] as $interestName) {
            Interest::query()->firstOrCreate(['name' => $interestName]);
        }

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('Hello@123!'),
                'role' => UserRole::Admin->value,
                'roles' => [UserRole::Admin->value],
                'permissions' => [],
                'interests' => ['coding', 'reading'],
            ]
        );

        $admin->syncRoles(UserRole::Admin);

        $demoUser = User::query()->firstOrCreate(
            ['email' => 'user@test.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('Hello@123!'),
                'role' => UserRole::User->value,
                'roles' => [UserRole::User->value],
                'permissions' => [],
                'interests' => ['chess', 'reading'],
            ]
        );

        $demoUser->syncRoles(UserRole::User);
    }
}
