<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Weekly-content backlog. Installs evergreen, SEO-oriented articles as DRAFTS
 * (status=draft, published_at=null) so an admin reviews each and PUBLISHES on their own
 * cadence (one a week) — the "batch-then-drip" model.
 *
 * Multi-batch: each batch has its OWN sentinel and runs independently, so adding a later
 * batch never re-runs an earlier one. Non-destructive throughout: pillar categories via
 * firstOrCreate, each post seeded only if its slug doesn't already exist (never clobbers a
 * published/edited post, or a draft the admin deleted). To add the NEXT batch, append a
 * batchN() method + one more seedBatch() call with a fresh sentinel.
 *
 *   php artisan db:seed --class=Database\\Seeders\\BlogContentSeeder
 */
class BlogContentSeeder extends Seeder
{
    public function run(): void
    {
        $authorId = User::where('role', USER_ROLE_ADMIN)->value('id') ?? User::value('id');
        if (! $authorId) {
            return; // bare install, nothing to attribute to
        }

        $catId = $this->pillars();

        $this->seedBatch('blog_content_batch_v1', $this->batch1($catId), $authorId);
        $this->seedBatch('blog_content_batch_v2', $this->batch2($catId), $authorId);
    }

    /** Pillar categories (topical clusters). Idempotent — safe to call every run. */
    private function pillars(): array
    {
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
        return $catId;
    }

    /** Seed one batch once (guarded by its own sentinel); skip any post whose slug exists. */
    private function seedBatch(string $sentinel, array $posts, int $authorId): void
    {
        if (getOption($sentinel)) {
            return;
        }
        foreach ($posts as $p) {
            $slug = Str::slug($p['title']);
            if (BlogPost::where('slug', $slug)->withTrashed()->exists()) {
                continue;
            }
            BlogPost::create([
                'blog_category_id' => $p['category_id'],
                'author_id'        => $authorId,
                'title'            => $p['title'],
                'slug'             => $slug,
                'status'           => 'draft',
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
        setOption($sentinel, 1);
    }

    private function batch1(array $catId): array
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

    private function batch2(array $catId): array
    {
        return [
            [
                'category_id'      => $catId['rent-and-payments'],
                'title'            => 'Late rent is a process problem, not a people problem',
                'excerpt'          => 'Most late rent is not a bad tenant — it is friction and forgetting. Fix the process and the arrears mostly fix themselves. Here is how.',
                'meta_description' => 'Late rent in Kenya is usually a process problem, not a people problem. Here is how automated invoicing, reminders and easy payment cut arrears without chasing tenants.',
                'meta_keywords'    => 'reduce rent arrears, late rent reminders, rent collection Kenya, overdue rent',
                'body'             =>
                    "<p>When rent comes in late, the instinct is to blame the tenant. Sometimes that is fair. Far more often, the real culprit is the process around the payment: no clear reminder, an awkward way to pay, a due date nobody wrote down. Treat late rent as a process problem and most of it quietly disappears — without a single difficult conversation.</p>"
                    . "<h3>Remove the \"I forgot\"</h3><p>People do not plan to pay late; they get busy and the date slips. An invoice that arrives on time, with a gentle reminder before the due date and a clear nudge just after, removes the most common cause of arrears without you having to send a single message yourself. Automation does the chasing you would rather not do.</p>"
                    . "<h3>Remove the friction</h3><p>Every extra step between \"I should pay\" and \"paid\" is a chance to stall. When paying is a tap on the phone — M-Pesa built in, the right amount already attached to the right invoice — there is nothing to put off. Make the easy path the paying path.</p>"
                    . "<h3>See it before it grows</h3><p>Arrears are cheapest to fix early. A live view of who is paid, pending and overdue — across every unit — lets you catch a slipping tenant in week one, not month three. Early, friendly, specific beats late, tense and vague every time.</p>"
                    . "<h3>Keep the relationship intact</h3><p>The quiet benefit of an automated process is that the system does the nagging, not you. The reminders are neutral and consistent; you stay the reasonable landlord. Good tenants appreciate the clarity, and you avoid turning a cash-flow issue into a personal one.</p>"
                    . "<p>Invoice on time, remind automatically, make paying effortless, and watch the numbers. Arrears are usually a design problem — so redesign them away. <a href=\"/\">See how it fits together</a>.</p>",
            ],
            [
                'category_id'      => $catId['going-digital'],
                'title'            => 'Run all your properties from your phone',
                'excerpt'          => 'Managing property should not tie you to a desk or a notebook. Here is what it looks like to run every unit, tenant and payment from the phone already in your pocket.',
                'meta_description' => 'You should be able to run every unit, tenant and rent payment from your phone. Here is what mobile-first property management looks like — one dashboard, anywhere.',
                'meta_keywords'    => 'property management app, manage rentals from phone, landlord app Kenya, mobile rent management',
                'body'             =>
                    "<p>Property does not keep office hours. A tenant moves in on a Saturday, a payment lands at midnight, a question comes while you are upcountry. If managing your units means getting back to a desk and a notebook, you are always a step behind. The fix is simple: carry the whole thing in your pocket.</p>"
                    . "<h3>One dashboard, everywhere</h3><p>Every property, every unit, every tenant and every payment on one screen — the same whether you open it on a laptop or a phone. What is paid, pending and overdue is accurate in real time, so you always know where you stand without digging.</p>"
                    . "<h3>Install it like an app</h3><p>No app store, no download hassle. Add it to your home screen and it behaves like any other app — fast, full-screen, a tap away. Your tenants get the same: their invoices, receipts and payments, installable on their phone too.</p>"
                    . "<h3>Act in the moment</h3><p>Onboard a new tenant where you are standing. Check a payment the second it lands. Answer a query with the record in front of you. The work stops piling up for \"when I get home\", because home is wherever your phone is.</p>"
                    . "<h3>Nothing left in a notebook</h3><p>The quiet win of a phone-first system is that nothing lives only in your head or on paper any more. It is all captured, backed up and searchable — so a lost notebook or a forgotten detail stops being a risk to your income.</p>"
                    . "<p>If you can message on your phone, you can run your properties on it. That is the whole idea. <a href=\"/\">Try it from your phone</a>.</p>",
            ],
            [
                'category_id'      => $catId['tenants-leasing'],
                'title'            => 'Moving a tenant out: a clean, fair exit',
                'excerpt'          => 'Move-out is where goodwill is won or lost. Handle notice, the final invoice, the deposit and the handover well, and both sides leave on good terms — and talking well of you.',
                'meta_description' => 'How to move a tenant out cleanly in Kenya — handling notice, the final invoice, deposit settlement and handover so the tenancy ends fairly and both sides leave on good terms.',
                'meta_keywords'    => 'tenant move out Kenya, final rent invoice, deposit settlement, end of tenancy',
                'body'             =>
                    "<p>Most landlords put all their care into move-in and treat move-out as an afterthought. That is backwards. The end of a tenancy is the moment a tenant decides what to say about you — and a messy exit can undo two good years in a week. A clean, fair move-out is some of the cheapest reputation you will ever earn.</p>"
                    . "<h3>Start with clear notice</h3><p>Both sides should know the notice period and the leaving date without ambiguity. A tenancy that ends on a recorded, agreed date — not a vague \"end of the month\" — removes the first and most common source of friction. Everything else hangs off that date.</p>"
                    . "<h3>Get the final invoice right</h3><p>Pro-rate the last month if it is partial, roll in any outstanding dues, and show the tenant exactly how the final figure is built. A final invoice the tenant can see and understand is rarely disputed; a surprise number almost always is.</p>"
                    . "<h3>Settle the deposit transparently</h3><p>Walk the unit against the move-in record, itemise any genuine deductions with reasons the tenant can see, and refund the balance promptly. Speed matters here as much as fairness — a deposit returned quickly says more about you than almost anything else you do.</p>"
                    . "<h3>Close it cleanly on the record</h3><p>When the unit is handed back, mark the tenancy closed, free the unit for the next tenant, and keep the full history. A clean digital close means the next lease starts from a tidy place, and the departing tenant leaves with their record intact to carry onward.</p>"
                    . "<p>Notice, final invoice, deposit, handover — four steps, done transparently, turn a stressful moment into a good last impression. <a href=\"/blog\">More guides for landlords</a>.</p>",
            ],
            [
                'category_id'      => $catId['going-digital'],
                'title'            => 'What today’s tenants expect from a landlord',
                'excerpt'          => 'Tenants’ expectations have quietly moved — digital payments, instant receipts, quick answers. Meeting them is not luxury; it is how you keep the good ones.',
                'meta_description' => 'Tenant expectations have moved — digital rent payment, instant receipts, transparency and quick responses are now the baseline. Here is what modern tenants look for.',
                'meta_keywords'    => 'tenant expectations, modern landlord, tenant experience Kenya, retain good tenants',
                'body'             =>
                    "<p>The tenant who pays on time, looks after the place and stays for years is the most valuable thing a landlord has — and the easiest to lose to a small, avoidable frustration. What tenants expect has shifted, quietly but firmly. Meeting that new baseline is not about being fancy; it is about not giving your best tenants a reason to leave.</p>"
                    . "<h3>Pay the way they pay everything else</h3><p>Tenants already pay for airtime, shopping and transport from their phone. Rent that still means cash, a bank queue or a reminder text feels like a step backwards. Paying from the phone, M-Pesa included, is now simply expected.</p>"
                    . "<h3>Proof, without asking</h3><p>An instant receipt the moment rent clears removes a small anxiety every single month. Tenants should never have to ask \"did you get it?\" — and should never have to hunt for proof they paid when they need it.</p>"
                    . "<h3>Transparency they can see</h3><p>A clear record of what is due, what is paid and what a deposit is being held against builds trust quietly. Tenants relax when the numbers are visible and consistent; they tense up when everything depends on the landlord's memory.</p>"
                    . "<h3>A quick, human response</h3><p>Technology handles the routine so you have the time and the record to handle the real things — a repair, a question, a change — quickly and well. Meeting expectations on the small stuff buys you goodwill on the big stuff.</p>"
                    . "<p>Meet tenants where they already are, and the good ones stay. Retention is cheaper than re-letting, and it starts with a modern, respectful experience. <a href=\"/\">See what tenants get</a>.</p>",
            ],
        ];
    }
}
