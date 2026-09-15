<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoices) {}

    public function index(Request $request): View
    {
        $invoices = Invoice::query()
            ->with(['vendorOrder', 'seller', 'buyer'])
            ->when($request->filled('status'), fn ($query) => $query->where('payment_status', $request->string('status')))
            ->latest('issued_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.invoices.index', [
            'invoices' => $invoices,
        ]);
    }

    public function show(Invoice $invoice): View
    {
        return view('admin.invoices.show', [
            'invoice' => $invoice->load('items', 'seller', 'buyer', 'vendorOrder'),
        ]);
    }

    public function download(Invoice $invoice): Response
    {
        $path = $this->invoices->pdf($invoice);

        return response(Storage::disk('local')->get($path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$invoice->invoice_number.'.pdf"',
        ]);
    }
}
