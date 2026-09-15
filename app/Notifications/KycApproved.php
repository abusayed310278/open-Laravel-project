<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Notifications\Concerns\ChannelsFromPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KycApproved extends Notification implements ShouldQueue
{
    use ChannelsFromPreference, Queueable;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels($notifiable, NotificationCategory::Verification);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your verification was approved')
            ->greeting("You're verified, {$notifiable->name}!")
            ->line('Your identity/business documents have been reviewed and approved.')
            ->line('You can now publish listings on Openbox.')
            ->action('Go to your dashboard', route($notifiable->role->dashboardRoute()));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Verification approved',
            'body' => 'Your identity/business documents have been approved.',
        ];
    }
}
