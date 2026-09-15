<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Notifications\Concerns\ChannelsFromPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PendingPayoutsDigest extends Notification implements ShouldQueue
{
    use ChannelsFromPreference, Queueable;

    public function __construct(private readonly int $count, private readonly float $total) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels($notifiable, NotificationCategory::Payments);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payout requests awaiting review')
            ->greeting("Hi {$notifiable->name},")
            ->line("There are {$this->count} seller payout request(s) totalling \${$this->total} still waiting for review.")
            ->action('Review payouts', route('admin.payouts.index'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Payouts awaiting review',
            'body' => "{$this->count} request(s) totalling \${$this->total}",
        ];
    }
}
