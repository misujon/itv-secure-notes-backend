@extends('layouts.admin')

@section('title', $user->name)

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-app-heading">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.users.index') }}" class="hover:text-app-heading">Users</a>
    <span>/</span>
    <span class="font-medium text-app-heading">{{ $user->name }}</span>
@endsection

@section('content')
    <x-admin.page-heading
        :title="$user->name"
        :subtitle="$user->email"
    >
        <x-slot:actions>
            <x-admin.button variant="secondary" :href="route('admin.users.posts', (string) $user->_id)">
                View Public Posts ({{ $postsCount }})
            </x-admin.button>
            <x-admin.button :href="route('admin.users.edit', (string) $user->_id)">
                Edit User
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-heading>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-admin.card title="Account Details" class="lg:col-span-2">
            <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-app-muted">Name</dt>
                    <dd class="mt-1 text-sm font-medium text-app-heading">{{ $user->name }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-app-muted">Email</dt>
                    <dd class="mt-1 text-sm text-app-heading">{{ $user->email }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-app-muted">Role</dt>
                    <dd class="mt-1">
                        <x-admin.badge :variant="$user->isAdmin() ? 'primary' : 'neutral'">
                            {{ ucfirst((string) $user->role) }}
                        </x-admin.badge>
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-app-muted">Registered</dt>
                    <dd class="mt-1 text-sm text-app-heading">{{ $user->created_at?->format('M j, Y g:i A') }}</dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium uppercase tracking-wider text-app-muted">Interests</dt>
                    <dd class="mt-2 flex flex-wrap gap-2">
                        @forelse ($user->interests ?? [] as $interest)
                            <x-admin.badge variant="primary">{{ $interest }}</x-admin.badge>
                        @empty
                            <span class="text-sm text-app-muted">No interests specified</span>
                        @endforelse
                    </dd>
                </div>
            </dl>
        </x-admin.card>

        <x-admin.card title="Activity Summary">
            <dl class="space-y-4">
                <div class="flex items-center justify-between border-b border-app-border pb-3">
                    <dt class="text-sm text-app-muted">Total Notes</dt>
                    <dd class="text-lg font-semibold text-app-heading">{{ $notesCount }}</dd>
                </div>
                <div class="flex items-center justify-between border-b border-app-border pb-3">
                    <dt class="text-sm text-app-muted">Total Posts</dt>
                    <dd class="text-lg font-semibold text-app-heading">{{ $postsCount }}</dd>
                </div>
            </dl>

            <div class="mt-5 flex flex-col gap-2">
                <x-admin.button variant="secondary" :href="route('admin.notes.index')" class="w-full">
                    Browse All Notes
                </x-admin.button>
                <x-admin.button variant="secondary" :href="route('admin.users.posts', (string) $user->_id)" class="w-full">
                    View User Posts ($lookup)
                </x-admin.button>
            </div>
        </x-admin.card>
    </div>
@endsection
