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
                route('admin.support.show', $ticket->id),
                $user
            );

            return $ticket;
        });
    }

    /**
     * Admin-initiated outreach: the admin STARTS a thread with a user (owner, affiliate, any role) —
     * support is otherwise requester-initiated only. Creates the ticket with the TARGET as requester
     * and the admin's message as the first (admin) reply, so the target's unread badge + in-app bell
     * light exactly like a normal admin reply. The target is ALSO emailed (a support message is an
     * event — the client is likely outside the app, so in-app alone wouldn't reach them), unless
     * $notifyByEmail is false (e.g. the welcome, which already has its own welcome email).
     */
    public function adminStartConversation(User $target, User $admin, string $subject, string $body, bool $notifyByEmail = true): SupportTicket
    {
        $ticket = DB::transaction(function () use ($target, $admin, $subject, $body) {
            $ticket = SupportTicket::create([
                'requester_user_id' => $target->id,
                'requester_role'    => $target->role,
                'subject'           => $subject,
                'priority'          => 'normal',
                'status'            => SupportTicket::STATUS_ANSWERED, // admin spoke; awaiting the user
                'admin_unread'      => false,
                'requester_unread'  => true,
                'last_reply_at'     => now(),
            ]);

            SupportTicketReply::create([
                'support_ticket_id' => $ticket->id,
                'user_id'           => $admin->id,
                'is_admin'          => true,
                'body'              => $body,
            ]);

            addNotification(
                __('New message from the team'),
                __('You have a new message: ') . $subject,
                route('support.show', $ticket->id),
                null,
                $target->id,
                $admin->id
            );

            return $ticket;
        });

        if ($notifyByEmail) {
            $this->emailRequester($ticket, $target, $subject, $body);
        }

        return $ticket;
    }

    /**
     * Append a reply. If the author is admin → the ticket is "answered" and the requester gets an
     * unread flag + notification; if the requester replies → it flips back to "open" for admin.
     */
    public function reply(SupportTicket $ticket, User $author, string $body, bool $isAdmin): SupportTicketReply
    {
        $reply = DB::transaction(function () use ($ticket, $author, $body, $isAdmin) {
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
                    route('admin.support.show', $ticket->id),
                    $author
                );
            }

            return $reply;
        });

        // A support message is an event — email the client too, since they're likely outside the app
        // and an in-app bell alone wouldn't reach them. Only admin→client replies (not the client's own).
        if ($isAdmin) {
            $this->emailRequester($ticket, $ticket->requester, __('New reply: ') . $ticket->subject, $body);
        }

        return $reply;
    }

    /**
     * Email the thread's requester a CS-branded copy of an admin message (reusing LifecycleMail, so it's
     * on-brand by construction) with a link to view & reply. Fail-safe: a mail hiccup never breaks the
     * support flow (the in-app notification already landed).
     */
    private function emailRequester(?SupportTicket $ticket, ?User $requester, string $subject, string $body): void
    {
        try {
            if (! $ticket || ! $requester || ! $requester->email) {
                return;
            }

            $appName = getOption('app_name') ?: 'Centresidence';
            $url     = \Illuminate\Support\Facades\Route::has('support.show') ? route('support.show', $ticket->id) : url('/');

            \Illuminate\Support\Facades\Mail::to($requester->email)->queue(new \App\Mail\LifecycleMail($subject, [
                'preheader' => \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', $body)), 120),
                'eyebrow'   => __('Message from the :app team', ['app' => $appName]),
                'title'     => $ticket->subject,
                'blocks'    => [
                    ['type' => 'text', 'html' => nl2br(e($body))],
                    ['type' => 'button', 'url' => $url, 'label' => __('View & reply')],
                    ['type' => 'note', 'text' => __('Tap the button to reply from Support inside your :app account — we read and answer every message.', ['app' => $appName])],
                ],
            ]));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Support email to requester failed: ' . $e->getMessage());
        }
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

    /**
     * In-app notification to every admin. The notification bell INNER-JOINs users on sender_id, so a
     * null sender is created-but-never-shown — pass the triggering user (the requester/author) as the
     * sender so the admin's bell actually lights. Falls back to the earliest admin as a system sender
     * if no triggering user is available.
     */
    private function notifyAdmins(string $title, string $body, string $url, ?User $sender = null): void
    {
        $senderId = $sender?->id ?: User::where('role', USER_ROLE_ADMIN)->min('id');
        if (! $senderId) {
            return; // no admins → no recipients anyway
        }

        User::where('role', USER_ROLE_ADMIN)->pluck('id')->each(function ($adminId) use ($title, $body, $url, $senderId) {
            addNotification($title, $body, $url, null, $adminId, (int) $senderId);
        });
    }
}
