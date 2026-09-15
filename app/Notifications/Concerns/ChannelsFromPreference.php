<?php

namespace App\Notifications\Concerns;

use App\Enums\NotificationCategory;
use App\Models\User;

trait ChannelsFromPreference
{
    /**
     * Always deliver to the in-app notification center (database); only
     * add the mail channel if the user hasn't opted out of this category.
     *
     * @return array<int, string>
     */
    protected function channels(object $notifiable, NotificationCategory $category): array
    {
        if ($notifiable instanceof User && ! $notifiable->wantsEmailFor($category)) {
            return ['database'];
        }

        return ['mail', 'database'];
    }
}
