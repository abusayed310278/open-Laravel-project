<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #27272a; line-height: 1.4; }
        h1 { font-size: 20px; margin: 0 0 4px; color: #111827; }
        .muted { color: #71717a; }
        .header { width: 100%; margin-bottom: 24px; }
        .header td { vertical-align: top; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table.items th { text-align: left; border-bottom: 1px solid #d4d4d8; padding: 6px 4px; font-size: 11px; text-transform: uppercase; color: #71717a; }
        table.items td { padding: 8px 4px; border-bottom: 1px solid #f4f4f5; font-size: 12px; }
        table.items td.num, table.items th.num { text-align: right; }
        table.totals { width: 260px; margin-top: 16px; margin-left: auto; }
        table.totals td { padding: 4px 0; font-size: 12px; }
        table.totals td.num { text-align: right; }
        table.totals tr.total td { font-weight: bold; font-size: 14px; border-top: 1px solid #27272a; padding-top: 8px; color: #111827; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 10px; font-size: 11px; font-weight: bold; background: #ecfdf5; color: #047857; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                <h1>{{ config('app.name', 'Openbox') }}</h1>
                <p class="muted">Order Invoice #{{ $order->order_number }}</p>
            </td>
            <td style="text-align: right;">
                <p class="muted">Date: {{ $order->created_at->format('M j, Y') }}</p>
                <span class="badge">{{ $order->status->label() }}</span>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 14px;">
                <p><strong>Billed To:</strong><br>
                {{ $order->customer->name }}<br>
                <span class="muted">{{ $order->customer->email }}</span>
                @if ($order->billingAddress)
                    <br>{{ $order->billingAddress->address_line1 }}
                    @if ($order->billingAddress->address_line2), {{ $order->billingAddress->address_line2 }}@endif
                    <br>{{ $order->billingAddress->city }}, {{ $order->billingAddress->state }} {{ $order->billingAddress->postal_code }}
                    <br>{{ $order->billingAddress->country }}
                @endif
                </p>
            </td>
            <td style="text-align: right; padding-top: 14px;">
                <p><strong>Shipped To:</strong><br>
                @if ($order->shippingAddress)
                    {{ $order->shippingAddress->full_name ?? $order->customer->name }}<br>
                    {{ $order->shippingAddress->address_line1 }}
                    @if ($order->shippingAddress->address_line2), {{ $order->shippingAddress->address_line2 }}@endif
                    <br>{{ $order->shippingAddress->city }}, {{ $order->shippingAddress->state }} {{ $order->shippingAddress->postal_code }}
                    <br>{{ $order->shippingAddress->country }}
                @else
                    {{ $order->customer->name }}
                @endif
                </p>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Item</th>
                <th>Seller</th>
                <th>SKU</th>
                <th>Condition</th>
                <th class="num">Qty</th>
                <th class="num">Unit Price</th>
                <th class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td><strong>{{ $item->product_title }}</strong></td>
                    <td class="muted">{{ $item->vendorOrder?->vendor?->name ?? 'Openbox' }}</td>
                    <td class="muted">{{ $item->sku ?? '—' }}</td>
                    <td class="muted">{{ ucfirst($item->product_condition ?? 'Refurbished') }}</td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="num">${{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="num">${{ number_format($order->subtotal, 2) }}</td></tr>
        <tr><td>Shipping</td><td class="num">${{ number_format($order->shipping_total, 2) }}</td></tr>
        <tr><td>Tax</td><td class="num">${{ number_format($order->tax_total, 2) }}</td></tr>
        @if ($order->discount > 0 || $order->coupon_discount > 0)
            <tr><td>Discount</td><td class="num">-${{ number_format($order->discount + $order->coupon_discount, 2) }}</td></tr>
        @endif
        <tr class="total"><td>Total</td><td class="num">${{ number_format($order->total, 2) }}</td></tr>
    </table>
</body>
</html>
