<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\ProductVerification;
use App\Notifications\Concerns\ChannelsFromPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationScheduled extends Notification implements ShouldQueue
{
    use ChannelsFromPreference, Queueable;

    public function __construct(private readonly ProductVerification $verification) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels($notifiable, NotificationCategory::Verification);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->verification->loadMissing(['product', 'location']);

        return (new MailMessage)
            ->subject('Verification appointment confirmed')
            ->greeting("Hi {$notifiable->name},")
            ->line("Your appointment to verify \"{$this->verification->product->title}\" is confirmed.")
            ->line("Location: {$this->verification->location->name}, {$this->verification->location->address}")
            ->line('Date: '.$this->verification->scheduled_at->format('l, F j, Y \a\t g:i A'))
            ->line('Bring the item along with any accessories that need to be tested.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Verification appointment confirmed',
            'body' => "\"{$this->verification->product->title}\" — ".$this->verification->scheduled_at->format('M j, g:i A'),
            'product_verification_id' => $this->verification->id,
        ];
    }
}
