<?php

namespace App\Services;

use App\Enums\SupportTicketStatus;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use App\Notifications\NewSupportTicket;
use App\Notifications\SupportTicketReplied;
use Illuminate\Support\Facades\Notification;

class SupportTicketService
{
    public function create(User $user, string $subject, string $category, string $body): SupportTicket
    {
        $ticket = SupportTicket::create([
            'ticket_number' => $this->nextTicketNumber(),
            'user_id' => $user->id,
            'subject' => $subject,
            'category' => $category,
            'status' => SupportTicketStatus::Open,
        ]);

        $ticket->messages()->create([
            'sender_id' => $user->id,
            'body' => $body,
        ]);

        Notification::send(User::query()->where('role', UserRole::Admin)->get(), new NewSupportTicket($ticket));

        ActivityLog::record('support.ticket_created', $ticket);

        return $ticket;
    }

    public function reply(SupportTicket $ticket, User $sender, string $body): SupportTicketMessage
    {
        $message = $ticket->messages()->create([
            'sender_id' => $sender->id,
            'body' => $body,
        ]);

        // A customer replying re-opens a ticket waiting on them; staff
        // replying flips it to waiting on the customer.
        $isCustomerReply = $sender->id === $ticket->user_id;

        $ticket->update(['status' => $isCustomerReply
            ? SupportTicketStatus::InProgress
            : SupportTicketStatus::WaitingCustomer,
        ]);

        if (! $isCustomerReply) {
            $ticket->user->notify(new SupportTicketReplied($message));
        }

        return $message;
    }

    public function updateStatus(SupportTicket $ticket, SupportTicketStatus $status): SupportTicket
    {
        $ticket->update(['status' => $status]);

        ActivityLog::record('support.ticket_status_updated', $ticket, ['status' => $status->value]);

        return $ticket;
    }

    public function assign(SupportTicket $ticket, User $assignee): SupportTicket
    {
        $ticket->update(['assigned_to' => $assignee->id]);

        ActivityLog::record('support.ticket_assigned', $ticket, ['assignee' => $assignee->id]);

        return $ticket;
    }

    private function nextTicketNumber(): string
    {
        $year = now()->format('Y');
        $count = SupportTicket::query()->whereYear('created_at', now()->year)->count() + 1;

        return "TKT-{$year}-".str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }
}
