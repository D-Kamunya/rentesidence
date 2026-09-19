<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use Illuminate\Http\Request;

/**
 * The requester side of the reusable support rail — shared by EVERY account type. The views extend
 * a layout resolved from the current user's role, so one controller + one set of views serve owners,
 * affiliates, finance partners, tenants and any future role. Admin has its own controller.
 */
class SupportController extends Controller
{
    public function __construct(private SupportTicketService $support)
    {
    }

    /** Map the signed-in user's role to their app layout, so the shared views render in their shell. */
    private function layout(): string
    {
        return [
            USER_ROLE_OWNER           => 'owner.layouts.app',
            USER_ROLE_TENANT          => 'tenant.layouts.app',
            USER_ROLE_MAINTAINER      => 'maintainer.layouts.app',
            USER_ROLE_AFFILIATE       => 'affiliate.layouts.app',
            USER_ROLE_FINANCE_PARTNER => 'finance-partner.layouts.app',
        ][(int) auth()->user()->role] ?? 'owner.layouts.app';
    }

    public function index()
    {
        $tickets = SupportTicket::where('requester_user_id', auth()->id())
            ->withCount('replies')
            ->latest('last_reply_at')
            ->paginate(15);

        return view('support.index', [
            'layout'    => $this->layout(),
            'tickets'   => $tickets,
            'pageTitle' => __('Support'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject'  => 'required|string|max:160',
            'category' => 'nullable|string|max:60',
            'body'     => 'required|string|max:5000',
        ]);

        $ticket = $this->support->open(auth()->user(), $data);

        return redirect()->route('support.show', $ticket->id)
            ->with('success', __('Your message has been sent to support — we\'ll reply here.'));
    }

    public function show(SupportTicket $ticket)
    {
        abort_unless((int) $ticket->requester_user_id === (int) auth()->id(), 404);

        $this->support->markRead($ticket, 'requester');

        return view('support.show', [
            'layout'    => $this->layout(),
            'ticket'    => $ticket->load('replies.author'),
            'pageTitle' => __('Support'),
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        abort_unless((int) $ticket->requester_user_id === (int) auth()->id(), 404);

        if ($ticket->isClosed()) {
            return back()->with('error', __('This ticket is closed. Open a new one if you still need help.'));
        }

        $data = $request->validate(['body' => 'required|string|max:5000']);
        $this->support->reply($ticket, auth()->user(), $data['body'], false);

        return back()->with('success', __('Reply sent.'));
    }
}
