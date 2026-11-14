@extends('layouts.admin')

@section('title', 'All Notes')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-app-heading">Dashboard</a>
    <span>/</span>
    <span class="font-medium text-app-heading">All Notes</span>
@endsection

@section('content')
    <x-admin.page-heading
        title="All Notes"
        subtitle="Read-only visibility across all users' notes."
    >
        <x-slot:actions>
            <x-admin.button :href="route('admin.my-notes.index')" variant="secondary">
                Manage My Notes
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-heading>

    <x-admin.card :padding="false">
        @if ($notes->isEmpty())
            <div class="p-6">
                <x-admin.empty-state title="No notes found" description="Notes created by users will appear here." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-app-border text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-app-muted">
                        <tr>
                            <th class="px-6 py-3.5">Title</th>
                            <th class="px-6 py-3.5">Owner</th>
                            <th class="px-6 py-3.5">Preview</th>
                            <th class="px-6 py-3.5">Created</th>
                            <th class="px-6 py-3.5">Updated</th>
                            <th class="px-6 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-app-border bg-white">
                        @foreach ($notes as $note)
                            <tr class="hover:bg-slate-50/75">
                                <td class="px-6 py-4 font-medium text-app-heading">
                                    <a href="{{ route('admin.notes.show', (string) $note->_id) }}" class="hover:text-brand-600">
                                        {{ $note->title }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($note->user)
                                        <a href="{{ route('admin.users.show', (string) $note->user->_id) }}" class="text-brand-600 hover:text-brand-700">
                                            {{ $note->user->name }}
                                        </a>
                                    @else
                                        <span class="text-app-muted">Unknown</span>
                                    @endif
                                </td>
                                <td class="max-w-xs truncate px-6 py-4 text-app-muted">
                                    {{ \Illuminate\Support\Str::limit((string) $note->content, 70) }}
                                </td>
                                <td class="px-6 py-4 text-app-muted">
                                    {{ $note->created_at?->format('M j, Y') }}
                                </td>
                                <td class="px-6 py-4 text-app-muted">
                                    {{ $note->updated_at?->format('M j, Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.notes.show', (string) $note->_id) }}" class="text-xs font-medium text-brand-600 hover:text-brand-700">
                                        View
                                    </a>
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
