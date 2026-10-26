<?php

namespace App\Http\Controllers\Saas\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MessageReplyRequest;
use App\Jobs\SendLoginDetailsJob;
use App\Models\User;
use App\Services\ContactMessageService;
use App\Services\OwnerOnboardingService;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Lead;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

class ContactMessageController extends Controller
{
    public $contactMessageService;
    public function __construct()
    {
        $this->contactMessageService = new ContactMessageService;
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->contactMessageService->getAllData();
        } else {
            $data['pageTitle'] = __('Messages');
            return view('saas.admin.message', $data);
        }
    }

    public function getInfo(Request $request)
    {
        $data = $this->contactMessageService->getInfo($request->id);
        $data->is_view = ACTIVE;
        $data->save();
        // Surface the referring affiliate (if any) so the admin knows this is an attributed lead.
        $data->load('referralAffiliate.user');
        $data->referral_affiliate_name = optional(optional($data->referralAffiliate)->user)->name;
        return $data;
    }

    public function reply(MessageReplyRequest $request)
    {
        return $this->contactMessageService->reply($request);
    }

    public function destroy($id)
    {
        return $this->contactMessageService->delete($id);
    }

    /**
     * One-click: onboard a trial-intent contact message into an owner account (after the admin's
     * own due diligence). Reuses the shared owner-onboarding machinery — temp password, credentials
     * by email + SMS, forced reset on first login. Marks the message with the owner it created so
     * it can't be onboarded twice. If the enquiry came through an affiliate's referral link, the
     * owner is credited to that affiliate (owners.affiliate_id) so their commission lines start.
     */
    public function createOwner(Request $request, $id)
    {
        $message = Message::findOrFail($id);

        if ((string) ($message->intent ?? '') !== 'trial') {
            return back()->with('error', __('Only a trial / get-started enquiry can be turned into an owner account.'));
        }
        if (! empty($message->owner_id)) {
            return back()->with('error', __('An owner account was already created from this message.'));
        }
        if (empty($message->email) || ! filter_var($message->email, FILTER_VALIDATE_EMAIL)) {
            return back()->with('error', __('This message has no valid email — the account and its setup link are keyed on it.'));
        }
        if (User::where('email', $message->email)->exists()) {
            return back()->with('error', __('An account already exists with this email address.'));
        }

        DB::beginTransaction();
        try {
            $onboard = app(OwnerOnboardingService::class)->create([
                'first_name'   => $message->first_name,
                'last_name'    => $message->last_name,
                'phone'        => $message->phone,
                'email'        => $message->email,
                // Attribution loop: if this enquiry came through an affiliate's referral link, credit
                // the resulting owner to them (owners.affiliate_id). Null for an organic enquiry.
                'affiliate_id' => $message->referral_affiliate_id ?: null,
            ]);

            $message->owner_id = $onboard['owner']->id;
            $message->save();

            DB::commit();

            SendLoginDetailsJob::dispatch($onboard['user'], $onboard['password']);

            if (config('app.debug')) {
                session()->flash('dev_credentials', app(OwnerOnboardingService::class)->devCredentials($onboard['user'], $onboard['password']));
            }

            return back()->with('success', __('Owner account created — login details sent to :email.', ['email' => $message->email]));
        } catch (\Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Trial-message owner creation failed: ' . $e->getMessage(), ['message_id' => $id]);
            return back()->with('error', __('Could not create the owner account. Please try again.'));
        }
    }

    /**
     * Stream 1 — route an UNATTRIBUTED inbound enquiry into the affiliate lead marketplace so any
     * affiliate can claim and work it (removes the company-as-bottleneck lead supply). Admin-gated by
     * design (the "admin okays it" model): a direct, conversion-ready ask shouldn't be dumped into a
     * shared pool, so the admin decides which enquiries go in. A REFERRED enquiry is never pooled —
     * it's already attributed to the referring affiliate; an onboarded or already-pooled one is blocked.
     * Pool leads still flow through the normal claim → trial → admin-approve pipeline (anti-phantom intact).
     */
    public function sendToMarketplace(Request $request, $id)
    {
        $message = Message::findOrFail($id);

        if ($message->referral_affiliate_id) {
            return back()->with('error', __('This enquiry came through an affiliate\'s referral link — it\'s already credited to them, so it can\'t go to the shared marketplace.'));
        }
        if (! empty($message->owner_id)) {
            return back()->with('error', __('An owner account was already created from this enquiry.'));
        }
        if (! empty($message->lead_id)) {
            return back()->with('error', __('This enquiry is already in the lead marketplace.'));
        }

        $personName = trim("{$message->first_name} {$message->last_name}") ?: ($message->email ?: __('Prospect'));

        DB::beginTransaction();
        try {
            // No company_name on a contact enquiry — seed it from the person's name as a placeholder
            // the claiming affiliate enriches. Reuse an existing company by phone where possible.
            $normalized = strtolower(trim(preg_replace('/\s+/', ' ', $personName)));
            $company = Company::when($message->phone, fn ($q) => $q->where('phone', $message->phone))
                ->orWhere('normalized_name', $normalized)
                ->first();

            if (! $company) {
                $company = Company::create([
                    'company_name'    => $personName,
                    'normalized_name' => $normalized,
                    'country'         => config('referrals.default_country', 'Kenya'),
                    'phone'           => $message->phone,
                    'email'           => $message->email,
                ]);
            }

            $lead = Lead::create([
                'company_id'           => $company->id,
                'affiliate_id'         => null,
                'contact_person_name'  => $personName,
                'contact_person_role'  => 'owner',
                'temperature'          => 'cold',
                'status'               => 'active',
                // Marketplace leads must be 'admin'-sourced (the enum is admin|affiliate) so the
                // expiry → return-to-pool cycle works (Lead::shouldReturnToPool). Origin is in notes.
                'source'               => 'admin',
                'marketplace_status'   => 'marketplace',
                'marketplace_at'       => now(),
                'ownership_expires_at' => null,
                'notes'                => __('From a website enquiry: :msg', ['msg' => \Illuminate\Support\Str::limit($message->message, 400)]),
            ]);

            $message->lead_id = $lead->id;
            $message->save();

            DB::commit();

            return back()->with('success', __('Added to the lead marketplace — affiliates can now claim and work this lead.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Send-to-marketplace failed: ' . $e->getMessage(), ['message_id' => $id]);
            return back()->with('error', __('Could not add this enquiry to the marketplace. Please try again.'));
        }
    }
}
