@extends('layouts.admin')

@section('title', 'Add Brand')

@section('content')
    <x-breadcrumb :items="['Brands' => route('admin.brands.index'), 'Add' => null]" />

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data" class="space-y-5">
            @include('admin.brands._form')
        </form>
    </x-card>
@endsection
