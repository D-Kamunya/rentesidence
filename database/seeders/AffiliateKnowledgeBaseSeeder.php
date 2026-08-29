<?php

namespace Database\Seeders;

use App\Models\KnowledgeBaseArticle;
use App\Models\KnowledgeBaseCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * PRODUCTION-SAFE knowledge base for AFFILIATES (audience 'affiliates') — quick
 * reference that complements the Academy: what the product does, how to sell it,
 * and how you get paid. Keyed by slug with updateOrCreate so re-running refreshes
 * the shipped content to the current version of the product.
 *
 *   php artisan db:seed --class=Database\\Seeders\\AffiliateKnowledgeBaseSeeder
 */
class AffiliateKnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        $authorId = User::where('role', USER_ROLE_ADMIN)->value('id') ?? User::value('id');
        if (! $authorId) {
            return;
        }

        $categories = [
            'af-getting-started' => ['Getting started', 'The opportunity, how you earn, and the Academy.', 'ri-rocket-line', 1],
            'af-the-product'     => ['What you’re selling', 'Understand Centresidence inside out.', 'ri-building-4-line', 2],
            'af-selling'         => ['Selling & the pipeline', 'Finding owners, demos, objections, closing.', 'ri-user-star-line', 3],
            'af-earnings'        => ['Earnings & your tools', 'Commissions, withdrawals, referrals, integrity.', 'ri-coins-line', 4],
        ];

        $cat = [];
        foreach ($categories as $slug => [$name, $desc, $icon, $order]) {
            $cat[$slug] = KnowledgeBaseCategory::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'description' => $desc, 'icon' => $icon,
                'audience' => 'affiliates', 'sort_order' => $order, 'is_active' => true,
            ]);
        }

        foreach ($this->articles() as $i => $a) {
            KnowledgeBaseArticle::updateOrCreate(['slug' => $a['slug']], [
                'kb_category_id' => $cat[$a['category']]->id, 'created_by' => $authorId, 'updated_by' => $authorId,
                'title' => $a['title'], 'type' => 'article', 'audience' => 'affiliates',
                'status' => 'published', 'excerpt' => $a['excerpt'], 'body' => $a['body'],
                'sort_order' => $i + 1, 'published_at' => Carbon::now(),
            ]);
        }
    }

    private function articles(): array
    {
        return [
            // ── Getting started ────────────────────────────────────────────
            [
                'slug' => 'af-welcome', 'category' => 'af-getting-started',
                'title' => 'Welcome — the opportunity and how you earn',
                'excerpt' => 'You introduce property owners to Centresidence and earn a first-time plus recurring commission on each one.',
                'body' => '<h3>Your role</h3><p>Centresidence is an all-in-one platform for property owners — automatic M-Pesa rent collection, tenant/unit management, invoicing, maintenance, reporting and even infrastructure financing repaid from rent. Your job is to <strong>introduce owners and help them get started</strong>; the platform is built to be plug-and-play, so once they’re in, the system does the heavy lifting.</p><h3>How you earn</h3><ul><li><strong>First-time commission</strong> — when a referred owner converts to a paying subscription.</li><li><strong>Recurring commission</strong> — a share of their ongoing payments for as long as the arrangement runs, so a growing base of active owners means growing passive income.</li></ul><p>Rates are set by Centresidence and shown in your dashboard. Earnings accrue to a balance you withdraw to M-Pesa.</p>',
            ],
            [
                'slug' => 'af-the-academy', 'category' => 'af-getting-started',
                'title' => 'The Academy — why it’s required',
                'excerpt' => 'Pass the training (80% per module) to unlock your affiliate tools. It teaches you the whole product.',
                'body' => '<h3>Complete it first</h3><p>Your tools — Leads, Referrals, Marketing materials, Commissions and Withdrawals — unlock only after you finish the <strong>Academy</strong>. Each module ends with a short quiz; you need <strong>80%</strong> to pass, with up to 3 attempts before a module is flagged for review.</p><h3>Why it matters</h3><p>To sell Centresidence well you have to understand it. The Academy walks you through the entire product and the craft of selling it, so you can talk to any landlord with confidence. Treat it as your playbook — and come back to this Knowledge Base whenever you need a quick refresher.</p>',
            ],

            // ── What you’re selling ────────────────────────────────────────
            [
                'slug' => 'af-product-rent', 'category' => 'af-the-product',
                'title' => 'The core — properties, tenants & rent collection',
                'excerpt' => 'Owners manage buildings and units, onboard tenants, and collect rent automatically over M-Pesa.',
                'body' => '<h3>Properties & units</h3><p>Owners set up buildings as <strong>properties</strong> split into <strong>units</strong>, each with its own rent, deposit and late-fee rules — a clean map of everything they own.</p><h3>Tenants</h3><p>Owners add tenants to units, accept <strong>tenant applications</strong>, and keep full <strong>tenant history</strong>. Each tenant gets a secure login to view invoices, pay, raise issues and read notices.</p><h3>Rent collection — the headline</h3><p>Centresidence auto-generates each rent invoice and the tenant pays via an <strong>M-Pesa prompt on their phone</strong>; the owner sees the payment and receipt <em>instantly</em>. <strong>Recurring rent</strong> regenerates invoices every cycle automatically. The whole <strong>Billing Center</strong> handles deposits, utilities, incidentals and late fees the same easy way. This is the feature that sells itself — most landlords’ biggest pain is chasing rent.</p>',
            ],
            [
                'slug' => 'af-product-toolkit', 'category' => 'af-the-product',
                'title' => 'The full management toolkit',
                'excerpt' => 'Maintenance, tickets, notices, expenses, reports, documents, e-sign agreements, listings, SMS and screening.',
                'body' => '<h3>One system for everything</h3><ul><li><strong>Maintenance & maintainers</strong> — tenants log requests; owners assign a caretaker and track to completion.</li><li><strong>Tickets</strong> — a tenant↔owner support channel.</li><li><strong>Notice Board</strong> — broadcast notices to a unit or whole property.</li><li><strong>Expenses & Reports</strong> — record costs and see earnings, profit/loss, <strong>occupancy</strong>, lease and maintenance figures.</li><li><strong>Documents</strong> — store leases, IDs and files securely.</li><li><strong>Agreements (e-sign)</strong> — send a lease to sign in-app, verified by an SMS one-time code, producing a certified PDF and audit trail.</li><li><strong>Listings</strong> — advertise vacant units to cut void periods.</li><li><strong>SMS credits</strong> — message tenants directly.</li><li><strong>Tenant screening</strong> — check a prospective tenant before letting.</li></ul><p><em>Pitch:</em> replace a drawer of receipts, a noisy WhatsApp group and a rent notebook with one organised system. Show the two or three that hit the owner’s pain.</p>',
            ],
            [
                'slug' => 'af-product-money-engine', 'category' => 'af-the-product',
                'title' => 'The money engine — pricing, marketplace & financing',
                'excerpt' => 'Transaction pricing (pay-as-you-go), a tenant marketplace, and infrastructure financing repaid from rent.',
                'body' => '<h3>Flexible pricing</h3><p>Owners choose a <strong>subscription</strong> or <strong>transaction (pay-as-you-go)</strong> mode, where the platform takes a small percentage of rent collected — no monthly fee. "You only pay when you collect" wins hesitant owners.</p><h3>Tenant marketplace (My Shop)</h3><p>Owners can sell products and services to their own tenants (water, gas, cleaning, and more) via M-Pesa — an extra income stream inside the building.</p><h3>Infrastructure financing — the standout</h3><p>Owners install <strong>smart infrastructure</strong> (prepaid meters, smart locks) financed by a partner and <strong>repaid automatically from rent at source</strong>. Tenants top up utilities by M-Pesa; that usage becomes prepaid, recurring income; and the owner upgrades their property with <em>no upfront cash</em>. When an owner says "I already have a system", these differentiators are your answer.</p>',
            ],

            // ── Selling & the pipeline ─────────────────────────────────────
            [
                'slug' => 'af-finding-leads', 'category' => 'af-selling',
                'title' => 'Finding & qualifying leads',
                'excerpt' => 'Target multi-unit landlords, qualify fast, and register every prospect to protect it for 60 days.',
                'body' => '<h3>Who to target</h3><p>Landlords with several rental units, small agencies, and owners still chasing rent by hand. More units = more value to them and more recurring commission to you.</p><h3>Where to find them</h3><p>Warm referrals, landlord and estate groups, caretakers who know owners, property managers, and simply asking who owns a building. A qualified warm lead beats a big cold list.</p><h3>Qualify fast</h3><p>Learn: how many units? how do they collect rent today? what frustrates them most? That tells you the fit and which feature to lead with.</p><h3>Capture & protect</h3><p>Register every prospect under <strong>Leads</strong> immediately. It organises your pipeline and <strong>protects the lead as yours for 60 days</strong> — an unregistered lead can’t be credited to you. Capture first, sell second.</p>',
            ],
            [
                'slug' => 'af-running-a-demo', 'category' => 'af-selling',
                'title' => 'Running a great demo',
                'excerpt' => 'Ask about their pain, show M-Pesa rent collection, layer 2–3 relevant features, then close to a trial.',
                'body' => '<h3>A winning structure</h3><ol><li><strong>Ask first</strong> — how do they collect rent today, and what’s most painful?</li><li><strong>Show the fix</strong> — invoice → tenant M-Pesa prompt → owner sees payment + receipt instantly. This one flow wins most demos.</li><li><strong>Layer 2–3 relevant features</strong> — tenants/units, maintenance, notices, reports/occupancy or e-sign agreements — only what matches their pain.</li><li><strong>Show a differentiator</strong> — transaction pricing, the marketplace, or rent-repaid infrastructure.</li><li><strong>Close</strong> — offer to start their trial now.</li></ol><p><strong>Do</strong> keep it about them and go slow on the one flow that matters. <strong>Don’t</strong> tour every menu, dwell on price, or over-promise. Update the lead’s stage as you go.</p>',
            ],
            [
                'slug' => 'af-objections', 'category' => 'af-selling',
                'title' => 'Handling common objections',
                'excerpt' => 'Answers to "too expensive", "I have a system", "tenants won’t use M-Pesa", and "let me think".',
                'body' => '<ul><li><strong>"Too expensive."</strong> → Transaction mode: no monthly fee, just a small % of rent you collect; it pays for itself the first time it saves a missed rent.</li><li><strong>"I already have a system / a book."</strong> → Ask what it does when a tenant is late or when they need last year’s numbers. Show automatic M-Pesa collection, occupancy/reports, and the marketplace + rent-repaid infrastructure a notebook can’t match.</li><li><strong>"My tenants won’t use M-Pesa."</strong> → They already pay by M-Pesa; here it’s just a prompt + a receipt, easier than sending manually.</li><li><strong>"I don’t trust money online."</strong> → Rent settles to the owner, every payment has a receipt, access is secure — transparency is the point.</li><li><strong>"Let me think about it."</strong> → Offer a no-risk trial on one property.</li></ul><p><strong>Golden rule:</strong> never invent an answer. If unsure, say you’ll find out and check this Knowledge Base — honesty closes more than bluffing.</p>',
            ],
            [
                'slug' => 'af-pipeline', 'category' => 'af-selling',
                'title' => 'The pipeline: lead → demo → trial → account',
                'excerpt' => 'Move each lead through the four stages, follow up promptly, and work inside the 60-day window.',
                'body' => '<h3>The four stages</h3><ol><li><strong>Lead</strong> — captured.</li><li><strong>Demo</strong> — shown the product.</li><li><strong>Trial</strong> — trying it on their property.</li><li><strong>Account</strong> — they convert to a paying subscription; your <strong>first-time commission</strong> is earned, and recurring commission follows.</li></ol><h3>Work it</h3><p>Update each lead’s stage as it moves so you know where to spend time and are credited correctly. <strong>Follow up promptly</strong> — most deals are lost to silence, not a "no". A check-in after the demo and again after the trial starts wins conversions. Keep momentum inside the <strong>60-day</strong> ownership window.</p>',
            ],

            // ── Earnings & your tools ──────────────────────────────────────
            [
                'slug' => 'af-commissions', 'category' => 'af-earnings',
                'title' => 'Commissions — how you’re paid',
                'excerpt' => 'First-time on conversion, recurring on ongoing payments; both accrue to your withdrawable balance.',
                'body' => '<h3>Two streams</h3><p>Your <strong>first-time commission</strong> is earned when a referred owner converts to a paying subscription. Your <strong>recurring commission</strong> is a share of their ongoing payments for as long as the arrangement runs — this is where a strong affiliate builds compounding income.</p><h3>Your balance</h3><p>All commissions accrue to your balance under <strong>Commissions</strong>, where you can see each event and its status. Rates are configured by Centresidence and shown in your dashboard.</p>',
            ],
            [
                'slug' => 'af-withdrawals', 'category' => 'af-earnings',
                'title' => 'Withdrawing your earnings',
                'excerpt' => 'Request a withdrawal of your balance to M-Pesa; keep your payout details accurate.',
                'body' => '<h3>Withdraw to M-Pesa</h3><p>When you’re ready, request a <strong>withdrawal</strong> of your commission balance; the platform processes it to M-Pesa. Keep your payout details up to date so nothing is delayed, and check the status of each request in your withdrawals view.</p>',
            ],
            [
                'slug' => 'af-referrals-materials', 'category' => 'af-earnings',
                'title' => 'Referral links, materials & the leaderboard',
                'excerpt' => 'Share your referral links, use ready-made marketing materials, and track your rank.',
                'body' => '<h3>Referrals</h3><p>Use your <strong>referral</strong> links/codes so the owners you bring are correctly attributed to you.</p><h3>Marketing materials</h3><p>The <strong>Materials</strong> section has ready-made assets you can send prospects — use them to look professional and save time.</p><h3>Leaderboard</h3><p>Your results rank you on the <strong>leaderboard</strong> — a friendly nudge to keep improving and aim for the top spots.</p>',
            ],
            [
                'slug' => 'af-integrity', 'category' => 'af-earnings',
                'title' => 'Selling with integrity',
                'excerpt' => 'Be honest, respect others’ leads and the 60-day ownership, and never spam or pressure.',
                'body' => '<h3>Your reputation is your biggest asset</h3><p>Always describe the product <strong>honestly</strong> — never promise what it can’t do. Respect other affiliates’ registered leads and the <strong>60-day</strong> ownership window. Never spam or pressure; help owners make a good decision. Misrepresenting the product to close a sale is against the rules and can cost you your account. Do it right and your recurring income compounds month after month.</p>',
            ],
        ];
    }
}
