@extends('layouts.app')

@section('title', 'Access denied')

@section('content')
    <x-error-page
        code="403"
        title="You don't have access to this page"
        message="Your account doesn't have permission to view this. If you think that's wrong, contact support or head back to the homepage."
        icon='<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>'
    />
@endsection
