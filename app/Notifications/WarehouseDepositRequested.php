<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\WarehouseProduct;
use App\Notifications\Concerns\ChannelsFromPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WarehouseDepositRequested extends Notification implements ShouldQueue
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
            ->subject('Warehouse deposit request received')
            ->greeting("Hi {$notifiable->name},")
            ->line("Your request to deposit \"{$this->entry->product->title}\" at {$this->entry->warehouse->name} has been received.")
            ->line('We\'ll notify you once it has been received and stored.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Warehouse deposit requested',
            'body' => "\"{$this->entry->product->title}\" is pending delivery.",
            'warehouse_product_id' => $this->entry->id,
        ];
    }
}
