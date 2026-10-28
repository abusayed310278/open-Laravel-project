<?php

namespace App\Services;

use App\Enums\InvoicePaymentStatus;
use App\Models\Invoice;
use App\Models\VendorOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    public function generateForVendorOrder(VendorOrder $vendorOrder): Invoice
    {
        $vendorOrder->loadMissing('items', 'vendor', 'order');

        $invoice = Invoice::create([
            'invoice_number' => $this->nextInvoiceNumber(),
            'order_id' => $vendorOrder->order_id,
            'vendor_order_id' => $vendorOrder->id,
            'seller_id' => $vendorOrder->vendor_id,
            'buyer_id' => $vendorOrder->order->customer_id,
            'seller_type' => $vendorOrder->vendor->role->value,
            'subtotal' => $vendorOrder->subtotal,
            'tax' => $vendorOrder->tax,
            'discount' => 0,
            'shipping' => $vendorOrder->shipping,
            'total' => $vendorOrder->total,
            'payment_status' => InvoicePaymentStatus::Unpaid,
            'issued_at' => now(),
        ]);

        foreach ($vendorOrder->items as $item) {
            $invoice->items()->create([
                'product_title' => $item->product_title,
                'sku' => $item->sku,
                'grade' => $item->product_grade,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ]);
        }

        return $invoice;
    }

    public function markPaid(VendorOrder $vendorOrder): void
    {
        $vendorOrder->invoice?->update(['payment_status' => InvoicePaymentStatus::Paid]);
    }

    public function markRefunded(VendorOrder $vendorOrder): void
    {
        $vendorOrder->invoice?->update(['payment_status' => InvoicePaymentStatus::Refunded]);
    }

    /**
     * Render the invoice to PDF and cache it on the private disk, reusing
     * the cached file on subsequent calls rather than re-rendering.
     */
    public function pdf(Invoice $invoice): string
    {
        if ($invoice->pdf_path && Storage::disk('local')->exists($invoice->pdf_path)) {
            return $invoice->pdf_path;
        }

        $invoice->loadMissing('items', 'seller', 'buyer', 'order', 'vendorOrder');

        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $invoice]);
        $path = "invoices/{$invoice->invoice_number}.pdf";

        Storage::disk('local')->put($path, $pdf->output());
        $invoice->update(['pdf_path' => $path]);

        return $path;
    }

    /**
     * Render a consolidated order-level invoice PDF covering all products
     * in the customer's order.
     */
    public function orderPdf(\App\Models\Order $order): string
    {
        $path = "invoices/orders/{$order->order_number}.pdf";

        $order->loadMissing([
            'items.vendorOrder.vendor',
            'customer',
            'billingAddress',
            'shippingAddress',
            'vendorOrders.vendor',
        ]);

        $pdf = Pdf::loadView('invoices.order-pdf', ['order' => $order]);

        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }

    private function nextInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "INV-{$year}-";

        $latest = Invoice::query()
            ->where('invoice_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('invoice_number');

        $seq = 0;
        if ($latest && preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $latest, $m)) {
            $seq = (int) $m[1];
        }

        do {
            $seq++;
            $candidate = "{$prefix}" . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
        } while (Invoice::where('invoice_number', $candidate)->exists());

        return $candidate;
    }
}
