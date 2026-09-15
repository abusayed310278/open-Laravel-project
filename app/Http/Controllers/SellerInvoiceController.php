<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SellerInvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoices) {}

    public function index(): View
    {
        return view('seller.invoices.index', [
            'invoices' => Auth::user()->invoicesAsSeller()->with('vendorOrder', 'buyer')->latest('issued_at')->paginate(15),
            'routePrefix' => Auth::user()->isBusiness() ? 'business.' : 'saler.',
        ]);
    }

    public function show(Invoice $invoice): View
    {
        abort_unless($invoice->seller_id === Auth::id(), 403);

        return view('seller.invoices.show', [
            'invoice' => $invoice->load('items', 'buyer', 'vendorOrder'),
            'routePrefix' => Auth::user()->isBusiness() ? 'business.' : 'saler.',
        ]);
    }

    public function download(Invoice $invoice): Response
    {
        abort_unless($invoice->seller_id === Auth::id(), 403);

        $path = $this->invoices->pdf($invoice);

        return response(Storage::disk('local')->get($path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$invoice->invoice_number.'.pdf"',
        ]);
    }
}
