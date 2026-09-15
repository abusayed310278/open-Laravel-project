@extends('layouts.app')

@section('title', 'Too many requests')

@section('content')
    <x-error-page
        code="429"
        title="Slow down a little"
        message="You've made too many requests in a short time. Wait a minute and try again."
        icon='<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>'
    />
@endsection
