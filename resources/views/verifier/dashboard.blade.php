@extends('layouts.verifier')

@section('title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-5">
        <x-stat-card label="Today's Appointments" value="0" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>' />
        <x-stat-card label="Pending Inspections" value="0" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>' />
        <x-stat-card label="Completed This Month" value="0" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' />
    </div>

    <x-card title="Getting Started">
        <p class="text-sm text-gray-500 leading-relaxed">
            This is the foundation shell for the Verifier Portal. The inspection checklist, grade assignment form,
            and appointment queue will populate once the Product Verification phase lands.
        </p>
    </x-card>
@endsection
