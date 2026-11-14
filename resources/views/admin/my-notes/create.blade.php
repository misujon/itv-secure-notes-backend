@extends('layouts.admin')

@section('title', 'Create Note')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-app-heading">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.my-notes.index') }}" class="hover:text-app-heading">My Notes</a>
    <span>/</span>
    <span class="font-medium text-app-heading">Create</span>
@endsection

@section('content')
    <x-admin.page-heading
        title="Create Note"
        subtitle="Add a new personal note."
    />

    <x-admin.card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.my-notes.store') }}" class="space-y-5">
            @csrf

            <x-admin.input label="Title" name="title" required />

            <x-admin.textarea label="Content" name="content" rows="8" required />

            <div class="flex items-center justify-end gap-3 border-t border-app-border pt-5">
                <x-admin.button variant="secondary" :href="route('admin.my-notes.index')">
                    Cancel
                </x-admin.button>
                <x-admin.button type="submit">
                    Save Note
                </x-admin.button>
            </div>
        </form>
    </x-admin.card>
@endsection
