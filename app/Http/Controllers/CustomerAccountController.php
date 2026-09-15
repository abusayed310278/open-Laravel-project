<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        return view('account.dashboard', [
            'ordersCount' => $user->orders()->count(),
            'wishlistCount' => $user->wishlist?->items()->count() ?? 0,
            'unreadMessagesCount' => ChatMessage::whereHas('conversation', fn ($query) => $query->where('buyer_id', $user->id))
                ->whereNull('read_at')
                ->where('sender_id', '!=', $user->id)
                ->count(),
            'reviewsCount' => $user->reviewsWritten()->count(),
            'recentOrders' => $user->orders()->latest()->limit(5)->get(),
        ]);
    }
}
