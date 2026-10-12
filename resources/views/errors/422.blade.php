@extends('layouts.app')

@section('title', 'Unable to process request')

@section('content')
    <x-error-page
        code="422"
        title="Unprocessable Request"
        :message="$exception?->getMessage() ?: 'The request could not be processed due to invalid or missing data. Please verify your details and try again.'"
        icon='<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>'
        primary-label="Return to cart"
        :primary-href="route('cart')"
    />
@endsection
