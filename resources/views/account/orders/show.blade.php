@extends('layouts.customer')

@section('account-content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">Order {{ $order->order_number }}</h1>
            <x-badge :color="$order->status->badgeColor()">{{ $order->status->label() }}</x-badge>
        </div>

        @foreach ($order->vendorOrders as $vendorOrder)
            @php
                $submission = $vendorOrder->manualPaymentSubmission;
                $activeRefund = $vendorOrder->refunds->first(fn ($r) => in_array($r->status->value, ['pending', 'approved', 'processing']));
            @endphp

            <x-card :title="'#'.$vendorOrder->vendor_order_number">
                <x-slot:action>
                    <x-badge :color="$vendorOrder->status->badgeColor()">{{ $vendorOrder->status->label() }}</x-badge>
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

                <p class="text-xs text-gray-400 mt-3">Payment: {{ $vendorOrder->payment_method->label() }}</p>

                @if ($vendorOrder->payment_method->value === 'manual_bank')
                    <div class="mt-4 border-t border-gray-50 pt-4">
                        @php
                            $bank = $vendorOrder->payment_route->value === 'openbox'
                                ? $openboxBankDetails
                                : ($vendorOrder->vendor->paymentSettings->bank_details ?? null);
                        @endphp

                        @if ($bank)
                            <div class="bg-gray-50 rounded-md p-4 text-sm text-gray-700 mb-4">
                                <p class="font-medium text-gray-800 mb-1">Bank transfer details</p>
                                @if (is_array($bank))
                                    <p>{{ $bank['bank_name'] ?? '' }}</p>
                                    <p>{{ $bank['account_name'] ?? '' }}</p>
                                    <p>{{ $bank['account_number'] ?? '' }}</p>
                                    <p>{{ $bank['routing_number'] ?? '' }}</p>
                                @else
                                    <p class="whitespace-pre-line">{{ $bank }}</p>
                                @endif
                            </div>
                        @endif

                        @if (! $submission)
                            <p class="text-sm font-medium text-gray-800 mb-2">Submit your bank transfer proof</p>
                            <form method="POST" action="{{ route('account.payment-proof.store', $vendorOrder) }}" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <input type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf" required class="text-sm">
                                <div class="grid sm:grid-cols-2 gap-3">
                                    <x-input label="Reference (optional)" name="reference" type="text" />
                                    <x-input label="Bank name (optional)" name="bank_name" type="text" />
                                </div>
                                <x-button type="submit" size="sm">Submit Proof</x-button>
                            </form>
                        @else
                            <p class="text-sm text-gray-600">
                                Payment proof: <x-badge :color="$submission->status->badgeColor()">{{ $submission->status->label() }}</x-badge>
                            </p>
                            @if ($submission->status->value === 'rejected')
                                <p class="text-xs text-red-500 mt-1">{{ $submission->notes }}</p>
                                <form method="POST" action="{{ route('account.payment-proof.store', $vendorOrder) }}" enctype="multipart/form-data" class="space-y-3 mt-3">
                                    @csrf
                                    <input type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf" required class="text-sm">
                                    <x-button type="submit" size="sm">Resubmit Proof</x-button>
                                </form>
                            @endif
                        @endif
                    </div>
                @endif

                @if (in_array($vendorOrder->status->value, ['delivered', 'shipped', 'out_for_delivery', 'confirmed', 'processing', 'packed']))
                    <div class="mt-4 border-t border-gray-50 pt-4">
                        @if ($activeRefund)
                            <p class="text-sm text-gray-600">
                                Refund request: <x-badge :color="$activeRefund->status->badgeColor()">{{ $activeRefund->status->label() }}</x-badge>
                            </p>
                        @else
                            <button type="button" onclick="document.getElementById('refund-{{ $vendorOrder->id }}').showModal()" class="text-sm text-red-600 font-medium hover:underline">Request a refund</button>

                            <dialog id="refund-{{ $vendorOrder->id }}" class="rounded-md p-6 w-full max-w-sm backdrop:bg-black/40">
                                <form method="POST" action="{{ route('account.refund-requests.store', $vendorOrder) }}" class="space-y-4">
                                    @csrf
                                    <x-textarea label="Reason for refund" name="reason" rows="3" required />
                                    <div class="flex justify-end gap-2">
                                        <button type="button" onclick="this.closest('dialog').close()" class="px-4 py-2 text-sm text-gray-500">Cancel</button>
                                        <x-button type="submit" variant="danger">Request Refund</x-button>
                                    </div>
                                </form>
                            </dialog>
                        @endif
                    </div>
                @endif
            </x-card>
        @endforeach

        @if (! empty($eligibleReviewables))
            <x-card title="Leave a Review">
                <div class="space-y-3">
                    @foreach ($eligibleReviewables as $reviewable)
                        <button type="button" onclick="document.getElementById('review-{{ $reviewable['type']->value }}-{{ $reviewable['id'] }}').showModal()" class="w-full flex items-center justify-between border border-gray-100 rounded-md p-3 text-left hover:bg-gray-50">
                            <span class="text-sm text-gray-700">
                                <span class="text-xs text-gray-400 uppercase tracking-wide">{{ $reviewable['type']->label() }}</span><br>
                                {{ $reviewable['label'] }}
                            </span>
                            <span class="text-brand-600 text-sm font-medium">Write a review</span>
                        </button>

                        <dialog id="review-{{ $reviewable['type']->value }}-{{ $reviewable['id'] }}" class="rounded-md p-6 w-full max-w-md backdrop:bg-black/40">
                            <form method="POST" action="{{ route('account.reviews.store', $order) }}" class="space-y-4">
                                @csrf
                                <input type="hidden" name="reviewable_type" value="{{ $reviewable['type']->value }}">
                                <input type="hidden" name="reviewable_id" value="{{ $reviewable['id'] }}">

                                <p class="font-medium text-gray-900">{{ $reviewable['label'] }}</p>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Rating</label>
                                    <select name="rating" class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                                        @for ($i = 5; $i >= 1; $i--)
                                            <option value="{{ $i }}">{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <x-input label="Title (optional)" name="title" type="text" />
                                <x-textarea label="Review" name="body" rows="4" required />

                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="this.closest('dialog').close()" class="px-4 py-2 text-sm text-gray-500">Cancel</button>
                                    <x-button type="submit">Submit Review</x-button>
                                </div>
                            </form>
                        </dialog>
                    @endforeach
                </div>
            </x-card>
        @endif

        <x-card title="Shipping Address">
            @if ($order->shippingAddress)
                <p class="text-sm text-gray-700">{{ $order->shippingAddress->name }} · {{ $order->shippingAddress->phone }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ $order->shippingAddress->oneLine() }}</p>
            @endif
        </x-card>

        <x-card title="Summary">
            <div class="space-y-1.5 text-sm">
                <div class="flex justify-between text-gray-500"><span>Subtotal</span><span>${{ number_format($order->subtotal, 2) }}</span></div>
                <div class="flex justify-between text-gray-500"><span>Shipping</span><span>${{ number_format($order->shipping_total, 2) }}</span></div>
                <div class="flex justify-between font-bold text-gray-900 pt-2 border-t border-gray-100 mt-2"><span>Total</span><span>${{ number_format($order->total, 2) }}</span></div>
            </div>
        </x-card>
    </div>
@endsection
