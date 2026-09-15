@extends('layouts.admin')

@section('title', 'Edit ' . $group->name)

@section('content')
    <x-breadcrumb :items="['Attribute Groups' => route('admin.attribute-groups.index'), $group->name => null]" />

    <div class="max-w-xl">
        <x-card :title="'Edit ' . $group->name">
            <form method="POST" action="{{ route('admin.attribute-groups.update', $group) }}" class="space-y-4">
                @method('PUT')
                @include('admin.attribute-groups._form', ['group' => $group])
            </form>
        </x-card>
    </div>
@endsection
