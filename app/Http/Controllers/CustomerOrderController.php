<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\InvoiceService;
use App\Services\ReviewService;
use App\Services\SettingsService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly ReviewService $reviews,
        private readonly InvoiceService $invoices,
    ) {}

    public function index(): View
    {
        return view('account.orders.index', [
            'orders' => Auth::user()->orders()->with('vendorOrders.invoice')->latest()->paginate(10),
        ]);
    }

    public function show(Order $order): View
    {
        abort_unless($order->customer_id === Auth::id(), 403);

        return view('account.orders.show', [
            'order' => $order->load('vendorOrders.items', 'vendorOrders.invoice', 'vendorOrders.manualPaymentSubmission', 'vendorOrders.refunds', 'vendorOrders.vendor.paymentSettings', 'shippingAddress', 'billingAddress'),
            'openboxBankDetails' => $this->settings->get('bank_details'),
            'eligibleReviewables' => $this->reviews->eligibleReviewables(Auth::user(), $order),
        ]);
    }

    public function invoice(Order $order): View
    {
        abort_unless($order->customer_id === Auth::id(), 403);

        return view('account.orders.invoice', [
            'order' => $order->load([
                'items.vendorOrder.vendor',
                'customer',
                'billingAddress',
                'shippingAddress',
                'vendorOrders.vendor',
            ]),
        ]);
    }

    public function downloadInvoice(Order $order): Response
    {
        abort_unless($order->customer_id === Auth::id(), 403);

        $path = $this->invoices->orderPdf($order);

        return response(Storage::disk('local')->get($path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Invoice-'.$order->order_number.'.pdf"',
        ]);
    }
}
