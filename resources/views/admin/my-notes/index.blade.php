@extends('layouts.admin')

@section('title', 'My Notes')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-app-heading">Dashboard</a>
    <span>/</span>
    <span class="font-medium text-app-heading">My Notes</span>
@endsection

@section('content')
    <x-admin.page-heading
        title="My Notes"
        subtitle="Create, edit, and manage your personal notes."
    >
        <x-slot:actions>
            <x-admin.button :href="route('admin.my-notes.create')">
                New Note
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-heading>

    <x-admin.card :padding="false">
        @if ($notes->isEmpty())
            <div class="p-6">
                <x-admin.empty-state title="No personal notes yet" description="Create your first personal note.">
                    <x-slot:action>
                        <x-admin.button :href="route('admin.my-notes.create')">
                            Create Note
                        </x-admin.button>
                    </x-slot:action>
                </x-admin.empty-state>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-app-border text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-app-muted">
                        <tr>
                            <th class="px-6 py-3.5">Title</th>
                            <th class="px-6 py-3.5">Preview</th>
                            <th class="px-6 py-3.5">Updated</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-app-border bg-white">
                        @foreach ($notes as $note)
                            <tr class="hover:bg-slate-50/75">
                                <td class="px-6 py-4 font-medium text-app-heading">
                                    {{ $note->title }}
                                </td>
                                <td class="max-w-sm truncate px-6 py-4 text-app-muted">
                                    {{ \Illuminate\Support\Str::limit((string) $note->content, 80) }}
                                </td>
                                <td class="px-6 py-4 text-app-muted">
                                    {{ $note->updated_at?->format('M j, Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-3">
                                        <a href="{{ route('admin.my-notes.edit', (string) $note->_id) }}" class="text-xs font-medium text-brand-600 hover:text-brand-700">
                                            Edit
                                        </a>
                                        <x-admin.confirm-dialog
                                            title="Delete note"
                                            :message="'Delete \''.$note->title.'\'? This cannot be undone.'"
                                            :action="route('admin.my-notes.destroy', (string) $note->_id)"
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

            <x-admin.pagination :paginator="$notes" />
        @endif
    </x-admin.card>
@endsection
