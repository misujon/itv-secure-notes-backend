@extends('layouts.admin')

@section('title', 'Profile')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-app-heading">Dashboard</a>
    <span>/</span>
    <span class="font-medium text-app-heading">Profile</span>
@endsection

@section('content')
    <x-admin.page-heading
        title="My Profile"
        subtitle="Manage your personal account details and interests."
    />

    <x-admin.card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-5">
            @csrf
            @method('PATCH')

            <div class="flex items-center justify-between rounded-lg border border-app-border bg-slate-50 px-4 py-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-app-muted">Account Role</p>
                    <p class="mt-0.5 text-sm font-medium text-app-heading">{{ ucfirst((string) $user->role) }}</p>
                </div>
                <x-admin.badge variant="primary">Administrator</x-admin.badge>
            </div>

            <x-admin.input label="Full name" name="name" :value="$user->name" required />

            <x-admin.input label="Email address" name="email" type="email" :value="$user->email" required />

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
                <x-admin.button type="submit">
                    Update Profile
                </x-admin.button>
            </div>
        </form>
    </x-admin.card>
@endsection
