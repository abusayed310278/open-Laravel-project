@extends('layouts.customer')

@section('account-content')
    <div class="space-y-6">
        <h1 class="text-xl font-bold text-gray-900">Return Requests</h1>

        <x-card>
            @forelse ($refunds as $refund)
                <div class="flex items-center justify-between py-4 border-b border-gray-50 last:border-0">
                    <div>
                        <p class="font-semibold text-gray-900">
                            {{ $refund->vendorOrder?->items?->first()?->product_title ?? 'Order '.$refund->vendorOrder?->vendor_order_number }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Return requested on {{ $refund->created_at->format('M j, Y') }} · Reason: {{ $refund->reason }}
                        </p>
                    </div>
                    <x-badge :color="$refund->status->badgeColor()">{{ $refund->status->label() }}</x-badge>
                </div>
            @empty
                <p class="text-sm text-gray-400 py-6 text-center">No return requests.</p>
            @endforelse
        </x-card>
    </div>
@endsection
