@extends('layouts.admin')

@section('title', 'Add Attribute Group')

@section('content')
    <x-breadcrumb :items="['Attribute Groups' => route('admin.attribute-groups.index'), 'Add Group' => null]" />

    <div class="max-w-xl">
        <x-card title="New Attribute Group">
            <form method="POST" action="{{ route('admin.attribute-groups.store') }}" class="space-y-4">
                @include('admin.attribute-groups._form', ['group' => $group])
            </form>
        </x-card>
    </div>
@endsection
