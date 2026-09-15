@extends('layouts.app')

@section('title', 'Something went wrong')

@section('content')
    <x-error-page
        code="500"
        title="Something went wrong on our end"
        message="This one's on us, not you. Our team has been notified — please try again in a moment."
        icon='<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>'
    />
@endsection
