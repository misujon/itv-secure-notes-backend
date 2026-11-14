@props([
    'title' => 'Confirm deletion',
    'message' => 'Are you sure you want to delete this item? This action cannot be undone.',
    'confirmLabel' => 'Delete',
    'action',
    'method' => 'DELETE',
])

<div x-data="{ open: false }" class="inline-block">
    <div @click="open = true">
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-cloak
        @keydown.escape.window="open = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
    >
        <div class="fixed inset-0 bg-slate-900/50" @click="open = false"></div>

        <div class="relative w-full max-w-md rounded-xl border border-app-border bg-white p-6 shadow-lg">
            <h3 class="text-base font-semibold text-app-heading">{{ $title }}</h3>
            <p class="mt-2 text-sm text-app-muted">{{ $message }}</p>

            <div class="mt-6 flex items-center justify-end gap-3">
                <x-admin.button type="button" variant="secondary" @click="open = false">
                    Cancel
                </x-admin.button>

                <form method="POST" action="{{ $action }}">
                    @csrf
                    @method($method)
                    <x-admin.button type="submit" variant="danger">
                        {{ $confirmLabel }}
                    </x-admin.button>
                </form>
            </div>
        </div>
    </div>
</div>
