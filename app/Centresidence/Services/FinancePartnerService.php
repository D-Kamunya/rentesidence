<?php

namespace App\Centresidence\Services;

use App\Centresidence\Models\ApplicationDocumentRequirement;
use App\Centresidence\Models\FinancePartner;
use App\Centresidence\Models\FinancePartnerModule;
use App\Centresidence\Models\UnderwritingRule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Backend for the finance-partner dashboard (handbook §9.2). A finance partner
 * is a real platform user (role USER_ROLE_FINANCE_PARTNER) linked to a
 * finance_partners record, who self-serves the products they offer: which
 * modules they finance, interest rates, repayment tenors, underwriting rules
 * and required documents — all of which an owner then reviews and chooses from.
 *
 * This is the service layer; the Blade portal (routes/controllers/views) is a
 * thin layer on top, added when the partner UI is built.
 */
class FinancePartnerService
{
    /**
     * Provision a finance partner: create the linked login user (role 6) and
     * the finance_partners record.
     *
     * @param  array  $partner  finance_partners attributes (company_name, …)
     * @param  array  $user     {first_name, last_name, email, password}
     */
    public function provision(array $partner, array $user): FinancePartner
    {
        return DB::transaction(function () use ($partner, $user) {
            $account = User::create([
                'first_name' => $user['first_name'] ?? $partner['company_name'] ?? 'Finance',
                'last_name' => $user['last_name'] ?? 'Partner',
                'email' => $user['email'],
                'password' => Hash::make($user['password'] ?? str()->random(16)),
                'role' => USER_ROLE_FINANCE_PARTNER,
                'status' => ACTIVE,
            ]);

            return FinancePartner::create(array_merge([
                'status' => FinancePartner::STATUS_ONBOARDING,
            ], $partner, [
                'user_id' => $account->id,
            ]));
        });
    }

    /** A partner publishes/updates a financing product for a module. */
    public function createProduct(FinancePartner $partner, array $attributes): FinancePartnerModule
    {
        return $partner->products()->create($attributes);
    }

    public function updateProduct(FinancePartnerModule $product, array $attributes): FinancePartnerModule
    {
        $product->update($attributes);

        return $product->refresh();
    }

    /** Add a configurable underwriting rule to a product. */
    public function addUnderwritingRule(FinancePartnerModule $product, array $attributes): UnderwritingRule
    {
        return $product->underwritingRules()->create($attributes);
    }

    /** Declare a document the partner requires for a product. */
    public function addDocumentRequirement(FinancePartnerModule $product, array $attributes): ApplicationDocumentRequirement
    {
        return $product->documentRequirements()->create($attributes);
    }

    /** Products visible to owners for a module, ordered by partner priority. */
    public function marketplaceProductsForModule(int $moduleId)
    {
        return FinancePartnerModule::query()
            ->active()
            ->where('module_id', $moduleId)
            ->with('partner')
            ->orderBy('display_priority')
            ->get()
            ->filter(fn ($product) => optional($product->partner)->status === FinancePartner::STATUS_ACTIVE)
            ->values();
    }
}
