@extends('layouts.admin')

@section('title', 'Edit '.$brand->name)

@section('content')
    <x-breadcrumb :items="['Brands' => route('admin.brands.index'), $brand->name => null]" />

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.brands.update', $brand) }}" enctype="multipart/form-data" class="space-y-5">
            @include('admin.brands._form')
        </form>
    </x-card>
@endsection
