<?php

use App\Models\ChatConversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private channel for real-time chat conversations
Broadcast::channel('chat.{conversationId}', function (User $user, int $conversationId) {
    if ($user->isAdmin()) {
        return true;
    }

    $conversation = ChatConversation::find($conversationId);
    if (! $conversation) {
        return false;
    }

    return (int) $user->id === (int) $conversation->buyer_id || (int) $user->id === (int) $conversation->seller_id;
});
