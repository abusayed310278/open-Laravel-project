<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\ManualPaymentSubmission;
use App\Notifications\Concerns\ChannelsFromPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ManualPaymentVerified extends Notification implements ShouldQueue
{
    use ChannelsFromPreference, Queueable;

    public function __construct(private readonly ManualPaymentSubmission $submission) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels($notifiable, NotificationCategory::Payments);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->submission->loadMissing('vendorOrder');

        return (new MailMessage)
            ->subject('Payment verified')
            ->greeting("Hi {$notifiable->name},")
            ->line("Your bank transfer for order #{$this->submission->vendorOrder->vendor_order_number} has been verified.")
            ->action('View order', route('account.orders.show', $this->submission->order_id));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Payment verified',
            'body' => 'Your bank transfer was verified.',
            'order_id' => $this->submission->order_id,
        ];
    }
}
