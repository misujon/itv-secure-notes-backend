@extends('layouts.admin')

@section('title', 'Posts')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-app-heading">Dashboard</a>
    <span>/</span>
    <span class="font-medium text-app-heading">Posts</span>
@endsection

@section('content')
    <x-admin.page-heading
        title="Public Posts"
        subtitle="Create, edit, and manage community posts."
    >
        <x-slot:actions>
            <x-admin.button :href="route('admin.posts.create')">
                New Post
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-heading>

    <x-admin.card :padding="false">
        @if ($posts->isEmpty())
            <div class="p-6">
                <x-admin.empty-state title="No posts yet" description="Publish your first public post.">
                    <x-slot:action>
                        <x-admin.button :href="route('admin.posts.create')">
                            Create Post
                        </x-admin.button>
                    </x-slot:action>
                </x-admin.empty-state>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-app-border text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-app-muted">
                        <tr>
                            <th class="px-6 py-3.5">Title</th>
                            <th class="px-6 py-3.5">Author</th>
                            <th class="px-6 py-3.5">Preview</th>
                            <th class="px-6 py-3.5">Updated</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-app-border bg-white">
                        @foreach ($posts as $post)
                            <tr class="hover:bg-slate-50/75">
                                <td class="px-6 py-4 font-medium text-app-heading">
                                    <a href="{{ route('admin.posts.show', (string) $post->_id) }}" class="hover:text-brand-600">
                                        {{ $post->title }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-app-muted">
                                    @if ($post->user)
                                        <a href="{{ route('admin.users.posts', (string) $post->user->_id) }}" class="text-brand-600 hover:text-brand-700">
                                            {{ $post->user->name }}
                                        </a>
                                    @else
                                        <span>Unknown</span>
                                    @endif
                                </td>
                                <td class="max-w-xs truncate px-6 py-4 text-app-muted">
                                    {{ \Illuminate\Support\Str::limit(\App\Support\HtmlSanitizer::toPlainText((string) $post->content), 70) }}
                                </td>
                                <td class="px-6 py-4 text-app-muted">
                                    {{ $post->updated_at?->format('M j, Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-3">
                                        <a href="{{ route('admin.posts.show', (string) $post->_id) }}" class="text-xs font-medium text-slate-600 hover:text-app-heading">
                                            View
                                        </a>
                                        <a href="{{ route('admin.posts.edit', (string) $post->_id) }}" class="text-xs font-medium text-brand-600 hover:text-brand-700">
                                            Edit
                                        </a>
                                        <x-admin.confirm-dialog
                                            title="Delete post"
                                            :message="'Delete \''.$post->title.'\'? This cannot be undone.'"
                                            :action="route('admin.posts.destroy', (string) $post->_id)"
                                        >
                                            <x-slot:trigger>
                                                <button type="button" class="text-xs font-medium text-status-danger hover:text-red-700 cursor-pointer">
                                                    Delete
                                                </button>
                                            </x-slot:trigger>
                                        </x-admin.confirm-dialog>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-admin.pagination :paginator="$posts" />
        @endif
    </x-admin.card>
@endsection
