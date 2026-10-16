<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewChatMessage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatService
{
    public function startOrGetConversation(User $user1, User $user2, ?Product $product = null): ChatConversation
    {
        abort_if($user1->id === $user2->id, 422, 'You cannot message yourself.');

        $existing = ChatConversation::query()
            ->when($product, fn ($q) => $q->where('product_id', $product->id), fn ($q) => $q->whereNull('product_id'))
            ->where(function ($q) use ($user1, $user2) {
                $q->where(fn ($sub) => $sub->where('buyer_id', $user1->id)->where('seller_id', $user2->id))
                  ->orWhere(fn ($sub) => $sub->where('buyer_id', $user2->id)->where('seller_id', $user1->id));
            })
            ->first();

        if ($existing) {
            return $existing;
        }

        return ChatConversation::create([
            'product_id' => $product?->id,
            'buyer_id' => $user1->id,
            'seller_id' => $user2->id,
            'seller_type' => $user2->role?->value ?? 'saler',
        ]);
    }

    public function sendMessage(ChatConversation $conversation, User $sender, ?string $body, ?UploadedFile $attachment = null): ChatMessage
    {
        $pathname = $attachment?->getPathname();
        $realPath = $attachment?->getRealPath();
        $filePath = ($realPath && file_exists($realPath)) ? $realPath : (($pathname && file_exists($pathname)) ? $pathname : null);

        $hasAttachment = $attachment
            && $attachment->isValid()
            && $filePath !== null;

        abort_if(blank($body) && ! $hasAttachment, 422, 'Message cannot be empty.');

        $attachmentPath = null;
        if ($hasAttachment) {
            $extension = strtolower($attachment->getClientOriginalExtension() ?: $attachment->guessExtension() ?: 'bin');
            $filename = Str::random(40) . '.' . $extension;

            if ($filePath && is_readable($filePath)) {
                $stream = @fopen($filePath, 'rb');
                if ($stream !== false) {
                    Storage::disk('local')->put('chat-attachments/' . $filename, $stream);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                    $attachmentPath = 'chat-attachments/' . $filename;
                }
            }

            if (! $attachmentPath) {
                $content = $attachment->getContent();
                if ($content !== false && $content !== '') {
                    Storage::disk('local')->put('chat-attachments/' . $filename, $content);
                    $attachmentPath = 'chat-attachments/' . $filename;
                }
            }
        }

        $message = $conversation->messages()->create([
            'sender_id' => $sender->id,
            'body' => $body,
            'attachment_path' => $attachmentPath,
        ]);

        $conversation->update(['last_message_at' => now()]);

        $this->notifyRecipients($conversation, $message, $sender);

        return $message;
    }

    public function markRead(ChatConversation $conversation, User $reader): void
    {
        $conversation->messages()
            ->where('sender_id', '!=', $reader->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Auto-mark any in-app database notifications for this conversation as read
        $reader->unreadNotifications()
            ->where('data->conversation_id', $conversation->id)
            ->update(['read_at' => now()]);
    }

    /**
     * Determine and notify participants who should receive alerts for this message.
     */
    protected function notifyRecipients(ChatConversation $conversation, ChatMessage $message, User $sender): void
    {
        $conversation->loadMissing(['buyer', 'seller']);

        $recipients = collect();

        // Check if either party in the conversation is an admin
        $hasAdminParty = ($conversation->buyer?->isAdmin() || $conversation->seller?->isAdmin());

        if ($sender->isAdmin()) {
            // Admin replied: notify the non-admin participant (seller, buyer, verifier)
            $nonAdmin = $conversation->buyer?->isAdmin() ? $conversation->seller : $conversation->buyer;
            if ($nonAdmin && $nonAdmin->id !== $sender->id) {
                $recipients->push($nonAdmin);
            }
        } elseif ($hasAdminParty) {
            // A seller or customer is messaging an admin:
            // Alert all admin users so whoever is active on duty/dashboard receives the notification
            $admins = User::query()
                ->where('role', UserRole::Admin)
                ->where('id', '!=', $sender->id)
                ->get();

            $recipients = $recipients->merge($admins);
        } else {
            // Regular user-to-user (e.g. buyer <-> seller)
            $other = $conversation->otherParty($sender);
            if ($other && $other->id !== $sender->id) {
                $recipients->push($other);
            }
        }

        // Deduplicate recipients by ID
        $recipients = $recipients->unique('id');

        foreach ($recipients as $recipient) {
            try {
                $recipient->notify(new NewChatMessage($message));
            } catch (\Throwable $e) {
                Log::warning("Failed to notify user #{$recipient->id} about chat message #{$message->id}: {$e->getMessage()}");
            }
        }
    }
}
