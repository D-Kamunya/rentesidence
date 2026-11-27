<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\PropertyService;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * The owner's Vacancy Storefront SHARE HUB: their permanent, self-updating share link + QR for
 * "what vacancies do you have?" (owner-level), plus a per-property link + QR to post on each
 * building. Mirrors the OnboardingCardController QR pattern (SVG — no imagick/gd needed).
 *
 * It also surfaces the photo-upload nudge: vacant units with no photos won't sell, so each is
 * flagged with a one-tap jump to the property to add real pictures.
 */
class VacancyShareController extends Controller
{
    public function __construct(private PropertyService $propertyService)
    {
    }

    public function index()
    {
        $owner = auth()->user();
        $token = $owner->ensureStorefrontToken();

        // Live vacant units across the owner's properties, grouped by property.
        $vacantByProperty = $this->propertyService->getVacantUnitsGroupedForOwner($owner->id);

        $properties = Property::where('owner_user_id', $owner->id)
            ->with(['propertyDetail', 'fileAttachThumbnail'])
            ->orderBy('id')
            ->get();

        $cards = $properties->map(function (Property $property) use ($vacantByProperty) {
            $vacant        = $vacantByProperty->get($property->id) ?? collect();
            $missingPhotos = $vacant->filter(fn ($u) => $u->images->isEmpty());

            // Two photo levels: the BUILDING photo (property thumbnail) vs each UNIT's interior photos.
            $buildingPhoto = $property->thumbnail_image ?? '';
            $hasBuilding   = $buildingPhoto !== '' && ! str_contains($buildingPhoto, 'no-image');

            $url = route('storefront.property', $property->id);

            return (object) [
                'property'            => $property,
                'vacant_count'        => $vacant->count(),
                'missing_photos'      => $missingPhotos->count(),
                'has_building_photo'  => $hasBuilding,
                'url'                 => $url,
                'qr'                  => $this->qr($url),
            ];
        });

        $ownerUrl = $token ? route('storefront.owner', $token) : null;

        return view('owner.storefront.share', [
            'ownerUrl'          => $ownerUrl,
            'ownerQr'           => $ownerUrl ? $this->qr($ownerUrl) : null,
            'tokenMissing'      => $token === null,
            'cards'             => $cards,
            'totalVacant'       => $vacantByProperty->sum(fn ($u) => $u->count()),
            'totalMissingPhoto' => $cards->sum('missing_photos'),
            // Buildings (with a live vacancy) that still have no exterior/building photo.
            'totalMissingBuilding' => $cards->filter(fn ($c) => $c->vacant_count > 0 && ! $c->has_building_photo)->count(),
            'appName'           => getOption('app_name') ?: 'Centresidence',
        ]);
    }

    private function qr(string $url): string
    {
        return QrCode::format('svg')->size(220)->margin(0)->errorCorrection('M')->generate($url);
    }
}
