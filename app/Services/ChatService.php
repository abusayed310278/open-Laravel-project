<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class ChatService
{
    public function startOrGetConversation(User $buyer, User $seller, ?Product $product = null): ChatConversation
    {
        abort_if($buyer->id === $seller->id, 422, 'You cannot message yourself.');

        return ChatConversation::query()->firstOrCreate([
            'product_id' => $product?->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ], [
            'seller_type' => $seller->role->value,
        ]);
    }

    public function sendMessage(ChatConversation $conversation, User $sender, ?string $body, ?UploadedFile $attachment = null): ChatMessage
    {
        abort_if(blank($body) && ! $attachment, 422, 'Message cannot be empty.');

        $message = $conversation->messages()->create([
            'sender_id' => $sender->id,
            'body' => $body,
            'attachment_path' => $attachment?->store('chat-attachments', 'local'),
        ]);

        $conversation->update(['last_message_at' => now()]);

        return $message;
    }

    public function markRead(ChatConversation $conversation, User $reader): void
    {
        $conversation->messages()
            ->where('sender_id', '!=', $reader->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
