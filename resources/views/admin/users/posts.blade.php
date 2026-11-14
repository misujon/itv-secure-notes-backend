@extends('layouts.admin')

@section('title', $user['name'].' — Posts')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-app-heading">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.users.index') }}" class="hover:text-app-heading">Users</a>
    <span>/</span>
    <a href="{{ route('admin.users.show', $user['id']) }}" class="hover:text-app-heading">{{ $user['name'] }}</a>
    <span>/</span>
    <span class="font-medium text-app-heading">Posts</span>
@endsection

@section('content')
    <x-admin.page-heading
        :title="$user['name'].'\'s Public Posts'"
        :subtitle="'Retrieved via single MongoDB $lookup aggregation pipeline ('.$user['email'].')'"
    >
        <x-slot:actions>
            <x-admin.button variant="secondary" :href="route('admin.users.show', $user['id'])">
                Back to User
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-heading>

    <x-admin.card :padding="false">
        @if ($posts->isEmpty())
            <div class="p-6">
                <x-admin.empty-state title="No posts yet" description="This user has not published any public posts." />
            </div>
        @else
            <div class="divide-y divide-app-border">
                @foreach ($posts as $post)
                    <article class="p-6">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="text-base font-semibold text-app-heading">{{ $post['title'] }}</h3>
                            @if (! empty($post['created_at']))
                                <time class="text-xs text-app-muted">
                                    {{ \Illuminate\Support\Carbon::parse($post['created_at'])->format('M j, Y g:i A') }}
                                </time>
                            @endif
                        </div>
                        <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-slate-600">{{ $post['content'] }}</p>
                    </article>
                @endforeach
            </div>

            <x-admin.pagination :paginator="$posts" />
        @endif
    </x-admin.card>
@endsection
