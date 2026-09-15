@extends('layouts.admin')

@section('title', 'Settings — System')

@section('content')
    @include('admin.settings._tabs')

    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <x-card title="Maintenance">
        <p class="text-sm text-gray-500 mb-5">
            These run the equivalent Artisan command directly against this server. Every run is written to the
            activity log.
        </p>

        <div class="grid sm:grid-cols-2 gap-4">
            @foreach ($actions as $key => $label)
                <form
                    method="POST" action="{{ route('admin.settings.system.run', $key) }}"
                    data-confirm="{{ $label }}? This runs immediately."
                    class="border border-gray-100 rounded-md p-4 flex items-center justify-between gap-3"
                >
                    @csrf
                    <span class="text-sm font-medium text-gray-800">{{ $label }}</span>
                    <x-button type="submit" variant="secondary" size="sm">Run</x-button>
                </form>
            @endforeach
        </div>
    </x-card>
@endsection
