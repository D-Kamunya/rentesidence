<?php

namespace Database\Seeders;

use App\Models\AcademyModule;
use App\Models\AcademyOption;
use App\Models\AcademyQuestion;
use App\Models\AffiliateAcademyProgress;
use Illuminate\Database\Seeder;

/**
 * The affiliate Academy — the gated training an affiliate must pass (80% per module)
 * before the affiliate tools unlock. This is the DEEPENED, config-aware course: it
 * teaches affiliates to actually sell, demo, onboard and earn — not just the "what/why".
 *
 * CONFIG-AWARE CONTENT: module copy may contain {{tokens}} (e.g. {{first_rate}},
 * {{sms_free_price}}) which are substituted from LIVE settings at render time by
 * AcademyContentService — so a rate change in admin updates every module that quotes it.
 * When editing content in the admin editor, LEAVE the {{tokens}} in place; they auto-fill.
 *
 * AUTHORITATIVE-ONCE (versioned). On an install that hasn't seen the current version, the
 * whole course is rebuilt to this canonical baseline — discarding earlier content, including
 * live admin tweaks from a previous era — then a sentinel locks it so later redeploys never
 * silently clobber new admin edits. Bump the version (…v4, v5, …) to push a fresh baseline.
 * Modules are upserted by TITLE so a matching module keeps its id (and its affiliate progress
 * rows); quizzes are rebuilt (progress references module_id, not questions, so that is safe).
 * Sticky graduation (academy_status on the affiliate) keeps every certified affiliate certified
 * regardless of content changes — no forced re-training.
 *
 * This array was extracted VERBATIM from the reviewed local-DB course (content reworded in the
 * admin editor + correct-answer positions reshuffled so they are not all first). Option array
 * order == display order; keep it.
 *
 *   php artisan db:seed --class=Database\\Seeders\\AffiliateAcademySeeder
 */
class AffiliateAcademySeeder extends Seeder
{
    /** Bump to re-baseline the whole course authoritatively on the next deploy. */
    private const VERSION_SENTINEL = 'affiliate_academy_v6';

    public function run(): void
    {
        if (getOption(self::VERSION_SENTINEL)) {
            return;
        }

        $defs       = $this->modules();
        $keepTitles = array_column($defs, 'title');

        // Prune any module NOT in the canonical course (old names, placeholders) together with
        // its questions, options and progress rows — a clean authoritative rebuild.
        AcademyModule::whereNotIn('title', $keepTitles)->get()->each(function ($m) {
            $qIds = AcademyQuestion::where('module_id', $m->id)->pluck('id');
            AcademyOption::whereIn('question_id', $qIds)->delete();
            AcademyQuestion::where('module_id', $m->id)->delete();
            AffiliateAcademyProgress::where('module_id', $m->id)->delete();
            $m->delete();
        });

        foreach ($defs as $i => $def) {
            $module = AcademyModule::updateOrCreate(
                ['title' => $def['title']],
                [
                    'content'          => $def['content'],
                    'youtube_url'      => $def['youtube_url'] ?? null,
                    'duration_minutes' => $def['duration'] ?? 6,
                    'module_order'     => $i + 1,
                    'is_active'        => true,
                ]
            );

            // Rebuild this module's quiz (safe — progress keys on module_id, not questions).
            $qIds = AcademyQuestion::where('module_id', $module->id)->pluck('id');
            AcademyOption::whereIn('question_id', $qIds)->delete();
            AcademyQuestion::where('module_id', $module->id)->delete();

            foreach ($def['questions'] as $qi => $q) {
                $question = AcademyQuestion::create([
                    'module_id'      => $module->id,
                    'question'       => $q['q'],
                    'question_order' => $qi + 1,
                ]);
                foreach ($q['options'] as $optText => $isCorrect) {
                    AcademyOption::create([
                        'question_id' => $question->id,
                        'option_text' => $optText,
                        'is_correct'  => $isCorrect,
                    ]);
                }
            }
        }

        setOption(self::VERSION_SENTINEL, '1');
    }

    private function modules(): array
    {
        return [
            // Module 1 — Welcome — the opportunity, the model & how you earn
            [
                'title' => 'Welcome — the opportunity, the model & how you earn',
                'duration' => 8,
                'content' => '<h3>Welcome, partner</h3><p>Centresidence is an all-in-one platform for property owners — it collects rent automatically over M-Pesa, manages tenants, units, invoices, maintenance, agreements and more, and can even finance smart infrastructure that is repaid from rent. <strong>Your job is to introduce property owners to Centresidence and help them get started.</strong> The platform does the heavy lifting; your focus is finding owners and getting them live.</p><h3>Our growth model — read this twice</h3><p>Centresidence seeks to grow along with its network of partners and hence chooses affiliate marketing as the main growth engine as opposed to purely buying ad spaces. We grow together with <strong>you</strong>: our global network of partners is the engine. Two approaches sit under everything you will learn:</p><ul><li><strong>Virtual-first selling.</strong> You do not need to travel. You sell over the phone, WhatsApp and video, show a guided demo, and onboard the owner remotely. One partner can reach owners anywhere. (If an owner is near you and a face-to-face visit will close it, go ahead - it increases your closing chances)</li><li><strong>Free-first, the “get them in” model.</strong> We bring owners in on the <strong>Free</strong> plan — zero barrier, nothing to lose — then earn as they <em>use</em> the system, and they upgrade later because paid plans make their usage cheaper. A free owner is still a paying owner to you.</li></ul><h3>How you earn — on everything your owners do</h3><p>You earn two kinds of commission, across <strong>every</strong> line your owners use — their plan, the rent they collect, tenant screening, e-sign agreements, the marketplace and financing:</p><ul><li><strong>First-time commission</strong> — a share ({{first_rate}}) of our take the first time an owner does something that earns.</li><li><strong>Recurring commission</strong> — a smaller share ({{recurring_rate}}) of our take on their ongoing activity, for {{recurring_months}} months. This is how your income compounds: every active owner keeps paying you.</li></ul><p>Because you earn on <em>activity</em>, not just a subscription, <strong>even a Free-plan owner can earn you money</strong> — through tenant screening, lease agreements and marketplace sales (Free owners pay the most on the marketplace, so your cut there is the biggest). And the moment an owner moves onto <strong>Transaction mode</strong>, you also earn {{rent_commission}} of their rent each month — for the first {{recurring_months}} months of that owner. Finish this Academy (80% per module) and your tools unlock: Leads, Referrals, Marketing materials, Commissions and Withdrawals.</p>',
                'questions' => [
                    ['q' => 'What is our main growth model?', 'options' => [
                        'Buying as many online ads as possible' => false,
                        'A network of partners selling virtually (phone/WhatsApp/video)' => true,
                        'Waiting for owners to find the website on their own' => false,
                    ]],
                    ['q' => 'Why does a Free-plan owner still earn you money?', 'options' => [
                        'You are paid a flat fee per sign-up regardless of activity' => false,
                        'They don’t — only paid subscriptions pay commission' => false,
                        'You earn on what they DO — screening, agreements and marketplace sales they pay for — not just a plan' => true,
                    ]],
                    ['q' => 'What are the two kinds of commission?', 'options' => [
                        'A first-time share of our take, plus a smaller recurring share on ongoing activity' => true,
                        'A joining bonus and an exit bonus' => false,
                        'A share of the tenant’s rent and the tenant’s deposit' => false,
                    ]],
                ],
            ],

            // Module 2 — The product I — properties, tenants & automatic rent
            [
                'title' => 'The product I — properties, tenants & automatic rent',
                'duration' => 9,
                'content' => '<h3>Properties &amp; units</h3><p>An owner sets up their buildings as <strong>properties</strong>, each split into <strong>units</strong> (house/apartment/shop). Rent, deposit and late-fee rules are set per unit — a clean map of everything they own in one place.</p><h3>Tenants &amp; onboarding</h3><p>Owners add tenants to units. Prospective tenants can submit <strong>applications</strong>, and owners keep a full <strong>tenant history</strong>. Each tenant gets a secure login to see invoices, pay, raise issues and read notices. It is highly advisable to assist the owner in the set-up process or contact us for assistance through support so we can deliver maximum value to our clients and reduce any chances of onboarding friction.</p><h3>Rent collection — the heart of it</h3><p>This is the feature that sells itself. Centresidence generates each tenant’s <strong>rent invoice automatically</strong>, and the tenant pays with an <strong>M-Pesa prompt on their phone</strong> — the money routes correctly and the owner sees it <em>instantly</em>, with a receipt. <strong>Recurring rent</strong> means invoices regenerate every cycle with no effort from the owner. No chasing, no cash handling, no lost records.<strong> Note:</strong> Centresidence does not handle any owners\' rent money unless the owner is on <strong>transaction payment mode</strong> or has an <strong> active facility (loan)</strong>. This will probably be one of the main worry points for owners out there.</p><h3>Billing Center</h3><p>Beyond rent, owners raise any charge like deposits, water, incidentals, late fees — as invoices from the <strong>Billing Center</strong>, paid the same easy way and all recorded.</p><p><em>Demo tip:</em> almost every landlord’s biggest pain is <strong>chasing rent</strong>. Lead with automatic M-Pesa rent collection and you have their attention.</p>',
                'questions' => [
                    ['q' => 'How do tenants pay rent on Centresidence?', 'options' => [
                        'They must visit a bank branch' => false,
                        'An M-Pesa prompt on their phone; the owner sees it instantly with a receipt' => true,
                        'Only in cash to the caretaker' => false,
                    ]],
                    ['q' => 'What does “recurring rent” mean for the owner?', 'options' => [
                        'Tenants may skip paying' => false,
                        'The rent amount rises every month' => false,
                        'Rent invoices regenerate each cycle automatically' => true,
                    ]],
                    ['q' => 'Which pain should you usually lead with?', 'options' => [
                        'Choosing paint colours' => false,
                        'Chasing rent and poor records' => true,
                        'Too many tenant applications' => false,
                    ]],
                    ['q' => 'When does centresidence handle an owner/landlord\'s rent?', 'options' => [
                        'Everytime - Centresidence always keeps owner money' => false,
                        'Never - Centresidence never handles owner money' => false,
                        'Only if the owner is on transaction payment mode or has an active loan facility' => true,
                    ]],
                ],
            ],

            // Module 3 — The product II — the full management toolkit
            [
                'title' => 'The product II — the full management toolkit',
                'duration' => 10,
                'content' => '<h3>Everything an owner runs from one place</h3><ul><li><strong>Maintenance &amp; maintainers</strong> — tenants log requests; owners assign a caretaker/maintainer and track them to completion.</li><li><strong>Tickets</strong> — a support channel between tenant and owner.</li><li><strong>Notice Board</strong> — broadcast notices (water shut-off, reminders, meetings) to a unit or a whole property.</li><li><strong>Expenses</strong> — record property costs, so profit is real, not guessed.</li><li><strong>Reports</strong> — earnings, profit/loss, expenses, lease, <strong>occupancy</strong> and maintenance — numbers owners never had before.</li><li><strong>Documents</strong> — store leases, IDs and files safely per tenant/property.</li><li><strong>Agreements (e-sign)</strong> — send a lease the tenant signs in-app, verified by an SMS one-time code, producing a certified PDF and audit trail. No printing, no chasing signatures.</li><li><strong>Listings</strong> — advertise vacant units to cut void periods.</li><li><strong>SMS credits</strong> — message tenants directly from the system.</li><li><strong>Reminders</strong> - SMS reminders the system sends before and when invoices are due. A silent "chaser" so the owner doesn\'t have to do it.</li><li><strong>Tenant screening</strong> — check a prospective tenant’s history/creditworthiness before letting.</li></ul><h3>The pitch</h3><p>An owner replaces a drawer of receipts, a WhatsApp full of complaints and a notebook of rent with <strong>one organised system</strong>. In a demo, pick the two or three of these that hit <em>this</em> owner’s pain — never all of them at once.</p>',
                'questions' => [
                    ['q' => 'How are lease agreements signed?', 'options' => [
                        'They can’t be signed digitally' => false,
                        'Only on printed paper posted by mail' => false,
                        'In-app, verified by an SMS one-time code, producing a certified PDF + audit trail' => true,
                    ]],
                    ['q' => 'What does tenant screening let an owner do?', 'options' => [
                        'Check a prospective tenant’s history/creditworthiness before letting' => true,
                        'Evict a tenant instantly' => false,
                        'Set the tenant’s salary' => false,
                    ]],
                    ['q' => 'What’s the right way to use the toolkit in a demo?', 'options' => [
                        'Walk through every menu in order' => false,
                        'Show the 2–3 features that match this owner’s pain' => true,
                        'Focus on the colour scheme' => false,
                    ]],
                ],
            ],

            // Module 4 — The product III — the money engine
            [
                'title' => 'The product III — the money engine',
                'duration' => 9,
                'content' => '<h3>What sets Centresidence apart</h3><p>Modules 2 and 3 cover what every good system does. This is what competitors likely <em>don’t</em> have — reach for it when an owner says “I already have a system”.</p><h3>The tenant marketplace (My Shop)</h3><p>Owners can sell <strong>products and services to their own tenants</strong> — water, gas, cleaning, anything — through an in-app shop, paid by M-Pesa. It turns a building into a small marketplace and an extra income stream for the owner. (You earn a commission on these sales too.)</p><h3>Infrastructure financing — the standout</h3><p>An owner can install <strong>smart infrastructure</strong> (prepaid water/gas meters, smart locks) <strong>financed by a partner and repaid automatically from rent</strong>. Tenants top up utilities by M-Pesa; that usage becomes prepaid, recurring income; and the financing is deducted at source from rent — so the owner upgrades their property with <strong>no upfront cash</strong>. Utilities stop being a cost and become an income line. (You’ll go deeper on finance products later.)</p><h3>Why this matters to you</h3><p>The money engine — the marketplace and rent-repaid infrastructure is your differentiator. It is also more activity your owners run through the platform, which means more commission lines for you.</p>',
                'questions' => [
                    ['q' => 'How is infrastructure financing repaid?', 'options' => [
                        'It is never repaid' => false,
                        'The owner pays the full cost upfront in cash' => false,
                        'Automatically from rent, deducted at source — no upfront cash from the owner' => true,
                    ]],
                    ['q' => 'What is the tenant marketplace (My Shop)?', 'options' => [
                        'A place tenants buy other houses' => false,
                        'Owners sell products/services to their tenants via M-Pesa — an extra income stream' => true,
                        'A stock-trading platform' => false,
                    ]],
                    ['q' => 'Why does the money engine matter to you as a partner?', 'options' => [
                        'It’s the differentiator AND more owner activity = more commission lines for you' => true,
                        'It has nothing to do with your earnings' => false,
                        'It replaces the need to find owners' => false,
                    ]],
                ],
            ],

            // Module 5 — Plans & pricing — Free, Trial, Transaction & Subscription
            [
                'title' => 'Plans & pricing — Free, Trial, Transaction & Subscription',
                'duration' => 11,
                'content' => '<h3>Four ways an owner can be on Centresidence</h3><p>You must know these well since pricing is where deals are won or lost. The whole model is simple: <strong>get the owner in on Free, let them use it, and the savings pull them up to a paid plan.</strong></p><h4>1. Free — the way in</h4><p>Permanent, no subscription fee, manages up to {{free_cap}} units, and <strong>every feature works</strong>. The catch is not features — it is <strong>usage price</strong>. On Free the owner pays <strong>full freight on usage</strong>: SMS costs <strong>{{sms_free_price}}</strong> per message, and the platform’s marketplace commission is at its highest. That is deliberate: Free is how we earn from owners who don’t subscribe, and it is the reason to upgrade. <em>Email reminders are free</em>, so the owner is never stuck.</p><h4>2. Trial — the taste</h4><p>A time-boxed trial you request for a warm owner. It grants usage <strong>free</strong> ({{trial_sms_grant}} free SMS, up to {{trial_cap}} units) so they feel the full system at <strong>no cost</strong>. When it ends, the account <strong>lands softly on Free</strong> — it never locks them out. Use the trial as a <strong>closing tool</strong>: the clock gives you a reason to follow up and convert.</p><h4>3. Transaction (pay-as-you-go)</h4><p><strong>No monthly fee</strong> — the platform simply takes a small percentage ({{platform_rent_fee}}) of rent collected. Perfect for the fee-averse owner: “you only pay when you collect.” Transaction mode also unlocks <strong>financing</strong>.</p><h4>4. Subscription</h4><p>A flat monthly price per unit band (grow your portfolio without growing the bill). Paid plans <strong>reduce usage costs</strong>: SMS drops to about <strong>{{sms_paid_price}}</strong> (vs {{sms_free_price}} on Free) and the marketplace commission is lower. For an owner with volume, upgrading <em>saves money</em>.</p><h3>Why owners upgrade — your upgrade story</h3><p>You don’t “sell a subscription”. You get them collecting rent on Free, and then show the maths: “You’re sending X SMS a month at {{sms_free_price}} each — on a plan they’re {{sms_paid_price}}. The plan pays for itself.” The system’s Upgrade Advisor nudges this too. Cheaper usage is the pull; you just point at it.</p>',
                'questions' => [
                    ['q' => 'What is the real difference between Free and a paid plan?', 'options' => [
                        'Free can only manage one unit' => false,
                        'Free is missing most features; paid unlocks them' => false,
                        'Features are the same; Free pays full price on usage (e.g. SMS & Credits), paid plans make usage cheaper' => true,
                    ]],
                    ['q' => 'What happens when a trial ends?', 'options' => [
                        'The owner is logged out permanently' => false,
                        'The account lands softly on the Free plan — it is never locked out' => true,
                        'All their data is deleted' => false,
                    ]],
                    ['q' => 'How should you pitch an upgrade?', 'options' => [
                        'Insist everyone must subscribe on day one' => false,
                        'Tell them Free will stop working soon' => false,
                        'Show that cheaper usage on a paid plan (e.g. lower SMS cost) saves them money' => true,
                    ]],
                    ['q' => 'Which mode has no monthly fee but takes a small % of rent collected?', 'options' => [
                        'Subscription' => false,
                        'Transaction (pay-as-you-go)' => true,
                        'Trial' => false,
                    ]],
                ],
            ],

            // Module 6 — Your earnings, in detail
            [
                'title' => 'Your earnings, in detail',
                'duration' => 9,
                'content' => '<h3>Exactly how your commission is worked out</h3><p>You earn a share of <strong>our</strong> take on your owners’ activity — so it’s always sustainable and rewards you well for the work put you in. The live rates are on your dashboard, and the <strong>earnings calculator on the Academy home page</strong> works them out for you. Here is what each line pays:</p><ul><li><strong>Rent collected — Transaction mode</strong> — when an owner is on <strong>Transaction mode</strong>, you earn a flat <strong>{{rent_commission}}</strong> of all the rent they collect, <em>every month for the first {{recurring_months}} months</em>. This is your bread and butter — getting active owners/landlords/agencies onto Transaction is the single best thing you can do for your income. (Free and subscription owners don’t carry this line — only Transaction.)</li><li><strong>Subscriptions</strong> — when an owner is on a paid plan you earn <strong>{{first_rate}}</strong> of the first payment, then <strong>{{recurring_rate}}</strong> for {{recurring_months}} months.</li><li><strong>Marketplace sales</strong> — you earn up to <strong>{{marketplace_cut}}</strong> of our commission on what owners sell to their tenants — on <em>any</em> plan, and our cut is highest on Free, so your share there is also the biggest.</li><li><strong>Screening, agreements &amp; financing</strong> — the same first-time-then-recurring share of our take, on <em>any</em> plan, each time an owner uses them.</li></ul><p><strong>How long each line pays:</strong> commission on an owner runs for <strong>{{recurring_months}} months</strong> from their first payment on that line — it is not indefinite. That is exactly why bringing on a steady flow of new owners is what grows your income.</p><h3>The big idea</h3><p>Your income is built from <em>activity</em> across many owners. A handful of busy <strong>Transaction-mode</strong> owners — each paying you {{rent_commission}} of their rent every month for their first {{recurring_months}} months — can out-earn a single subscription. And no owner is ever dead weight: even a <strong>Free</strong> owner earns you through the screening, agreements and marketplace sales they pay for. So you never neglect an owner. You instead guide them onto <strong>Transaction</strong> (to earn on their rent) and help every owner use the paid features. For owners on free, help them make use of the marketplace features so you can also earn as you show them incremental cash flow opportunities.</p><p><strong>Try it now:</strong> open the Academy home page and move the numbers in the earnings calculator to see your projected income. All the figures are pre-configured to our exact commission metrics, so what you see is what you’ll earn.</p>',
                'questions' => [
                    ['q' => 'You earn rent commission (a flat share of rent collected) on which owners?', 'options' => [
                        'Only owners on a subscription plan' => false,
                        'Every owner, including Free' => false,
                        'Owners on Transaction mode' => true,
                    ]],
                    ['q' => 'Your commission is calculated as…', 'options' => [
                        'A share of OUR take on the owner’s activity — never more than we make' => true,
                        'A fixed amount set by the owner' => false,
                        'A percentage of the tenant’s deposit' => false,
                    ]],
                    ['q' => 'Why can a few busy Transaction-mode owners out-earn a single subscriber?', 'options' => [
                        'Transaction owners pay you a fixed monthly salary' => false,
                        'You earn a share of their rent each month during the commission window' => true,
                        'They don’t — only subscriptions ever pay' => false,
                    ]],
                ],
            ],

            // Module 7 — Inside the system — setting an owner up & running the flows
            [
                'title' => 'Inside the system — setting an owner up & running the flows',
                'duration' => 12,
                'content' => '<h3>Know the owner’s journey by heart</h3><p>To demo well and onboard remotely, you must be able to walk an owner through the core flows yourself. Here is the path, end to end. (The <strong>Knowledge Base</strong> in your tools has step-by-step detail and screenshots for each — keep it open while you demo.)</p><h4>1. Set up the property</h4><p>From the owner dashboard: <strong>Properties → Add Property</strong>, then add <strong>Units</strong> under it, setting rent and deposit per unit. This is the foundation — nothing else works until a unit exists.</p><h4>2. Add a tenant</h4><p>Open a unit and <strong>add the tenant</strong> (name, phone, rent start). The system creates the tenant a <strong>secure login</strong> and sends their credentials — so the tenant can pay and self-serve from day one. This tenant-onboarding step is what turns a static list into a live, paying building.</p><h4>3. Generate the first invoice &amp; collect</h4><p>Rent invoices generate automatically each cycle; you can also raise one from the <strong>Billing Center</strong>. The tenant gets an invoice SMS with an instant pay link that allows payment without needing them to login. The pay now button in the instant pay form sends an <strong>M-Pesa prompt</strong> , the payment is made and the owner sees the money land <em>with a receipt</em>. Showing this one loop — invoice out, prompt, paid, receipt — closes most demos.</p><h4>4. Show where everything lives</h4><p>Point out <strong>Reports</strong> (occupancy, profit/loss), <strong>Maintenance</strong>, <strong>Notices</strong>, <strong>Agreements</strong> and <strong>SMS credits</strong> so the owner sees the system is one home for everything.</p><h3>Onboarding an owner today (concierge)</h3><p>You bring an owner on by <strong>registering them as a Lead</strong>, then <strong>requesting a Trial</strong> for them (next modules). Their account is created for them with a temporary password sent to <em>their own</em> phone/email, which they reset on first login. Do this <em>with the owner on the call</em> — help them log in and set up their first property and tenant before you hang up. An owner who collects their first rent during the call almost never churns.</p>',
                'questions' => [
                    ['q' => 'What must exist before anything else works for an owner?', 'options' => [
                        'A paid subscription' => false,
                        'A printed lease' => false,
                        'A property with at least one unit (rent/deposit set)' => true,
                    ]],
                    ['q' => 'What happens when a tenant is added to a unit?', 'options' => [
                        'The system creates them a secure login and sends their credentials so they can pay/self-serve' => true,
                        'Nothing until the owner upgrades' => false,
                        'The tenant is charged a joining fee' => false,
                    ]],
                    ['q' => 'What is the best outcome of a concierge onboarding call?', 'options' => [
                        'The owner is left to figure it out alone later' => false,
                        'The owner logs in, sets up a property/tenant, and collects their first rent during the call' => true,
                        'You set a hard deadline to pay or lose access' => false,
                    ]],
                    ['q' => 'Where do you find step-by-step detail while demoing?', 'options' => [
                        'The tenant’s app' => false,
                        'You must memorise everything; there is no reference' => false,
                        'The Knowledge Base in your tools' => true,
                    ]],
                ],
            ],

            // Module 8 — Finding, qualifying & owning your leads
            [
                'title' => 'Finding, qualifying & owning your leads',
                'duration' => 9,
                'content' => '<h3>Who to target</h3><p>Your best prospects feel the pain the product solves: <strong>landlords with several units</strong>, agencies, and owners still chasing rent by hand or on paper or those using legacy systems. More units = more value to them and more recurring commission to you.</p><h3>Where to find them</h3><p>Warm contacts first; local landlord and estate groups (incl. WhatsApp); caretakers who know owners; property managers; and simply asking “who owns this building?”. If applicable, you could even start with where you are currently renting since you already have an existing relationship with your landlord. A warm, qualified lead beats a big cold list every time.</p><h3>Qualify quickly</h3><p>In the first chat learn: <strong>how many units? how do they collect rent today? what frustrates them most?</strong> That tells you if they fit and which feature to lead with.</p><h3>Lead temperature</h3><p>As you work a lead, mark how warm it is so you spend time where it counts:</p><ul><li><strong>Hot</strong> — interested, ready for a demo or trial soon. Act now.</li><li><strong>Warm</strong> — curious, needs nurturing. Follow up on a schedule.</li><li><strong>Cold</strong> — not now. Park it and revisit later.</li></ul><h3>Capture first — and the lead is YOURS</h3><p>Register every prospect under <strong>Leads</strong> the moment you speak to them. A lead you personally add is <strong>yours</strong>. It carries an activity timer, but even if that timer lapses, <strong>the lead stays attributed to you — you can simply renew it and keep working it.</strong> Your own leads remain yours. An unregistered lead, by contrast, can’t be paid to you — so capture first, sell second.</p>',
                'questions' => [
                    ['q' => 'What should you learn when qualifying a lead?', 'options' => [
                        'Their favourite football team' => false,
                        'How many units, how they collect rent now, and their biggest frustration' => true,
                        'Nothing — just sign them up immediately' => false,
                    ]],
                    ['q' => 'What happens to a lead YOU personally added if its timer lapses?', 'options' => [
                        'It is deleted and the history is lost' => false,
                        'It is given to another affiliate immediately' => false,
                        'It stays yours — you can renew it and keep working it' => true,
                    ]],
                    ['q' => 'Why mark a lead’s temperature (hot/warm/cold)?', 'options' => [
                        'To spend your time where it counts — act on hot, nurture warm, park cold' => true,
                        'It is required before you can log in' => false,
                        'It changes the commission rate' => false,
                    ]],
                    ['q' => 'Which is an example of a very simple way to sign in your first owner/user?', 'options' => [
                        'Talking to your friends' => false,
                        'Starting where you currently rent since you already have a relationship with your landlord' => true,
                        'Physically visiting residential buildings' => false,
                    ]],
                ],
            ],

            // Module 9 — The lead system in depth — notes, scheduling & trials
            [
                'title' => 'The lead system in depth — notes, scheduling & trials',
                'duration' => 9,
                'content' => '<h3>Work the lead, not just the list</h3><p>Your <strong>Leads</strong> area is your whole pipeline. Used well, it does half your selling for you.</p><h4>Log everything as a note</h4><p>After every call or message, add a <strong>note/activity</strong> to the lead: what was said, objections raised, what you promised, when to follow up. This is your memory — you’ll work dozens of owners and will not recall the detail. It also builds a history that travels with the lead.</p><h4>Schedule the demo</h4><p>When a lead is warm, <strong>schedule the demo</strong> and set a reminder. A booked time converts far better than “I’ll call you sometime”.</p><h4>Request a trial</h4><p>When an owner is ready to try it on their own property, <strong>request a Trial</strong> from the lead. This starts their account (they get credentials on their own phone/email) and moves the lead forward. If a trial needs more time, you can <strong>request an extension</strong> — a stalled trial is a cue to reach out, not to give up.</p><h4>Follow the suggestions</h4><p>The system raises <strong>suggested actions</strong> on your leads (follow up, a lead going cold, a demo to book). Treat these as your daily to-do list — they’re there to stop good leads slipping.</p><h3>Trial vs Free — which to offer</h3><p>Both are free to the owner. Default owners onto <strong>Free</strong> (zero barrier, permanent). Use a <strong>Trial</strong> when an owner is warm and you want them to <em>feel</em> the full system with free usage and a gentle nudge to help them convert. Either way they end up on Free if they don’t upgrade — so you never lose them.</p>',
                'questions' => [
                    ['q' => 'What should you do after every call with a lead?', 'options' => [
                        'Nothing; rely on memory' => false,
                        'Log a note/activity — what was said, next steps, when to follow up' => true,
                        'Immediately mark them converted' => false,
                    ]],
                    ['q' => 'What are the system’s “suggested actions” for?', 'options' => [
                        'Setting the commission rate' => false,
                        'Adverts for other products' => false,
                        'A daily to-do list so good leads don’t slip (follow-ups, cooling leads, demos to book)' => true,
                    ]],
                    ['q' => 'When is a Trial (rather than plain Free) the right offer?', 'options' => [
                        'For every single prospect on first contact' => false,
                        'For a warm owner you want to feel the full system, with a clock to convert against' => true,
                        'Never — trials are not useful' => false,
                    ]],
                ],
            ],

            // Module 10 — The Lead Marketplace — claim ready leads
            [
                'title' => 'The Lead Marketplace — claim ready leads',
                'duration' => 7,
                'content' => '<h3>Extra pipeline, on top of your own</h3><p>You’ll find many owners yourself — but you’re not limited to your contacts. Centresidence also gives you a shared <strong>Lead Marketplace</strong>: a pool of prospective owners (people who enquired through the website, and leads the company sources) that <strong>any affiliate can claim and work</strong>.</p><h3>How it works</h3><ol><li>Open <strong>Leads → Lead Marketplace</strong>. Browse the unclaimed leads; filter by location, property type or how warm they are.</li><li><strong>Claim</strong> a lead you can actively work. The moment you claim it, it’s <em>yours</em> — it moves into your pipeline and no other affiliate can take it.</li><li>Work it like any lead: reach out, qualify, demo, convert. If that owner signs up, the commission is <strong>yours</strong>, first-time and recurring.</li></ol><h3>The 60-day window — important</h3><p>A claimed marketplace lead is protected to you, but <strong>unlike your own leads, it is not yours forever</strong>. If you don’t convert it within <strong>60 days</strong>, it <strong>returns to the marketplace</strong> for another affiliate to pick up — with its history preserved, so whoever works it next sees what’s already been done. So claim marketplace leads only when you can follow up soon, and move them along. You can still claim your marketplace leads back if they expire and are returned to the marketplace - however, it is not a guarantee that you will always find them there. Marketplace leads are always on demand so the best approach is to keep working a lead to closure.</p><h3>Use both</h3><p>The strongest partners combine <strong>their own warm leads</strong> (highest conversion, and yours to keep) with the <strong>marketplace</strong> (extra volume when your own pipeline is thin). Register your own prospects to keep them; top up from the marketplace when you have capacity.</p>',
                'questions' => [
                    ['q' => 'What happens to a MARKETPLACE lead you don’t convert within 60 days?', 'options' => [
                        'It returns to the marketplace for another affiliate — with its history preserved' => true,
                        'It stays yours forever like your own leads' => false,
                        'It is deleted completely' => false,
                    ]],
                    ['q' => 'What happens the moment you claim a marketplace lead?', 'options' => [
                        'You are charged a fee' => false,
                        'Nothing changes; anyone can still take it' => false,
                        'It becomes yours and others can no longer take it' => true,
                    ]],
                    ['q' => 'What’s the smart way to use the marketplace?', 'options' => [
                        'Claim as many as possible and deal with them later' => false,
                        'Claim only what you can follow up soon, and combine it with your own warm leads' => true,
                        'Use it only; never find your own leads' => false,
                    ]],
                ],
            ],

            // Module 11 — The virtual-sales playbook & where to start
            [
                'title' => 'The virtual-sales playbook & where to start',
                'duration' => 10,
                'content' => '<h3>Our winning motion — virtual-first</h3><p>You do not need to travel to succeed. Our go-to model is <strong>virtual selling</strong>: reach an owner by phone, WhatsApp or video; send or screen-share the <strong>guided demo</strong>; onboard them remotely; and follow up from wherever you are. This is how one partner reaches owners across a town, a county, or the whole country — and it’s why we can grow fast. <em>In-person is still your option</em> when an owner is nearby and a visit will clinch it — use your judgement; the advantage is yours either way.</p><h3>The Free-first play</h3><p>Lead with <strong>Free</strong>. “There’s no fee to start — let’s get your rent collecting today, and you only ever pay for what you use.” Zero barrier removes the biggest objection before it’s raised. Get them <em>using</em> it; the upgrade follows naturally when the savings add up.</p><h3>Where to start — your first owners</h3><p>Don’t start cold. Start with the owners <strong>closest to you</strong>:</p><ol><li><strong>Your own landlord</strong>, and any landlord you already know.</li><li><strong>Your circle</strong> — family, friends, neighbours who own rentals, and the caretakers/agents you know.</li><li><strong>Your area</strong> — the buildings around where you live and work.</li></ol><p>These convert fastest because trust already exists. A few easy wins early build your confidence, your income, and testimonials you can use on the next owner. If every partner simply onboards the landlords they already know, the network grows virally.</p><h3>Go online — this is our main marketing engine</h3><p>Once you’ve warmed up on your own circle, the real scale is <strong>online</strong>. Most landlords and agents are already on social media every day — so that is where you plant your flag. You don’t wait to be found; you put Centresidence in front of owners where they already scroll, and let interested ones come to <em>you</em>.</p><ul><li><strong>Social media</strong> — TikTok, Facebook, Instagram, WhatsApp Status and YouTube. What works: a short clip of rent landing by M-Pesa, a 30-second tour of the dashboard, a quick “stop chasing rent” tip, or a before/after of a landlord’s month. Some of these materials will be availed to you via marketing materials.</li><li><strong>Online communities &amp; forums</strong> — landlord and real-estate groups, estate and resident WhatsApp/Facebook groups, and local property forums. Be genuinely helpful first; the offer lands far better after you’ve answered a real question.</li><li><strong>Run ads with the materials we give you</strong> — use the ready-made <strong>marketing materials</strong> in your toolkit to run simple, low-cost ads in your own social spaces, targeting owners in your town or county. You don’t have to design anything — just post and boost.</li></ul><p>The whole point is to get an interested owner to <strong>message you</strong> — and from that first reply, your <strong>virtual-sales arm</strong> takes over end to end: book a quick call or video, screen-share the <strong>guided demo</strong>, set them up on <strong>Free</strong> right there on the call, and help them collect their first rent. <strong>Post → reply → virtual demo → signed up</strong>, without either of you leaving home. One good post can reach more owners overnight than a week of door-knocking — this is how a single partner builds a pipeline that spans the whole country.</p><h3>On the ground: new construction &amp; development sites</h3><p>Every new residential building is a future client — and the best time to reach the owner is <strong>before tenants move in</strong>, when they’re deciding how they’ll run the place. Walk the developments coming up in your area and you’ll find owners and developers who don’t yet have a system.</p><ul><li><strong>Target sites at the finishing stage</strong> — handover is close and the owner is thinking about rent collection, units and tenants.</li><li><strong>Ask for the developer, owner, or site/project manager</strong> — they’ll point you to whoever decides.</li><li><strong>Lead with what you solve</strong> — collect rent, track units, screen tenants, and get paid on time from the very first tenant, free to start.</li><li><strong>Offer a guided demo on the spot.</strong> A building that opens on Centresidence from day one never has to migrate later.</li></ul><p><strong>Worked in construction?</strong> You have an edge: you already know the owners of projects you’ve delivered. A warm introduction — <em>“the system I’d use to run this building”</em> — beats any cold call. You’re introducing them to Centresidence; they choose to adopt it, but your relationship opens the door.</p><p>A brand-new building also has <strong>many empty units to fill</strong>, and that’s where our <strong>House Hunt</strong> tenant network comes in. Be honest that it’s still growing — think of it as a <em>rising asset</em> rather than an instant fix: the more owners and tenants join, the better it connects vacant units with people looking for a home. So offer it as a reason to get on board <strong>now</strong> and grow with it from the start, not as a promise of instant tenants.</p><h3>Your rhythm</h3><p>Reach out — or let your posts bring owners to you → qualify → guided demo → get them on Free (concierge setup on the call) → help them collect their first rent → <strong>move active owners/agencies/landlords onto Transaction</strong> (pay only when they collect — and now you earn {{rent_commission}} of their rent each month for the first {{recurring_months}} months) → point at a subscription when their volume justifies it. Log each step in Leads. Consistency beats intensity: a steady daily handful of owners compounds into serious recurring income.</p>',
                'questions' => [
                    ['q' => 'What is our go-to sales motion?', 'options' => [
                        'Waiting for owners to call us' => false,
                        'In-person field visits only' => false,
                        'Virtual-first (phone/WhatsApp/video + guided demo + remote onboarding); in-person optional' => true,
                    ]],
                    ['q' => 'Where should a new partner start finding owners?', 'options' => [
                        'With owners closest to them — their own landlord, circle and area, where trust already exists' => true,
                        'With cold strangers in another country' => false,
                        'Only with the Lead Marketplace' => false,
                    ]],
                    ['q' => 'What do you lead the offer with?', 'options' => [
                        'The most expensive subscription' => false,
                        'Free — zero barrier, start collecting rent today, pay only for what you use' => true,
                        'A demand for payment upfront' => false,
                    ]],
                ],
            ],

            // Module 12 — Running a great demo
            [
                'title' => 'Running a great demo',
                'duration' => 9,
                'content' => '<h3>The guided demo</h3><p>You’ll have a <strong>standardised guided demo</strong> to run — a ready-made walkthrough you launch on a call or share as a link, so every owner sees the product at its best, no matter how new you are. Learn it, then make it feel like a conversation.</p><h3>Structure of a winning demo</h3><ol><li><strong>Ask first.</strong> “How do you collect rent today? What’s the most painful part?” Let them name the problem.</li><li><strong>Show the fix.</strong> Rent collection: an invoice goes out, the tenant gets an M-Pesa prompt, the owner sees the payment and receipt instantly. This one flow wins most demos.</li><li><strong>Layer 2–3 relevant features</strong> — only what matches <em>their</em> pain (reports/occupancy, maintenance, notices, e-sign agreements).</li><li><strong>Show a differentiator</strong> — the tenant marketplace, or rent-repaid infrastructure financing.</li><li><strong>Close to a next step</strong> — get them onto Free right now, set up with you on the call, or start a trial.</li></ol><h3>Do &amp; don’t</h3><p><strong>Do</strong> keep it about them, use their real numbers, go slow on the one flow that matters, and update the lead’s stage as you go. <strong>Don’t</strong> tour every menu or talk mostly about price or over-promise.</p>',
                'questions' => [
                    ['q' => 'What is the single most powerful thing to show?', 'options' => [
                        'Every menu item in order' => false,
                        'The login screen animation' => false,
                        'Rent collection: invoice → tenant M-Pesa prompt → owner sees payment instantly' => true,
                    ]],
                    ['q' => 'What is the guided demo for?', 'options' => [
                        'A test you must pass' => false,
                        'A ready-made walkthrough so every owner sees the product at its best, whatever your experience' => true,
                        'A document for the tenant' => false,
                    ]],
                    ['q' => 'How should a demo end?', 'options' => [
                        'With a clear next step — onto Free with you on the call, or a trial' => true,
                        'With no follow-up' => false,
                        'By quoting only the highest price' => false,
                    ]],
                ],
            ],

            // Module 13 — Handling common objections
            [
                'title' => 'Handling common objections',
                'duration' => 8,
                'content' => '<h3>An objection is interest in disguise</h3><p>Answer calmly and honestly — never invent.</p><ul><li><strong>“It’s too expensive.”</strong> → It’s <em>free</em> to start. Point out Free, or Transaction mode (a small % only when they collect). It pays for itself the first time it saves a missed rent.</li><li><strong>“I already have a system / a book.”</strong> → Ask what it does when a tenant is late, or when they need last year’s numbers. Show automatic M-Pesa collection, reports/occupancy and the differentiators a notebook can’t match.</li><li><strong>“My tenants won’t use M-Pesa.”</strong> → Almost everyone already pays by M-Pesa; here it’s just a prompt and a receipt — easier than sending money manually.</li><li><strong>“I don’t trust putting my money online.”</strong> → Rent settles to the owner; every payment is recorded with a receipt; access is secure. Transparency is the point.</li><li><strong>“Let me think about it.”</strong> → “No problem — it’s free, let’s just set up one property now so you can see it on your own units as you think it through.” Lower the stakes.</li></ul><h3>Golden rule</h3><p>If you don’t know, say you’ll find out — and check the Knowledge Base. Honesty closes more deals than bluffing, and it protects your account.</p>',
                'questions' => [
                    ['q' => 'Best answer to “it’s too expensive”?', 'options' => [
                        'End the conversation' => false,
                        'Tell them everyone else is more expensive' => false,
                        'It’s free to start — show Free, or Transaction mode (a small % only when they collect)' => true,
                    ]],
                    ['q' => 'If you don’t know the answer to an objection, you should…', 'options' => [
                        'Make something up that sounds convincing' => false,
                        'Say you’ll find out and check the Knowledge Base — never invent an answer' => true,
                        'Change the subject' => false,
                    ]],
                    ['q' => 'Good response to “let me think about it”?', 'options' => [
                        'Walk away permanently' => false,
                        'Pressure them to pay today or lose the offer' => false,
                        'Lower the stakes — “it’s free, let’s set up one property now so you can see it as you think it through”' => true,
                    ]],
                ],
            ],

            // Module 14 — Finance products in depth
            [
                'title' => 'Finance products in depth',
                'duration' => 9,
                'content' => '<h3>Why finance matters to you</h3><p>Centresidence isn’t only management software — it’s becoming a <strong>finance platform for property</strong>, and it’s a strong reason an owner chooses us over a plain rent app. On financing you earn <strong>{{first_rate}}</strong> of <em>our origination fee</em> when a facility is disbursed — then <strong>{{recurring_rate}}</strong> on any further facilities for that owner within the {{recurring_months}}-month window. It’s a share of <em>our fee</em>, so it’s always sustainable (on a large facility that’s still a healthy one-off payout).</p><h3>Infrastructure financing</h3><p>An owner installs <strong>smart infrastructure</strong> — prepaid water/gas meters, smart locks — <strong>financed by a partner and repaid automatically from rent</strong>. The owner upgrades with no upfront cash; tenants prepay utilities by M-Pesa (recurring income); the repayment is taken at source from rent. To use financing, an owner is on <strong>Transaction mode</strong> (the rail that lets repayment ride on rent).</p><h3>Who it’s for</h3><p>Owners with utilities they currently lose money or time on (shared water/power, manual meter reading), or who want to modernise a building without a lump sum. If an owner complains about utility costs or disputes, that’s a financing conversation. Soon the scope will broaden to include other income generating projects and personal use facilities</p><h3>Sell it honestly</h3><p>Financing is a real obligation repaid from real rent. Explain the repayment clearly, never over-promise returns, and let the owner decide. Done right, it turns a property’s biggest headache into an income line for the owner — and a long, sticky owner who keeps earning you across their rent and everyday usage.</p><h3>A special opportunity — finance &amp; banking contacts</h3><p>If, in your network, you know people in <strong>banking, credit or finance</strong>, tell us. Centresidence works with finance partners, and a good introduction can open a much bigger door than a single owner. We’ll guide you on how those introductions are handled and rewarded.</p>',
                'questions' => [
                    ['q' => 'How is infrastructure financing repaid?', 'options' => [
                        'Automatically from rent, at source — the owner needs no upfront cash' => true,
                        'As a lump sum paid by the tenant' => false,
                        'It is a grant that is never repaid' => false,
                    ]],
                    ['q' => 'Which mode does an owner need to use financing?', 'options' => [
                        'They must be on the Free plan' => false,
                        'Transaction mode — the rail that lets repayment ride on rent' => true,
                        'No plan is needed' => false,
                    ]],
                    ['q' => 'You know someone senior in banking/credit. What should you do?', 'options' => [
                        'Promise them guaranteed profits to sign up' => false,
                        'Nothing; it’s irrelevant to Centresidence' => false,
                        'Tell us — Centresidence works with finance partners and will guide how the introduction is handled and rewarded' => true,
                    ]],
                ],
            ],

            // Module 15 — Getting paid, your tools & selling with integrity
            [
                'title' => 'Getting paid, your tools & selling with integrity',
                'duration' => 7,
                'content' => '<h3>How you’re paid</h3><p>Commissions accrue to your <strong>balance</strong> under <em>Commissions</em> — first-time and recurring — across <strong>everything your owners do</strong>: subscriptions, rent collected, the marketplace, screening, agreements and financing. Your dashboard’s <em>“what your owners generated for you this month”</em> breaks it down by line. When ready, request a <strong>withdrawal to M-Pesa</strong>; keep your payout details accurate so nothing is delayed.</p><h3>Your tools (unlocked after this Academy)</h3><ul><li><strong>Leads</strong> — your pipeline (your own + the marketplace).</li><li><strong>Referrals</strong> — your links/codes to share.</li><li><strong>Marketing materials</strong> — ready-made assets to send prospects.</li><li><strong>Leaderboard</strong> — see how you rank and aim for the top.</li><li><strong>Knowledge Base</strong> — quick answers whenever you’re unsure.</li></ul><h3>Sell with integrity</h3><p>Your reputation is your biggest asset. Always: describe the product <strong>honestly</strong> (never promise what it can’t do); respect other affiliates’ claimed leads and ownership windows; and never spam or pressure. Misrepresenting the product or fabricating owners to chase commission is against the rules and can cost you your account — and because you’re paid on real <em>activity</em>, a fake owner earns you nothing anyway. Do it right and your recurring income compounds, owner after owner, month after month.</p><h3>You’re ready</h3><p>Finish this module and your tools unlock. Start with the owners closest to you, lead with Free, run a clean demo, onboard them on the call, and log every step. Welcome to the network — now go bring Centresidence to the owners who need it.</p>',
                'questions' => [
                    ['q' => 'How do you receive your earnings?', 'options' => [
                        'It is added to your electricity bill' => false,
                        'Withdraw your commission balance to M-Pesa' => true,
                        'You are paid only in airtime' => false,
                    ]],
                    ['q' => 'Why does fabricating fake owners not even pay?', 'options' => [
                        'You’re paid on real activity — a fake owner who never transacts earns you nothing (and risks your account)' => true,
                        'It pays very well with no risk' => false,
                        'Fake owners earn double commission' => false,
                    ]],
                    ['q' => 'Which behaviour is against the rules?', 'options' => [
                        'Following up politely' => false,
                        'Registering a lead promptly' => false,
                        'Misrepresenting the product, or fabricating owners, to chase commission' => true,
                    ]],
                ],
            ],
        ];
    }
}
