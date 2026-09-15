@extends('layouts.admin')

@section('title', 'Commission Rules')

@section('content')
    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <x-card title="Add Rule">
        <form method="POST" action="{{ route('admin.commission-rules.store') }}" class="grid sm:grid-cols-3 lg:grid-cols-6 gap-4 items-end">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Type</label>
                <select name="type" class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <x-input label="Reference ID (optional)" name="reference_id" type="number" />
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Commission</label>
                <select name="commission_type" class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                    @foreach ($commissionTypes as $ct)
                        <option value="{{ $ct->value }}">{{ $ct->label() }}</option>
                    @endforeach
                </select>
            </div>
            <x-input label="Value" name="value" type="number" step="0.01" min="0" required />
            <x-input label="Priority" name="priority" type="number" value="0" />
            <x-button type="submit">Add Rule</x-button>
        </form>
        <p class="text-xs text-gray-400 mt-3">Reference ID: product ID for "Specific Product", seller user ID for "Specific Seller", category ID for "Category". Leave blank for Global or Seller Type.</p>
    </x-card>

    <x-card title="Active Rules" class="mt-6">
        <x-table :headers="['Type', 'Reference', 'Commission', 'Priority', 'Status', '']" id="commission-rules-table">
            @forelse ($rules as $rule)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 text-gray-700">{{ $rule->type->label() }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $rule->reference_id ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-900">
                        {{ $rule->commission_type->value === 'percentage' ? number_format($rule->value, 2).'%' : '$'.number_format($rule->value, 2) }}
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $rule->priority }}</td>
                    <td class="px-4 py-3"><x-badge :color="$rule->is_active ? 'green' : 'gray'">{{ $rule->is_active ? 'Active' : 'Inactive' }}</x-badge></td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('admin.commission-rules.destroy', $rule) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 font-medium hover:underline text-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No commission rules yet — sales take 0% commission until one is added.</td>
                </tr>
            @endforelse
        </x-table>
    </x-card>
@endsection
