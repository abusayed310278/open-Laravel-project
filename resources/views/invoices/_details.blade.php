@php
    /** @var \App\Models\Invoice $invoice */
@endphp

<x-card :title="'Invoice '.$invoice->invoice_number">
    <x-slot:action>
        <x-badge :color="$invoice->payment_status->badgeColor()">{{ $invoice->payment_status->label() }}</x-badge>
    </x-slot:action>

    <div class="grid sm:grid-cols-2 gap-4 mb-6 text-sm">
        <div>
            <p class="text-gray-400 text-xs uppercase tracking-wide mb-1">Invoice</p>
            <p class="font-mono font-medium text-gray-900">{{ $invoice->invoice_number }}</p>
            <p class="text-gray-500 mt-1">Issued {{ $invoice->issued_at->format('M j, Y') }}</p>
        </div>
        <div class="sm:text-right">
            @isset($invoice->seller)
                <p class="text-gray-400 text-xs uppercase tracking-wide mb-1">Seller</p>
                <p class="text-gray-800">{{ $invoice->seller->name }}</p>
            @endisset
            @isset($invoice->buyer)
                <p class="text-gray-400 text-xs uppercase tracking-wide mb-1 mt-2">Buyer</p>
                <p class="text-gray-800">{{ $invoice->buyer->name }}</p>
            @endisset
        </div>
    </div>

    <div class="divide-y divide-gray-50">
        @foreach ($invoice->items as $item)
            <div class="flex items-center justify-between py-3 text-sm">
                <div>
                    <p class="font-medium text-gray-800">{{ $item->product_title }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $item->sku ?? '—' }} @if($item->grade) · Grade {{ $item->grade }} @endif · Qty {{ $item->quantity }}
                    </p>
                </div>
                <span class="font-semibold text-gray-900">${{ number_format($item->total_price, 2) }}</span>
            </div>
        @endforeach
    </div>

    <div class="space-y-1.5 text-sm mt-6 pt-4 border-t border-gray-100">
        <div class="flex justify-between text-gray-500"><span>Subtotal</span><span>${{ number_format($invoice->subtotal, 2) }}</span></div>
        <div class="flex justify-between text-gray-500"><span>Shipping</span><span>${{ number_format($invoice->shipping, 2) }}</span></div>
        <div class="flex justify-between text-gray-500"><span>Tax</span><span>${{ number_format($invoice->tax, 2) }}</span></div>
        @if ($invoice->discount > 0)
            <div class="flex justify-between text-gray-500"><span>Discount</span><span>-${{ number_format($invoice->discount, 2) }}</span></div>
        @endif
        <div class="flex justify-between font-bold text-gray-900 pt-2 border-t border-gray-100 mt-2"><span>Total</span><span>${{ number_format($invoice->total, 2) }}</span></div>
    </div>

    @isset($downloadRoute)
        <div class="mt-6">
            <x-button as="a" :href="$downloadRoute">Download PDF</x-button>
        </div>
    @endisset
</x-card>
