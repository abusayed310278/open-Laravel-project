<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\ReviewService;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly ReviewService $reviews,
    ) {}

    public function index(): View
    {
        return view('account.orders.index', [
            'orders' => Auth::user()->orders()->with('vendorOrders')->latest()->paginate(10),
        ]);
    }

    public function show(Order $order): View
    {
        abort_unless($order->customer_id === Auth::id(), 403);

        return view('account.orders.show', [
            'order' => $order->load('vendorOrders.items', 'vendorOrders.manualPaymentSubmission', 'vendorOrders.refunds', 'vendorOrders.vendor.paymentSettings', 'shippingAddress', 'billingAddress'),
            'openboxBankDetails' => $this->settings->get('bank_details'),
            'eligibleReviewables' => $this->reviews->eligibleReviewables(Auth::user(), $order),
        ]);
    }
}
