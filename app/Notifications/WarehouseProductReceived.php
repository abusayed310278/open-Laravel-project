<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\WarehouseProduct;
use App\Notifications\Concerns\ChannelsFromPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WarehouseProductReceived extends Notification implements ShouldQueue
{
    use ChannelsFromPreference, Queueable;

    public function __construct(private readonly WarehouseProduct $entry) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels($notifiable, NotificationCategory::Warehouse);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->entry->loadMissing(['product', 'warehouse']);

        return (new MailMessage)
            ->subject('Your item arrived at the warehouse')
            ->greeting("Hi {$notifiable->name},")
            ->line("\"{$this->entry->product->title}\" has been received and stored at {$this->entry->warehouse->name}.")
            ->line('It now ships and settles payment through Openbox directly.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Item received at warehouse',
            'body' => "\"{$this->entry->product->title}\" is now stored.",
            'warehouse_product_id' => $this->entry->id,
        ];
    }
}
