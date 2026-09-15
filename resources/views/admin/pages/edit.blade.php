@extends('layouts.admin')

@section('title', 'Edit Page')

@section('content')
    <form method="POST" action="{{ route('admin.pages.update', $page) }}">
        @csrf
        @method('PUT')
        @include('admin.pages._form')
    </form>
@endsection
