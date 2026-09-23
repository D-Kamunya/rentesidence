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
     * it can't be onboarded twice. Not a referral — no attribution, no reward.
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
                'affiliate_id' => null,
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
}
