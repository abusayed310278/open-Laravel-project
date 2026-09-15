@extends('layouts.admin')

@section('title', 'Add Product')

@section('content')
    <x-breadcrumb :items="['Products' => route('admin.products.index'), 'Add' => null]" />

    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf
        @include('products._form')
    </form>
@endsection
