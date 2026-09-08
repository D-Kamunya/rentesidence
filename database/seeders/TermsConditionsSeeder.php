<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeds a comprehensive DEFAULT owner/agency Terms & Conditions into the `terms_conditions`
 * option so the platform ships with a real legal backstop for the escrow/refund/financing/agency
 * model — editable live via Admin → Settings → Terms & Conditions (summernote), shown on the public
 * /terms-conditions page.
 *
 * ONLY-IF-ABSENT: never overwrites an existing (admin/counsel-edited) value, so a re-deploy can't
 * clobber the reviewed version. To intentionally re-seed, clear the value first.
 *
 * ⚠️ THIS IS A DRAFTING STARTING POINT, NOT LEGAL ADVICE. It must be reviewed and adjusted by
 * qualified legal counsel before it is relied upon — it is a legal instrument in a CBK / Data
 * Protection Act (Kenya) sensitive context (rent flows, payouts, financing, agency mandates).
 * The plain-English "why" for each clause is captured in [[marketplace-domain-pass]] / [[agency-system-design]].
 */
class TermsConditionsSeeder extends Seeder
{
    public function run(): void
    {
        $existing = Setting::where('option_key', 'terms_conditions')->value('option_value');
        if (! empty(trim(strip_tags((string) $existing)))) {
            return; // a real value already exists — do not clobber the reviewed version
        }

        $app   = getOption('app_name') ?: 'Centresidence';
        $email = getOption('app_email') ?: 'info@centresidence.com';

        $body = $this->draft($app, $email);
        Setting::updateOrCreate(['option_key' => 'terms_conditions'], ['option_value' => $body]);
        config(['settings.terms_conditions' => $body]);

        // The current T&C version drives the owner accept-gate (EnsureTermsAccepted). Only-if-absent
        // so an admin who has bumped it (forcing re-acceptance of a revised T&C) is never reset.
        if (! Setting::where('option_key', 'terms_version')->exists()) {
            Setting::updateOrCreate(['option_key' => 'terms_version'], ['option_value' => '1.0']);
            config(['settings.terms_version' => '1.0']);
        }
    }

    /** The default T&C body as newline-free HTML (frontend nl2br-s the stored value). */
    private function draft(string $app, string $email): string
    {
        $h  = fn (string $t) => '<h2>' . e($t) . '</h2>';
        $p  = fn (string $t) => '<p>' . $t . '</p>';
        $li = fn (array $items) => '<ul>' . implode('', array_map(fn ($i) => '<li>' . $i . '</li>', $items)) . '</ul>';

        $blocks = [];

        $blocks[] = $p('<em>Effective date: [to be set on publication]. Version 1.0 (draft — pending legal review).</em>');
        $blocks[] = $p('These Terms &amp; Conditions ("Terms") govern access to and use of the ' . e($app) . ' platform and services (the "Platform") operated by [Company Legal Name], a company registered in the Republic of Kenya [registration no. / address] ("' . e($app) . '", "we", "us", "our"). By creating an account, accepting these Terms at sign-up or first login, or using the Platform, you ("you", the "Account Holder") agree to be bound by them. If you do not agree, do not use the Platform.');

        // 1. Definitions
        $blocks[] = $h('1. Definitions');
        $blocks[] = $li([
            '<strong>Owner / Landlord</strong> — the person or entity that owns a property listed or managed on the Platform and to whom rent is ultimately due.',
            '<strong>Agency</strong> — a licensed property-management company operating one or more Owners\' portfolios on the Platform under a written mandate.',
            '<strong>Tenant</strong> — a person occupying a unit and paying rent or other charges through the Platform.',
            '<strong>Services</strong> — property &amp; tenant management, rent collection &amp; payments, maintenance requests, the in-app marketplace, prepaid utilities &amp; smart metering, property finance, tenant screening, and related features.',
            '<strong>Wallet</strong> — the in-Platform ledger recording amounts attributable to you and available for payout.',
            '<strong>Payment Partner</strong> — the licensed mobile-money, bank or payment-service provider(s) through which funds are collected, held and disbursed.',
        ]);

        // 2. Nature of the Platform / regulatory
        $blocks[] = $h('2. Our role');
        $blocks[] = $p(e($app) . ' provides technology and software that facilitates property management, payments, and related services. Where funds are collected, held, pooled or disbursed on the Platform, this is done through regulated Payment Partners and/or under the applicable licences; ' . e($app) . ' is not a bank and does not take deposits in its own name. Nothing on the Platform constitutes legal, tax, financial or investment advice.');

        // 3. Accounts & eligibility
        $blocks[] = $h('3. Accounts, eligibility &amp; security');
        $blocks[] = $li([
            'You must be at least 18 and legally able to enter contracts. You warrant that information you provide is accurate and kept up to date.',
            'You are responsible for activity under your account and for keeping your credentials secure. Where an account is created on your behalf (for example by an Agency), the account and its login remain personal to you: you must claim it and set your own password, and no third party is entitled to your credentials.',
            'We may require identity, ownership or licensing verification, and may refuse, suspend or close accounts that fail verification or breach these Terms.',
        ]);

        // 4. Fees & deductions
        $blocks[] = $h('4. Fees, commissions &amp; deductions');
        $blocks[] = $p('The following fees may apply. Current rates are shown in-app on the relevant screens and may be updated from time to time (see clause 15). By using a Service you authorise the associated deduction.');
        $blocks[] = $li([
            '<strong>Transaction fee</strong> — a platform fee (e.g. 1% of the rent transaction) on rent collected in transaction mode.',
            '<strong>Subscription fee</strong> — where you are on a subscription plan, the recurring plan fee for the chosen tier.',
            '<strong>Marketplace commission</strong> — a commission on each marketplace sale, at the rate set for your plan tier, deducted from the sale proceeds before payout.',
            '<strong>Property finance charges</strong> — where you take financing, the agreed origination and/or servicing charges, repaid from rent as set out in the facility agreement (clause 8).',
            '<strong>Infrastructure &amp; metering</strong> — a margin/commission embedded in prepaid utility token pricing for metered water, gas or similar utilities (clause 7).',
            '<strong>Credits</strong> — SMS, tenant-screening and e-agreement features are metered by prepaid credits at the in-app price once any included allowance is used.',
        ]);

        // 5. Payments & payouts
        $blocks[] = $h('5. Payments, wallet &amp; payouts');
        $blocks[] = $li([
            'Rent, marketplace and other payments are processed through the Payment Partners. Amounts attributable to you are recorded in your Wallet.',
            'Payouts (for example wallet withdrawals) are made to the mobile-money or bank destination you register, subject to verification, minimums, holds and processing times shown in-app. You are responsible for the accuracy of your payout destination.',
            'We may place reasonable holds on funds to manage refunds, chargebacks, disputes, suspected fraud, or legal/regulatory requirements. Where a payout fails, the amount is returned to your Wallet.',
            'Taxes arising from your income or transactions are your responsibility.',
        ]);

        // 6. Marketplace escrow & refunds
        $blocks[] = $h('6. Marketplace: escrow, delivery &amp; refunds');
        $blocks[] = $li([
            'Payment for a marketplace order is <strong>held in escrow</strong> and is not released to the seller at the moment of payment.',
            'Funds are <strong>released to the seller when the buyer confirms receipt</strong>, or automatically after the return/settlement window closes following delivery. <strong>Tip:</strong> asking your buyer to tap "Confirm receipt" on delivery releases your payout immediately rather than waiting out the window.',
            'A refund requested within the window is paid to the buyer from the held funds, so there is normally no clawback from a seller who has not yet been paid.',
            'Refunds are reviewed and released by us (or an authorised administrator) and paid to the buyer\'s registered mobile-money account. Where a seller has already been paid out and a refund is later approved, the corresponding amount is reversed from the seller\'s Wallet and may be recovered from future earnings.',
            'Disputes are handled first through the Platform\'s dispute process; unresolved disputes are subject to clause 16.',
        ]);

        // 7. Prepaid utilities & metering
        $blocks[] = $h('7. Prepaid utilities &amp; smart metering');
        $blocks[] = $li([
            'Where prepaid utility metering (e.g. water or gas) is enabled, Tenants purchase usage tokens in advance at the applicable tariff.',
            'Tariffs and any embedded margin are disclosed in-app. Prepaid balances are for the metered utility only.',
            'Utility supply must not be withheld or disconnected as a means of enforcing unrelated obligations, and all metering must comply with applicable law and any utility-provider requirements.',
        ]);

        // 8. Property finance
        $blocks[] = $h('8. Property finance');
        $blocks[] = $li([
            'Financing facilities, where offered, are governed by a separate facility agreement setting out the amount, charges, and repayment terms. These Terms do not themselves create a facility.',
            'Repayments may be collected from rent as expressly agreed, which requires your consent to the assignment/allocation of the relevant rent to repayment at source.',
            'Disbursement is made only after approval and any required conditions are met. Only the Owner/Landlord (not an Agency acting alone) may bind a property to a financing obligation.',
        ]);

        // 9. Agency terms
        $blocks[] = $h('9. Agencies &amp; management mandates');
        $blocks[] = $p('Where an Agency manages a property on the Platform, the following apply in addition to the rest of these Terms:');
        $blocks[] = $li([
            '<strong>Mandate.</strong> The Agency operates only under a written, revocable mandate from the Owner. The property and its data belong to the Owner; the Agency holds no ownership by virtue of operating it. On revocation, the mandate ends and the property detaches from the Agency.',
            '<strong>Licensing &amp; compliance.</strong> The Agency warrants it holds all required registrations/licences to manage property and handle client funds, and is responsible for its own regulatory compliance.',
            '<strong>Settlement mode.</strong> At onboarding the Agency selects how rent is settled: (A) the Agency collects rent to <em>its own</em> registered account and remits to Owners itself — in which case the Agency alone is responsible for holding and remitting those funds and for the associated regulatory obligations; or (B) ' . e($app) . ' collects, credits the Agency\'s commission to its Wallet, and remits the Owner\'s share to the Owner\'s <em>own</em> registered destination.',
            '<strong>Owner protection.</strong> In mode (B), the Owner\'s share is paid only to a destination the Owner has set on their own claimed account; an Agency cannot set or change an Owner\'s payout destination. Automated Owner payouts activate once the Owner has claimed their account.',
            '<strong>Commission.</strong> The Agency\'s management commission (typically a percentage of rent per unit) is as agreed with each Owner. ' . e($app) . ' calculates and records the split but is not party to the Owner–Agency commercial arrangement.',
            '<strong>Transparency.</strong> The Owner is entitled to a transparency view of rent collected, commission taken, remittances and property status.',
            '<strong>Referrals.</strong> Where an Agency introduces an Owner to a Service (for example property finance) and the Owner independently takes it up, a referral fee may be payable to the Agency as disclosed in-app; this never overrides the Owner\'s sole right to approve any obligation.',
        ]);

        // 10. Tenant data, screening & creditworthiness
        $blocks[] = $h('10. Tenant data, screening &amp; payment-behaviour');
        $blocks[] = $li([
            'Screening and payment-behaviour features must be used only for legitimate, lawful tenancy purposes and with any consent required by law.',
            'You must not use the data to discriminate unlawfully or for any purpose not permitted by these Terms or applicable law.',
            'Personal data is processed in accordance with clause 13 and the Kenya Data Protection Act, 2019.',
        ]);

        // 11. Owner obligations
        $blocks[] = $h('11. Your obligations &amp; warranties');
        $blocks[] = $li([
            'You warrant you have the legal right to let, manage, sell or list the properties you place on the Platform, and that your use complies with applicable landlord–tenant, consumer, tax and data-protection law.',
            'You are responsible for the accuracy of listings, invoices, charges and tenant records you create, and for your dealings with your Tenants.',
            'You will not use the Platform for unlawful, fraudulent, or abusive purposes, or in a way that harms the Platform or other users.',
        ]);

        // 12. Suspension & termination
        $blocks[] = $h('12. Suspension, termination &amp; account closure');
        $blocks[] = $li([
            'You may stop using the Platform at any time; some obligations (e.g. settlement of amounts owed, refunds, ongoing tenancies) survive.',
            'We may suspend or terminate access for breach of these Terms, non-payment, suspected fraud, or legal/regulatory reasons, giving notice where practicable.',
            'On closure we will settle undisputed balances due to you, subject to any lawful holds, and retain records as required by law.',
        ]);

        // 13. Data protection
        $blocks[] = $h('13. Data protection &amp; privacy');
        $blocks[] = $p('We process personal data in accordance with our Privacy Policy and the Kenya Data Protection Act, 2019. Where you place other people\'s data (e.g. Tenants) on the Platform, you confirm you have the lawful basis to do so and will honour data-subject rights.');

        // 14. Liability
        $blocks[] = $h('14. Disclaimers, liability &amp; indemnity');
        $blocks[] = $li([
            'The Platform is provided "as is". To the extent permitted by law, we exclude implied warranties and are not liable for indirect or consequential loss, or for loss arising from your breach, third-party acts (including Payment Partners), or events beyond our reasonable control.',
            'Nothing excludes liability that cannot lawfully be excluded. Our aggregate liability is limited to the fees you paid us for the Service giving rise to the claim in the 3 months before it arose.',
            'You indemnify us against claims arising from your breach of these Terms or your unlawful use of the Platform.',
        ]);

        // 15. Changes
        $blocks[] = $h('15. Changes to these Terms and to fees');
        $blocks[] = $p('We may update these Terms and the applicable fees. Material changes will be notified in-app or by email, and continued use after the effective date constitutes acceptance. The current version is always available at the Terms &amp; Conditions link.');

        // 16. Disputes & governing law
        $blocks[] = $h('16. Governing law &amp; dispute resolution');
        $blocks[] = $p('These Terms are governed by the laws of the Republic of Kenya. The parties will attempt to resolve disputes amicably; failing which, disputes are subject to [mediation/arbitration in Kenya per the Arbitration Act, 1995 / the exclusive jurisdiction of the Kenyan courts] — to be finalised with counsel.');

        // 17. Contact
        $blocks[] = $h('17. Contact');
        $blocks[] = $p('Questions about these Terms: ' . e($email) . '.');

        return implode('', $blocks);
    }
}
