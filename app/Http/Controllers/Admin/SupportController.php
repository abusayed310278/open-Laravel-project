<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SupportTicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupportTicketReplyRequest;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function __construct(private readonly SupportTicketService $tickets) {}

    public function index(Request $request): View
    {
        $ticketsQuery = SupportTicket::query()
            ->with(['user', 'assignee'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest();

        return view('admin.support.index', [
            'tickets' => $ticketsQuery->paginate(20)->withQueryString(),
            'statuses' => SupportTicketStatus::cases(),
        ]);
    }

    public function show(SupportTicket $ticket): View
    {
        return view('admin.support.show', [
            'ticket' => $ticket->load(['messages.sender', 'user', 'assignee']),
            'statuses' => SupportTicketStatus::cases(),
            'staff' => User::query()->where('role', 'admin')->get(),
        ]);
    }

    public function reply(StoreSupportTicketReplyRequest $request, SupportTicket $ticket): RedirectResponse
    {
        $this->tickets->reply($ticket, $request->user(), $request->string('body')->value());

        return back()->with('status', 'Reply sent.');
    }

    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $request->validate(['status' => ['required', Rule::enum(SupportTicketStatus::class)]]);

        $this->tickets->updateStatus($ticket, SupportTicketStatus::from($request->string('status')->value()));

        return back()->with('status', 'Ticket status updated.');
    }

    public function assign(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $request->validate(['assigned_to' => ['required', 'exists:users,id']]);

        $this->tickets->assign($ticket, User::findOrFail($request->integer('assigned_to')));

        return back()->with('status', 'Ticket assigned.');
    }
}
