@extends('layouts.admin')

@section('title', 'Verification Requirements')

@section('content')

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
                                <div class="flex items-center justify-end gap-3">
                                    <form method="POST" action="{{ route('admin.verification-requirements.toggle', $item) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-xs font-medium text-gray-500 hover:text-brand-600">
                                            {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.verification-requirements.destroy', $item) }}" data-confirm="Remove this requirement?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700">Remove</button>
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
