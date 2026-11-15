@extends('layouts.admin')

@section('title', 'Edit Note')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-app-heading">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.notes.index') }}" class="hover:text-app-heading">All Notes</a>
    <span>/</span>
    <a href="{{ route('admin.notes.show', (string) $note->_id) }}" class="hover:text-app-heading">{{ \Illuminate\Support\Str::limit($note->title, 24) }}</a>
    <span>/</span>
    <span class="font-medium text-app-heading">Edit</span>
@endsection

@section('content')
    <x-admin.page-heading
        title="Edit Note"
        :subtitle="'Owner: '.($note->user?->name ?? 'Unknown')"
    />

    <x-admin.card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.notes.update', (string) $note->_id) }}" class="space-y-5">
            @csrf
            @method('PATCH')

            <x-admin.input label="Title" name="title" :value="$note->title" required />

            <x-admin.rich-editor label="Content" name="content" :value="$note->content" placeholder="Write note content..." required />

            <div class="flex items-center justify-end gap-3 border-t border-app-border pt-5">
                <x-admin.button variant="secondary" :href="route('admin.notes.show', (string) $note->_id)">
                    Cancel
                </x-admin.button>
                <x-admin.button type="submit">
                    Update Note
                </x-admin.button>
            </div>
        </form>
    </x-admin.card>
@endsection
