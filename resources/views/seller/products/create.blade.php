@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Add Product')

@section('content')
    @php($routePrefix = auth()->user()->isBusiness() ? 'business.' : 'saler.')

    <x-breadcrumb :items="['Products' => route($routePrefix.'products.index'), 'Add' => null]" />

    <form method="POST" action="{{ route($routePrefix.'products.store') }}" enctype="multipart/form-data">
        @csrf
        @include('products._form')
    </form>
@endsection
