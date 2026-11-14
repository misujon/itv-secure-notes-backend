<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-app-bg">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Sign In — {{ config('app.name', 'Secure Notes') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center px-4 py-12 font-sans text-app-heading antialiased">
    <div class="w-full max-w-md space-y-6">
        <div class="text-center">
            <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-xl bg-brand-500 text-base font-bold text-white shadow-xs">
                SN
            </div>
            <h1 class="mt-4 text-2xl font-semibold tracking-tight text-app-heading">Admin Portal</h1>
            <p class="mt-1 text-sm text-app-muted">Sign in with your administrator credentials</p>
        </div>

        <div class="rounded-xl border border-app-border bg-app-surface p-6 shadow-xs sm:p-8">
            @if (session('error'))
                <x-admin.alert type="error" class="mb-5">
                    {{ session('error') }}
                </x-admin.alert>
            @endif

            <form
                method="POST"
                action="{{ route('admin.login.store') }}"
                class="space-y-5"
                x-data="{ submitting: false }"
                @submit="submitting = true"
            >
                @csrf

                <x-admin.input
                    label="Email address"
                    name="email"
                    type="email"
                    autocomplete="email"
                    required
                    autofocus
                    placeholder="admin@example.com"
                />

                <x-admin.input
                    label="Password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                    placeholder="••••••••"
                />

                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            @checked(old('remember'))
                            class="h-4 w-4 rounded border-app-border text-brand-500 focus:ring-brand-500"
                        />
                        <span>Keep me signed in</span>
                    </label>
                </div>

                <x-admin.button type="submit" class="w-full" x-bind:disabled="submitting">
                    <span x-show="!submitting">Sign in</span>
                    <span x-show="submitting" x-cloak>Signing in...</span>
                </x-admin.button>
            </form>
        </div>
    </div>
</body>
</html>
