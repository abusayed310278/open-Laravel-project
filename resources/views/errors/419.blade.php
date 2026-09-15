@extends('layouts.app')

@section('title', 'Session expired')

@section('content')
    <x-error-page
        code="419"
        title="Your session expired"
        message="This page sat open too long and your session timed out for security. Please go back and try again."
        icon='<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
        primary-label="Back to homepage"
    />
@endsection
