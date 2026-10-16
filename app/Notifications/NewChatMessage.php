<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewChatMessage extends Notification
{
    use Queueable;

    public function __construct(public readonly ChatMessage $message) {}

    /**
     * In-app database notifications are delivered immediately so the
     * topbar bell indicator and dropdown update without queue delays.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->message->loadMissing(['sender.profile', 'sender.salerProfile', 'sender.businessProfile', 'conversation']);
        $sender = $this->message->sender;

        $senderName = $sender?->name ?? 'User';
        if ($sender?->isSaler() || $sender?->isBusiness()) {
            $storeName = $sender->salerProfile?->store_name ?? $sender->businessProfile?->store_name;
            if ($storeName && $storeName !== $sender->name) {
                $senderName = "{$storeName} ({$sender->name})";
            } elseif ($storeName) {
                $senderName = $storeName;
            }
        } elseif ($sender?->isAdmin()) {
            $senderName = 'Admin Support';
        }

        $snippet = $this->message->body;
        if (blank($snippet) && $this->message->attachment_path) {
            $snippet = 'Sent an attachment: ' . $this->message->attachmentName();
        }

        $url = $this->conversationUrlFor($notifiable);

        return [
            'title' => "New message from {$senderName}",
            'body' => Str::limit($snippet ?? 'New message received', 120),
            'conversation_id' => $this->message->conversation_id,
            'message_id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $senderName,
            'url' => $url,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->message->loadMissing(['sender', 'conversation']);
        $senderName = $this->message->sender?->name ?? 'A user';
        $url = $this->conversationUrlFor($notifiable);

        return (new MailMessage)
            ->subject("New message from {$senderName}")
            ->greeting("Hi {$notifiable->name},")
            ->line("You received a new message from {$senderName}:")
            ->line('"' . Str::limit($this->message->body ?? 'Attachment sent', 200) . '"')
            ->action('View Conversation', $url);
    }

    public function conversationUrlFor(object $notifiable): string
    {
        if (! ($notifiable instanceof User)) {
            return url('/messages');
        }

        $conversation = $this->message->conversation;

        return match (true) {
            $notifiable->isAdmin() => route('admin.chat.show', $conversation),
            $notifiable->isBusiness() => route('business.chat.show', $conversation),
            $notifiable->isSaler() => route('saler.chat.show', $conversation),
            $notifiable->isVerifier() => route('verifier.chat.show', $conversation),
            default => route('account.chat.show', $conversation),
        };
    }
}
