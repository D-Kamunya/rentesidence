<?php

namespace App\Http\Controllers\Affiliates;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Share;

class MarketingToolController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $affiliate = $user->affiliate; // assumes relation user->affiliate
        $referralCode = $affiliate->referral_code ?? null;

        if (!$referralCode) {
            abort(404, 'Referral code not found.');
        }

        // Referral URL
        $referralUrl = route('frontend', ['referral' => $referralCode]). '#contact-us';

        // QR Code URL
        $qrCodeUrl = "https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=" . urlencode($referralUrl);

        // Social Share Links
        $shareButtons = Share::page($referralUrl, 'Join me on OurSite!')
        ->facebook()
        ->twitter()
        ->linkedin()
        ->whatsapp();
        // $shareLinks = [
        //     'facebook' => $shareButtons->facebook,
        //     'twitter' => $shareButtons->twitter,
        //     'linkedin' => $shareButtons->linkedin,
        //     'whatsapp' => $shareButtons->whatsapp,
        // ];

        $qrImage = QrCode::size(200)->generate($referralUrl);

        // Banner Images (You can store in DB, here example from storage)
        if (empty(getOption('app_preloader'))) {
            $banner1Img = asset('assets/images/users/empty-user.jpg');
        } else {
            $banner1Img = getSettingImage('app_preloader');
        }

        if (empty(getOption('sign_in_image'))) {
            $banner2Img = asset('assets/images/users/empty-user.jpg');
        } else {
            $banner2Img = getSettingImage('sign_in_image');
        }

        $banners = [
            [
                'image_url' => $banner1Img,
                'embed_code' => '<a href="' . $referralUrl . '"><img src="' . $banner1Img . '" alt="Join Now"></a>'
            ],
            [
                'image_url' => $banner2Img,
                'embed_code' => '<a href="' . $referralUrl . '"><img src="' . $banner2Img . '" alt="Sign Up Today"></a>'
            ]
        ];

        // Email Template
        $emailTemplate = "Hi there,\n\nI thought you might be interested in this amazing platform.\nJoin using my link: {$referralUrl}\n\nBest regards,\n{$user->name}";

        // WhatsApp Template
        $whatsappTemplate = "Hey! Check this out: {$referralUrl}";

        return view('affiliate.marketing-tools', compact(
            'referralUrl',
            'qrCodeUrl',
            'shareButtons',
            'banners',
            'emailTemplate',
            'whatsappTemplate',
            'qrImage'
        ));
    }
}
