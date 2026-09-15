---
paths:
  - 'app/Services/InvoiceService.php,app/Http/Controllers/{Admin/InvoiceController,CustomerInvoiceController,SellerInvoiceController}.php'
---

# Admin

## Invoices are auto-generated per vendor order, PDF is cached on first download
`InvoiceService::generateForVendorOrder()` is called once from `CheckoutService::placeOrder()` right after each `VendorOrder`'s items are created — one invoice per vendor order (not per whole `Order`), mirroring the same items 1:1. Don't call it anywhere else; there's a unique constraint on `invoices.vendor_order_id`.

`payment_status` starts `unpaid` and is flipped by `PaymentService`: `markPaid()` on `verifyManualPayment()`/`confirmCodCollected()`, `markRefunded()` on `approveRefund()`. Never set `payment_status` directly from a controller — always go through `PaymentService` so the invoice stays in sync with the underlying `Transaction`.

PDF rendering uses `barryvdh/laravel-dompdf` (already in composer.json, no config published) against `resources/views/invoices/pdf.blade.php` — keep that template plain-CSS (no Tailwind classes; dompdf doesn't run a browser engine). `InvoiceService::pdf()` renders once and caches the file path in `invoices.pdf_path` on the `local` disk — check `Storage::disk('local')->exists($invoice->pdf_path)` before re-rendering, don't regenerate on every download.
