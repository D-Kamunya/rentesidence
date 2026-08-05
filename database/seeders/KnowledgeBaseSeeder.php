<?php

namespace Database\Seeders;

use App\Models\KnowledgeBaseArticle;
use App\Models\KnowledgeBaseCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * PRODUCTION-SAFE knowledge-base content for FINANCE PARTNERS, so newly
 * onboarded partners can self-train instead of being walked through manually.
 * Idempotent (firstOrCreate by slug) — safe to run on the live server and
 * never clobbers admin edits.
 *
 *   php artisan db:seed --class=Database\\Seeders\\KnowledgeBaseSeeder
 */
class KnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        // Articles require an author (kb_articles.created_by FK). Attribute to an
        // admin; bail quietly if the install has no users yet (nothing to author for).
        $authorId = User::where('role', USER_ROLE_ADMIN)->value('id') ?? User::value('id');
        if (! $authorId) {
            return;
        }

        $categories = [
            'fp-getting-started'        => ['Getting started', 'The partnership and how to begin.', 'ri-rocket-line', 1],
            'fp-modules'                => ['The modules', 'What you can finance and why owners adopt it.', 'ri-stack-line', 2],
            'fp-facilities'             => ['Facilities & products', 'Publishing products, applications, facilities.', 'ri-funds-line', 3],
            'fp-repayment-settlement'   => ['Repayment & settlement', 'Interest, repayment pacing and how you get paid.', 'ri-coins-line', 4],
        ];

        $cat = [];
        foreach ($categories as $slug => [$name, $desc, $icon, $order]) {
            $cat[$slug] = KnowledgeBaseCategory::firstOrCreate(['slug' => $slug], [
                'name' => $name, 'description' => $desc, 'icon' => $icon,
                'audience' => 'finance_partners', 'sort_order' => $order, 'is_active' => true,
            ]);
        }

        foreach ($this->articles() as $i => $a) {
            KnowledgeBaseArticle::firstOrCreate(['slug' => $a['slug']], [
                'kb_category_id' => $cat[$a['category']]->id, 'created_by' => $authorId, 'updated_by' => $authorId,
                'title' => $a['title'], 'type' => 'article', 'audience' => 'finance_partners',
                'status' => 'published', 'excerpt' => $a['excerpt'], 'body' => $a['body'],
                'sort_order' => $i + 1, 'published_at' => Carbon::now(),
            ]);
        }
    }

    private function articles(): array
    {
        return [
            [
                'slug' => 'fp-welcome', 'category' => 'fp-getting-started',
                'title' => 'Welcome — how the Centresidence partnership works',
                'excerpt' => 'The big picture: you fund smart infrastructure for property owners and are repaid from rent at source.',
                'body' => '<h3>What you do here</h3><p>Centresidence lets property owners deploy smart infrastructure — water and gas meters, smart locks and more — that turns utilities and access into prepaid, recurring income. As a finance partner, you fund that deployment and are repaid over time, with a key safeguard: <strong>repayment is deducted at source from the owner\'s rent</strong> before it reaches them.</p><h3>Why repayment is secured</h3><p>Owners on financing are moved onto "transaction mode", where tenant rent is collected through the company account. Each cycle, your facility\'s repayment is taken from that rent first and settled to you. This lowers default risk compared with chasing repayments after the fact.</p><h3>Your three tools</h3><ul><li><strong>Products</strong> — the financing offers you publish (rate, tenor, limits).</li><li><strong>Applications</strong> — owner requests to finance a module, with soft underwriting against your rules.</li><li><strong>Facilities</strong> — live loans, their schedules and balances.</li></ul><p>Use the <em>Modules</em> section to learn what each module is and why owners adopt it, then publish a product to start receiving applications.</p>',
            ],
            [
                'slug' => 'fp-what-the-modules-are', 'category' => 'fp-modules',
                'title' => 'What the modules are (a financier\'s view)',
                'excerpt' => 'Metered modules generate prepaid revenue; non-metered modules lift rent. Both repay from rent at source.',
                'body' => '<h3>Two kinds of module</h3><p><strong>Metered</strong> modules (e.g. smart water and gas meters) generate prepaid token revenue — tenants top up via M-Pesa and consume against it. Demand is recurring and largely non-discretionary, which supports steady repayment.</p><p><strong>Non-metered</strong> modules (e.g. smart locks) don\'t produce token revenue; they lift rent and cut turnover and key costs. Here, repayment is supported by the rent uplift rather than usage, so underwrite against the increase the owner can realistically capture.</p><h3>How to read a module</h3><p>Open any module under <em>Modules</em> to see its financier overview, the per-unit deployment cost, current owner demand and active facilities. One device is typically deployed per unit, so the financed amount scales with unit count.</p><h3>Who is paid</h3><p>For infrastructure modules, Centresidence is the official installer — the facility is disbursed to Centresidence, which supplies and installs the hardware. The owner never receives cash; they receive working infrastructure and repay you from rent.</p>',
            ],
            [
                'slug' => 'fp-how-to-set-up-a-product', 'category' => 'fp-facilities',
                'title' => 'How to set up a financing product',
                'excerpt' => 'Publish an offer for a module: rate, interest type, tenor, amount limits and max rent-deduction.',
                'body' => '<h3>Create a product</h3><p>Go to <em>My Products → New product</em> and choose the module you want to finance. Then set your terms:</p><ul><li><strong>Interest rate &amp; type</strong> — reducing-balance, flat or fixed.</li><li><strong>Tenor</strong> — minimum and maximum repayment months.</li><li><strong>Amount limits</strong> — the minimum and maximum you will finance per facility (the owner\'s financed amount must fall within this).</li><li><strong>Max rent-deduction %</strong> — the largest share of a rent payment that may be applied to repayment.</li></ul><h3>Underwriting rules</h3><p>Attach rules (e.g. minimum occupancy, minimum cashflow history) that run as soft checks when an owner applies. Hard rules block an application; soft rules are advisory and recorded for your decision.</p><p>Once published, your product appears to owners financing that module, and applications start flowing to your <em>Applications</em> queue.</p>',
            ],
            [
                'slug' => 'fp-how-a-facility-works', 'category' => 'fp-facilities',
                'title' => 'How a facility works, end to end',
                'excerpt' => 'From application to disbursement to rent-secured repayment and completion.',
                'body' => '<h3>The lifecycle</h3><ol><li><strong>Application</strong> — an owner applies against your product; soft underwriting runs on their property cashflow.</li><li><strong>Review</strong> — you approve (defaulting to the financed amount) or reject with a reason.</li><li><strong>Facility &amp; schedule</strong> — on approval, a facility and repayment schedule are created automatically.</li><li><strong>Disbursement</strong> — funds are released to Centresidence, which installs the hardware; any owner down-payment is collected at the same time.</li><li><strong>Repayment</strong> — each cycle, repayment is deducted at source from rent and settled to you.</li><li><strong>Completion</strong> — the facility closes once fully repaid (or is settled early).</li></ol><h3>Partial financing</h3><p>Owners may put down a contribution and finance only the remainder — your product\'s limits apply to the <em>financed</em> amount, and interest is charged on that portion only.</p>',
            ],
            [
                'slug' => 'fp-interest-models', 'category' => 'fp-repayment-settlement',
                'title' => 'Interest models: reducing-balance vs flat',
                'excerpt' => 'Reducing-balance accrues over time and rewards early repayment; flat is pre-booked.',
                'body' => '<h3>Reducing-balance</h3><p>Interest accrues on the outstanding principal as periods mature. If the owner repays early, future (unearned) interest is saved — so the borrower benefits from paying down faster.</p><h3>Flat</h3><p>The full interest is pre-booked at facility creation. Early repayment does not reduce the interest owed.</p><p>You choose the model per product. The owner sees an estimated monthly repayment computed from your rate, type and tenor before they apply, so expectations are set up front.</p>',
            ],
            [
                'slug' => 'fp-repayment-pacing', 'category' => 'fp-repayment-settlement',
                'title' => 'Repayment pacing & early settlement',
                'excerpt' => 'Collection pauses once the monthly target is met; owners can opt into faster repayment or settle early.',
                'body' => '<h3>Per-cycle target (default)</h3><p>By default, collection pauses for the cycle once the facility\'s monthly target has been met — so a strong rent month doesn\'t over-deduct. Repayment resumes next cycle.</p><h3>Accelerated repayment</h3><p>Owners can opt into accelerated repayment, where every qualifying rent payment is applied until the facility clears — useful for paying down faster.</p><h3>Early settlement</h3><p>If your product allows it, an owner can settle the facility early. The payoff is the outstanding principal plus interest accrued to date, any outstanding penalty, and your configured early-settlement fee. With reducing-balance interest, settling early genuinely saves the owner future interest.</p>',
            ],
            [
                'slug' => 'fp-settlement-and-payouts', 'category' => 'fp-repayment-settlement',
                'title' => 'How settlement & payouts work',
                'excerpt' => 'Centresidence collects, deducts and remits your share to your settlement account.',
                'body' => '<h3>Company-as-intermediary</h3><p>Because tenant rent is a single M-Pesa payment, Centresidence collects it, deducts the facility repayment, and remits your share to the settlement account on your partner profile. You don\'t need to integrate with each owner.</p><h3>Cadence</h3><p>Settlements run on a schedule — either daily (if enabled on your profile) or monthly on your settlement day. Each payout is recorded with a reference you can reconcile against.</p><h3>Your settlement account</h3><p>Keep your settlement details current on your profile. M-Pesa B2B (paybill/till, including bank paybills) is supported now; direct bank rails are planned behind the same settlement seam.</p>',
            ],
            [
                'slug' => 'fp-reviewing-applications', 'category' => 'fp-facilities',
                'title' => 'Reviewing applications & underwriting',
                'excerpt' => 'What you see on an application, and how to approve or reject.',
                'body' => '<h3>What an application shows</h3><p>Each application shows the property, module, quantity, the total deployment cost, any owner down-payment, the amount <em>you finance</em>, the estimated monthly repayment and the tenor — plus the result of the soft underwriting run against your rules.</p><h3>Approving</h3><p>The approved amount defaults to the financed amount. On approval, the facility and its repayment schedule are created automatically and the owner is notified.</p><h3>Rejecting</h3><p>Provide a clear reason; the owner sees it and can adjust and reapply. Hard underwriting failures block submission before they reach you, so your queue stays clean.</p>',
            ],
        ];
    }
}
