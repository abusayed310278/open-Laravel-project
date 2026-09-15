<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\ProductVerification;
use App\Notifications\Concerns\ChannelsFromPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationRejected extends Notification implements ShouldQueue
{
    use ChannelsFromPreference, Queueable;

    public function __construct(
        private readonly ProductVerification $verification,
        private readonly string $reason,
    ) {}

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
            ->subject("Verification didn't pass")
            ->greeting("Hi {$notifiable->name},")
            ->line("\"{$this->verification->product->title}\" didn't pass physical inspection.")
            ->line("Reason: {$this->reason}")
            ->line('You can address the issue and request verification again at any time.')
            ->action('View listing', route($notifiable->role->dashboardRoute()));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Verification failed',
            'body' => $this->reason,
            'product_verification_id' => $this->verification->id,
        ];
    }
}
