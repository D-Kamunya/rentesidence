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
    public function index()
    {
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
            'message' => 'required|string|max:255'
        ]);
        DB::beginTransaction();
        try {
            $message = new Message();
            $message->first_name = $request->first_name;
            $message->last_name = $request->last_name;
            $message->email = $request->email;
            $message->phone = $request->phone;
            $message->subject = $request->subject;
            $message->message = $request->message;
            $message->save();
            DB::commit();

            // send mail
            if (getOption('send_email_status', 0) == ACTIVE) {
                $emails = [$request->email];
                $subject = __('Thanks for contacting us');
                $title = __('Thank you');
                $message = __('for contacting us, we will reply promptly once your message is received.');
                $mailService = new MailService;
                $mailService->sendContactThankYouMail($emails, $subject, $message, $title);
            }
            return $this->success([], __(SENT_SUCCESSFULLY));
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], __(SOMETHING_WENT_WRONG));
        }
    }
}
