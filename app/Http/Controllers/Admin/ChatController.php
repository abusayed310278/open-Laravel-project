<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View
    {
        return view('admin.chat.index', [
            'conversations' => ChatConversation::query()
                ->with(['buyer', 'seller', 'product', 'latestMessage'])
                ->orderByDesc('last_message_at')
                ->paginate(20),
        ]);
    }

    public function show(ChatConversation $conversation): View
    {
        return view('admin.chat.show', [
            'conversation' => $conversation->load(['buyer', 'seller', 'product']),
            'messages' => $conversation->messages()->with('sender')->oldest()->get(),
        ]);
    }
}
