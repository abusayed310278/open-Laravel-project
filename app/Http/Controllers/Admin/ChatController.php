<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatMessageRequest;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(private readonly ChatService $chat) {}

    public function index(Request $request): View
    {
        $currentRole = $request->query('role');
        $search = trim($request->query('search', ''));
        $tab = $request->query('tab', 'users');

        $usersQuery = User::query()
            ->where('id', '!=', Auth::id())
            ->with('profile')
            ->when($currentRole, function ($q) use ($currentRole) {
                $q->where('role', $currentRole);
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest();

        $users = $usersQuery->paginate(15, ['*'], 'users_page')->withQueryString();

        $conversationsQuery = ChatConversation::query()
            ->with(['buyer.profile', 'seller.profile', 'product', 'latestMessage'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->whereHas('buyer', fn ($b) => $b->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('seller', fn ($s) => $s->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('product', fn ($p) => $p->where('title', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('last_message_at');

        $conversations = $conversationsQuery->paginate(15, ['*'], 'conversations_page')->withQueryString();

        $roleCounts = [
            'all' => User::where('id', '!=', Auth::id())->count(),
            'saler' => User::where('role', UserRole::Saler)->count(),
            'business' => User::where('role', UserRole::Business)->count(),
            'verifier' => User::where('role', UserRole::Verifier)->count(),
            'customer' => User::where('role', UserRole::Customer)->count(),
            'admin' => User::where('role', UserRole::Admin)->where('id', '!=', Auth::id())->count(),
        ];

        return view('admin.chat.index', [
            'users' => $users,
            'conversations' => $conversations,
            'currentRole' => $currentRole,
            'search' => $search,
            'tab' => $tab,
            'roleCounts' => $roleCounts,
            'roles' => UserRole::cases(),
        ]);
    }

    public function startWithUser(User $user): RedirectResponse
    {
        $admin = Auth::user();
        abort_if($admin->id === $user->id, 422, 'You cannot message yourself.');

        $conversation = $this->chat->startOrGetConversation($admin, $user, null);

        return redirect()->route('admin.chat.show', $conversation);
    }

    public function show(ChatConversation $conversation): View
    {
        $this->chat->markRead($conversation, Auth::user());

        return view('admin.chat.show', [
            'conversation' => $conversation->load(['buyer.profile', 'seller.profile', 'product']),
            'messages' => $conversation->messages()->with('sender.profile')->oldest()->get(),
        ]);
    }

    public function store(StoreChatMessageRequest $request, ChatConversation $conversation): RedirectResponse
    {
        $this->chat->sendMessage(
            $conversation,
            Auth::user(),
            $request->string('body')->value() ?: null,
            $request->file('attachment')
        );

        return back();
    }

    public function poll(ChatConversation $conversation, int $afterId): JsonResponse
    {
        $messages = $conversation->messages()->with('sender.profile')->where('id', '>', $afterId)->oldest()->get();

        if ($messages->isNotEmpty()) {
            $this->chat->markRead($conversation, Auth::user());
        }

        return response()->json($messages->map(fn ($m) => [
            'id' => $m->id,
            'sender_id' => $m->sender_id,
            'sender_name' => $m->sender->name,
            'body' => $m->body,
            'attachment_url' => $m->attachment_path ? route('chat.attachment', $m) : null,
            'is_image' => $m->isImage(),
            'created_at' => $m->created_at->format('g:i A'),
        ]));
    }
}
