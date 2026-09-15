<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\Order;
use App\Models\VendorOrder;
use App\Notifications\Concerns\ChannelsFromPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlaced extends Notification implements ShouldQueue
{
    use ChannelsFromPreference, Queueable;

    /**
     * When $vendorOrder is set, this is the "you made a sale" version sent
     * to a seller; otherwise it's the "your order is confirmed" version
     * sent to the buyer.
     */
    public function __construct(
        private readonly Order $order,
        private readonly ?VendorOrder $vendorOrder = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels($notifiable, NotificationCategory::Orders);
    }

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->vendorOrder) {
            return (new MailMessage)
                ->subject("New order #{$this->vendorOrder->vendor_order_number}")
                ->greeting("You made a sale, {$notifiable->name}!")
                ->line("Order #{$this->vendorOrder->vendor_order_number} — {$this->vendorOrder->items->count()} item(s), total \${$this->vendorOrder->total}.")
                ->action('View order', route($notifiable->role->dashboardRoute()));
        }

        return (new MailMessage)
            ->subject("Order confirmed — #{$this->order->order_number}")
            ->greeting("Thanks for your order, {$notifiable->name}!")
            ->line("Order #{$this->order->order_number} is confirmed — total \${$this->order->total}.")
            ->line('We\'ll email you as each seller ships your items.')
            ->action('View order', route('account.orders.show', $this->order));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->vendorOrder ? 'New order received' : 'Order confirmed',
            'body' => $this->vendorOrder
                ? "Order #{$this->vendorOrder->vendor_order_number}"
                : "Order #{$this->order->order_number} confirmed",
            'order_id' => $this->order->id,
        ];
    }
}
