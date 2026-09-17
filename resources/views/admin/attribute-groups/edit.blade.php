@extends(request()->routeIs('business.*') ? 'layouts.business' : (request()->routeIs('saler.*') ? 'layouts.saler' : 'layouts.admin'))

@section('title', 'Edit ' . $group->name)

@php
    $portalPrefix = request()->routeIs('business.*') ? 'business.' : (request()->routeIs('saler.*') ? 'saler.' : 'admin.');
@endphp

@section('content')
    <x-breadcrumb :items="['Attribute Groups' => route($portalPrefix . 'attribute-groups.index'), $group->name => null]" />

    <div class="max-w-xl">
        <x-card :title="'Edit ' . $group->name">
            <form method="POST" action="{{ route($portalPrefix . 'attribute-groups.update', $group) }}" class="space-y-4">
                @method('PUT')
                @include('admin.attribute-groups._form', ['group' => $group])
            </form>
        </x-card>
    </div>
@endsection
