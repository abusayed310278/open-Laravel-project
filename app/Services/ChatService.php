<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;

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
