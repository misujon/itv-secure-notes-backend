<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-app-bg">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Portal') — {{ config('app.name', 'Secure Notes') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans text-app-heading antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex min-h-full">
        {{-- Mobile sidebar backdrop --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"
            aria-hidden="true"
        ></div>

        {{-- Sidebar (desktop + mobile drawer) --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-app-border bg-app-surface transition-transform duration-200 ease-out lg:static lg:z-auto lg:shrink-0"
            aria-label="Admin navigation"
        >
            <div class="flex h-16 items-center justify-between border-b border-app-border px-5">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-500 text-sm font-bold text-white">
                        SN
                    </span>
                    <span class="text-sm font-semibold tracking-tight text-app-heading">
                        Secure Notes
                    </span>
                </a>

                <button
                    type="button"
                    @click="sidebarOpen = false"
                    class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 lg:hidden"
                    aria-label="Close navigation"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Overview</p>
                    <div class="mt-2 space-y-1">
                        <x-admin.nav-item :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                            <x-slot:icon>
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                            </x-slot:icon>
                            Dashboard
                        </x-admin.nav-item>
                    </div>
                </div>

                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Management</p>
                    <div class="mt-2 space-y-1">
                        <x-admin.nav-item :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                            <x-slot:icon>
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </x-slot:icon>
                            Users
                        </x-admin.nav-item>

                        <x-admin.nav-item :href="route('admin.notes.index')" :active="request()->routeIs('admin.notes.*')">
                            <x-slot:icon>
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </x-slot:icon>
                            All Notes
                        </x-admin.nav-item>

                        <x-admin.nav-item :href="route('admin.interests.index')" :active="request()->routeIs('admin.interests.*')">
                            <x-slot:icon>
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </x-slot:icon>
                            Interest Groups
                        </x-admin.nav-item>
                    </div>
                </div>

                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Personal</p>
                    <div class="mt-2 space-y-1">
                        <x-admin.nav-item :href="route('admin.my-notes.index')" :active="request()->routeIs('admin.my-notes.*')">
                            <x-slot:icon>
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </x-slot:icon>
                            My Notes
                        </x-admin.nav-item>

                        <x-admin.nav-item :href="route('admin.profile.edit')" :active="request()->routeIs('admin.profile.*')">
                            <x-slot:icon>
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </x-slot:icon>
                            Profile
                        </x-admin.nav-item>
                    </div>
                </div>
            </nav>

            <div class="border-t border-app-border px-4 py-3 text-xs text-app-muted">
                MongoDB Admin Portal
            </div>
        </aside>

        {{-- Main shell --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-app-border bg-app-surface px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden"
                        aria-label="Open navigation"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    @hasSection('breadcrumbs')
                        <nav class="hidden text-sm text-app-muted sm:flex sm:items-center sm:gap-2" aria-label="Breadcrumb">
                            @yield('breadcrumbs')
                        </nav>
                    @endif
                </div>

                <div class="relative" x-data="{ open: false }">
                    <button
                        type="button"
                        @click="open = !open"
                        @click.outside="open = false"
                        class="flex items-center gap-2.5 rounded-lg border border-app-border bg-white px-3 py-1.5 text-sm font-medium text-app-heading hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-500"
                        aria-expanded="false"
                    >
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-50 text-xs font-semibold text-brand-700">
                            {{ strtoupper(substr((string) auth('web')->user()?->name, 0, 1)) }}
                        </span>
                        <span class="hidden sm:inline">{{ auth('web')->user()?->name }}</span>
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        x-cloak
                        class="absolute right-0 mt-2 w-56 rounded-xl border border-app-border bg-white py-1.5 shadow-lg"
                    >
                        <div class="border-b border-app-border px-4 py-2">
                            <p class="truncate text-sm font-medium text-app-heading">{{ auth('web')->user()?->name }}</p>
                            <p class="truncate text-xs text-app-muted">{{ auth('web')->user()?->email }}</p>
                        </div>

                        <a href="{{ route('admin.profile.edit') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                            Profile Settings
                        </a>

                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-status-danger hover:bg-red-50 cursor-pointer">
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-6xl space-y-6">
                    @if (session('success'))
                        <x-admin.alert type="success">
                            {{ session('success') }}
                        </x-admin.alert>
                    @endif

                    @if (session('error'))
                        <x-admin.alert type="error">
                            {{ session('error') }}
                        </x-admin.alert>
                    @endif

                    @if ($errors->any())
                        <x-admin.alert type="error" title="Please fix the following errors:">
                            <ul class="mt-1 list-disc space-y-0.5 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </x-admin.alert>
                    @endif

                    @yield('content')
                </div>
            </main>

            <footer class="border-t border-app-border bg-app-surface px-4 py-4 text-xs text-app-muted sm:px-6 lg:px-8">
                <div class="mx-auto flex max-w-6xl items-center justify-between">
                    <span>{{ config('app.name', 'Secure Notes') }} Admin Portal</span>
                    <span>Laravel {{ app()->version() }}</span>
                </div>
            </footer>
        </div>
    </div>
</body>
</html>
