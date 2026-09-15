<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\Subscription;
use App\Notifications\Concerns\ChannelsFromPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionActivated extends Notification implements ShouldQueue
{
    use ChannelsFromPreference, Queueable;

    public function __construct(private readonly Subscription $subscription) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels($notifiable, NotificationCategory::Subscriptions);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->subscription->loadMissing('plan');

        return (new MailMessage)
            ->subject('Your subscription is active')
            ->greeting("Thanks, {$notifiable->name}!")
            ->line("Your \"{$this->subscription->plan->name}\" plan is now active.")
            ->action('Go to your dashboard', route($notifiable->role->dashboardRoute()));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Subscription activated',
            'body' => "\"{$this->subscription->plan->name}\" is now active.",
            'subscription_id' => $this->subscription->id,
        ];
    }
}
