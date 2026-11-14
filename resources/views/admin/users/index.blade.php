@extends('layouts.admin')

@section('title', 'Users')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-app-heading">Dashboard</a>
    <span>/</span>
    <span class="font-medium text-app-heading">Users</span>
@endsection

@section('content')
    <x-admin.page-heading
        title="Users"
        subtitle="Manage registered users, roles, and profile interests."
    >
        <x-slot:actions>
            <x-admin.button :href="route('admin.users.create')">
                New User
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-heading>

    <x-admin.card>
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-56 flex-1">
                <x-admin.input
                    label="Search"
                    name="search"
                    :value="$search"
                    placeholder="Name or email..."
                />
            </div>

            <div class="w-44">
                <x-admin.select
                    label="Role"
                    name="role"
                    :options="['user' => 'User', 'admin' => 'Admin']"
                    :selected="$role"
                    placeholder="All roles"
                />
            </div>

            <div class="flex items-center gap-2">
                <x-admin.button type="submit" variant="secondary">
                    Filter
                </x-admin.button>
                @if ($search !== '' || $role !== '')
                    <x-admin.button variant="ghost" :href="route('admin.users.index')">
                        Reset
                    </x-admin.button>
                @endif
            </div>
        </form>
    </x-admin.card>

    <x-admin.card :padding="false">
        @if ($users->isEmpty())
            <div class="p-6">
                <x-admin.empty-state title="No users found" description="Try adjusting your search or create a new user." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-app-border text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-app-muted">
                        <tr>
                            <th class="px-6 py-3.5">User</th>
                            <th class="px-6 py-3.5">Role</th>
                            <th class="px-6 py-3.5">Interests</th>
                            <th class="px-6 py-3.5">Registered</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-app-border bg-white">
                        @foreach ($users as $user)
                            <tr class="hover:bg-slate-50/75">
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.users.show', (string) $user->_id) }}" class="font-medium text-app-heading hover:text-brand-600">
                                        {{ $user->name }}
                                    </a>
                                    <p class="text-xs text-app-muted">{{ $user->email }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <x-admin.badge :variant="$user->isAdmin() ? 'primary' : 'neutral'">
                                        {{ ucfirst((string) $user->role) }}
                                    </x-admin.badge>
                                </td>
                                <td class="px-6 py-4 text-app-muted">
                                    {{ count($user->interests ?? []) }}
                                </td>
                                <td class="px-6 py-4 text-app-muted">
                                    {{ $user->created_at?->format('M j, Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('admin.users.show', (string) $user->_id) }}" class="text-xs font-medium text-slate-600 hover:text-app-heading">
                                            View
                                        </a>
                                        <a href="{{ route('admin.users.edit', (string) $user->_id) }}" class="text-xs font-medium text-brand-600 hover:text-brand-700">
                                            Edit
                                        </a>
                                        <x-admin.confirm-dialog
                                            title="Delete user"
                                            :message="'Delete '.$user->name.'? Existing notes and posts are preserved.'"
                                            :action="route('admin.users.destroy', (string) $user->_id)"
                                        >
                                            <x-slot:trigger>
                                                <button type="button" class="text-xs font-medium text-status-danger hover:text-red-700 cursor-pointer">
                                                    Delete
                                                </button>
                                            </x-slot:trigger>
                                        </x-admin.confirm-dialog>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-admin.pagination :paginator="$users" />
        @endif
    </x-admin.card>
@endsection
