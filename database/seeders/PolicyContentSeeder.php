<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeds a comprehensive DEFAULT Privacy Policy and Cookie Policy into the `privacy_policy` and
 * `cookie_policy` options so the platform ships with real, system-accurate policy pages instead of
 * blank ones — editable live via Admin → Manage Policy (summernote), shown on the public
 * /privacy-policy and /cookie-policy pages. Mirrors TermsConditionsSeeder.
 *
 * AUTHORITATIVE-ONCE: a version sentinel gates each seed, so on the FIRST run we force our baseline
 * even over an existing (blank/stub) value, then lock it — any LATER counsel edit on the live
 * platform sticks. Bump the sentinel (…_v2) to intentionally re-baseline a revised policy.
 *
 * ⚠️ DRAFTING STARTING POINT, NOT LEGAL ADVICE. Kenya's Data Protection Act, 2019 (ODPC) applies
 * to this platform (rent-payment behaviour, KYC documents, M-Pesa transactions, metering telemetry).
 * These drafts must be reviewed by qualified counsel / a data-protection officer before they are
 * relied upon, and the [placeholders] filled in (legal name, registration, DPO/registration details).
 */
class PolicyContentSeeder extends Seeder
{
    public function run(): void
    {
        $app   = getOption('app_name') ?: 'Centresidence';
        $email = getOption('app_email') ?: 'info@centresidence.com';

        if (! getOption('privacy_policy_seed_v1')) {
            setOption('privacy_policy', $this->privacy($app, $email));
            setOption('privacy_policy_seed_v1', '1');
        }

        if (! getOption('cookie_policy_seed_v1')) {
            setOption('cookie_policy', $this->cookie($app, $email));
            setOption('cookie_policy_seed_v1', '1');
        }
    }

    private function h(string $t): string
    {
        return '<h2>' . e($t) . '</h2>';
    }

    private function p(string $t): string
    {
        return '<p>' . $t . '</p>';
    }

    private function li(array $items): string
    {
        return '<ul>' . implode('', array_map(fn ($i) => '<li>' . $i . '</li>', $items)) . '</ul>';
    }

    /** Privacy policy body as newline-free HTML (the frontend nl2br-s the stored value). */
    private function privacy(string $app, string $email): string
    {
        $b = [];

        $b[] = $this->p('<em>Effective date: [to be set on publication]. Version 1.0 (draft — pending legal / data-protection review).</em>');
        $b[] = $this->p('This Privacy Policy explains how the ' . e($app) . ' platform (the "Platform"), operated by [Company Legal Name], a company registered in the Republic of Kenya [registration no. / registered address] ("' . e($app) . '", "we", "us", "our"), collects, uses, shares and protects personal data. It applies to everyone who uses the Platform — property owners and their agents, tenants, affiliates, finance partners, caretakers and administrators. We are a data controller under the Kenya Data Protection Act, 2019, and are committed to handling your data lawfully, fairly and transparently.');

        $b[] = $this->h('1. Information we collect');
        $b[] = $this->p('We collect only the data we need to run the Platform. Depending on your role, this may include:');
        $b[] = $this->li([
            '<strong>Account &amp; identity</strong> — your name, email address, phone number, role, profile photo, and login credentials (passwords are stored only as one-way hashes; we never see your chosen password).',
            '<strong>Property &amp; tenancy</strong> — properties and units, tenancy details, rent amounts, invoices, agreements you sign electronically, and deposits held.',
            '<strong>Payment data</strong> — M-Pesa (and other gateway) transaction references and amounts for rent, deposits, tokens, marketplace orders, subscriptions and payouts. We do <strong>not</strong> store your M-Pesa PIN or full bank details; payments are processed by the payment provider.',
            '<strong>Rent-payment behaviour</strong> — the objective record of how invoices were billed and paid (on-time, late, arrears, tenure), which forms the rental creditworthiness record described in section 5.',
            '<strong>Verification documents (KYC)</strong> — identity and supporting documents a property owner or the Platform requests from a tenant.',
            '<strong>Metering &amp; device telemetry</strong> — where smart utility meters or locks are financed and deployed, prepaid-unit purchases and consumption readings for the relevant unit.',
            '<strong>Communications</strong> — SMS, email and in-app notifications we send you, and messages or support requests you send us.',
            '<strong>Technical data</strong> — IP address, device and browser information, and basic usage needed to operate, secure and troubleshoot the Platform.',
        ]);

        $b[] = $this->h('2. How we use your information');
        $b[] = $this->li([
            'To create and administer your account and provide the Platform to you and (where applicable) to the property owner who manages your tenancy.',
            'To generate and deliver invoices, collect rent and other payments over M-Pesa, and settle payouts and financier remittances.',
            'To operate the rental payment record and creditworthiness score, tenant screening, and financing underwriting (see sections 5 and 6).',
            'To send you transactional and service communications (credentials, invoices, receipts, reminders, notices) by email and SMS.',
            'To detect, investigate and prevent fraud, abuse, security incidents and platform failures, and to keep the service reliable.',
            'To comply with our legal, tax and regulatory obligations, and to establish, exercise or defend legal claims.',
        ]);

        $b[] = $this->h('3. Our legal bases');
        $b[] = $this->p('Under the Data Protection Act, 2019 we rely on: performance of a <strong>contract</strong> with you (running your account and processing your payments); your <strong>consent</strong> where we ask for it (for example certain communications); our <strong>legitimate interests</strong> in operating, securing and improving the Platform, balanced against your rights; and compliance with a <strong>legal obligation</strong>. You may withdraw consent at any time where consent is the basis.');

        $b[] = $this->h('4. Payments and M-Pesa');
        $b[] = $this->p('Payments on the Platform are processed through Safaricom M-Pesa and other payment providers. When you pay, the provider handles your payment credentials directly; we receive a transaction reference and amount to reconcile your invoice, wallet or order. Where rent is collected through the Platform in transaction mode, funds are received into the ' . e($app) . ' collection account, our fees and any at-source financing repayment are applied, and the balance is credited to the owner as a withdrawable wallet balance, paid out on request.');

        $b[] = $this->h('5. Your rental payment record and Tenant ID');
        $b[] = $this->p('The Platform builds a portable, tenant-owned record of <strong>objective rental payment behaviour</strong> (how invoices were actually paid) which can be summarised into a rental creditworthiness score. This record is factual, not opinion. It is transparent to you: you can view your own record and score, you can see when a property owner has looked it up, and you can <strong>dispute</strong> anything you believe is inaccurate. Access by owners is metered and logged. This record supports tenant screening and, in future, tenant-facing financial products — always built from the factual payment history rather than subjective landlord ratings.');

        $b[] = $this->h('6. When we share information');
        $b[] = $this->p('We do <strong>not</strong> sell your personal data. We share it only as needed to run the Platform:');
        $b[] = $this->li([
            '<strong>Your property owner / their caretaker</strong> — a tenant\'s tenancy, invoice, payment and requested-document data is visible to the owner (and any caretaker they authorise) who manages that tenancy, so they can administer it.',
            '<strong>Finance partners</strong> — where an owner applies for financing, the information needed to underwrite that application (including relevant cashflow and occupancy summaries) is shared with the chosen finance partner.',
            '<strong>Affiliates</strong> — limited attribution data (that a referral converted) so referral rewards can be calculated; affiliates do not receive your identity documents or payment credentials.',
            '<strong>Service providers (processors)</strong> — the payment provider (Safaricom M-Pesa), our SMS gateway, our email provider, hosting and, where deployed, the LoRaWAN network operator for metering — each processing data only on our instructions.',
            '<strong>Administrators</strong> — ' . e($app) . ' staff who operate and support the Platform, under confidentiality obligations.',
            '<strong>Legal &amp; safety</strong> — where required by law, regulation, court order, or to protect the rights, safety or property of any person.',
        ]);

        $b[] = $this->h('7. Data retention');
        $b[] = $this->p('We keep personal data only as long as needed for the purposes above or as required by law (for example, financial and tax records). Where a tenancy ends, factual payment history is retained as part of your portable rental record; you may request review or erasure subject to our legal obligations and legitimate retention needs.');

        $b[] = $this->h('8. Your rights');
        $b[] = $this->p('Subject to the Data Protection Act, 2019, you have the right to: be informed about how your data is used; access your data; request correction of inaccurate data; request erasure; object to or restrict certain processing; and, where applicable, data portability. To exercise these rights, contact us at ' . e($email) . '. You also have the right to lodge a complaint with the Office of the Data Protection Commissioner (ODPC) of Kenya.');

        $b[] = $this->h('9. Security');
        $b[] = $this->p('We protect your data with technical and organisational measures — encrypted transport, hashed passwords, access controls, authenticated payment callbacks, and an internal system that detects and alerts us to genuine security and payment failures. No system is perfectly secure, but we work to keep your data safe and to respond promptly to incidents.');

        $b[] = $this->h('10. Cookies');
        $b[] = $this->p('The Platform uses cookies and similar technologies. See our <a href="/cookie-policy">Cookie Policy</a> for details on what we use and how to control them.');

        $b[] = $this->h('11. International transfers');
        $b[] = $this->p('Where any processor stores or processes data outside Kenya, we take steps to ensure your data continues to be protected in line with the Data Protection Act, 2019.');

        $b[] = $this->h('12. Children');
        $b[] = $this->p('The Platform is intended for adults. We do not knowingly collect data from children; if you believe a child has provided us data, contact us and we will address it.');

        $b[] = $this->h('13. Changes to this policy');
        $b[] = $this->p('We may update this Privacy Policy from time to time. Material changes will be notified through the Platform. Continued use after an update constitutes acceptance of the revised policy.');

        $b[] = $this->h('14. Contact us');
        $b[] = $this->p('For any privacy question or to exercise your rights, contact us at ' . e($email) . ' or [Company address / Data Protection Officer contact].');

        return implode('', $b);
    }

    /** Cookie policy body as newline-free HTML. */
    private function cookie(string $app, string $email): string
    {
        $b = [];

        $b[] = $this->p('<em>Effective date: [to be set on publication]. Version 1.0 (draft — pending legal review).</em>');
        $b[] = $this->p('This Cookie Policy explains how the ' . e($app) . ' platform (the "Platform") uses cookies and similar technologies (such as browser storage and a service worker) when you use it. Read it alongside our <a href="/privacy-policy">Privacy Policy</a>.');

        $b[] = $this->h('1. What are cookies and similar technologies?');
        $b[] = $this->p('Cookies are small text files a website stores on your device. "Similar technologies" include <strong>local storage</strong> (small data kept in your browser) and a <strong>service worker</strong> (a script that lets the Platform be installed as an app and show a friendly page when you are offline). Together they let the Platform sign you in, remember your preferences, and work reliably.');

        $b[] = $this->h('2. How we use them');
        $b[] = $this->p('We keep this to the essentials — we do not use cookies to build advertising profiles or sell your data.');
        $b[] = $this->li([
            '<strong>Strictly necessary</strong> — a session cookie to keep you signed in, and a security token (CSRF) to protect forms you submit. The Platform cannot function without these.',
            '<strong>Functional / preferences</strong> — small conveniences remembered on your device, such as a chosen tab, filter or a dismissed prompt, and (as an installable app) a service worker plus browser storage that enable offline support and the "install app" experience.',
            '<strong>Analytics</strong> — where enabled, limited, privacy-respecting usage measurement to help us understand and improve how the Platform is used. [Adjust or remove this line to match the analytics actually deployed.]',
        ]);

        $b[] = $this->h('3. Third-party cookies');
        $b[] = $this->p('Some pages rely on trusted providers that may set their own cookies — for example the payment provider during checkout, or a mapping/embedded component. These are governed by the respective provider\'s own privacy and cookie policies.');

        $b[] = $this->h('4. Managing cookies');
        $b[] = $this->p('You can control or delete cookies through your browser settings, and clear a site\'s stored data at any time. Blocking strictly-necessary cookies will stop you from signing in and using the Platform. Disabling the service worker or clearing site data simply removes offline support and the installed-app conveniences — the Platform still works normally online.');

        $b[] = $this->h('5. Changes to this policy');
        $b[] = $this->p('We may update this Cookie Policy from time to time; material changes will be notified through the Platform.');

        $b[] = $this->h('6. Contact us');
        $b[] = $this->p('Questions about our use of cookies? Contact us at ' . e($email) . '.');

        return implode('', $b);
    }
}
