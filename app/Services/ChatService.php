<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
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
