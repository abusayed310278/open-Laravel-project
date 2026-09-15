@extends('layouts.admin')

@section('title', 'Add Category')

@section('content')
    <x-breadcrumb :items="['Categories' => route('admin.categories.index'), 'Add' => null]" />

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="space-y-5">
            @include('admin.categories._form')
        </form>
    </x-card>
@endsection
