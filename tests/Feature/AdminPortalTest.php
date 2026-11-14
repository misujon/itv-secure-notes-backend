<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Permission;
use App\Enums\UserRole;
use App\Models\Note;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPortalTest extends TestCase
{
    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get('/admin/dashboard')
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_login_view_dashboard_and_logout(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'portaladmin@example.com',
            'password' => Hash::make('Secret123!'),
        ]);

        $this->get('/admin/login')->assertOk();

        $this->post('/admin/login', [
            'email' => 'portaladmin@example.com',
            'password' => 'Secret123!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'web');

        $this->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Total Users');

        $this->post('/admin/logout')
            ->assertRedirect(route('admin.login'));

        $this->assertGuest('web');
    }

    public function test_invalid_login_and_regular_user_login_are_rejected(): void
    {
        $regularUser = User::factory()->create([
            'email' => 'regular@example.com',
            'password' => Hash::make('Secret123!'),
        ]);

        $this->from('/admin/login')
            ->post('/admin/login', [
                'email' => 'regular@example.com',
                'password' => 'WrongPassword!',
            ])
            ->assertRedirect('/admin/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest('web');

        $this->from('/admin/login')
            ->post('/admin/login', [
                'email' => $regularUser->email,
                'password' => 'Secret123!',
            ])
            ->assertRedirect('/admin/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest('web');
    }

    public function test_authenticated_regular_user_is_denied_admin_routes_and_gates_enforce_permissions(): void
    {
        $user = User::factory()->create(['role' => UserRole::User->value]);
        $admin = User::factory()->admin()->create();

        $this->assertTrue(Gate::forUser($user)->allows(Permission::NotesViewOwn->value));
        $this->assertFalse(Gate::forUser($user)->allows(Permission::UsersViewAny->value));
        $this->assertTrue(Gate::forUser($admin)->allows(Permission::UsersViewAny->value));
        $this->assertTrue(Gate::forUser($admin)->allows(Permission::InterestsViewGroups->value));

        $this->actingAs($user, 'web');

        $this->get('/admin/dashboard')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/notes')->assertForbidden();
        $this->get('/admin/interests')->assertForbidden();
    }

    public function test_admin_can_manage_users_through_blade_portal(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin, 'web');

        $this->get('/admin/users')
            ->assertOk()
            ->assertSee($admin->email);

        $this->post('/admin/users', [
            'name' => 'Portal User',
            'email' => 'portaluser@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'role' => UserRole::User->value,
            'interests_input' => 'chess, reading',
        ])->assertRedirect();

        $created = User::query()->where('email', 'portaluser@example.com')->first();
        $this->assertNotNull($created);
        $this->assertSame(['chess', 'reading'], $created->interests);

        $this->get('/admin/users/'.$created->_id)
            ->assertOk()
            ->assertSee('Portal User')
            ->assertDontSee($created->password);

        $this->patch('/admin/users/'.$created->_id, [
            'name' => 'Portal User Updated',
            'email' => 'portaluser@example.com',
            'role' => UserRole::User->value,
            'interests_input' => 'music',
        ])->assertRedirect(route('admin.users.show', (string) $created->_id));

        $this->assertSame('Portal User Updated', $created->fresh()?->name);

        $this->delete('/admin/users/'.$created->_id)
            ->assertRedirect(route('admin.users.index'));

        $this->assertNull(User::query()->find($created->_id));

        // Last admin cannot be deleted
        $this->delete('/admin/users/'.$admin->_id)
            ->assertSessionHas('error');

        $this->assertNotNull(User::query()->find($admin->_id));
    }

    public function test_admin_can_view_all_notes_read_only_and_manage_own_notes(): void
    {
        $admin = User::factory()->admin()->create();
        $otherUser = User::factory()->create(['name' => 'Note Owner']);
        $otherNote = Note::factory()->create([
            'user_id' => $otherUser->_id,
            'title' => 'Other Users Secret Note',
        ]);

        $this->actingAs($admin, 'web');

        $this->get('/admin/notes')
            ->assertOk()
            ->assertSee('Other Users Secret Note')
            ->assertSee('Note Owner');

        $this->get('/admin/notes/'.$otherNote->_id)
            ->assertOk()
            ->assertSee('Other Users Secret Note')
            ->assertDontSee('Edit in My Notes');

        // Admin cannot edit or delete another user's note via My Notes
        $this->get('/admin/my-notes/'.$otherNote->_id.'/edit')->assertForbidden();
        $this->patch('/admin/my-notes/'.$otherNote->_id, ['title' => 'Tampered'])->assertForbidden();
        $this->delete('/admin/my-notes/'.$otherNote->_id)->assertForbidden();

        // Admin can create, edit, and delete their own note
        $this->post('/admin/my-notes', [
            'title' => 'Admin Own Note',
            'content' => 'Private admin content.',
        ])->assertRedirect(route('admin.my-notes.index'));

        $ownNote = Note::query()->where('title', 'Admin Own Note')->first();
        $this->assertNotNull($ownNote);

        $this->patch('/admin/my-notes/'.$ownNote->_id, [
            'title' => 'Admin Updated Note',
            'content' => 'Updated content.',
        ])->assertRedirect(route('admin.my-notes.index'));

        $this->assertSame('Admin Updated Note', $ownNote->fresh()?->title);

        $this->delete('/admin/my-notes/'.$ownNote->_id)
            ->assertRedirect(route('admin.my-notes.index'));

        $this->assertNull(Note::query()->find($ownNote->_id));
    }

    public function test_admin_can_update_profile_without_changing_role(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Old Admin']);
        $this->actingAs($admin, 'web');

        $this->get('/admin/profile')->assertOk()->assertSee('Old Admin');

        $this->patch('/admin/profile', [
            'name' => 'New Admin',
            'email' => $admin->email,
            'role' => UserRole::User->value,
            'interests_input' => 'architecture, security',
        ])->assertRedirect(route('admin.profile.edit'));

        $fresh = $admin->fresh();
        $this->assertSame('New Admin', $fresh?->name);
        $this->assertSame(UserRole::Admin->value, $fresh?->role);
        $this->assertSame(['architecture', 'security'], $fresh?->interests);
    }

    public function test_admin_can_view_interest_groups_and_user_posts_aggregations(): void
    {
        $admin = User::factory()->admin()->create(['interests' => ['chess']]);
        $author = User::factory()->create(['name' => 'Post Author', 'interests' => ['chess', 'reading']]);
        Post::factory()->create([
            'user_id' => $author->_id,
            'title' => 'MongoDB Lookup Post',
            'content' => 'Aggregated cleanly.',
        ]);

        $this->actingAs($admin, 'web');

        $this->get('/admin/interests')
            ->assertOk()
            ->assertSee('chess')
            ->assertSee('reading')
            ->assertSee('Post Author');

        $this->get('/admin/users/'.$author->_id.'/posts')
            ->assertOk()
            ->assertSee('Post Author')
            ->assertSee('MongoDB Lookup Post');
    }
}
