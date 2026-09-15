<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\WarehouseProduct;
use App\Notifications\Concerns\ChannelsFromPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WarehouseProductReleased extends Notification implements ShouldQueue
{
    use ChannelsFromPreference, Queueable;

    public function __construct(
        private readonly WarehouseProduct $entry,
        private readonly string $reason,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels($notifiable, NotificationCategory::Warehouse);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->entry->loadMissing('product');

        return (new MailMessage)
            ->subject('Your item was released from the warehouse')
            ->greeting("Hi {$notifiable->name},")
            ->line("\"{$this->entry->product->title}\" was released from warehouse storage.")
            ->line("Reason: {$this->reason}");
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Item released from warehouse',
            'body' => $this->reason,
            'warehouse_product_id' => $this->entry->id,
        ];
    }
}
