<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\SupportTicket;
use App\Notifications\Concerns\ChannelsFromPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSupportTicket extends Notification implements ShouldQueue
{
    use ChannelsFromPreference, Queueable;

    public function __construct(private readonly SupportTicket $ticket) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels($notifiable, NotificationCategory::Support);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New support ticket — '.$this->ticket->ticket_number)
            ->greeting("Hi {$notifiable->name},")
            ->line("A new support ticket was opened: \"{$this->ticket->subject}\".")
            ->action('View ticket', route('admin.support.show', $this->ticket));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New support ticket',
            'body' => $this->ticket->subject,
            'ticket_id' => $this->ticket->id,
        ];
    }
}
