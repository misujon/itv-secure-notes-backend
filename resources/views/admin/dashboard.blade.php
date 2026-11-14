@extends('layouts.admin')

@section('title', 'Dashboard')

@section('breadcrumbs')
    <span class="font-medium text-app-heading">Dashboard</span>
@endsection

@section('content')
    <x-admin.page-heading
        title="Dashboard"
        subtitle="Overview of users, notes, posts, and recent platform activity."
    >
        <x-slot:actions>
            <x-admin.button variant="secondary" :href="route('admin.interests.index')">
                Interest Groups
            </x-admin.button>
            <x-admin.button :href="route('admin.users.create')">
                New User
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-heading>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <x-admin.card>
            <p class="text-sm font-medium text-app-muted">Total Users</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-app-heading">{{ number_format($stats['users']) }}</p>
            <a href="{{ route('admin.users.index') }}" class="mt-3 inline-block text-sm font-medium text-brand-600 hover:text-brand-700">
                Manage users &rarr;
            </a>
        </x-admin.card>

        <x-admin.card>
            <p class="text-sm font-medium text-app-muted">Total Notes</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-app-heading">{{ number_format($stats['notes']) }}</p>
            <a href="{{ route('admin.notes.index') }}" class="mt-3 inline-block text-sm font-medium text-brand-600 hover:text-brand-700">
                View all notes &rarr;
            </a>
        </x-admin.card>

        <x-admin.card>
            <p class="text-sm font-medium text-app-muted">Total Public Posts</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-app-heading">{{ number_format($stats['posts']) }}</p>
            <a href="{{ route('admin.interests.index') }}" class="mt-3 inline-block text-sm font-medium text-brand-600 hover:text-brand-700">
                Explore interest groups &rarr;
            </a>
        </x-admin.card>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-admin.card title="Recent Users" description="Newest registered accounts" :padding="false">
            <x-slot:header>
                <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">
                    View all
                </a>
            </x-slot:header>

            @if ($recentUsers->isEmpty())
                <div class="p-6">
                    <x-admin.empty-state title="No users yet" description="Create your first user account to get started." />
                </div>
            @else
                <ul class="divide-y divide-app-border">
                    @foreach ($recentUsers as $user)
                        <li class="flex items-center justify-between gap-4 px-6 py-3.5">
                            <div class="min-w-0">
                                <a href="{{ route('admin.users.show', (string) $user->_id) }}" class="truncate text-sm font-medium text-app-heading hover:text-brand-600">
                                    {{ $user->name }}
                                </a>
                                <p class="truncate text-xs text-app-muted">{{ $user->email }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-3">
                                <x-admin.badge :variant="$user->isAdmin() ? 'primary' : 'neutral'">
                                    {{ ucfirst((string) $user->role) }}
                                </x-admin.badge>
                                <span class="text-xs text-app-muted">{{ $user->created_at?->format('M j, Y') }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>

        <x-admin.card title="Recent Notes" description="Latest notes across all users (read-only)" :padding="false">
            <x-slot:header>
                <a href="{{ route('admin.notes.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">
                    View all
                </a>
            </x-slot:header>

            @if ($recentNotes->isEmpty())
                <div class="p-6">
                    <x-admin.empty-state title="No notes yet" description="Notes created by users will appear here." />
                </div>
            @else
                <ul class="divide-y divide-app-border">
                    @foreach ($recentNotes as $note)
                        <li class="flex items-center justify-between gap-4 px-6 py-3.5">
                            <div class="min-w-0">
                                <a href="{{ route('admin.notes.show', (string) $note->_id) }}" class="truncate text-sm font-medium text-app-heading hover:text-brand-600">
                                    {{ $note->title }}
                                </a>
                                <p class="truncate text-xs text-app-muted">
                                    By {{ $note->user?->name ?? 'Unknown User' }}
                                </p>
                            </div>
                            <span class="shrink-0 text-xs text-app-muted">{{ $note->created_at?->format('M j, Y') }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>
    </div>
@endsection
