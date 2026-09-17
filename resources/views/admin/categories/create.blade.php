@extends(request()->routeIs('business.*') ? 'layouts.business' : (request()->routeIs('saler.*') ? 'layouts.saler' : 'layouts.admin'))

@section('title', 'Add Category')

@php
    $portalPrefix = request()->routeIs('business.*') ? 'business.' : (request()->routeIs('saler.*') ? 'saler.' : 'admin.');
@endphp

@section('content')
    <x-breadcrumb :items="['Categories' => route($portalPrefix . 'categories.index'), 'Add' => null]" />

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route($portalPrefix . 'categories.store') }}" enctype="multipart/form-data" class="space-y-5">
            @include('admin.categories._form')
        </form>
    </x-card>
@endsection
