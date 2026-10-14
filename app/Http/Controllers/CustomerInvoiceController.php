<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CustomerInvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoices) {}

    public function index(): View
    {
        $perPage = (int) request()->input('per_page', 10);
        if ($perPage < 5 || $perPage > 100) {
            $perPage = 10;
        }

        return view('account.invoices.index', [
            'orders' => Auth::user()->orders()->with('items', 'vendorOrders')->latest()->paginate($perPage)->withQueryString(),
        ]);
    }

    public function show(Invoice $invoice): RedirectResponse
    {
        abort_unless($invoice->buyer_id === Auth::id(), 403);

        return redirect()->route('account.orders.invoice', $invoice->order_id);
    }

    public function download(Invoice $invoice): Response
    {
        abort_unless($invoice->buyer_id === Auth::id(), 403);

        $order = $invoice->order;
        $path = $this->invoices->orderPdf($order);

        return response(Storage::disk('local')->get($path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Invoice-'.$order->order_number.'.pdf"',
        ]);
    }
}
