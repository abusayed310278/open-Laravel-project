@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
    @php
        $tabs = [
            'overview' => 'Overview',
            'users' => 'Users',
            'products' => 'Products',
            'subscriptions' => 'Subscriptions',
            'verification' => 'Verification',
            'warehouse' => 'Warehouse',
            'payouts' => 'Payouts',
        ];
    @endphp

    <div class="border-b border-gray-100 flex items-center gap-6 mb-6 overflow-x-auto">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('admin.reports.index', ['tab' => $key, 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}"
               class="text-sm font-medium py-3 border-b-2 whitespace-nowrap {{ $tab === $key ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <x-card>
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <x-input label="From" name="from" type="date" :value="$from->format('Y-m-d')" />
            <x-input label="To" name="to" type="date" :value="$to->format('Y-m-d')" />
            <x-button type="submit">Apply</x-button>
        </form>
    </x-card>

    @if ($tab === 'overview')
        @include('admin.reports._overview', ['d' => $data])
    @elseif ($tab === 'users')
        @include('admin.reports._users', ['d' => $data])
    @elseif ($tab === 'products')
        @include('admin.reports._products', ['d' => $data])
    @elseif ($tab === 'subscriptions')
        @include('admin.reports._subscriptions', ['d' => $data])
    @elseif ($tab === 'verification')
        @include('admin.reports._verification', ['d' => $data])
    @elseif ($tab === 'warehouse')
        @include('admin.reports._warehouse', ['d' => $data])
    @elseif ($tab === 'payouts')
        @include('admin.reports._payouts', ['d' => $data])
    @endif
@endsection
