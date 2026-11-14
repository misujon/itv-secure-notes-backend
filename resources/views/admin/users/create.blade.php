@extends('layouts.admin')

@section('title', 'Create User')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-app-heading">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.users.index') }}" class="hover:text-app-heading">Users</a>
    <span>/</span>
    <span class="font-medium text-app-heading">Create</span>
@endsection

@section('content')
    <x-admin.page-heading
        title="Create User"
        subtitle="Add a new user or administrator account."
    />

    <x-admin.card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
            @csrf

            <x-admin.input label="Full name" name="name" required />

            <x-admin.input label="Email address" name="email" type="email" required />

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-admin.input label="Password" name="password" type="password" required hint="Minimum 8 characters" />
                <x-admin.input label="Confirm password" name="password_confirmation" type="password" required />
            </div>

            <x-admin.select
                label="Role"
                name="role"
                :options="['user' => 'User', 'admin' => 'Admin']"
                selected="user"
                required
            />

            <x-admin.input
                label="Interests"
                name="interests_input"
                placeholder="chess, reading, music"
                hint="Comma-separated list of interests"
            />

            <div class="flex items-center justify-end gap-3 border-t border-app-border pt-5">
                <x-admin.button variant="secondary" :href="route('admin.users.index')">
                    Cancel
                </x-admin.button>
                <x-admin.button type="submit">
                    Create User
                </x-admin.button>
            </div>
        </form>
    </x-admin.card>
@endsection
