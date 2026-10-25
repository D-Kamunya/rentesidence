<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutreachCampaign;
use App\Models\OutreachRecipient;
use App\Services\OutreachService;
use App\Support\AffiliateInvite;
use Illuminate\Http\Request;

/**
 * Admin outreach campaigns — paste a vetted list, send a CS-layout email to each (recruitment
 * campaigns auto-inject the live invite link so a submission creates the account instantly), and
 * track conversions + bounded follow-ups from one screen. Replaces V1's hand-typed, forget-to-chase
 * mass-mailing. Focused on recruitment; the `type` field leaves room without extra UI.
 */
class OutreachController extends Controller
{
    public function __construct(private OutreachService $outreach)
    {
    }

    public function index()
    {
        $campaigns = OutreachCampaign::withCount([
            'recipients',
            'recipients as sent_count'      => fn ($q) => $q->whereIn('status', ['sent', 'converted']),
            'recipients as converted_count' => fn ($q) => $q->where('status', 'converted'),
        ])->latest()->paginate(20);

        return view('admin.outreach.index', [
            'pageTitle' => __('Outreach Campaigns'),
            'campaigns' => $campaigns,
        ]);
    }

    public function create()
    {
        return view('admin.outreach.create', [
            'pageTitle'    => __('New Campaign'),
            'defaultSubject' => OutreachCampaign::defaultRecruitmentSubject(),
            'defaultBody'    => OutreachCampaign::defaultRecruitmentBody(),
            'inviteActive'   => AffiliateInvite::isActive(),
            'inviteLink'     => AffiliateInvite::link(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:160',
            'type'       => 'required|in:recruitment,general',
            'subject'    => 'required|string|max:200',
            'body'       => 'required|string|max:20000',
            'recipients' => 'required|string',
            'follow_up_days' => 'nullable|integer|min:1|max:60',
            'max_follow_ups' => 'nullable|integer|min:0|max:3',
            'send_per_hour'  => 'nullable|integer|min:1|max:2000',
        ]);

        $parsed = $this->outreach->parseRecipients($data['recipients']);
        if (empty($parsed)) {
            return back()->withInput()->with('error', __('No valid email addresses were found in the list.'));
        }

        $campaign = OutreachCampaign::create([
            'name'           => $data['name'],
            'type'           => $data['type'],
            'subject'        => $data['subject'],
            'body'           => $data['body'],
            'status'         => 'draft',
            'follow_up_days' => $data['follow_up_days'] ?? 4,
            'max_follow_ups' => $data['max_follow_ups'] ?? 1,
            'send_per_hour'  => $data['send_per_hour'] ?? OutreachCampaign::DEFAULT_SEND_PER_HOUR,
            'created_by'     => auth()->id(),
        ]);
        $added = $this->outreach->addRecipients($campaign, $parsed);

        return redirect()->route('admin.outreach.show', $campaign->id)
            ->with('success', __(':n recipients added. Review, then send when ready.', ['n' => $added]));
    }

    public function show($id)
    {
        $campaign = OutreachCampaign::findOrFail($id);
        // Keep conversion counts fresh whenever the admin opens the campaign.
        $this->outreach->reconcileConversions($campaign);

        $counts = [
            'total'     => $campaign->recipients()->count(),
            'pending'   => $campaign->recipients()->where('status', 'pending')->count(),
            'sent'      => $campaign->recipients()->where('status', 'sent')->count(),
            'converted' => $campaign->recipients()->where('status', 'converted')->count(),
            'failed'    => $campaign->recipients()->where('status', 'failed')->count(),
        ];

        $recipients = $campaign->recipients()->orderByRaw("FIELD(status,'converted','sent','pending','failed')")
            ->orderBy('email')->paginate(50);

        return view('admin.outreach.show', [
            'pageTitle'    => $campaign->name,
            'campaign'     => $campaign,
            'counts'       => $counts,
            'recipients'   => $recipients,
            'inviteActive' => AffiliateInvite::isActive(),
            'inviteLink'   => AffiliateInvite::link(),
        ]);
    }

    /** Add more recipients to an existing campaign (the newly added ones are 'pending'). */
    public function addRecipients(Request $request, $id)
    {
        $campaign = OutreachCampaign::findOrFail($id);
        $data = $request->validate(['recipients' => 'required|string']);

        $parsed = $this->outreach->parseRecipients($data['recipients']);
        if (empty($parsed)) {
            return back()->with('error', __('No valid email addresses were found.'));
        }
        $added = $this->outreach->addRecipients($campaign, $parsed);

        return back()->with('success', __(':n new recipients added.', ['n' => $added]));
    }

    public function send($id)
    {
        $campaign = OutreachCampaign::findOrFail($id);

        // Recruitment campaigns MUST carry a live invite link, or the "account created instantly"
        // promise breaks. Block the send and point the admin to generate one.
        if ($campaign->isRecruitment() && ! AffiliateInvite::isActive()) {
            return back()->with('error', __('Generate an active invitation link first (Affiliates → Applications) — recruitment emails need it.'));
        }

        $pending = $campaign->recipients()->where('status', 'pending')->count();
        if ($pending === 0) {
            return back()->with('error', __('There are no pending recipients to send to.'));
        }

        $sent = $this->outreach->send($campaign);

        $rate  = max(1, (int) ($campaign->send_per_hour ?: OutreachCampaign::DEFAULT_SEND_PER_HOUR));
        $hours = ceil($sent / $rate);

        return back()->with('success', __('Queued :n recipients, paced at :rate/hour (~:h hour(s) to finish) so it stays under your host\'s limit. Follow-ups to non-responders are automatic.', ['n' => $sent, 'rate' => $rate, 'h' => $hours]));
    }

    public function destroy($id)
    {
        $campaign = OutreachCampaign::findOrFail($id);
        $campaign->delete(); // cascades recipients
        return redirect()->route('admin.outreach.index')->with('success', __('Campaign deleted.'));
    }
}
