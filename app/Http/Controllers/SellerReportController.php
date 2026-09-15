<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SellerReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function index(Request $request): View
    {
        $user = Auth::user();
        $from = $request->filled('from') ? Carbon::parse($request->string('from')->value()) : now()->subDays(29)->startOfDay();
        $to = $request->filled('to') ? Carbon::parse($request->string('to')->value()) : now()->endOfDay();

        $subscription = $user->activeSubscription;

        return view('seller.reports.index', [
            'from' => $from,
            'to' => $to,
            'subscription' => $subscription,
            'listingCredit' => $subscription?->credits,
            'isSaler' => $user->isSaler(),
            ...$this->reports->sellerSummary($user, $from, $to),
        ]);
    }
}
