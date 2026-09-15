<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function index(Request $request): View
    {
        $from = $request->filled('from') ? Carbon::parse($request->string('from')->value()) : now()->subDays(29)->startOfDay();
        $to = $request->filled('to') ? Carbon::parse($request->string('to')->value()) : now()->endOfDay();
        $tab = $request->string('tab')->value() ?: 'overview';

        $data = match ($tab) {
            'users' => $this->reports->userReport($from, $to),
            'products' => $this->reports->productReport(),
            'subscriptions' => $this->reports->subscriptionReport($from, $to),
            'verification' => $this->reports->verificationReport($from, $to),
            'warehouse' => $this->reports->warehouseReport($from, $to),
            'payouts' => $this->reports->payoutReport($from, $to),
            default => $this->reports->summary($from, $to),
        };

        return view('admin.reports.index', [
            'from' => $from,
            'to' => $to,
            'tab' => $tab,
            'data' => $data,
        ]);
    }
}
