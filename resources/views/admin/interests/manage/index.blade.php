@extends('layouts.admin')

@section('title', 'Manage Interests')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-app-heading">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.interests.index') }}" class="hover:text-app-heading">Interests</a>
    <span>/</span>
    <span class="font-medium text-app-heading">Manage</span>
@endsection

@section('content')
    <x-admin.page-heading
        title="Manage Interests"
        subtitle="Create, edit, and archive selectable user interests."
    >
        <x-slot:actions>
            <x-admin.button variant="secondary" :href="route('admin.interests.manage.index', $onlyTrashed ? [] : ['trashed' => 1])">
                {{ $onlyTrashed ? 'Active Interests' : 'Trash' }}
            </x-admin.button>
            <x-admin.button :href="route('admin.interests.manage.create')">
                New Interest
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-heading>

    <x-admin.card :padding="false">
        @if ($interests->isEmpty())
            <div class="p-6">
                <x-admin.empty-state
                    :title="$onlyTrashed ? 'Trash is empty' : 'No interests yet'"
                    :description="$onlyTrashed ? 'No deleted interests found.' : 'Create interests that users can select during signup and profile updates.'"
                >
                    @if (! $onlyTrashed)
                        <x-slot:action>
                            <x-admin.button :href="route('admin.interests.manage.create')">
                                Create Interest
                            </x-admin.button>
                        </x-slot:action>
                    @endif
                </x-admin.empty-state>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-app-border text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-app-muted">
                        <tr>
                            <th class="px-6 py-3.5">Name</th>
                            <th class="px-6 py-3.5">Updated</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-app-border bg-white">
                        @foreach ($interests as $interest)
                            <tr class="hover:bg-slate-50/75">
                                <td class="px-6 py-4 font-medium text-app-heading">
                                    {{ $interest->name }}
                                </td>
                                <td class="px-6 py-4 text-app-muted">
                                    {{ $interest->updated_at?->format('M j, Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if ($onlyTrashed)
                                        <form method="POST" action="{{ route('admin.interests.manage.restore', (string) $interest->_id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="cursor-pointer text-xs font-medium text-brand-600 hover:text-brand-700">
                                                Restore
                                            </button>
                                        </form>
                                    @else
                                        <div class="inline-flex items-center gap-3">
                                            <a href="{{ route('admin.interests.manage.edit', (string) $interest->_id) }}" class="text-xs font-medium text-brand-600 hover:text-brand-700">
                                                Edit
                                            </a>
                                            <x-admin.confirm-dialog
                                                title="Delete interest"
                                                :message="'Move \''.$interest->name.'\' to trash?'"
                                                :action="route('admin.interests.manage.destroy', (string) $interest->_id)"
                                            >
                                                <x-slot:trigger>
                                                    <button type="button" class="cursor-pointer text-xs font-medium text-rose-600 hover:text-rose-700">
                                                        Delete
                                                    </button>
                                                </x-slot:trigger>
                                            </x-admin.confirm-dialog>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-admin.card>

    <div class="mt-6">
        <x-admin.pagination :paginator="$interests" />
    </div>
@endsection
