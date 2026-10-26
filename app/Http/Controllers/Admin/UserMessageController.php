<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\Mail\SendAdminMessageMail;
use App\Models\User;
use App\Services\SupportTicketService;
use Illuminate\Http\Request;

/**
 * Admin-initiated contact: lets an admin START a conversation with a user (owner, affiliate, …) —
 * the support system is otherwise requester-initiated only. Rides the support rails, so the target
 * gets a nav badge + in-app bell (like any admin reply) plus an email prompting them to log in.
 */
class UserMessageController extends Controller
{
    public function send(Request $request, SupportTicketService $support)
    {
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'subject' => 'required|string|max:160',
            'body'    => 'required|string|max:5000',
        ]);

        $target = User::findOrFail($data['user_id']);
        if ((int) $target->role === (int) USER_ROLE_ADMIN) {
            return back()->with('error', __('You can only message non-admin users.'));
        }

        $ticket = $support->adminStartConversation($target, $request->user(), $data['subject'], $data['body']);
        SendAdminMessageMail::dispatch($target->id, $ticket->id, $data['subject'], $data['body']);

        return back()->with('success', __('Message sent to :name — it\'s in their support inbox (with a bell + badge) and emailed to them.', [
            'name' => trim(($target->first_name ?? '') . ' ' . ($target->last_name ?? '')) ?: $target->email,
        ]));
    }
}
