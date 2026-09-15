<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        return view('notifications.index', [
            'notifications' => $user->notifications()->paginate(20),
            'layout' => $this->layoutFor($user),
            'section' => $this->sectionFor($user),
        ]);
    }

    public function markRead(string $notification): RedirectResponse
    {
        Auth::user()->notifications()->where('id', $notification)->first()?->markAsRead();

        return back();
    }

    public function markAllRead(): RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back();
    }

    private function layoutFor(User $user): string
    {
        return match (true) {
            $user->isAdmin() => 'layouts.admin',
            $user->isVerifier() => 'layouts.verifier',
            $user->isBusiness() => 'layouts.business',
            $user->isSaler() => 'layouts.saler',
            default => 'layouts.customer',
        };
    }

    private function sectionFor(User $user): string
    {
        return $user->isCustomer() ? 'account-content' : 'content';
    }
}
