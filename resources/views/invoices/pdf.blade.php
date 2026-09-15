<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #27272a; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .muted { color: #71717a; }
        .header { width: 100%; margin-bottom: 24px; }
        .header td { vertical-align: top; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table.items th { text-align: left; border-bottom: 1px solid #d4d4d8; padding: 6px 4px; font-size: 11px; text-transform: uppercase; color: #71717a; }
        table.items td { padding: 8px 4px; border-bottom: 1px solid #f4f4f5; }
        table.items td.num, table.items th.num { text-align: right; }
        table.totals { width: 260px; margin-top: 16px; margin-left: auto; }
        table.totals td { padding: 4px 0; }
        table.totals td.num { text-align: right; }
        table.totals tr.total td { font-weight: bold; font-size: 14px; border-top: 1px solid #27272a; padding-top: 8px; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 11px; background: #f4f4f5; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                <h1>{{ config('app.name') }}</h1>
                <p class="muted">Invoice {{ $invoice->invoice_number }}</p>
            </td>
            <td style="text-align: right;">
                <p class="muted">Issued {{ $invoice->issued_at->format('M j, Y') }}</p>
                <span class="badge">{{ $invoice->payment_status->label() }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <p><strong>Sold by</strong><br>{{ $invoice->seller->name }}</p>
            </td>
            <td style="text-align: right;">
                <p><strong>Billed to</strong><br>{{ $invoice->buyer->name }}<br>{{ $invoice->buyer->email }}</p>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Item</th>
                <th>SKU</th>
                <th>Grade</th>
                <th class="num">Qty</th>
                <th class="num">Unit Price</th>
                <th class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->product_title }}</td>
                    <td>{{ $item->sku ?? '—' }}</td>
                    <td>{{ $item->grade ?? '—' }}</td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="num">${{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="num">${{ number_format($invoice->subtotal, 2) }}</td></tr>
        <tr><td>Shipping</td><td class="num">${{ number_format($invoice->shipping, 2) }}</td></tr>
        <tr><td>Tax</td><td class="num">${{ number_format($invoice->tax, 2) }}</td></tr>
        @if ($invoice->discount > 0)
            <tr><td>Discount</td><td class="num">-${{ number_format($invoice->discount, 2) }}</td></tr>
        @endif
        <tr class="total"><td>Total</td><td class="num">${{ number_format($invoice->total, 2) }}</td></tr>
    </table>
</body>
</html>
