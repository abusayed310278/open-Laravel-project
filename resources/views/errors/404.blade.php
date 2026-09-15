@extends('layouts.app')

@section('title', 'Page not found')

@section('content')
    <x-error-page
        code="404"
        title="We couldn't find that page"
        message="The link might be broken, or the page may have moved. Try searching, or head back to the marketplace."
        icon='<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>'
        primary-label="Browse the marketplace"
        :primary-href="Route::has('shop') ? route('shop') : route('home')"
    />
@endsection
