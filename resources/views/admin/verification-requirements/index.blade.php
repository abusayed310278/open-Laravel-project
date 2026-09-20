@extends('layouts.admin')

@section('title', 'Verification Requirements')

@section('content')
    @include('admin.verifications._tabs')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <x-card title="Add Requirement">
        <form method="POST" action="{{ route('admin.verification-requirements.store') }}" class="grid sm:grid-cols-4 gap-4 items-end">
            @csrf
            <x-select label="Role" name="role" :options="collect($roles)->mapWithKeys(fn ($role) => [$role->value => $role->label()])->all()" />
            <x-select label="Document type" name="document_type" :options="collect($documentTypes)->mapWithKeys(fn ($type) => [$type->value => $type->label()])->all()" />
            <label class="flex items-center gap-2 text-sm text-gray-600 pb-2.5">
                <input type="checkbox" name="is_required" value="1" checked class="w-4 h-4 rounded accent-brand-500">
                Required
            </label>
            <x-button type="submit">Add</x-button>
        </form>
    </x-card>

    @foreach ($roles as $role)
        <x-card :title="$role->label().' Requirements'">
            @php($items = $requirementsByRole->get($role->value, collect()))

            @if ($items->isEmpty())
                <p class="text-sm text-gray-400">No requirements configured.</p>
            @else
                <x-table :headers="['Document', 'Required', 'Status', '']">
                    @foreach ($items as $item)
                        <tr class="border-b border-gray-50">
                            <td class="px-4 py-3 text-gray-800">{{ $item->document_type->label() }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $item->is_required ? 'Yes' : 'Optional' }}</td>
                            <td class="px-4 py-3">
                                <x-badge :color="$item->is_active ? 'green' : 'gray'">{{ $item->is_active ? 'Active' : 'Inactive' }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center justify-end gap-1">
                                    <form method="POST" action="{{ route('admin.verification-requirements.toggle', $item) }}" class="inline-block m-0">
                                        @csrf @method('PATCH')
                                        @if ($item->is_active)
                                            <button type="submit" class="p-1.5 text-emerald-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition cursor-pointer" title="Active (Click to Deactivate)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            </button>
                                        @else
                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition cursor-pointer" title="Inactive (Click to Activate)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                            </button>
                                        @endif
                                    </form>
                                    <form method="POST" action="{{ route('admin.verification-requirements.destroy', $item) }}" data-confirm="Remove this requirement?" class="inline-block m-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Remove requirement">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </x-card>
    @endforeach
@endsection
