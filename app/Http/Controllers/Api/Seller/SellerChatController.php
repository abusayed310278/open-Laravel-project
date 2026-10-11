<?php

namespace App\Http\Controllers\Api\Seller;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Product;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SellerChatController extends Controller
{
    public function __construct(private readonly ChatService $chat) {}

    /**
     * List all direct chat conversations for the authenticated seller.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = ChatConversation::query()
            ->where(fn ($q) => $q->where('buyer_id', $user->id)->orWhere('seller_id', $user->id))
            ->with(['buyer.salerProfile', 'seller.salerProfile', 'product.images', 'latestMessage.sender'])
            ->orderByDesc('last_message_at')
            ->paginate($request->integer('per_page', 25));

        $data = $conversations->getCollection()->map(function (ChatConversation $conv) use ($user) {
            $other = $conv->otherParty($user);
            $unreadCount = $conv->messages()
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->count();

            $firstImg = $conv->product?->images?->first();
            $productImgUrl = null;
            if ($firstImg?->image_path) {
                $productImgUrl = str_starts_with($firstImg->image_path, 'http')
                    ? $firstImg->image_path
                    : asset('storage/' . $firstImg->image_path);
            }

            return [
                'id' => $conv->id,
                'other_user' => [
                    'id' => $other->id,
                    'name' => $other->name,
                    'store_name' => $other->salerProfile?->store_name ?? $other->name,
                    'role' => $other->role?->value ?? 'user',
                    'avatar_url' => $other->avatar ? (str_starts_with($other->avatar, 'http') ? $other->avatar : asset('storage/' . $other->avatar)) : null,
                ],
                'product' => $conv->product ? [
                    'id' => $conv->product->id,
                    'title' => $conv->product->title,
                    'price' => (float) $conv->product->price,
                    'image_url' => $productImgUrl,
                ] : null,
                'latest_message' => $conv->latestMessage ? [
                    'id' => $conv->latestMessage->id,
                    'body' => $conv->latestMessage->body,
                    'is_mine' => $conv->latestMessage->sender_id === $user->id,
                    'has_attachment' => (bool) $conv->latestMessage->attachment_path,
                    'attachment_name' => $conv->latestMessage->attachmentName(),
                    'created_at' => $conv->latestMessage->created_at?->diffForHumans(),
                ] : null,
                'unread_count' => $unreadCount,
                'last_message_at' => $conv->last_message_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'conversations' => $data,
            'current_page' => $conversations->currentPage(),
            'last_page' => $conversations->lastPage(),
            'total' => $conversations->total(),
        ]);
    }

    /**
     * Get details and messages of a single conversation.
     */
    public function show(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = $request->user();
        $this->authorizeParticipant($conversation, $user);

        // Mark unread messages as read
        $this->chat->markRead($conversation, $user);

        $other = $convOther = $conversation->otherParty($user);

        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function (ChatMessage $m) use ($user) {
                return [
                    'id' => $m->id,
                    'sender_id' => $m->sender_id,
                    'sender_name' => $m->sender?->name ?? 'User',
                    'is_mine' => $m->sender_id === $user->id,
                    'body' => $m->body,
                    'has_attachment' => (bool) $m->attachment_path,
                    'attachment_url' => $m->attachment_path ? route('api.seller.chat.attachment', $m) : null,
                    'attachment_name' => $m->attachmentName(),
                    'is_image' => $m->isImage(),
                    'is_pdf' => $m->isPdf(),
                    'created_at' => $m->created_at?->format('h:i A'),
                    'date' => $m->created_at?->format('M d, Y'),
                ];
            });

        $productImg = null;
        if ($conversation->product) {
            $firstImg = $conversation->product->images()->first();
            if ($firstImg?->image_path) {
                $productImg = str_starts_with($firstImg->image_path, 'http')
                    ? $firstImg->image_path
                    : asset('storage/' . $firstImg->image_path);
            }
        }

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'other_user' => [
                    'id' => $other->id,
                    'name' => $other->name,
                    'store_name' => $other->salerProfile?->store_name ?? $other->name,
                    'role' => $other->role?->value ?? 'user',
                ],
                'product' => $conversation->product ? [
                    'id' => $conversation->product->id,
                    'title' => $conversation->product->title,
                    'price' => (float) $conversation->product->price,
                    'image_url' => $productImg,
                ] : null,
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * Send a new message to an existing conversation.
     */
    public function store(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = $request->user();
        $this->authorizeParticipant($conversation, $user);

        $request->validate([
            'body' => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $attachment = $request->hasFile('attachment') ? $request->file('attachment') : null;
        $body = $request->input('body');

        if (blank($body) && ! $attachment) {
            return response()->json(['message' => 'Message body or attachment is required.'], 422);
        }

        $message = $this->chat->sendMessage($conversation, $user, $body, $attachment);

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'sender_name' => $user->name,
                'is_mine' => true,
                'body' => $message->body,
                'has_attachment' => (bool) $message->attachment_path,
                'attachment_url' => $message->attachment_path ? route('api.seller.chat.attachment', $message) : null,
                'attachment_name' => $message->attachmentName(),
                'is_image' => $message->isImage(),
                'is_pdf' => $message->isPdf(),
                'created_at' => $message->created_at?->format('h:i A'),
                'date' => $message->created_at?->format('M d, Y'),
            ],
        ], 201);
    }

    /**
     * Start a conversation with a specific user or support/admin.
     */
    public function start(Request $request): JsonResponse
    {
        $user = $request->user();

        $targetUserId = $request->input('user_id');
        $productId = $request->input('product_id');

        $targetUser = null;
        if ($targetUserId) {
            $targetUser = User::find($targetUserId);
        } else {
            // Default to Admin / Support
            $targetUser = User::where('role', UserRole::Admin)->first();
        }

        if (! $targetUser) {
            return response()->json(['message' => 'Target user or admin support not found.'], 404);
        }

        if ($targetUser->id === $user->id) {
            return response()->json(['message' => 'You cannot start a conversation with yourself.'], 422);
        }

        $product = $productId ? Product::find($productId) : null;

        $conversation = $this->chat->startOrGetConversation($user, $targetUser, $product);

        $initialBody = trim((string) $request->input('initial_message'));
        if ($initialBody !== '') {
            $this->chat->sendMessage($conversation, $user, $initialBody);
        }

        return response()->json([
            'conversation_id' => $conversation->id,
            'message' => 'Conversation ready',
        ]);
    }

    /**
     * Download or view attachment securely.
     */
    public function attachment(ChatMessage $message)
    {
        abort_unless($message->attachment_path && Storage::disk('local')->exists($message->attachment_path), 404);

        return Storage::disk('local')->response($message->attachment_path);
    }

    private function authorizeParticipant(ChatConversation $conversation, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        abort_unless(in_array($user->id, [$conversation->buyer_id, $conversation->seller_id], true), 403);
    }
}
