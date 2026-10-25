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
            // Subject is no longer a form field — the intent dropdown captures it. Kept nullable
            // in case an older client still posts one.
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:255',
            'intent' => 'nullable|string|in:general,trial,partner',
        ]);
        DB::beginTransaction();
        try {
            // Intent: distinguishes a genuine trial/signup enquiry from a general contact.
            $intent = in_array($request->intent, ['trial', 'partner', 'general'], true) ? $request->intent : 'general';

            // Derive the subject from the intent (the dropdown replaced the free-text field), so
            // the admin list + the reply-email subject line stay populated.
            $intentLabels = [
                'general' => __('General enquiry'),
                'trial'   => __('Getting started / free trial'),
                'partner' => __('Partnership or affiliate'),
            ];
            $subject = $request->filled('subject') ? $request->subject : ($intentLabels[$intent] ?? __('General enquiry'));

            $message = new Message();
            $message->first_name = $request->first_name;
            $message->last_name = $request->last_name;
            $message->email = $request->email;
            $message->phone = $request->phone;
            $message->subject = $subject;
            if (\Illuminate\Support\Facades\Schema::hasColumn('messages', 'intent')) {
                $message->intent = $intent;
            }
            $message->message = $request->message;

            // ── Attribution loop: credit the referring affiliate. The ?referral=CODE from their
            //    shareable link was captured (session + cookie) by CaptureAffiliateReferral; resolve
            //    it to an ACTIVE affiliate and stamp it on the enquiry so that when an admin onboards
            //    the owner from this message, the owner is credited to them (owners.affiliate_id). ──
            $referralCode = $request->input('referral')
                ?: $request->session()->get(\App\Http\Middleware\CaptureAffiliateReferral::KEY)
                ?: $request->cookie(\App\Http\Middleware\CaptureAffiliateReferral::KEY);
            $referralAffiliate = null;
            if (is_string($referralCode) && $referralCode !== ''
                && \Illuminate\Support\Facades\Schema::hasColumn('messages', 'referral_affiliate_id')) {
                $referralAffiliate = \App\Models\Affiliate::where('referral_code', $referralCode)
                    ->where('status', ACTIVE)->first();
                if ($referralAffiliate) {
                    $message->referral_code = $referralCode;
                    $message->referral_affiliate_id = $referralAffiliate->id;
                }
            }

            $message->save();
            DB::commit();

            // ── Notify the admin so a lead is NEVER missed (esp. a free-trial enquiry). The
            //    in-app bell fires ALWAYS; email additionally when email sending is enabled. ──
            $this->notifyAdminOfContact($request, $intent);

            // Tell the referring affiliate their link just produced an enquiry (never block the form).
            if ($referralAffiliate) {
                $this->notifyReferringAffiliate($referralAffiliate, $request);
            }

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

            // The bell query INNER-JOINs users on sender_id, so a null sender is created-but-hidden.
            // Use an admin as the system sender so these enquiry alerts actually surface.
            $systemSender = $admins->first()->id;
            foreach ($admins as $admin) {
                addNotification($title, $body, $url, null, $admin->id, $systemSender);
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

    /**
     * Let the referring affiliate know their shareable link just produced an enquiry — a live sign
     * their outreach is landing. Fully guarded (must never break the public form). Sent from an admin
     * (system) so the bell's inner-join on sender_id shows it.
     */
    private function notifyReferringAffiliate(\App\Models\Affiliate $affiliate, Request $request): void
    {
        try {
            if (! $affiliate->user_id) {
                return;
            }
            $who     = trim("{$request->first_name} {$request->last_name}") ?: __('A prospect');
            $sender  = \App\Models\User::where('role', USER_ROLE_ADMIN)->min('id') ?: (int) $affiliate->user_id;

            addNotification(
                __('A prospect you referred just reached out'),
                __(':who enquired through your referral link — we\'ll be in touch as they\'re onboarded.', ['who' => $who]),
                route('affiliate.dashboard'),
                null,
                (int) $affiliate->user_id,
                (int) $sender
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Referring-affiliate notify failed: ' . $e->getMessage());
        }
    }
}
