@extends('layouts.admin')

@section('title', 'Verifiers')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <x-card title="Add Verifier">
        <form method="POST" action="{{ route('admin.verifiers.store') }}" class="grid sm:grid-cols-4 gap-4 items-end">
            @csrf
            <x-input label="Name" name="name" type="text" />
            <x-input label="Email" name="email" type="email" />
            <x-input label="Employee ID" name="employee_id" type="text" />
            <x-select label="Location" name="assigned_location_id" placeholder="Unassigned" :options="$locations->pluck('name', 'id')" />
            <x-button type="submit" class="sm:col-span-4">Create Verifier Account</x-button>
        </form>
    </x-card>

    <x-card title="All Verifiers">
        <x-table :headers="['Name', 'Email', 'Employee ID', 'Location']" id="verifiers-table">
            @forelse ($verifiers as $verifier)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $verifier->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $verifier->email }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $verifier->verifierProfile?->employee_id }}</td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('admin.verifiers.assign-location', $verifier->verifierProfile) }}">
                            @csrf @method('PATCH')
                            <select name="assigned_location_id" onchange="this.form.submit()" class="border border-gray-200 rounded-md px-2.5 py-1.5 text-xs bg-white">
                                <option value="">Unassigned</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}" @selected($verifier->verifierProfile?->assigned_location_id === $location->id)>{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-10 text-center text-gray-400 text-sm">No verifiers yet.</td>
                </tr>
            @endforelse
        </x-table>
    </x-card>
@endsection
