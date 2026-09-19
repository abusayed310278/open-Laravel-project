@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Order '.$vendorOrder->vendor_order_number)

@section('content')
    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-card :title="'#'.$vendorOrder->vendor_order_number">
                <x-slot:action>
                    <div class="flex items-center gap-2">
                        @if ($vendorOrder->invoice)
                            <a href="{{ route($routePrefix.'invoices.show', $vendorOrder->invoice) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Invoice {{ $vendorOrder->invoice->invoice_number }}
                            </a>
                        @endif
                        <x-badge :color="$vendorOrder->status->badgeColor()">{{ $vendorOrder->status->label() }}</x-badge>
                    </div>
                </x-slot:action>

                <div class="divide-y divide-gray-50">
                    @foreach ($vendorOrder->items as $item)
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $item->product_title }}</p>
                                <p class="text-xs text-gray-400">Qty {{ $item->quantity }} · ${{ number_format($item->unit_price, 2) }} each</p>
                            </div>
                            <span class="text-sm font-semibold text-gray-900">${{ number_format($item->total_price, 2) }}</span>
                        </div>
                    @endforeach
                </div>

                @if ($vendorOrder->tracking_number)
                    <p class="text-xs text-gray-400 mt-3">Tracking: {{ $vendorOrder->tracking_number }}</p>
                @endif
            </x-card>

            <x-card title="Shipping Address">
                @if ($vendorOrder->order->shippingAddress)
                    <p class="text-sm text-gray-700">{{ $vendorOrder->order->shippingAddress->name }} · {{ $vendorOrder->order->shippingAddress->phone }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ $vendorOrder->order->shippingAddress->oneLine() }}</p>
                @endif
            </x-card>

            <x-card title="History">
                <div class="space-y-3">
                    @foreach ($vendorOrder->statusHistories as $history)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-700">{{ \App\Enums\OrderStatus::from($history->status)->label() }}</span>
                            <span class="text-gray-400">{{ $history->created_at->format('M j, Y g:ia') }}</span>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>

        <div>
            <x-card title="Customer">
                <p class="text-sm text-gray-700">{{ $vendorOrder->order->customer->name }}</p>
                <p class="text-xs text-gray-400">{{ $vendorOrder->order->customer->email }}</p>
            </x-card>

            @if (! empty($nextStatuses))
                <x-card title="Update Status" class="mt-6">
                    <form method="POST" action="{{ route($routePrefix.'orders.status', $vendorOrder) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">New status</label>
                            <select name="status" class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                                @foreach ($nextStatuses as $status)
                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-input label="Tracking number (optional)" name="tracking_number" type="text" />
                        <x-button type="submit" class="w-full justify-center">Update</x-button>
                    </form>
                </x-card>
            @endif
        </div>
    </div>
@endsection
