@extends(request()->routeIs('business.*') ? 'layouts.business' : (request()->routeIs('saler.*') ? 'layouts.saler' : 'layouts.admin'))

@section('title', 'Add Attribute Group')

@php
    $portalPrefix = request()->routeIs('business.*') ? 'business.' : (request()->routeIs('saler.*') ? 'saler.' : 'admin.');
@endphp

@section('content')
    <x-breadcrumb :items="['Attribute Groups' => route($portalPrefix . 'attribute-groups.index'), 'Add Group' => null]" />

    <div class="max-w-xl">
        <x-card title="New Attribute Group">
            <form method="POST" action="{{ route($portalPrefix . 'attribute-groups.store') }}" class="space-y-4">
                @include('admin.attribute-groups._form', ['group' => $group])
            </form>
        </x-card>
    </div>
@endsection
