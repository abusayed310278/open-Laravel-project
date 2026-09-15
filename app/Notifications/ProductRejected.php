<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\Product;
use App\Notifications\Concerns\ChannelsFromPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductRejected extends Notification implements ShouldQueue
{
    use ChannelsFromPreference, Queueable;

    public function __construct(
        private readonly Product $product,
        private readonly string $reason,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels($notifiable, NotificationCategory::Products);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your listing needs changes')
            ->greeting("Hi {$notifiable->name},")
            ->line("\"{$this->product->title}\" wasn't approved this time.")
            ->line("Reason: {$this->reason}")
            ->line('You can edit and resubmit it at any time.')
            ->action('Edit listing', route($notifiable->role->dashboardRoute()));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Listing rejected',
            'body' => $this->reason,
            'product_id' => $this->product->id,
        ];
    }
}
