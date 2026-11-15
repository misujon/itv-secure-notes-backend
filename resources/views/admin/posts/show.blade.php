@extends('layouts.admin')

@section('title', $post->title)

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-app-heading">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.posts.index') }}" class="hover:text-app-heading">Posts</a>
    <span>/</span>
    <span class="font-medium text-app-heading">{{ \Illuminate\Support\Str::limit($post->title, 30) }}</span>
@endsection

@section('content')
    <x-admin.page-heading
        :title="$post->title"
        :subtitle="'Author: '.($post->user?->name ?? 'Unknown')"
    >
        <x-slot:actions>
            <x-admin.button variant="secondary" :href="route('admin.posts.index')">
                Back to Posts
            </x-admin.button>
            <x-admin.button :href="route('admin.posts.edit', (string) $post->_id)">
                Edit Post
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-heading>

    <x-admin.card>
        <div class="mb-5 flex flex-wrap items-center gap-4 border-b border-app-border pb-4 text-xs text-app-muted">
            <span>Created: {{ $post->created_at?->format('M j, Y g:i A') }}</span>
            <span>Updated: {{ $post->updated_at?->format('M j, Y g:i A') }}</span>
            @if ($post->user)
                <span>Author email: {{ $post->user->email }}</span>
            @endif
        </div>

        <div class="rich-text-content text-sm leading-relaxed text-app-heading">{!! \App\Support\HtmlSanitizer::toSafeHtml($post->content) !!}</div>
    </x-admin.card>
@endsection
