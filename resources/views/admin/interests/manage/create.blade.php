@extends('layouts.admin')

@section('title', 'Create Interest')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-app-heading">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.interests.manage.index') }}" class="hover:text-app-heading">Manage Interests</a>
    <span>/</span>
    <span class="font-medium text-app-heading">Create</span>
@endsection

@section('content')
    <x-admin.page-heading
        title="Create Interest"
        subtitle="Add a new interest option for users."
    />

    <x-admin.card class="max-w-xl">
        <form method="POST" action="{{ route('admin.interests.manage.store') }}" class="space-y-5">
            @csrf

            <x-admin.input label="Interest Name" name="name" placeholder="e.g. photography" required />

            <div class="flex items-center justify-end gap-3 border-t border-app-border pt-5">
                <x-admin.button variant="secondary" :href="route('admin.interests.manage.index')">
                    Cancel
                </x-admin.button>
                <x-admin.button type="submit">
                    Create Interest
                </x-admin.button>
            </div>
        </form>
    </x-admin.card>
@endsection
