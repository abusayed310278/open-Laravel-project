@extends('layouts.admin')

@section('title', 'Manage Whole Site — Roles & Permissions')

@section('content')
<div class="space-y-6">
    @include('admin.site-management._tabs')

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Role-Based Access Control (RBAC) Matrix</h2>
            <p class="text-xs text-gray-500 mt-0.5">Configure system permissions for each user role dynamically.</p>
        </div>
    </div>

    {{-- Roles Accordion / Grid --}}
    <div class="space-y-6">
        @foreach ($roles as $role)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
                {{-- Role Header --}}
                <div class="p-5 bg-gray-50/60 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-extrabold text-gray-900">{{ $role->name }}</h3>
                            <span class="font-mono text-xs text-gray-500 bg-white px-2 py-0.5 rounded border border-gray-200">{{ $role->slug }}</span>
                            @if ($role->is_system)
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">System Role</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ $role->description }}</p>
                    </div>

                    <div class="text-xs text-gray-500 font-semibold self-end sm:self-auto">
                        <span class="text-brand-600 font-bold text-sm">{{ $role->permissions->count() }}</span> permissions assigned
                    </div>
                </div>

                {{-- Permissions Matrix Form --}}
                <form method="POST" action="{{ route('admin.site-management.roles.permissions', $role) }}" class="p-6">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        @foreach ($permissionsGrouped as $group => $permissions)
                            <div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                                    <span>{{ strtoupper($group) }} PERMISSIONS</span>
                                </h4>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach ($permissions as $perm)
                                        @php
                                            $isAssigned = $role->permissions->contains('id', $perm->id);
                                        @endphp
                                        <label class="flex items-start gap-3 p-3 rounded-xl border {{ $isAssigned ? 'bg-brand-50/40 border-brand-200' : 'bg-gray-50/50 border-gray-100 hover:bg-gray-50' }} transition-colors cursor-pointer select-none">
                                            <input
                                                type="checkbox"
                                                name="permissions[]"
                                                value="{{ $perm->id }}"
                                                {{ $isAssigned ? 'checked' : '' }}
                                                class="mt-0.5 w-4 h-4 rounded text-brand-500 focus:ring-brand-400 border-gray-300"
                                            >
                                            <div>
                                                <div class="text-xs font-bold text-gray-900">{{ $perm->name }}</div>
                                                <div class="font-mono text-[10px] text-gray-400 mt-0.5">{{ $perm->slug }}</div>
                                                <div class="text-[11px] text-gray-500 mt-1 leading-snug">{{ $perm->description }}</div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-end">
                        <button
                            type="submit"
                            class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-bold shadow-xs hover:shadow-sm transition-all cursor-pointer"
                        >
                            Save {{ $role->name }} Permissions
                        </button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endsection
