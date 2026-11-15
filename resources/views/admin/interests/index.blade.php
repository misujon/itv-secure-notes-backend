@extends('layouts.admin')

@section('title', 'Interest Groups')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-app-heading">Dashboard</a>
    <span>/</span>
    <span class="font-medium text-app-heading">Interest Groups</span>
@endsection

@section('content')
    <x-admin.page-heading
        title="Interest Groups"
        subtitle="Users grouped by shared interests via MongoDB aggregation ($unwind, $group, $sort)."
    >
        <x-slot:actions>
            <x-admin.button :href="route('admin.interests.manage.index')">
                Manage Interests
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-heading>

    <x-admin.card :padding="false">
        @if ($groups->isEmpty())
            <div class="p-6">
                <x-admin.empty-state title="No interest groups found" description="Users with profile interests will be grouped here." />
            </div>
        @else
            <div class="divide-y divide-app-border">
                @foreach ($groups as $group)
                    <div class="p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <h3 class="text-base font-semibold text-app-heading">{{ $group['interest'] }}</h3>
                                <x-admin.badge variant="primary">
                                    {{ $group['user_count'] }} {{ \Illuminate\Support\Str::plural('user', (int) $group['user_count']) }}
                                </x-admin.badge>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($group['users'] ?? [] as $member)
                                <a
                                    href="{{ route('admin.users.show', $member['id']) }}"
                                    class="inline-flex items-center gap-2 rounded-lg border border-app-border bg-slate-50 px-3 py-1.5 text-xs text-app-heading hover:border-brand-500 hover:bg-brand-50"
                                >
                                    <span class="font-medium">{{ $member['name'] }}</span>
                                    <span class="text-app-muted">{{ $member['email'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <x-admin.pagination :paginator="$groups" />
        @endif
    </x-admin.card>
@endsection
