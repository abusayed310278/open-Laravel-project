<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CustomerInvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoices) {}

    public function index(): View
    {
        return view('account.invoices.index', [
            'invoices' => Auth::user()->invoicesAsBuyer()->with('vendorOrder')->latest('issued_at')->paginate(15),
        ]);
    }

    public function show(Invoice $invoice): View
    {
        abort_unless($invoice->buyer_id === Auth::id(), 403);

        return view('account.invoices.show', [
            'invoice' => $invoice->load('items', 'seller', 'vendorOrder'),
        ]);
    }

    public function download(Invoice $invoice): Response
    {
        abort_unless($invoice->buyer_id === Auth::id(), 403);

        $path = $this->invoices->pdf($invoice);

        return response(Storage::disk('local')->get($path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$invoice->invoice_number.'.pdf"',
        ]);
    }
}
