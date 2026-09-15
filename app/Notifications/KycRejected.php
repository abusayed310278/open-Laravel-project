<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Enums\UserRole;
use App\Notifications\Concerns\ChannelsFromPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KycRejected extends Notification implements ShouldQueue
{
    use ChannelsFromPreference, Queueable;

    public function __construct(private readonly string $reason) {}

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
            ->subject('Your verification needs attention')
            ->greeting("Hi {$notifiable->name},")
            ->line('We reviewed your submitted documents and couldn\'t approve them this time.')
            ->line("Reason: {$this->reason}")
            ->line('You can address this and resubmit at any time.')
            ->action('Resubmit documents', route($notifiable->role === UserRole::Business ? 'business.verification.index' : 'saler.verification.index'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Verification rejected',
            'body' => $this->reason,
        ];
    }
}
