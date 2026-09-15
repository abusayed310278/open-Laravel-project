<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupportTicketReplyRequest;
use App\Http\Requests\StoreSupportTicketRequest;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function __construct(private readonly SupportTicketService $tickets) {}

    public function index(): View
    {
        $user = Auth::user();

        return view('support.index', [
            'tickets' => $user->supportTickets()->latest()->paginate(15),
            'categories' => SupportTicket::CATEGORIES,
            'layout' => $this->layoutFor($user),
            'section' => $this->sectionFor($user),
            'routePrefix' => $this->routeName(''),
        ]);
    }

    public function store(StoreSupportTicketRequest $request): RedirectResponse
    {
        $ticket = $this->tickets->create(
            Auth::user(),
            $request->string('subject')->value(),
            $request->string('category')->value(),
            $request->string('body')->value(),
        );

        return redirect()->route($this->routeName('support.show'), $ticket)->with('status', 'Ticket submitted — we\'ll respond soon.');
    }

    public function show(SupportTicket $ticket): View
    {
        abort_unless($ticket->user_id === Auth::id(), 403);

        return view('support.show', [
            'ticket' => $ticket->load(['messages.sender']),
            'layout' => $this->layoutFor(Auth::user()),
            'section' => $this->sectionFor(Auth::user()),
            'routePrefix' => $this->routeName(''),
        ]);
    }

    public function reply(StoreSupportTicketReplyRequest $request, SupportTicket $ticket): RedirectResponse
    {
        abort_unless($ticket->user_id === Auth::id(), 403);

        $this->tickets->reply($ticket, Auth::user(), $request->string('body')->value());

        return back();
    }

    private function layoutFor(User $user): string
    {
        return match (true) {
            $user->isBusiness() => 'layouts.business',
            $user->isSaler() => 'layouts.saler',
            default => 'layouts.customer',
        };
    }

    private function sectionFor(User $user): string
    {
        return $user->isBusiness() || $user->isSaler() ? 'content' : 'account-content';
    }

    private function routeName(string $name): string
    {
        $user = Auth::user();

        $prefix = match (true) {
            $user->isBusiness() => 'business.',
            $user->isSaler() => 'saler.',
            default => 'account.',
        };

        return $prefix.$name;
    }
}
