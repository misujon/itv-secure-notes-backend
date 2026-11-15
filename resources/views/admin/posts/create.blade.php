@extends('layouts.admin')

@section('title', 'Create Post')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-app-heading">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.posts.index') }}" class="hover:text-app-heading">Posts</a>
    <span>/</span>
    <span class="font-medium text-app-heading">Create</span>
@endsection

@section('content')
    <x-admin.page-heading
        title="Create Post"
        subtitle="Publish a new public post."
    />

    <x-admin.card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.posts.store') }}" class="space-y-5">
            @csrf

            <x-admin.input label="Title" name="title" required />

            <x-admin.rich-editor label="Content" name="content" placeholder="Write your public post..." required />

            <div class="flex items-center justify-end gap-3 border-t border-app-border pt-5">
                <x-admin.button variant="secondary" :href="route('admin.posts.index')">
                    Cancel
                </x-admin.button>
                <x-admin.button type="submit">
                    Publish Post
                </x-admin.button>
            </div>
        </form>
    </x-admin.card>
@endsection
