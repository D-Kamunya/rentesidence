<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * A printable, per-property tenant onboarding card: the property name + a QR code that opens the
 * "get started" on-ramp (install the app / sign in). The owner prints it and pins it on the
 * property's notice board — replacing manual, per-tenant training with a scannable doorway.
 */
class OnboardingCardController extends Controller
{
    public function show($property)
    {
        $property = Property::where('owner_user_id', auth()->id())->findOrFail($property);

        $url = route('get-started');
        // SVG needs no imagick/gd — safe on any host.
        $qr  = QrCode::format('svg')->size(240)->margin(0)->errorCorrection('M')->generate($url);

        return view('owner.property.onboarding-card', [
            'property' => $property,
            'qrSvg'    => $qr,
            'url'      => $url,
            'appName'  => getOption('app_name') ?: 'Centresidence',
        ]);
    }
}
