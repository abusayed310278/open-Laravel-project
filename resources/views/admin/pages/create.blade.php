@extends('layouts.admin')

@section('title', 'New Page')

@section('content')
    <form method="POST" action="{{ route('admin.pages.store') }}">
        @csrf
        @include('admin.pages._form')
    </form>
@endsection
