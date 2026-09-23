<?php

namespace App\Services;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The reusable SUPPORT rail. Any account holder opens a threaded conversation with admin; admin
 * replies; both sides carry an unread flag that drives the menu badges. Role-agnostic on purpose —
 * a new account type only needs a sidebar entry, no changes here.
 */
class SupportTicketService
{
    /** Open a new ticket (the subject + first message), notify admins. Returns the ticket. */
    public function open(User $user, array $data): SupportTicket
    {
        return DB::transaction(function () use ($user, $data) {
            $ticket = SupportTicket::create([
                'requester_user_id' => $user->id,
                'requester_role'    => $user->role,
                'subject'           => $data['subject'],
                'category'          => $data['category'] ?? null,
                'priority'          => $data['priority'] ?? 'normal',
                'status'            => SupportTicket::STATUS_OPEN,
                'admin_unread'      => true,
                'requester_unread'  => false,
                'last_reply_at'     => now(),
            ]);

            SupportTicketReply::create([
                'support_ticket_id' => $ticket->id,
                'user_id'           => $user->id,
                'is_admin'          => false,
                'body'              => $data['body'],
            ]);

            $this->notifyAdmins(
                __('New support ticket'),
                trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) . ': ' . $ticket->subject,
                route('admin.support.show', $ticket->id)
            );

            return $ticket;
        });
    }

    /**
     * Append a reply. If the author is admin → the ticket is "answered" and the requester gets an
     * unread flag + notification; if the requester replies → it flips back to "open" for admin.
     */
    public function reply(SupportTicket $ticket, User $author, string $body, bool $isAdmin): SupportTicketReply
    {
        return DB::transaction(function () use ($ticket, $author, $body, $isAdmin) {
            $reply = SupportTicketReply::create([
                'support_ticket_id' => $ticket->id,
                'user_id'           => $author->id,
                'is_admin'          => $isAdmin,
                'body'              => $body,
            ]);

            if ($isAdmin) {
                $ticket->update([
                    'status'           => SupportTicket::STATUS_ANSWERED,
                    'requester_unread' => true,
                    'admin_unread'     => false,
                    'last_reply_at'    => now(),
                ]);
                addNotification(
                    __('Support reply'),
                    __('Support has replied to your ticket: ') . $ticket->subject,
                    route('support.show', $ticket->id),
                    null,
                    $ticket->requester_user_id,
                    $author->id
                );
            } else {
                $ticket->update([
                    'status'         => SupportTicket::STATUS_OPEN,
                    'admin_unread'   => true,
                    'last_reply_at'  => now(),
                ]);
                $this->notifyAdmins(
                    __('New support reply'),
                    trim(($author->first_name ?? '') . ' ' . ($author->last_name ?? '')) . ': ' . $ticket->subject,
                    route('admin.support.show', $ticket->id)
                );
            }

            return $reply;
        });
    }

    /** Clear the unread flag for one side when they open the thread. */
    public function markRead(SupportTicket $ticket, string $side): void
    {
        if ($side === 'admin' && $ticket->admin_unread) {
            $ticket->update(['admin_unread' => false]);
        } elseif ($side === 'requester' && $ticket->requester_unread) {
            $ticket->update(['requester_unread' => false]);
        }
    }

    /** Admin action: resolve / reopen / close. */
    public function setStatus(SupportTicket $ticket, string $status): void
    {
        $ticket->update(['status' => $status]);
    }

    /** Admin badge: tickets with new requester activity awaiting a reply (not closed). */
    public function adminOpenCount(): int
    {
        return SupportTicket::where('admin_unread', true)
            ->where('status', '!=', SupportTicket::STATUS_CLOSED)
            ->count();
    }

    /** Requester badge: this user's tickets with an unread admin reply (not closed). */
    public function requesterUnreadCount(int $userId): int
    {
        return SupportTicket::where('requester_user_id', $userId)
            ->where('requester_unread', true)
            ->where('status', '!=', SupportTicket::STATUS_CLOSED)
            ->count();
    }

    /** In-app notification to every admin. */
    private function notifyAdmins(string $title, string $body, string $url): void
    {
        User::where('role', USER_ROLE_ADMIN)->pluck('id')->each(function ($adminId) use ($title, $body, $url) {
            addNotification($title, $body, $url, null, $adminId, null);
        });
    }
}
