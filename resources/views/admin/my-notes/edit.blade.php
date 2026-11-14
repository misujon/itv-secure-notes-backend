@extends('layouts.admin')

@section('title', 'Edit Note')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-app-heading">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.my-notes.index') }}" class="hover:text-app-heading">My Notes</a>
    <span>/</span>
    <span class="font-medium text-app-heading">Edit</span>
@endsection

@section('content')
    <x-admin.page-heading
        title="Edit Note"
        :subtitle="'Updating '.$note->title"
    />

    <x-admin.card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.my-notes.update', (string) $note->_id) }}" class="space-y-5">
            @csrf
            @method('PATCH')

            <x-admin.input label="Title" name="title" :value="$note->title" required />

            <x-admin.textarea label="Content" name="content" :value="$note->content" rows="8" required />

            <div class="flex items-center justify-end gap-3 border-t border-app-border pt-5">
                <x-admin.button variant="secondary" :href="route('admin.my-notes.index')">
                    Cancel
                </x-admin.button>
                <x-admin.button type="submit">
                    Update Note
                </x-admin.button>
            </div>
        </form>
    </x-admin.card>
@endsection
