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
 *
 * Keyed by slug with updateOrCreate, so re-running refreshes the SHIPPED content
 * to the current version of the product — run it on deploy to keep the KB in sync
 * with the code. (After go-live, admin edits in the UI are the source of truth;
 * don't re-run blindly if partners rely on hand-tuned articles.)
 *
 *   php artisan db:seed --class=Database\\Seeders\\KnowledgeBaseSeeder
 */
class KnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        // Articles require an author (kb_articles.created_by FK). Attribute to an
        // admin; bail quietly if the install has no users yet.
        $authorId = User::where('role', USER_ROLE_ADMIN)->value('id') ?? User::value('id');
        if (! $authorId) {
            return;
        }

        $categories = [
            'fp-getting-started' => ['Getting started', 'The partnership, your payout account, and your dashboard.', 'ri-rocket-line', 1],
            'fp-modules'         => ['The modules', 'What you can finance and why owners adopt it.', 'ri-stack-line', 2],
            'fp-products'        => ['Products & terms', 'Publishing offers and every setting explained.', 'ri-price-tag-3-line', 3],
            'fp-applications'    => ['Applications & disbursement', 'Reviewing, approving and releasing funds.', 'ri-file-list-3-line', 4],
            'fp-servicing'       => ['Servicing & getting paid', 'Tracking a facility, repayment and remittances.', 'ri-coins-line', 5],
        ];

        $cat = [];
        foreach ($categories as $slug => [$name, $desc, $icon, $order]) {
            $cat[$slug] = KnowledgeBaseCategory::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'description' => $desc, 'icon' => $icon,
                'audience' => 'finance_partners', 'sort_order' => $order, 'is_active' => true,
            ]);
        }

        foreach ($this->articles() as $i => $a) {
            KnowledgeBaseArticle::updateOrCreate(['slug' => $a['slug']], [
                'kb_category_id' => $cat[$a['category']]->id, 'created_by' => $authorId, 'updated_by' => $authorId,
                'title' => $a['title'], 'type' => 'article', 'audience' => 'finance_partners',
                'status' => 'published', 'excerpt' => $a['excerpt'], 'body' => $a['body'],
                'sort_order' => $i + 1, 'published_at' => Carbon::now(),
            ]);
        }

        // Retire content this rewrite replaced, so re-running always converges to
        // the current set (older slugs split/merged into the new articles above).
        KnowledgeBaseArticle::whereIn('slug', [
            'fp-how-a-facility-works', 'fp-interest-models', 'fp-settlement-and-payouts',
        ])->delete();
        KnowledgeBaseCategory::where('audience', 'finance_partners')
            ->whereIn('slug', ['fp-facilities', 'fp-repayment-settlement'])
            ->delete();
    }

    private function articles(): array
    {
        return [
            // ── Getting started ────────────────────────────────────────────
            [
                'slug' => 'fp-welcome', 'category' => 'fp-getting-started',
                'title' => 'Welcome — how the Centresidence partnership works',
                'excerpt' => 'You fund smart infrastructure for property owners and are repaid from their rent, at source.',
                'body' => '<h3>What you do here</h3><p>Centresidence lets property owners deploy smart infrastructure — water and gas meters, smart locks and more — that turns utilities and access into prepaid, recurring income. As a finance partner, you fund that deployment and are repaid over time, with one key safeguard: <strong>repayment is deducted at source from the owner\'s rent</strong> before it reaches them.</p><h3>Why repayment is secured</h3><p>Owners on financing are moved onto "transaction mode", where tenant rent is collected through the company account. Each cycle your facility\'s repayment is taken from that rent first and settled to you — so you are not chasing repayments after the fact.</p><h3>Your workspace</h3><ul><li><strong>Payout account</strong> — where your money is settled. Set this first; you can\'t publish without it.</li><li><strong>Products</strong> — the financing offers you publish (rate, tenor, limits, policies).</li><li><strong>Applications</strong> — owner requests to finance a module, with soft underwriting against your rules.</li><li><strong>Facilities</strong> — live loans; open one for its full servicing overview.</li><li><strong>Remittances</strong> — the payouts we settle to you, and how to confirm them.</li></ul><p>Read <em>The modules</em> to understand what you\'re financing, set your payout account, then publish a product to start receiving applications.</p>',
            ],
            [
                'slug' => 'fp-payout-account', 'category' => 'fp-getting-started',
                'title' => 'Set your payout account (required before publishing)',
                'excerpt' => 'Where your repayments are settled. It must be a paybill, bank paybill or till — never a phone.',
                'body' => '<h3>Why it\'s required first</h3><p>Everything you earn is settled to this account, so you must set it before you can publish a product. Go to <em>Payout account</em> in the sidebar.</p><h3>Supported destinations</h3><ul><li><strong>M-Pesa Paybill</strong> — your paybill number, plus an account reference if you use one.</li><li><strong>Bank account</strong> — your bank\'s M-Pesa paybill and <em>your</em> account number as the reference. We pay banks by M-Pesa B2B to that paybill; the account number routes it to you.</li><li><strong>M-Pesa Till</strong> — your till number.</li></ul><h3>Why not a phone number?</h3><p>Payments to a phone (B2C) can\'t be tied to an account or reliably reconciled, so they aren\'t supported. A paybill or bank account is always tied to an account, which keeps every payout traceable on both sides.</p><p>Keep these details current — if they\'re wrong, a payout can\'t reach you.</p>',
            ],
            [
                'slug' => 'fp-dashboard-tour', 'category' => 'fp-getting-started',
                'title' => 'Your dashboard at a glance',
                'excerpt' => 'Products, pending applications, active facilities and outstanding principal — plus what needs your attention.',
                'body' => '<h3>The metrics</h3><ul><li><strong>Products</strong> — how many offers you have published.</li><li><strong>Pending applications</strong> — owner requests awaiting your decision.</li><li><strong>Active facilities</strong> — live loans currently being serviced.</li><li><strong>Outstanding principal</strong> — total principal still owed to you across all facilities.</li></ul><h3>Things that need you</h3><p>Watch for amber prompts: a missing payout account, an application waiting for approval, an approved facility still awaiting <em>disbursement</em>, or a settlement/remittance awaiting your confirmation. Anything amber is a cue that a counterparty has acted and it\'s your turn.</p>',
            ],

            // ── The modules ────────────────────────────────────────────────
            [
                'slug' => 'fp-what-the-modules-are', 'category' => 'fp-modules',
                'title' => 'What the modules are (a financier\'s view)',
                'excerpt' => 'Metered modules generate prepaid revenue; non-metered modules lift rent. Both repay from rent at source.',
                'body' => '<h3>Two kinds of module</h3><p><strong>Metered</strong> modules (e.g. smart water and gas meters) generate prepaid token revenue — tenants top up via M-Pesa and consume against it. Demand is recurring and largely non-discretionary, which supports steady repayment.</p><p><strong>Non-metered</strong> modules (e.g. smart locks) don\'t produce token revenue; they lift rent and cut turnover and key costs. Here repayment is supported by the rent uplift rather than usage, so underwrite against the increase the owner can realistically capture.</p><h3>How to read a module</h3><p>Open any module under <em>Modules</em> to see its financier overview, the per-unit deployment cost, current owner demand and active facilities. One device is typically deployed per unit, so the financed amount scales with unit count.</p>',
            ],
            [
                'slug' => 'fp-who-is-paid', 'category' => 'fp-modules',
                'title' => 'Who receives the money, and who owns what',
                'excerpt' => 'You fund the deployment; Centresidence procures and installs; the owner repays you from rent.',
                'body' => '<h3>The payee is Centresidence</h3><p>For infrastructure modules, Centresidence is the official installer. When you disburse a facility, <strong>you send the funds to Centresidence</strong>, which procures and installs the hardware. The owner never receives cash — they receive working infrastructure and repay you from rent.</p><h3>Never pay the owner directly</h3><p>Because we install and coordinate, disbursements always go to Centresidence, never to the owner. Your disbursement screen shows exactly where to send the funds (paybill and the facility reference), so you never need to ask.</p>',
            ],

            // ── Products & terms ───────────────────────────────────────────
            [
                'slug' => 'fp-how-to-set-up-a-product', 'category' => 'fp-products',
                'title' => 'Publishing a financing product',
                'excerpt' => 'Pick a module and set your terms — rate, tenor, limits and rent-deduction cap.',
                'body' => '<h3>Create a product</h3><p>Go to <em>My Products → New product</em> and choose the module you want to finance. (You need a payout account set first.) Then set your core terms:</p><ul><li><strong>Interest rate &amp; type</strong> — reducing-balance, flat or fixed.</li><li><strong>Tenor</strong> — minimum and maximum repayment months.</li><li><strong>Amount limits</strong> — the minimum and maximum you\'ll finance per facility; the owner\'s <em>financed</em> amount must fall within this.</li><li><strong>Max rent-deduction %</strong> — the largest share of a rent payment that may be applied to your repayment. This protects the owner and binds alongside their own consented cap.</li></ul><p>Once published, your product appears to owners financing that module and applications start flowing to your <em>Applications</em> queue.</p>',
            ],
            [
                'slug' => 'fp-product-policies', 'category' => 'fp-products',
                'title' => 'Repayment policies you control',
                'excerpt' => 'Early settlement, its fee, accelerated repayment and your settlement cadence — each is your choice per product.',
                'body' => '<h3>Early repayment allowed</h3><p><em>Yes</em> lets an owner clear the whole facility in one lump sum before term. On reducing-balance products the owner saves the future interest, so you earn less over the life of the loan — set an <strong>early-settlement fee</strong> (a % of outstanding principal, added to the payoff) to offset it. <em>No</em> keeps them on the schedule.</p><h3>Accelerated repayment allowed</h3><p><em>Yes</em> lets an owner voluntarily put more of each rent payment toward the facility (within your rent-deduction cap), clearing it faster. Like early settlement, faster clearing lowers total interest on reducing-balance products — no fee applies. <em>No</em> fixes the pace at the standard monthly amount.</p><h3>Settlement cadence</h3><p><strong>Daily settlement</strong> means you\'re remitted every day; otherwise it\'s monthly on your <strong>settlement day</strong>. This drives when collected repayments are batched and paid to you (see <em>How settlement &amp; remittances work</em>).</p><h3>Grace &amp; default thresholds</h3><p>Set the grace period before a missed period is flagged, and the days-past-due at which a facility is considered in default.</p>',
            ],
            [
                'slug' => 'fp-underwriting-rules', 'category' => 'fp-products',
                'title' => 'Underwriting rules & soft checks',
                'excerpt' => 'Attach requirements that run automatically when an owner applies.',
                'body' => '<h3>Requirements as rules</h3><p>On a product you can require things like a minimum occupancy rate or a minimum months of cashflow history. These run automatically against the owner\'s property when they apply.</p><h3>Hard vs soft</h3><p><strong>Hard rules</strong> block an application before it ever reaches you, so your queue stays clean. <strong>Soft rules</strong> are advisory — the check result is recorded on the application for your judgement, but doesn\'t block it. You always make the final call on approval.</p>',
            ],

            // ── Applications & disbursement ────────────────────────────────
            [
                'slug' => 'fp-reviewing-applications', 'category' => 'fp-applications',
                'title' => 'Reviewing applications & your return',
                'excerpt' => 'What each application shows, including the total you receive and your interest earned.',
                'body' => '<h3>What an application shows</h3><p>Each application shows the property, module, quantity, the total deployment cost, any owner down-payment, the amount <em>you finance</em>, the estimated monthly repayment and the tenor — plus the soft-underwriting result against your rules.</p><h3>Your return</h3><p>A highlighted panel shows the <strong>total you\'ll receive</strong> (estimated monthly × tenor) and your <strong>interest (profit)</strong> — the return over what you finance — so you can decide with the economics in front of you.</p><h3>Approving</h3><p>The approved amount defaults to the financed amount. On approval, the facility and its repayment schedule are created automatically and the owner is notified. You then release the funds from the same page (see the next article).</p><h3>Rejecting</h3><p>Provide a clear reason; the owner sees it and can adjust and reapply.</p>',
            ],
            [
                'slug' => 'fp-disbursement', 'category' => 'fp-applications',
                'title' => 'Disbursement — record and confirm',
                'excerpt' => 'You send the funds to Centresidence yourself, record how, and the payee confirms receipt to release the facility.',
                'body' => '<h3>One place, one flow</h3><p>Disbursement happens on the approved application, right below the decision — no separate trip to Facilities. A newly approved facility sits <strong>awaiting disbursement</strong> and does not yet repay from rent.</p><h3>How it works</h3><ol><li><strong>You send the funds</strong> to Centresidence yourself — by M-Pesa or bank. Your screen shows exactly where: the payee, the amount, the paybill and the facility number to quote as the reference.</li><li><strong>You record it</strong> — choose how you sent it (M-Pesa or bank / manual) and, optionally, a reference. Leave it blank and we auto-number it from the facility.</li><li><strong>The payee confirms receipt</strong>, which releases the facility. Repayment from rent can now begin.</li></ol><h3>Why record-and-confirm</h3><p>You are the payer, so there\'s no automatic pull — you release the money on your side and record it; the payee acknowledges receipt. That keeps both books honest and gives every disbursement a clear audit line in the facility\'s history.</p>',
            ],
            [
                'slug' => 'fp-facility-lifecycle', 'category' => 'fp-applications',
                'title' => 'The facility lifecycle, end to end',
                'excerpt' => 'Application → approval → disbursement → rent-secured repayment → completion.',
                'body' => '<h3>The lifecycle</h3><ol><li><strong>Application</strong> — an owner applies against your product; soft underwriting runs on their property cashflow.</li><li><strong>Approval</strong> — you approve (defaulting to the financed amount) or reject with a reason. A facility and repayment schedule are created automatically.</li><li><strong>Disbursement</strong> — you release funds to Centresidence and record it; the payee confirms. Only now does the facility become repayable. Any owner down-payment is collected at this point.</li><li><strong>Repayment</strong> — each cycle, repayment is deducted at source from the owner\'s rent and settled to you.</li><li><strong>Completion</strong> — the facility closes once fully repaid, or when the owner settles early.</li></ol><h3>The disbursement gate</h3><p>Rent only ever repays a facility that has been <strong>disbursed</strong>. An approved-but-undisbursed facility is never touched — so nothing is collected against money you haven\'t released.</p><h3>Partial financing</h3><p>Owners may put down a contribution and finance only the remainder; your product limits apply to the <em>financed</em> amount, and interest is charged on that portion only.</p>',
            ],

            // ── Servicing & getting paid ───────────────────────────────────
            [
                'slug' => 'fp-facility-overview', 'category' => 'fp-servicing',
                'title' => 'Tracking a facility: the overview page',
                'excerpt' => 'Open any facility to see the principal draw down, the schedule, every collection and your remittances.',
                'body' => '<h3>Watch the loan amortise</h3><p>Click a facility (Facilities → the facility number, or "Overview") to open its servicing detail. It leads with the <strong>outstanding principal</strong> and a progress bar showing how much of the principal has been repaid.</p><h3>What\'s on the page</h3><ul><li><strong>Servicing summary</strong> — collected to date, remitted to you, monthly target, next payment, arrears and disbursement status.</li><li><strong>Repayment schedule</strong> — the full contract amortisation, period by period, with each row colour-coded: green (paid), amber (partial), <strong>red (overdue)</strong>, blue (next due). The outstanding steps down as principal is repaid.</li><li><strong>Collections</strong> — every rent payment that serviced this facility, with the source and reconciliation status.</li><li><strong>Remittances</strong> — the batches that carried this facility\'s repayments back to you.</li></ul><p>The schedule is the contract plan; the actual pace follows real rent collections, which is why the headline outstanding is the true figure to watch.</p>',
            ],
            [
                'slug' => 'fp-repayment-pacing', 'category' => 'fp-servicing',
                'title' => 'Repayment pacing, interest & early settlement',
                'excerpt' => 'Per-cycle target by default; accelerated or early settlement when your product allows it.',
                'body' => '<h3>Interest models</h3><p><strong>Reducing-balance</strong> interest accrues on the outstanding principal over time, so paying down faster saves the owner future interest. <strong>Flat</strong> interest is pre-booked at facility creation and isn\'t reduced by early repayment. You choose per product; the owner sees an estimated monthly repayment before applying.</p><h3>Per-cycle target (default)</h3><p>By default, collection pauses for the cycle once the facility\'s monthly target is met — a strong rent month doesn\'t over-deduct. Repayment resumes next cycle.</p><h3>Accelerated repayment</h3><p>If your product allows it, the owner can opt to apply more of each rent payment (within the deduction cap) until the facility clears.</p><h3>Early settlement</h3><p>If your product allows it, the owner can settle in full early. The payoff is the outstanding principal + interest accrued to date + any outstanding penalty + your configured early-settlement fee. For a bank/manual payoff the settlement stays pending until you confirm receipt.</p>',
            ],
            [
                'slug' => 'fp-settlement-and-remittances', 'category' => 'fp-servicing',
                'title' => 'How settlement & remittances work',
                'excerpt' => 'We collect, deduct and remit your share to your payout account — on your cadence, with full reconciliation.',
                'body' => '<h3>Company-as-intermediary</h3><p>Because tenant rent is a single M-Pesa payment, Centresidence collects it, deducts your facility repayment from the rent portion, and remits your share to your payout account. You don\'t integrate with each owner.</p><h3>Cadence &amp; automation</h3><p>Remittances run on your cadence — <strong>daily</strong> if enabled on any of your products, otherwise <strong>monthly</strong> on your settlement day. A scheduled job prepares each due partner\'s batch and pays it by M-Pesa B2B to your payout account automatically. Bank remittances are marked sent and then you confirm receipt.</p><h3>The remittance lifecycle</h3><p>A batch moves <strong>prepared → sent → confirmed</strong>. On the <em>Remittances</em> page, expand any batch to see the exact repayments inside it — which facility, which rent payment and the amount — and the prepared/sent/confirmed timeline. When a bank remittance is marked sent, click <strong>Confirm receipt</strong> so it\'s reconciled on both sides.</p><h3>Reconciling per facility</h3><p>Each facility\'s overview also lists the remittances that carried its repayments, so you can tie money received back to the specific loan it serviced.</p>',
            ],
        ];
    }
}
