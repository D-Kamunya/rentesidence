<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\PropertyUnitImage;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

/**
 * Vacancy PHOTO MANAGER — a mobile-first "listing completeness" surface for one property. The
 * owner, standing in the building with their phone, adds the two photo levels that make a vacancy
 * sell: the BUILDING photo (exterior/common, = the property thumbnail that heads the storefront)
 * and per-UNIT interior photos. It's deliberately NOT the heavy stepped property editor — just
 * snap/upload, see thumbnails, delete.
 *
 * Reuses the existing storage rails: the building photo rides owner.property.thumbnailImage.update
 * (FileManager), unit photos get their own thin endpoint here (same security as the stepped form:
 * whitelisted extension AND a sniffed image/* MIME), deletes ride owner.unit.image.delete.
 *
 * This is the first instance of the broader completeness-nudge surface — size/other per-vertical
 * fields (e.g. measured m² for the storage vertical / Verified Listing) can slot in here later.
 */
class VacancyPhotoController extends Controller
{
    use ResponseTrait;

    /**
     * Portfolio-wide photo completeness dashboard — the go-to place for all image work. Every
     * property with its building-photo + unit-photo status, needs-attention first, each linking
     * into the per-property manager.
     */
    public function overview()
    {
        $properties = Property::where('owner_user_id', auth()->id())
            ->with(['fileAttachThumbnail', 'propertyUnits.images', 'propertyUnits.activeTenant'])
            ->orderBy('id')
            ->get();

        $rows = $properties->map(function (Property $p) {
            $units          = $p->propertyUnits;
            $vacant         = $units->filter(fn ($u) => $u->activeTenant === null);
            $unitsNoPhotos  = $units->filter(fn ($u) => $u->images->isEmpty());
            $vacantNoPhotos = $vacant->filter(fn ($u) => $u->images->isEmpty());

            $bp          = $p->thumbnail_image ?? '';
            $hasBuilding = $bp !== '' && ! str_contains($bp, 'no-image');
            $totalPhotos = $units->sum(fn ($u) => $u->images->count()) + ($hasBuilding ? 1 : 0);

            return (object) [
                'property'         => $p,
                'thumb'            => $hasBuilding ? $bp : null,
                'has_building'     => $hasBuilding,
                'units'            => $units->count(),
                'vacant'           => $vacant->count(),
                'units_no_photos'  => $unitsNoPhotos->count(),
                'vacant_no_photos' => $vacantNoPhotos->count(),
                'total_photos'     => $totalPhotos,
                'complete'         => $hasBuilding && $unitsNoPhotos->isEmpty() && $units->count() > 0,
                'url'              => route('owner.property.photos', ['property' => $p->id, 'from' => 'overview']),
            ];
        })->sortBy(fn ($r) => $r->complete ? 1 : 0)->values();

        return view('owner.property.photos-overview', [
            'pageTitle'     => __('Property Photos'),
            'rows'          => $rows,
            'needAttention' => $rows->where('complete', false)->count(),
        ]);
    }

    public function index($propertyId)
    {
        $property = Property::where('owner_user_id', auth()->id())
            ->with(['fileAttachThumbnail', 'propertyDetail'])
            ->findOrFail($propertyId);

        // Units with their photos; flag which are currently vacant (no active tenant) + which lack photos.
        $units = PropertyUnit::where('property_id', $property->id)
            ->with(['images', 'activeTenant'])
            ->orderBy('id')
            ->get()
            ->map(function (PropertyUnit $u) {
                $u->is_vacant     = $u->activeTenant === null;
                $u->needs_photos  = $u->images->isEmpty();
                return $u;
            });

        // Sort so the units that need attention (vacant + no photos) surface first.
        $units = $units->sortByDesc(fn ($u) => ($u->is_vacant ? 2 : 0) + ($u->needs_photos ? 1 : 0))->values();

        $buildingPhoto = $property->thumbnail_image ?? '';
        $hasBuilding   = $buildingPhoto !== '' && ! str_contains($buildingPhoto, 'no-image');

        // Return the owner to WHERE THEY CAME FROM: the Property Photos dashboard, or Share Vacancies.
        $fromOverview = request('from') === 'overview';

        return view('owner.property.photo-manager', [
            'pageTitle'        => __('Add Photos'),
            'property'         => $property,
            'units'            => $units,
            'buildingPhotoUrl' => $hasBuilding ? $buildingPhoto : null,
            'vacantNeedPhotos' => $units->where('is_vacant', true)->where('needs_photos', true)->count(),
            'backRoute'        => $fromOverview ? route('owner.property.photos-overview') : route('owner.vacancy.share'),
            'backLabel'        => $fromOverview ? __('Back to Property Photos') : __('Back to Share Vacancies'),
        ]);
    }

    /**
     * Add one or more photos to a single unit (owner-scoped). Returns the stored images so the page
     * can append thumbnails without a reload.
     */
    public function storeUnitImages(Request $request)
    {
        $request->validate([
            'unit_id'   => 'required|integer',
            'images'    => 'required|array|max:12',
            'images.*'  => 'required|image|max:8192', // 8MB each; image rule = server-side type guard
            'category'  => 'nullable|string|max:30',
        ]);

        $unit = PropertyUnit::with('property')->find($request->unit_id);

        if (! $unit || ! $unit->property || (int) $unit->property->owner_user_id !== (int) auth()->id()) {
            return $this->error([], __('Unit not found.'));
        }

        // Only accept a room slug valid FOR THIS UNIT (its bedroom/bath counts bound the numbered
        // slots, e.g. bedroom_3 is rejected on a 2-bed unit); anything else is treated as untagged.
        $category = $request->input('category');
        if (! array_key_exists((string) $category, unitPhotoCategoriesFor($unit))) {
            $category = null;
        }

        $saved = [];
        foreach ($request->file('images', []) as $image) {
            if (! $image) {
                continue;
            }
            // Defence in depth (mirrors the stepped form): whitelisted extension AND sniffed image/* MIME.
            $extension = strtolower($image->getClientOriginalExtension());
            if (! in_array($extension, imageExtensionList(), true)
                || strpos((string) $image->getMimeType(), 'image/') !== 0) {
                continue;
            }

            $folder = "PropertyUnit/{$unit->id}";
            // Downscale + re-encode before storing (storage hygiene). Never lose the upload: if GD
            // is unavailable, fall back to storing the original.
            $stored = storeResizedImage($image, $folder);
            if (! $stored) {
                $filename = uniqid() . '-' . time() . '.' . $extension;
                $image->storeAs("public/$folder", $filename);
                $stored = ['folder_name' => $folder, 'file_name' => $filename];
            }

            $row = PropertyUnitImage::create([
                'unit_id'     => $unit->id,
                'folder_name' => $stored['folder_name'],
                'file_name'   => $stored['file_name'],
                'category'    => $category,
            ]);

            $saved[] = ['id' => $row->id, 'url' => $row->file_url, 'category' => $category, 'category_label' => $row->category_label];
        }

        if (empty($saved)) {
            return $this->error([], __('No valid image was uploaded. Please use a JPG or PNG photo.'));
        }

        return $this->success(['images' => $saved], __('Photos added.'));
    }
}
