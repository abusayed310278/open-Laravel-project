<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.subscriptions.index', [
            'subscriptions' => Subscription::query()
                ->with(['user', 'plan'])
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->latest()
                ->paginate(20)
                ->withQueryString(),
        ]);
    }
}
