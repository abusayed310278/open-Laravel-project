@extends(request()->routeIs('business.*') ? 'layouts.business' : (request()->routeIs('saler.*') ? 'layouts.saler' : 'layouts.admin'))

@section('title', 'Add Brand')

@section('content')
    @php
        $portalPrefix = request()->routeIs('business.*') ? 'business.' : (request()->routeIs('saler.*') ? 'saler.' : 'admin.');
    @endphp

    <x-breadcrumb :items="['Brands' => route($portalPrefix . 'brands.index'), 'Add' => null]" />

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route($portalPrefix . 'brands.store') }}" enctype="multipart/form-data" class="space-y-5">
            @include('admin.brands._form')
        </form>
    </x-card>
@endsection
