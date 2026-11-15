@extends('layouts.admin')

@section('title', 'Edit Post')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-app-heading">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.posts.index') }}" class="hover:text-app-heading">Posts</a>
    <span>/</span>
    <span class="font-medium text-app-heading">Edit</span>
@endsection

@section('content')
    <x-admin.page-heading
        title="Edit Post"
        :subtitle="'Updating '.$post->title"
    />

    <x-admin.card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.posts.update', (string) $post->_id) }}" class="space-y-5">
            @csrf
            @method('PATCH')

            <x-admin.input label="Title" name="title" :value="$post->title" required />

            <x-admin.rich-editor label="Content" name="content" :value="$post->content" placeholder="Write your public post..." required />

            <div class="flex items-center justify-end gap-3 border-t border-app-border pt-5">
                <x-admin.button variant="secondary" :href="route('admin.posts.show', (string) $post->_id)">
                    Cancel
                </x-admin.button>
                <x-admin.button type="submit">
                    Update Post
                </x-admin.button>
            </div>
        </form>
    </x-admin.card>
@endsection
