<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\SupportTicketMessage;
use App\Notifications\Concerns\ChannelsFromPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportTicketReplied extends Notification implements ShouldQueue
{
    use ChannelsFromPreference, Queueable;

    public function __construct(private readonly SupportTicketMessage $message) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels($notifiable, NotificationCategory::Support);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->message->loadMissing('ticket');

        return (new MailMessage)
            ->subject('New reply — '.$this->message->ticket->ticket_number)
            ->greeting("Hi {$notifiable->name},")
            ->line("There's a new reply on your support ticket \"{$this->message->ticket->subject}\".")
            ->line($this->message->body);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New support reply',
            'body' => $this->message->body,
            'ticket_id' => $this->message->ticket_id,
        ];
    }
}
