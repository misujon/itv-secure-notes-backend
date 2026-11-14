@extends('layouts.admin')

@section('title', 'Edit User')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-app-heading">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.users.index') }}" class="hover:text-app-heading">Users</a>
    <span>/</span>
    <a href="{{ route('admin.users.show', (string) $user->_id) }}" class="hover:text-app-heading">{{ $user->name }}</a>
    <span>/</span>
    <span class="font-medium text-app-heading">Edit</span>
@endsection

@section('content')
    <x-admin.page-heading
        :title="'Edit '.$user->name"
        subtitle="Update account details, role, or reset password."
    />

    <x-admin.card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.users.update', (string) $user->_id) }}" class="space-y-5">
            @csrf
            @method('PATCH')

            <x-admin.input label="Full name" name="name" :value="$user->name" required />

            <x-admin.input label="Email address" name="email" type="email" :value="$user->email" required />

            <x-admin.select
                label="Role"
                name="role"
                :options="['user' => 'User', 'admin' => 'Admin']"
                :selected="$user->role"
                required
            />

            <x-admin.input
                label="Interests"
                name="interests_input"
                :value="implode(', ', $user->interests ?? [])"
                placeholder="chess, reading, music"
                hint="Comma-separated list of interests"
            />

            <div class="grid grid-cols-1 gap-5 border-t border-app-border pt-5 sm:grid-cols-2">
                <x-admin.input
                    label="New password"
                    name="password"
                    type="password"
                    hint="Leave blank to keep current password"
                />
                <x-admin.input
                    label="Confirm new password"
                    name="password_confirmation"
                    type="password"
                />
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-app-border pt-5">
                <x-admin.button variant="secondary" :href="route('admin.users.show', (string) $user->_id)">
                    Cancel
                </x-admin.button>
                <x-admin.button type="submit">
                    Save Changes
                </x-admin.button>
            </div>
        </form>
    </x-admin.card>
@endsection
