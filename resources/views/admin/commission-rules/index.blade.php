@extends('layouts.admin')

@section('title', 'Commission Rules')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @session('error')
        <x-alert type="error">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <x-slot:title>Commission Rules</x-slot:title>
        <x-slot:action>
            <x-button type="button" data-modal-open="add-rule-modal" size="sm">+ Add Rule</x-button>
        </x-slot:action>

        <x-table :headers="['Rule Type', 'Reference', 'Commission Rate', 'Priority', 'Status', 'Actions']" id="commission-rules-table">
            @forelse ($rules as $rule)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span class="p-1 rounded bg-brand-50 text-brand-600 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </span>
                            <span class="font-medium text-gray-900">{{ $rule->type->label() }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        @if ($rule->reference_id)
                            <span class="px-2 py-0.5 bg-gray-100 rounded text-gray-800 font-mono text-xs font-semibold">ID #{{ $rule->reference_id }}</span>
                        @else
                            <span class="text-gray-400 text-xs italic">Universal (All)</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                        @if ($rule->commission_type->value === 'percentage')
                            <span class="text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md">{{ number_format($rule->value, 2) }}%</span>
                        @else
                            <span class="text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md">${{ number_format($rule->value, 2) }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs font-mono text-gray-600">{{ $rule->priority }}</td>
                    <td class="px-4 py-3">
                        <x-badge :color="$rule->is_active ? 'green' : 'gray'">{{ $rule->is_active ? 'Active' : 'Inactive' }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center justify-end gap-1">
                            {{-- Toggle Status Icon --}}
                            <form method="POST" action="{{ route('admin.commission-rules.toggle-active', $rule) }}" class="inline-block m-0">
                                @csrf
                                @method('PATCH')
                                @if ($rule->is_active)
                                    <button type="submit" class="p-1.5 text-emerald-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition cursor-pointer" title="Active (Click to Deactivate)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </button>
                                @else
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition cursor-pointer" title="Inactive (Click to Activate)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                    </button>
                                @endif
                            </form>

                            {{-- Edit Icon --}}
                            <button type="button" data-modal-open="edit-rule-modal-{{ $rule->id }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer" title="Edit rule">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>

                            {{-- Delete Icon --}}
                            <form method="POST" action="{{ route('admin.commission-rules.destroy', $rule) }}" data-confirm="Delete this commission rule?" class="inline-block m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete rule">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No commission rules yet — sales take 0% commission until one is added.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$rules" />
    </x-card>

    {{-- Add Commission Rule Modal --}}
    <x-modal id="add-rule-modal" title="Add Commission Rule" maxWidth="max-w-lg">
        <form method="POST" action="{{ route('admin.commission-rules.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Rule Target Type</label>
                <select name="type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500" required>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>

            <x-input label="Reference ID (Optional)" name="reference_id" type="number" :value="old('reference_id')" placeholder="Product ID, Seller User ID, or Category ID" />
            <p class="text-[11px] text-gray-400 -mt-2">Product ID for "Specific Product", Seller ID for "Specific Seller", Category ID for "Category". Leave blank for Global.</p>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Commission Type</label>
                    <select name="commission_type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500" required>
                        @foreach ($commissionTypes as $ct)
                            <option value="{{ $ct->value }}" @selected(old('commission_type') === $ct->value)>{{ $ct->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <x-input label="Commission Value" name="value" type="number" step="0.01" min="0" :value="old('value')" placeholder="e.g. 5.00 or 10.00" required />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <x-input label="Rule Priority" name="priority" type="number" :value="old('priority', 0)" placeholder="0 (Higher = evaluated first)" />
                <div>
                    <x-select label="Status" name="is_active" :options="['1' => 'Active', '0' => 'Inactive']" :selected="old('is_active', '1')" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                    Cancel
                </button>
                <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">Save Rule</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Edit Commission Rule Modals --}}
    @foreach ($rules as $rule)
        <x-modal id="edit-rule-modal-{{ $rule->id }}" title="Edit Commission Rule" maxWidth="max-w-lg">
            <form method="POST" action="{{ route('admin.commission-rules.update', $rule) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Rule Target Type</label>
                    <select name="type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500" required>
                        @foreach ($types as $type)
                            <option value="{{ $type->value }}" @selected(old('type', $rule->type->value) === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <x-input label="Reference ID (Optional)" name="reference_id" type="number" :value="old('reference_id', $rule->reference_id)" placeholder="Product ID, Seller ID, or Category ID" />

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Commission Type</label>
                        <select name="commission_type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500" required>
                            @foreach ($commissionTypes as $ct)
                                <option value="{{ $ct->value }}" @selected(old('commission_type', $rule->commission_type->value) === $ct->value)>{{ $ct->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-input label="Commission Value" name="value" type="number" step="0.01" min="0" :value="old('value', $rule->value)" required />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <x-input label="Rule Priority" name="priority" type="number" :value="old('priority', $rule->priority)" />
                    <div>
                        <x-select label="Status" name="is_active" :options="['1' => 'Active', '0' => 'Inactive']" :selected="old('is_active', (string)(int)$rule->is_active)" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                    <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                        Cancel
                    </button>
                    <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">Update Rule</x-button>
                </div>
            </form>
        </x-modal>
    @endforeach

    <script>
        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('add-rule-modal')?.classList.remove('hidden');
            });
        @endif
    </script>
@endsection
