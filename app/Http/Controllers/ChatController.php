<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChatMessageRequest;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Product;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(private readonly ChatService $chat) {}

    public function index(): View
    {
        $user = Auth::user();

        $conversations = ChatConversation::query()
            ->where(fn ($q) => $q->where('buyer_id', $user->id)->orWhere('seller_id', $user->id))
            ->with(['buyer', 'seller', 'product', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return view('chat.index', [
            'conversations' => $conversations,
            'layout' => $this->layoutFor($user),
            'section' => $this->sectionFor($user),
            'routePrefix' => $this->routePrefixFor($user),
        ]);
    }

    public function show(ChatConversation $conversation): View
    {
        $this->authorizeParticipant($conversation);

        $this->chat->markRead($conversation, Auth::user());

        return view('chat.show', [
            'conversation' => $conversation->load(['buyer', 'seller', 'product']),
            'messages' => $conversation->messages()->with('sender')->oldest()->get(),
            'layout' => $this->layoutFor(Auth::user()),
            'section' => $this->sectionFor(Auth::user()),
            'routePrefix' => $this->routePrefixFor(Auth::user()),
        ]);
    }

    public function store(StoreChatMessageRequest $request, ChatConversation $conversation): RedirectResponse
    {
        $this->authorizeParticipant($conversation);

        $this->chat->sendMessage($conversation, Auth::user(), $request->string('body')->value() ?: null, $request->file('attachment'));

        return back();
    }

    /**
     * Poll for messages newer than the given id — vanilla-JS auto-refresh
     * on the conversation page, no websockets involved.
     */
    public function poll(ChatConversation $conversation, int $afterId): JsonResponse
    {
        $this->authorizeParticipant($conversation);

        $messages = $conversation->messages()->with('sender')->where('id', '>', $afterId)->oldest()->get();

        if ($messages->isNotEmpty()) {
            $this->chat->markRead($conversation, Auth::user());
        }

        return response()->json($messages->map(fn ($m) => [
            'id' => $m->id,
            'sender_id' => $m->sender_id,
            'sender_name' => $m->sender->name,
            'body' => $m->body,
            'attachment_url' => $m->attachment_path ? route('chat.attachment', $m) : null,
            'created_at' => $m->created_at->format('M j, g:ia'),
        ]));
    }

    public function startFromProduct(Product $product): RedirectResponse
    {
        $user = Auth::user();
        $conversation = $this->chat->startOrGetConversation($user, $product->user, $product);

        return redirect()->route($this->routePrefixFor($user).'chat.show', $conversation);
    }

    public function attachment(ChatMessage $message): RedirectResponse
    {
        $this->authorizeParticipant($message->conversation);

        return redirect()->away(Storage::disk('local')->temporaryUrl($message->attachment_path, now()->addMinutes(5)));
    }

    private function authorizeParticipant(ChatConversation $conversation): void
    {
        abort_unless(in_array(Auth::id(), [$conversation->buyer_id, $conversation->seller_id], true), 403);
    }

    private function layoutFor(User $user): string
    {
        return match (true) {
            $user->isBusiness() => 'layouts.business',
            $user->isSaler() => 'layouts.saler',
            default => 'layouts.customer',
        };
    }

    private function routePrefixFor(User $user): string
    {
        return match (true) {
            $user->isBusiness() => 'business.',
            $user->isSaler() => 'saler.',
            default => 'account.',
        };
    }

    private function sectionFor(User $user): string
    {
        return $user->isBusiness() || $user->isSaler() ? 'content' : 'account-content';
    }
}
