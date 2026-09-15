<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\Refund;
use App\Notifications\Concerns\ChannelsFromPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundStatusChanged extends Notification implements ShouldQueue
{
    use ChannelsFromPreference, Queueable;

    public function __construct(private readonly Refund $refund) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels($notifiable, NotificationCategory::Payments);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Refund '.$this->refund->status->label())
            ->greeting("Hi {$notifiable->name},")
            ->line("Your refund request for \${$this->refund->amount} has been {$this->refund->status->label()}.")
            ->action('View order', route('account.orders.show', $this->refund->order_id));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Refund '.$this->refund->status->label(),
            'body' => "\${$this->refund->amount} — {$this->refund->reason}",
            'order_id' => $this->refund->order_id,
        ];
    }
}
