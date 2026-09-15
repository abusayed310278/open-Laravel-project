@extends('layouts.admin')

@section('title', 'New Post')

@section('content')
    <form method="POST" action="{{ route('admin.blog.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.posts._form')
    </form>
@endsection
