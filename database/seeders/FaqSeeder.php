<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

/**
 * Marketing-site FAQ content — the practical questions a new owner / tenant / affiliate
 * actually asks, tied to the "go-live-ready" story.
 *
 * ASSERTIVE ONCE, then admin-owned. The existing/live FAQs are all placeholder ("What is a
 * property management app?"), so the FIRST run CLEARS them and installs this real set; a
 * sentinel option then blocks every later run so we never clobber an admin's hand-tuned copy.
 * To intentionally re-seed after editing here, bump the sentinel key. Safe on every deploy.
 *
 *   php artisan db:seed --class=Database\\Seeders\\FaqSeeder
 */
class FaqSeeder extends Seeder
{
    private const SENTINEL = 'starter_faq_seeded_v1';

    public function run(): void
    {
        if (getOption(self::SENTINEL)) {
            return; // already seeded once — admin owns the FAQs now.
        }

        // First seed is assertive: the existing FAQs are placeholder. Clear them (no FK dependents).
        Faq::withTrashed()->forceDelete();

        $faqs = [
            [
                'What does it cost to get started?',
                'Nothing. You can create an account and run the essentials — properties, tenants, rent and receipts — on the free tier at no cost. You only pay when you choose to do more, and the price is always shown up front.',
            ],
            [
                'How do tenants pay their rent?',
                'Straight from their phone, including M-Pesa. Payments are matched to the right invoice automatically and a receipt is sent to the tenant the moment it clears — no manual reconciliation.',
            ],
            [
                'Do I need any technical skills to use Centresidence?',
                'None. If you can use WhatsApp, you can use Centresidence. Setup is guided, and there is a full knowledge base built in if you ever get stuck.',
            ],
            [
                'Can I manage more than one property?',
                'Yes — as many properties and units as you like, all from a single dashboard. Nothing changes as you grow.',
            ],
            [
                'Is my data safe and private?',
                'Yes. Your account is private to you, traffic is encrypted, and your tenants only ever see their own information. We keep the records; you stay in control of them.',
            ],
            [
                'What happens when a tenant moves out?',
                'The move-out is handled end to end: the tenant gives notice, you acknowledge it, a final invoice and the deposit settlement are recorded, and you close the tenancy when the move-out is complete. Both sides keep a clear record of exactly what was refunded.',
            ],
            [
                'How are security deposits handled?',
                'A deposit is recorded as a refundable amount held for the tenant — kept separate from your rent income, never counted as earnings, and returned (less any agreed deductions) at move-out. Centresidence keeps the record so there is no dispute about what was held.',
            ],
            [
                'Can a tenant use Centresidence even if their landlord is not on it?',
                'Yes. A tenant keeps their own account — their rental record, their portable rental score, and home search — with or without their landlord on the platform. It is theirs to carry between homes.',
            ],
            [
                'What is a rental score?',
                'It is a portable record of a tenant\'s payment behaviour, owned by the tenant. Over time it becomes proof they can show any landlord, anywhere, that they are a reliable renter.',
            ],
            [
                'Can I add smart water or gas meters, or other upgrades?',
                'Yes. You can add infrastructure like smart meters and finance it through a partner — repaid from the property\'s own rent — or self-finance and own it outright. You choose; nothing is forced.',
            ],
            [
                'How do affiliates earn?',
                'Affiliates earn a transparent commission across the things they bring to the platform — from subscriptions to the infrastructure and finance lines — all itemised on their earnings ledger, with nothing hidden.',
            ],
            [
                'Is Centresidence ready for me to rely on day to day?',
                'Yes. Rent, receipts, tenants, deposits, move-outs, payments and support are all built and working today. You can run your properties on it from day one and grow into the finance and infrastructure tools when you are ready.',
            ],
        ];

        foreach ($faqs as [$question, $answer]) {
            Faq::create(['question' => $question, 'answer' => $answer, 'status' => ACTIVE]);
        }

        setOption(self::SENTINEL, 1);
    }
}
