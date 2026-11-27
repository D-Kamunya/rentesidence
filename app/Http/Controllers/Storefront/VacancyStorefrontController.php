<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\User;
use App\Models\VacancyNotifyRequest;
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * The public, shareable VACANCY STOREFRONT — Layer 1 of the vacancy-resolution funnel.
 *
 * It is House Hunt SCOPED, not a rebuild: the same vacancy definition (a unit with no active
 * tenant) and the same apply flow (tenant.house-hunt.application.submit), wrapped in a
 * share/QR surface and given two lenses:
 *   - OWNER   (/vacancies/o/{token}) — every vacancy across one owner's properties.
 *   - PROPERTY (/vacancies/p/{id})   — just one building (what a QR scanner standing there wants).
 *
 * The URL is permanent and the page renders LIVE state, so an owner posts the QR once and it is
 * self-updating: vacancies show (real photos + terms); none → a "notify me" funnel that converts
 * the prospect to a free standalone-tenant account and/or a House Watch area alert.
 */
class VacancyStorefrontController extends Controller
{
    public function __construct(private PropertyService $propertyService)
    {
    }

    /** Owner-scope: all of one owner's vacancies, resolved by their permanent share token. */
    public function owner(string $token)
    {
        abort_unless(Schema::hasColumn('users', 'storefront_token'), 404);

        $owner = User::where('storefront_token', $token)
            ->where('role', USER_ROLE_OWNER)
            ->whereNull('deleted_at')
            ->first();

        abort_if(! $owner, 404);

        $grouped = $this->propertyService->getVacantUnitsGroupedForOwner($owner->id);

        $groups = $grouped->map(fn ($units) => (object) [
            'property' => $units->first()->property,
            'units'    => $units,
        ])->values();

        return view('storefront.show', [
            'scope'          => 'owner',
            'ownerName'      => $this->ownerName($owner),
            'headline'       => __('Homes available from :name', ['name' => $this->ownerName($owner)]),
            'subheadline'    => __('Live vacancies — apply online in a couple of minutes.'),
            'groups'         => $groups,
            'totalUnits'     => $groups->sum(fn ($g) => $g->units->count()),
            'notifyScope'    => [
                'scope_type'          => 'owner',
                'scope_owner_user_id' => $owner->id,
                'scope_property_id'   => null,
            ],
            'areaPrefill'    => [
                'area_city'  => null,
                'area_state' => null,
            ],
        ]);
    }

    /** Property-scope: one building. Does NOT 404 on no vacancies — the QR must stay live. */
    public function property($propertyId)
    {
        $property = Property::with('propertyDetail')->find($propertyId);

        abort_if(! $property, 404);

        $units = $this->propertyService->getVacantUnitsForProperty($property->id);

        $groups = collect();
        if ($units->isNotEmpty()) {
            $groups->push((object) [
                'property' => $property,
                'units'    => $units,
            ]);
        }

        $owner    = User::find($property->owner_user_id);
        $location = $this->propertyLocation($property);

        return view('storefront.show', [
            'scope'          => 'property',
            'ownerName'      => $owner ? $this->ownerName($owner) : __('the owner'),
            'headline'       => $property->name,
            'subheadline'    => $location ?: __('Live vacancies — apply online in a couple of minutes.'),
            'groups'         => $groups,
            'totalUnits'     => $units->count(),
            'notifyScope'    => [
                'scope_type'          => 'property',
                'scope_owner_user_id' => $property->owner_user_id,
                'scope_property_id'   => $property->id,
            ],
            'areaPrefill'    => [
                'area_city'  => optional($property->propertyDetail)->city_id,
                'area_state' => optional($property->propertyDetail)->state_id,
            ],
        ]);
    }

    /**
     * Capture a "notify me" prospect (empty state) or a House Watch area alert. A normal form POST
     * (works without JS) → redirect back to the storefront with a flash that offers the free-tenant
     * signup, prefilled with their email. Degrades to a plain thank-you if the table isn't migrated.
     */
    public function notify(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'nullable|string|max:120',
            'email'               => 'nullable|email|max:150|required_without:contact_number',
            'contact_number'      => 'nullable|string|max:30|required_without:email',
            'area_city'           => 'nullable|string|max:120',
            'area_state'          => 'nullable|string|max:120',
            'scope_type'          => 'nullable|in:owner,property,general',
            'scope_owner_user_id' => 'nullable|integer',
            'scope_property_id'   => 'nullable|integer',
            'return_url'          => 'nullable|string|max:255',
        ], [
            'email.required_without'          => __('Please give us an email or a phone number so we can reach you.'),
            'contact_number.required_without' => __('Please give us an email or a phone number so we can reach you.'),
        ]);

        if (Schema::hasTable('vacancy_notify_requests')) {
            VacancyNotifyRequest::create([
                'name'                => $validated['name'] ?? null,
                'email'               => $validated['email'] ?? null,
                'contact_number'      => $validated['contact_number'] ?? null,
                'scope_type'          => $validated['scope_type'] ?? 'general',
                'scope_owner_user_id' => $validated['scope_owner_user_id'] ?? null,
                'scope_property_id'   => $validated['scope_property_id'] ?? null,
                'area_city'           => $validated['area_city'] ?? null,
                'area_state'          => $validated['area_state'] ?? null,
            ]);
        }

        $back = $this->safeReturnUrl($validated['return_url'] ?? null);

        return redirect($back)
            ->with('storefront_notified', true)
            ->with('notify_email', $validated['email'] ?? null);
    }

    private function ownerName(User $owner): string
    {
        $name = trim(($owner->first_name ?? '') . ' ' . ($owner->last_name ?? ''));
        return $name !== '' ? $name : __('the owner');
    }

    private function propertyLocation(Property $property): ?string
    {
        $detail = $property->propertyDetail;
        if (! $detail) {
            return null;
        }
        $parts = array_filter([
            $detail->city_id ? ucfirst(strtolower($detail->city_id)) : null,
            $detail->state_id ? ucfirst(strtolower($detail->state_id)) : null,
        ]);
        return $parts ? implode(', ', $parts) : null;
    }

    /** Only ever redirect back to one of our own storefront pages (no open redirect). */
    private function safeReturnUrl(?string $url): string
    {
        if ($url) {
            $path = parse_url($url, PHP_URL_PATH) ?: '';
            if (str_starts_with($path, '/vacancies/')) {
                return $path;
            }
        }
        return route('house.hunt');
    }
}
