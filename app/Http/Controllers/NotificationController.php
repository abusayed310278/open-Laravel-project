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

    public function unreadFeed(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json([
                'unread_count' => 0,
                'notifications' => [],
            ]);
        }

        $unread = $user->unreadNotifications()->limit(8)->get();

        $notifications = $unread->map(function ($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? 'Notification',
                'body' => $notification->data['body'] ?? '',
                'time_ago' => $notification->created_at?->diffForHumans() ?? 'Just now',
                'read_url' => route('notifications.read', $notification->id),
            ];
        });

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
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
