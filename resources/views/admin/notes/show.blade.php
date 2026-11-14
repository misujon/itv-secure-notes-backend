@extends('layouts.admin')

@section('title', $note->title)

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-app-heading">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.notes.index') }}" class="hover:text-app-heading">All Notes</a>
    <span>/</span>
    <span class="font-medium text-app-heading">{{ \Illuminate\Support\Str::limit($note->title, 30) }}</span>
@endsection

@section('content')
    <x-admin.page-heading
        :title="$note->title"
        :subtitle="'Owner: '.($note->user?->name ?? 'Unknown')"
    >
        <x-slot:actions>
            <x-admin.button variant="secondary" :href="route('admin.notes.index')">
                Back to All Notes
            </x-admin.button>
            @can('update', $note)
                <x-admin.button :href="route('admin.my-notes.edit', (string) $note->_id)">
                    Edit in My Notes
                </x-admin.button>
            @endcan
        </x-slot:actions>
    </x-admin.page-heading>

    <x-admin.card>
        <div class="mb-5 flex flex-wrap items-center gap-4 border-b border-app-border pb-4 text-xs text-app-muted">
            <span>Created: {{ $note->created_at?->format('M j, Y g:i A') }}</span>
            <span>Updated: {{ $note->updated_at?->format('M j, Y g:i A') }}</span>
            @if ($note->user)
                <span>Owner email: {{ $note->user->email }}</span>
            @endif
        </div>

        <div class="whitespace-pre-wrap text-sm leading-relaxed text-app-heading">{{ $note->content }}</div>
    </x-admin.card>
@endsection
