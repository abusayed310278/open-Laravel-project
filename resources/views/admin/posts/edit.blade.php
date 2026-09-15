@extends('layouts.admin')

@section('title', 'Edit Post')

@section('content')
    <form method="POST" action="{{ route('admin.blog.update', $post) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.posts._form')
    </form>
@endsection
