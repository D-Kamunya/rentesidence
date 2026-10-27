<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Services\FreeOnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The public face of the affiliate "Set up on Free" invite — the /free/activate/{token} page an
 * invited owner opens, and the password they set to activate.
 *
 * The owner account is created ONLY here, on the owner's own action (opening the link + choosing a
 * password = their consent). Un-accepted invites simply expire, so no phantom accounts accrue.
 */
class PublicFreeInviteController extends Controller
{
    public function __construct(private FreeOnboardingService $free)
    {
    }

    /** The activation landing: a short pitch + the set-password form (or an expired/used notice). */
    public function landing(string $token)
    {
        $lead  = Lead::with('company')->where('free_invite_token', $token)->first();
        $state = $lead ? $this->free->inviteState($lead) : 'invalid';

        if (! $lead || $state === 'invalid') {
            return view('free-invite.closed', [
                'pageTitle' => __('Invite not found'),
                'heading'   => __('This invite link is not valid'),
                'message'   => __('Double-check the link, or ask your contact to send you a fresh one.'),
            ]);
        }

        if ($state === 'accepted') {
            return view('free-invite.closed', [
                'pageTitle' => __('Already activated'),
                'heading'   => __('Your account is already active'),
                'message'   => __('This account has been activated — please sign in to continue.'),
                'showLogin' => true,
            ]);
        }

        if ($state === 'expired') {
            return view('free-invite.closed', [
                'pageTitle' => __('Invite expired'),
                'heading'   => __('This invite has expired'),
                'message'   => __('Activation links are valid for a limited time. Ask your contact to send you a new one.'),
            ]);
        }

        return view('free-invite.activate', [
            'pageTitle'   => __('Activate your free account'),
            'token'       => $token,
            'companyName' => optional($lead->company)->company_name,
            'email'       => $lead->free_invite_email ?: optional($lead->company)->email,
            'appName'     => getOption('app_name'),
        ]);
    }

    /** Set the password → create the Free account on the owner's consent → sign them in. */
    public function accept(Request $request, string $token)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $result = $this->free->acceptInvite($token, $request->input('password'));

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        Auth::login($result['user']);

        return redirect()->route('owner.dashboard')
            ->with('success', __('Welcome to :app — your free account is ready.', ['app' => getOption('app_name')]));
    }
}
