<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Permission as PermissionEnum;
use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    public function test_roles_and_permissions_are_seeded_in_mongodb(): void
    {
        $userRole = Role::findByName(UserRole::User->value);
        $adminRole = Role::findByName(UserRole::Admin->value);

        $this->assertNotNull($userRole);
        $this->assertNotNull($adminRole);
        $this->assertSame(count(PermissionEnum::cases()), Permission::query()->count());

        $this->assertTrue($userRole->hasPermissionTo(PermissionEnum::NotesCreate));
        $this->assertFalse($userRole->hasPermissionTo(PermissionEnum::UsersCreate));
        $this->assertTrue($adminRole->hasPermissionTo(PermissionEnum::UsersCreate));
        $this->assertTrue($adminRole->hasPermissionTo(PermissionEnum::NotesCreate));
    }

    public function test_user_spatie_style_role_and_permission_methods_work(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->hasRole(UserRole::User));
        $this->assertFalse($user->hasRole(UserRole::Admin));
        $this->assertTrue($user->hasPermissionTo(PermissionEnum::NotesViewOwn));
        $this->assertFalse($user->hasPermissionTo(PermissionEnum::UsersViewAny));

        // Direct permission grant & revoke
        $user->givePermissionTo(PermissionEnum::UsersViewAny);
        $this->assertTrue($user->hasPermissionTo(PermissionEnum::UsersViewAny));
        $this->assertTrue(Gate::forUser($user)->allows(PermissionEnum::UsersViewAny->value));

        $user->revokePermissionTo(PermissionEnum::UsersViewAny);
        $this->assertFalse($user->hasPermissionTo(PermissionEnum::UsersViewAny));

        // Role assignment & sync
        $user->assignRole(UserRole::Admin);
        $this->assertTrue($user->hasAnyRole([UserRole::User, UserRole::Admin]));
        $this->assertTrue($user->hasPermissionTo(PermissionEnum::InterestsViewGroups));

        $user->syncRoles(UserRole::User);
        $this->assertFalse($user->hasRole(UserRole::Admin));
        $this->assertFalse($user->hasPermissionTo(PermissionEnum::InterestsViewGroups));
    }
}
