<?php

namespace App\Http\Controllers;

use App\Enums\NotificationCategory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationPreferenceController extends Controller
{
    public function edit(): View
    {
        $user = Auth::user();
        $preferences = $user->notificationPreferences()->pluck('email_enabled', 'category');

        return view('notification-preferences.edit', [
            'categories' => NotificationCategory::cases(),
            'preferences' => $preferences,
            'layout' => $this->layoutFor($user),
            'section' => $this->sectionFor($user),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        foreach (NotificationCategory::cases() as $category) {
            $user->notificationPreferences()->updateOrCreate(
                ['category' => $category],
                ['email_enabled' => $request->boolean('categories.'.$category->value)],
            );
        }

        return back()->with('status', 'Notification preferences updated.');
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
