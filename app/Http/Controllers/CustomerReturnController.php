<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerReturnController extends Controller
{
    public function index(): View
    {
        return view('account.returns.index', [
            'refunds' => Refund::query()
                ->where('requested_by', Auth::id())
                ->with(['vendorOrder.items', 'vendorOrder.vendor'])
                ->latest()
                ->get(),
        ]);
    }
}
