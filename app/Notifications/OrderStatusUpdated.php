<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\VendorOrder;
use App\Notifications\Concerns\ChannelsFromPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification implements ShouldQueue
{
    use ChannelsFromPreference, Queueable;

    public function __construct(private readonly VendorOrder $vendorOrder) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels($notifiable, NotificationCategory::Orders);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Order update — #'.$this->vendorOrder->vendor_order_number)
            ->greeting("Hi {$notifiable->name},")
            ->line("Your order #{$this->vendorOrder->vendor_order_number} is now {$this->vendorOrder->status->label()}.");

        if ($this->vendorOrder->tracking_number) {
            $mail->line("Tracking number: {$this->vendorOrder->tracking_number}");
        }

        return $mail->action('View order', route('account.orders.show', $this->vendorOrder->order_id));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Order '.$this->vendorOrder->status->label(),
            'body' => "#{$this->vendorOrder->vendor_order_number}",
            'order_id' => $this->vendorOrder->order_id,
        ];
    }
}
