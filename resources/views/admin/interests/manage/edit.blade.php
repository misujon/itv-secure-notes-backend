@extends('layouts.admin')

@section('title', 'Edit Interest')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-app-heading">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.interests.manage.index') }}" class="hover:text-app-heading">Manage Interests</a>
    <span>/</span>
    <span class="font-medium text-app-heading">Edit</span>
@endsection

@section('content')
    <x-admin.page-heading
        title="Edit Interest"
        subtitle="Update interest name."
    />

    <x-admin.card class="max-w-xl">
        <form method="POST" action="{{ route('admin.interests.manage.update', (string) $interest->_id) }}" class="space-y-5">
            @csrf
            @method('PATCH')

            <x-admin.input label="Interest Name" name="name" :value="$interest->name" required />

            <div class="flex items-center justify-end gap-3 border-t border-app-border pt-5">
                <x-admin.button variant="secondary" :href="route('admin.interests.manage.index')">
                    Cancel
                </x-admin.button>
                <x-admin.button type="submit">
                    Save Changes
                </x-admin.button>
            </div>
        </form>
    </x-admin.card>
@endsection
