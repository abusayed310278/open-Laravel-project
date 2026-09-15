<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Notifications\Concerns\ChannelsFromPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class SubscriptionExpiring extends Notification implements ShouldQueue
{
    use ChannelsFromPreference, Queueable;

    public function __construct(
        private readonly string $label,
        private readonly Carbon $expiresAt,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels($notifiable, NotificationCategory::Subscriptions);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your '.$this->label.' expires soon')
            ->greeting("Hi {$notifiable->name},")
            ->line("Your {$this->label} expires on {$this->expiresAt->format('F j, Y')}.")
            ->line('Renew before then to keep your listings live without interruption.')
            ->action('Go to your dashboard', route($notifiable->role->dashboardRoute()));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => ucfirst($this->label).' expiring soon',
            'body' => 'Expires '.$this->expiresAt->format('M j, Y'),
        ];
    }
}
