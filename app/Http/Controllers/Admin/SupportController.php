<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use Illuminate\Http\Request;

/** Admin side of the reusable support rail — the inbox, the thread, replies and status control. */
class SupportController extends Controller
{
    public function __construct(private SupportTicketService $support)
    {
    }

    public function index(Request $request)
    {
        $status = $request->input('status');

        $tickets = SupportTicket::with('requester')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($w) use ($s) {
                    $w->where('subject', 'like', "%{$s}%")
                      ->orWhereHas('requester', fn ($r) => $r->where('first_name', 'like', "%{$s}%")->orWhere('last_name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"));
                });
            })
            // Waiting-on-admin first, then most recent.
            ->orderByDesc('admin_unread')
            ->latest('last_reply_at')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'open'     => SupportTicket::where('status', SupportTicket::STATUS_OPEN)->count(),
            'answered' => SupportTicket::where('status', SupportTicket::STATUS_ANSWERED)->count(),
            'resolved' => SupportTicket::where('status', SupportTicket::STATUS_RESOLVED)->count(),
            'closed'   => SupportTicket::where('status', SupportTicket::STATUS_CLOSED)->count(),
            'awaiting' => $this->support->adminOpenCount(),
        ];

        return view('admin.support.index', [
            'tickets'   => $tickets,
            'counts'    => $counts,
            'status'    => $status,
            'pageTitle' => __('Support'),
        ]);
    }

    public function show(SupportTicket $ticket)
    {
        $this->support->markRead($ticket, 'admin');

        return view('admin.support.show', [
            'ticket'    => $ticket->load('replies.author', 'requester'),
            'pageTitle' => __('Support'),
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        if ($ticket->isClosed()) {
            return back()->with('error', __('This ticket is closed. Reopen it to reply.'));
        }

        $data = $request->validate(['body' => 'required|string|max:5000']);
        $this->support->reply($ticket, auth()->user(), $data['body'], true);

        return back()->with('success', __('Reply sent to the requester.'));
    }

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', [
                SupportTicket::STATUS_OPEN, SupportTicket::STATUS_ANSWERED,
                SupportTicket::STATUS_RESOLVED, SupportTicket::STATUS_CLOSED,
            ]),
        ]);

        $this->support->setStatus($ticket, $data['status']);

        return back()->with('success', __('Ticket marked as :status.', ['status' => $data['status']]));
    }
}
