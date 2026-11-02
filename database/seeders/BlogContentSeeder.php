<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Weekly-content backlog, batch 1. Installs evergreen, SEO-oriented articles as DRAFTS
 * (status=draft, published_at=null) so an admin reviews each and PUBLISHES on their own
 * cadence (one a week) — the "batch-then-drip" model. Non-destructive: it never touches
 * existing posts, creates pillar categories only if absent, and seeds each post only if
 * its slug doesn't already exist. Sentinel-gated so a re-deploy won't resurrect a draft
 * the admin deleted. Bump the sentinel (…_v2) to add the next batch.
 *
 *   php artisan db:seed --class=Database\\Seeders\\BlogContentSeeder
 */
class BlogContentSeeder extends Seeder
{
    private const SENTINEL = 'blog_content_batch_v1';

    public function run(): void
    {
        if (getOption(self::SENTINEL)) {
            return;
        }

        $authorId = User::where('role', USER_ROLE_ADMIN)->value('id') ?? User::value('id');
        if (! $authorId) {
            return; // bare install, nothing to attribute to
        }

        // Pillars — topical clusters so weekly posts build authority instead of scattering.
        $pillars = [
            'going-digital'     => ['Going Digital', 'Why modern property management beats paper and spreadsheets.', '#185FA5', 2],
            'rent-and-payments' => ['Rent & Payments', 'Collecting rent the modern way — M-Pesa, receipts, reconciliation.', '#1D9E75', 3],
            'tenants-leasing'   => ['Tenants & Leasing', 'Screening, deposits and a fair landlord–tenant relationship.', '#C77D17', 4],
        ];
        $catId = [];
        foreach ($pillars as $slug => [$name, $desc, $color, $order]) {
            $cat = BlogCategory::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => $desc, 'color' => $color, 'sort_order' => $order, 'is_active' => 1]
            );
            $catId[$slug] = $cat->id;
        }

        foreach ($this->posts($catId) as $p) {
            $slug = Str::slug($p['title']);
            if (BlogPost::where('slug', $slug)->withTrashed()->exists()) {
                continue; // never clobber an existing/edited/published post
            }
            BlogPost::create([
                'blog_category_id' => $p['category_id'],
                'author_id'        => $authorId,
                'title'            => $p['title'],
                'slug'             => $slug,
                'status'           => 'draft',          // admin reviews, then publishes
                'published_at'     => null,
                'body'             => $p['body'],
                'excerpt'          => $p['excerpt'],
                'meta_title'       => $p['meta_title'] ?? $p['title'],
                'meta_description' => $p['meta_description'],
                'meta_keywords'    => $p['meta_keywords'] ?? null,
                'reading_time'     => max(1, (int) round(str_word_count(strip_tags($p['body'])) / 200)),
                'is_featured'      => 0,
            ]);
        }

        setOption(self::SENTINEL, 1);
    }

    private function posts(array $catId): array
    {
        return [
            [
                'category_id'      => $catId['going-digital'],
                'title'            => 'The real cost of running a property on paper',
                'excerpt'          => 'Rent books and spreadsheets feel free — but they quietly cost landlords money, time and peace of mind. Here is where it leaks, and what changes when you go digital.',
                'meta_description' => 'Manual rent books and spreadsheets quietly cost Kenyan landlords money and time. Here is where it leaks — and what changes when property management goes digital.',
                'meta_keywords'    => 'property management Kenya, digital rent collection, rent book, landlord software',
                'body'             =>
                    "<p>A rent book and a WhatsApp group feel free. For one or two units, they almost are. But as a portfolio grows, the paper way starts charging a quiet tax — in money you never collect, hours you never bill, and disputes you can never settle cleanly. Most landlords never add it up. It is worth doing.</p>"
                    . "<h3>Rent that slips through</h3><p>When invoicing lives in your head and follow-up depends on remembering, some rent simply goes uncollected — paid late, paid short, or forgotten on both sides. Even a small slippage each month, across several units, is real money over a year. A system that invoices on schedule and reconciles every payment to the right unit closes that gap without a single reminder from you.</p>"
                    . "<h3>Time you never get back</h3><p>Chasing payments, writing receipts by hand, reconciling M-Pesa messages against a notebook, answering \"did you get my rent?\" — none of it is landlording. It is admin. It scales badly: twice the units, twice the evenings. Digital collection turns that into something that runs on its own and reports back to one screen.</p>"
                    . "<h3>Disputes with no record</h3><p>Paper has no memory. When a tenant insists they paid, or a deposit is contested at move-out, you are arguing from memory against memory. A digital trail — dated invoices, matched payments, issued receipts, documented unit condition — ends most disputes before they start, because the record is simply there.</p>"
                    . "<h3>Data you are throwing away</h3><p>Every rent payment is a signal: who pays on time, which units earn, where arrears cluster. On paper that signal evaporates. Captured, it becomes a portable payment history your good tenants carry with them, and a clear picture of how your own portfolio is performing.</p>"
                    . "<h3>Going digital is not about tech</h3><p>It is about getting paid on time, keeping your evenings, and never arguing from memory again. The tools are the easy part — most owners are set up and collecting the same day. The point is the tax you stop paying. <a href=\"/\">See how it works</a>.</p>",
            ],
            [
                'category_id'      => $catId['rent-and-payments'],
                'title'            => 'How to collect rent over M-Pesa automatically',
                'excerpt'          => 'Cash handovers and follow-up texts do not scale. Here is what rent collection looks like when it is built for phones and M-Pesa — reconciled, receipted, and quiet.',
                'meta_description' => 'A practical guide to automating rent collection over M-Pesa in Kenya — invoices that generate on schedule, payments that reconcile themselves, and instant receipts.',
                'meta_keywords'    => 'collect rent M-Pesa, automatic rent collection Kenya, rent payment reconciliation, landlord M-Pesa',
                'body'             =>
                    "<p>The hardest part of managing property has never been the property. It is the money: who paid, who did not, and the awkward follow-up. Done by hand, it is a monthly grind. Built for M-Pesa, it becomes quiet and automatic.</p>"
                    . "<h3>Invoices that generate themselves</h3><p>You set the rent, the due date and the schedule once. After that, each unit's invoice is raised on time, every cycle, without you lifting a finger. Tenants see what they owe and when — no ambiguity, no \"how much was it again?\"</p>"
                    . "<h3>Payment that reconciles itself</h3><p>Tenants pay from their phone, M-Pesa built in. The payment is matched to the right tenant and the right invoice automatically — no squinting at confirmation messages, no notebook, no manual ticking-off. What is paid, pending and overdue is accurate in real time, across every property.</p>"
                    . "<h3>Receipts, instantly</h3><p>The moment a payment clears, a receipt goes out. That one detail removes a whole category of friction: nobody has to ask whether their rent arrived, and you never have to confirm it by hand.</p>"
                    . "<h3>What the tenant gets</h3><p>More than convenience. Every on-time payment builds a clean record — a portable rental history your tenant can carry to their next home. Paying rent stops being a chore and starts being a reputation, which is exactly the kind of tenant you want to keep.</p>"
                    . "<h3>What you get</h3><p>One dashboard, every unit, in real time — and your evenings back. Automating collection is the single change that removes the most work and the most awkwardness at once. <a href=\"/\">Start collecting this way</a>.</p>",
            ],
            [
                'category_id'      => $catId['tenants-leasing'],
                'title'            => 'How to screen a tenant in Kenya before you lease',
                'excerpt'          => 'A good tenant is cheaper than a bad one is expensive. Here is a fair, practical way to screen before you hand over the keys — and why consent and payment history matter.',
                'meta_description' => 'A fair, practical guide to tenant screening in Kenya — what to check before you lease, why consent matters, and how payment history builds a better signal than a reference call.',
                'meta_keywords'    => 'tenant screening Kenya, how to vet a tenant, rental background check, landlord screening',
                'body'             =>
                    "<p>Most of a landlord's pain traces back to a single decision: who you handed the keys to. Screening is not about suspicion — it is about matching a home to someone who can comfortably keep it, so the tenancy is good for both of you. Done fairly, it protects the tenant too.</p>"
                    . "<h3>What actually predicts a good tenancy</h3><p>Not a gut feeling, and not how someone dresses for a viewing. The signals that matter are affordability (is this rent a sustainable share of their income?), stability, and — most of all — how they have paid before. Past payment behaviour is the single best predictor of future payment behaviour.</p>"
                    . "<h3>Why payment history beats a reference call</h3><p>A reference from a previous landlord can be honest, vague, or quietly self-interested — a landlord keen to move on may not tell you everything. A verified record of on-time payments cannot be coached. As more of the market pays rent digitally, that history becomes something a good tenant carries with them, and something you can trust more than a phone call.</p>"
                    . "<h3>Do it fairly — and with consent</h3><p>Screening should be consistent and consented to, not arbitrary. Ask every applicant for the same information, tell them what you are checking and why, and get their agreement before you look. Fair screening is not only the right thing — it is also what keeps you on the right side of data-protection rules, and what good tenants are happy to go along with.</p>"
                    . "<h3>The upside for honest tenants</h3><p>A tenant who always pays on time has, until recently, had no way to prove it. A portable, verifiable rental record changes that: it rewards good behaviour with easier access to better homes. Screening that leans on real history is fairer than one that leans on assumptions.</p>"
                    . "<p>Screen consistently, ask for consent, and weight payment history heavily. It is the cheapest insurance a landlord can buy. <a href=\"/blog\">More guides for landlords</a>.</p>",
            ],
            [
                'category_id'      => $catId['tenants-leasing'],
                'title'            => 'Deposits done right: holding and refunding them fairly',
                'excerpt'          => 'Deposits cause more move-out disputes than anything else — almost always because of how they were held and documented, not the money itself. Here is how to get it right.',
                'meta_description' => 'How to handle rental deposits fairly in Kenya — treating a deposit as held money (not income), documenting unit condition, and refunding cleanly at move-out without disputes.',
                'meta_keywords'    => 'rental deposit Kenya, security deposit refund, tenant deposit dispute, move-out settlement',
                'body'             =>
                    "<p>Ask any landlord and any tenant about their worst rental experience, and a deposit is often at the centre of it. The money is rarely the real problem. The problem is almost always how it was held and what was — or was not — written down. Get those two things right and the dispute mostly disappears.</p>"
                    . "<h3>A deposit is held money, not income</h3><p>The first mistake is treating a deposit as rent you have already earned. It is not yours; it is the tenant's money, held against genuine damage or unpaid dues, and owed back if neither occurs. Keeping it clearly separate in your records — a liability you are holding, not revenue you have banked — protects you from spending money you may have to return, and signals good faith to the tenant from day one.</p>"
                    . "<h3>Document condition at move-in</h3><p>The second mistake is relying on memory. The time to record a unit's condition is the day the tenant moves in, not the day they move out and you are suddenly in dispute. A dated record — ideally with photos — of the state of the unit at handover is the single most powerful thing you can have. It turns \"it was already like that\" from an argument into a quick comparison.</p>"
                    . "<h3>Settle transparently at move-out</h3><p>At the end of the tenancy, walk the unit against the move-in record, itemise any genuine deductions (with reasons the tenant can see), settle outstanding dues, and refund the balance promptly. Transparency is what defuses the tension: a tenant who can see exactly what was deducted and why rarely disputes a fair figure. Silence and delay are what turn deposits into fights.</p>"
                    . "<h3>Why doing it well pays you back</h3><p>Deposits are returned at the emotional end of a tenancy — the moment a tenant decides whether they would recommend you or warn others. A clean, documented, prompt refund is some of the cheapest reputation you will ever buy. In a market where good tenants talk to each other, that matters more than the deposit itself.</p>"
                    . "<p>Hold it separately, document condition early, settle transparently. Three habits, and the most common dispute in renting mostly goes away. <a href=\"/\">See how the records come together</a>.</p>",
            ],
        ];
    }
}
