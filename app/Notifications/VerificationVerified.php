<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\ProductVerification;
use App\Notifications\Concerns\ChannelsFromPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationVerified extends Notification implements ShouldQueue
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
        $product = $this->verification->product;
        $grade = $product->grade->label();

        return (new MailMessage)
            ->subject('Your item passed verification')
            ->greeting("Great news, {$notifiable->name}!")
            ->line("\"{$product->title}\" passed physical inspection and is now verified.")
            ->line("Assigned grade: {$grade}")
            ->line('The verified badge and grade are now live on your listing. Note that the core listing fields are locked now that it has been verified.')
            ->action('View listing', route($notifiable->role->dashboardRoute()));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Verification passed',
            'body' => "\"{$this->verification->product->title}\" is now verified.",
            'product_verification_id' => $this->verification->id,
        ];
    }
}
