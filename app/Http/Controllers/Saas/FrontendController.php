<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Services\CorePageService;
use App\Services\FaqService;
use App\Services\FeatureService;
use App\Services\HowItWorkService;
use App\Services\PackageService;
use App\Services\SmsMail\MailService;
use App\Services\TestimonialService;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FrontendController extends Controller
{
    use ResponseTrait;
    public function index(Request $request)
    {
        $referral = $request->query('referral');
        $data['pageTitle'] = __('Welcome');
        $featureService = new FeatureService;
        $data['features'] = $featureService->getActiveAll();
        $howItWorkService = new HowItWorkService;
        $data['howItWorks'] = $howItWorkService->getActiveAll();
        $corePageService = new CorePageService;
        $data['corePages'] = $corePageService->getActiveAll();
        $packageService = new PackageService;
        $data['packages'] = $packageService->getActiveAll();
        $testimonialService = new TestimonialService;
        $data['testimonials'] = $testimonialService->getActiveAll();
        $faqService = new FaqService;
        $data['faqs'] = $faqService->getActiveAll();
        return view('saas.frontend.index', $data);
    }

    public function termsConditions()
    {
        $data['pageTitle'] = __("Terms & Conditions");
        $data['description'] = getOption('terms_conditions');
        return view('saas.frontend.policy', $data);
    }

    public function privacyPolicy()
    {
        $data['pageTitle'] = __("Privacy Policy");
        $data['description'] = getOption('privacy_policy');
        return view('saas.frontend.policy', $data);
    }

    public function cookiePolicy()
    {
        $data['pageTitle'] = __("Cookie Policy");
        $data['description'] = getOption('cookie_policy');
        return view('saas.frontend.policy', $data);
    }

    public function contactMessageStore(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'required|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:255',
            'intent' => 'nullable|string|in:general,trial,partner',
        ]);
        DB::beginTransaction();
        try {
            // Intent: distinguishes a genuine trial/signup enquiry from a general contact.
            $intent = in_array($request->intent, ['trial', 'partner', 'general'], true) ? $request->intent : 'general';

            $message = new Message();
            $message->first_name = $request->first_name;
            $message->last_name = $request->last_name;
            $message->email = $request->email;
            $message->phone = $request->phone;
            $message->subject = $request->subject;
            if (\Illuminate\Support\Facades\Schema::hasColumn('messages', 'intent')) {
                $message->intent = $intent;
            }
            $message->message = $request->message;
            $message->save();
            DB::commit();

            // ── Notify the admin so a lead is NEVER missed (esp. a free-trial enquiry). The
            //    in-app bell fires ALWAYS; email additionally when email sending is enabled. ──
            $this->notifyAdminOfContact($request, $intent);

            // Thank-you mail to the sender
            if (getOption('send_email_status', 0) == ACTIVE) {
                $emails = [$request->email];
                $subject = __('Thanks for contacting us');
                $title = __('Thank you');
                $mailBody = __('for contacting us, we will reply promptly once your message is received.');
                $mailService = new MailService;
                $mailService->sendContactThankYouMail($emails, $subject, $mailBody, $title);
            }
            return $this->success([], __(SENT_SUCCESSFULLY));
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], __(SOMETHING_WENT_WRONG));
        }
    }

    /**
     * Alert the admin(s) that a website enquiry arrived — the in-app bell always fires so a
     * lead is never missed (email additionally when email sending is enabled). Trial/signup
     * enquiries are flagged loudly so a hot lead stands out from a general message. Fully
     * guarded — a notification failure must never break the public form submission.
     */
    private function notifyAdminOfContact(Request $request, string $intent): void
    {
        try {
            $isTrial = $intent === 'trial';
            $who     = trim("{$request->first_name} {$request->last_name}") ?: __('A visitor');
            $admins  = \App\Models\User::where('role', USER_ROLE_ADMIN)->get();
            if ($admins->isEmpty()) {
                return;
            }

            $title = $isTrial ? __('New free-trial enquiry') : __('New contact message');
            $body  = $who . ' — ' . \Illuminate\Support\Str::limit($request->subject ?: $request->message, 90);
            $url   = route('admin.message.index');

            foreach ($admins as $admin) {
                addNotification($title, $body, $url, null, $admin->id);
            }

            if (getOption('send_email_status', 0) == ACTIVE) {
                $adminEmails = $admins->pluck('email')->filter()->values()->all();
                if (! empty($adminEmails)) {
                    $subject  = ($isTrial ? __('New free-trial enquiry') : __('New website enquiry'))
                        . ' — ' . ($request->subject ?: __('Website'));
                    $mailBody = __(':who (:email, :phone) sent:', [
                        'who' => $who, 'email' => $request->email, 'phone' => $request->phone,
                    ]) . '<br><br>' . e($request->message);
                    MailService::sendMail($adminEmails, $subject, $mailBody);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Contact admin-notify failed: ' . $e->getMessage());
        }
    }
}
