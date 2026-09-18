@extends('layouts.admin')

@section('title', 'Verifiers')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @session('error')
        <x-alert type="error">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <x-slot:title>Verifiers</x-slot:title>
        <x-slot:action>
            <x-button type="button" data-modal-open="add-verifier-modal" size="sm">+ Add Verifier</x-button>
        </x-slot:action>

        <x-table :headers="['Verifier', 'Email', 'Employee ID', 'Assigned Location', 'Status', 'Actions']" id="verifiers-table">
            @forelse ($verifiers as $verifier)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-xs shrink-0 border border-brand-100">
                                {{ strtoupper(substr($verifier->name, 0, 2)) }}
                            </div>
                            <div>
                                <span class="font-medium text-gray-900 block">{{ $verifier->name }}</span>
                                <span class="text-[11px] text-gray-400">ID #{{ $verifier->id }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $verifier->email }}</td>
                    <td class="px-4 py-3 text-xs font-mono text-gray-700">
                        <span class="px-2 py-0.5 bg-gray-100 rounded text-gray-800 font-semibold">{{ $verifier->verifierProfile?->employee_id ?? '—' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('admin.verifiers.assign-location', $verifier->verifierProfile) }}" class="inline-block m-0">
                            @csrf
                            @method('PATCH')
                            <select name="assigned_location_id" onchange="this.form.submit()" class="border border-gray-200 rounded-md px-2 py-1 text-xs bg-white text-gray-700 hover:border-gray-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 cursor-pointer">
                                <option value="">Unassigned</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}" @selected($verifier->verifierProfile?->assigned_location_id === $location->id)>
                                        {{ $location->name }} ({{ $location->city }})
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td class="px-4 py-3">
                        <x-badge :color="$verifier->status->badgeColor()">{{ $verifier->status->label() }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center justify-end gap-1">
                            {{-- Toggle Active / Suspended Icon --}}
                            <form method="POST" action="{{ route('admin.verifiers.toggle-status', $verifier) }}" class="inline-block m-0">
                                @csrf
                                @method('PATCH')
                                @if ($verifier->status === \App\Enums\UserStatus::Active)
                                    <button type="submit" class="p-1.5 text-emerald-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition cursor-pointer" title="Active (Click to Suspend)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </button>
                                @else
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition cursor-pointer" title="Inactive (Click to Activate)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                    </button>
                                @endif
                            </form>

                            {{-- Edit Icon --}}
                            <button type="button" data-modal-open="edit-verifier-modal-{{ $verifier->id }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer" title="Edit verifier">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>

                            {{-- Delete Icon --}}
                            <form method="POST" action="{{ route('admin.verifiers.destroy', $verifier) }}" data-confirm="Remove this verifier account?" class="inline-block m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete verifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No verifiers found.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$verifiers" />
    </x-card>

    {{-- Add Verifier Modal --}}
    <x-modal id="add-verifier-modal" title="Add Verifier Account" maxWidth="max-w-xl">
        <form method="POST" action="{{ route('admin.verifiers.store') }}" class="space-y-4">
            @csrf
            <x-input label="Full Name" name="name" type="text" :value="old('name')" placeholder="e.g. John Doe" required />

            <div class="grid grid-cols-2 gap-3">
                <x-input label="Email Address" name="email" type="email" :value="old('email')" placeholder="verifier@openbox.com" required />
                <x-input label="Employee ID" name="employee_id" type="text" :value="old('employee_id')" placeholder="e.g. EMP-2026-001" required />
            </div>

            <div>
                <x-select label="Assigned Location / Hub" name="assigned_location_id" placeholder="Select Location (Optional)" :options="$locations->pluck('name', 'id')" :selected="old('assigned_location_id')" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="add_verifier_password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="add_verifier_password"
                            placeholder="Enter password"
                            required
                            class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent pr-11"
                        >
                        <button type="button" data-password-toggle="add_verifier_password" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer" aria-label="Toggle password visibility">
                            <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="add_verifier_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Confirm Password</label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password_confirmation"
                            id="add_verifier_password_confirmation"
                            placeholder="Re-enter password"
                            required
                            class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent pr-11"
                        >
                        <button type="button" data-password-toggle="add_verifier_password_confirmation" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer" aria-label="Toggle password confirmation visibility">
                            <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                    Cancel
                </button>
                <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">Create Verifier Account</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Edit Verifier Modals --}}
    @foreach ($verifiers as $verifier)
        <x-modal id="edit-verifier-modal-{{ $verifier->id }}" title="Edit Verifier: {{ $verifier->name }}" maxWidth="max-w-xl">
            <form method="POST" action="{{ route('admin.verifiers.update', $verifier) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <x-input label="Full Name" name="name" type="text" :value="old('name', $verifier->name)" required />

                <div class="grid grid-cols-2 gap-3">
                    <x-input label="Email Address" name="email" type="email" :value="old('email', $verifier->email)" required />
                    <x-input label="Employee ID" name="employee_id" type="text" :value="old('employee_id', $verifier->verifierProfile?->employee_id)" required />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <x-select label="Assigned Location" name="assigned_location_id" placeholder="Unassigned" :options="$locations->pluck('name', 'id')" :selected="old('assigned_location_id', (string)$verifier->verifierProfile?->assigned_location_id)" />
                    <x-select label="Account Status" name="status" :options="['active' => 'Active', 'suspended' => 'Suspended', 'pending' => 'Pending', 'blocked' => 'Blocked']" :selected="old('status', $verifier->status->value)" required />
                </div>

                <div>
                    <label for="edit_verifier_password_{{ $verifier->id }}" class="block text-sm font-medium text-gray-700 mb-1.5">Reset Password (Optional)</label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="edit_verifier_password_{{ $verifier->id }}"
                            placeholder="Leave blank to keep existing password"
                            class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent pr-11"
                        >
                        <button type="button" data-password-toggle="edit_verifier_password_{{ $verifier->id }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer" aria-label="Toggle password visibility">
                            <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                    <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                        Cancel
                    </button>
                    <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">Update Verifier</x-button>
                </div>
            </form>
        </x-modal>
    @endforeach

    <script>
        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('add-verifier-modal')?.classList.remove('hidden');
            });
        @endif
    </script>
@endsection
