<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\Product;
use App\Notifications\Concerns\ChannelsFromPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductApproved extends Notification implements ShouldQueue
{
    use ChannelsFromPreference, Queueable;

    public function __construct(private readonly Product $product) {}

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
            ->subject('Your listing was approved')
            ->greeting("Good news, {$notifiable->name}!")
            ->line("\"{$this->product->title}\" has been approved and is ready to publish.")
            ->action('View listing', route($notifiable->role->dashboardRoute()));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Listing approved',
            'body' => "\"{$this->product->title}\" was approved.",
            'product_id' => $this->product->id,
        ];
    }
}
