<?php

namespace Database\Seeders;

use App\Models\AcademyModule;
use App\Models\AcademyOption;
use App\Models\AcademyQuestion;
use Illuminate\Database\Seeder;

/**
 * The affiliate Academy — the gated training an affiliate must pass (80% per
 * module) before the affiliate tools unlock. Idempotent: modules keyed by title,
 * each module's questions rebuilt on run so re-seeding converges to this course.
 * Affiliate progress rows reference module_id (not questions), so rebuilding the
 * quiz is safe.
 *
 *   php artisan db:seed --class=Database\\Seeders\\AffiliateAcademySeeder
 */
class AffiliateAcademySeeder extends Seeder
{
    public function run(): void
    {
        // Retire placeholder / superseded modules from earlier (the demo "test"
        // module and the original stub intro, now replaced by this full course).
        AcademyModule::whereIn('title', ['test', 'Introduction to Centresidence'])->each(function ($m) {
            AcademyQuestion::where('module_id', $m->id)->each(fn ($q) => AcademyOption::where('question_id', $q->id)->delete());
            AcademyQuestion::where('module_id', $m->id)->delete();
            $m->delete();
        });

        foreach ($this->modules() as $i => $def) {
            $module = AcademyModule::updateOrCreate(
                ['title' => $def['title']],
                [
                    'content'          => $def['content'],
                    'youtube_url'      => $def['youtube_url'] ?? null,
                    'duration_minutes' => $def['duration'] ?? 5,
                    'module_order'     => $i + 1,
                    'is_active'        => true,
                ]
            );

            // Rebuild this module's quiz so content stays authoritative.
            AcademyQuestion::where('module_id', $module->id)->each(fn ($q) => AcademyOption::where('question_id', $q->id)->delete());
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
    }

    private function modules(): array
    {
        return [
            // 1 ──────────────────────────────────────────────────────────────
            [
                'title' => 'Welcome — the opportunity and how you earn',
                'duration' => 7,
                'content' => '<h3>Your role</h3><p>Centresidence is an all-in-one platform for property owners — it collects rent automatically over M-Pesa, manages tenants, units, invoices, maintenance and more, and can even finance smart infrastructure that is repaid from rent. <strong>Your job as an affiliate is to introduce property owners to Centresidence and help them get started.</strong> The platform is built to be plug-and-play: once an owner is in, the system does the heavy lifting — your focus is finding owners.</p><h3>How you earn — two ways, on every owner</h3><ul><li><strong>First-time commission</strong> — earned when a referred owner converts to a paying subscription.</li><li><strong>Recurring commission</strong> — a share of their ongoing payments, for as long as the arrangement runs. This is how a good affiliate builds a growing, passive income: every active owner keeps paying you.</li></ul><p>Commission rates are set by Centresidence and shown in your dashboard. Earnings accrue to a balance you withdraw to M-Pesa.</p><h3>Why this training exists</h3><p>To sell Centresidence well you have to <em>understand</em> it. The next few modules teach you the whole product — what an owner actually gets — so you can speak to any landlord with confidence. Finish the Academy (80% per module) and your tools unlock: <em>Leads</em>, <em>Referrals</em>, <em>Marketing materials</em>, <em>Commissions</em> and <em>Withdrawals</em>.</p>',
                'questions' => [
                    ['q' => 'How does an affiliate earn?', 'options' => [
                        'A first-time commission when a referred owner subscribes, plus recurring commission on their ongoing payments' => true,
                        'A fixed monthly salary from Centresidence' => false,
                        'A share of the tenant’s rent paid to the landlord' => false,
                    ]],
                    ['q' => 'Who do you introduce to Centresidence?', 'options' => [
                        'Property owners and landlords who manage rentals' => true,
                        'Tenants looking for a house' => false,
                        'Anyone at all — it is a general app' => false,
                    ]],
                    ['q' => 'Why does an affiliate need to learn the whole product?', 'options' => [
                        'To speak to any landlord with confidence and demo it well' => true,
                        'It is not necessary — you only share a link' => false,
                        'To become a software developer' => false,
                    ]],
                ],
            ],
            // 2 ──────────────────────────────────────────────────────────────
            [
                'title' => 'The product I — properties, tenants & rent',
                'duration' => 9,
                'content' => '<h3>Properties & units</h3><p>An owner sets up their buildings as <strong>properties</strong>, each split into <strong>units</strong> (house/apartment/shop). Rent, deposit and late-fee rules are set per unit. This gives the owner a clean map of everything they own in one place.</p><h3>Tenants & onboarding</h3><p>Owners add their tenants to units. Prospective tenants can also submit <strong>tenant applications</strong>, and owners keep a full <strong>tenant history</strong>. Each tenant gets their own secure login to see invoices, pay, raise issues and read notices.</p><h3>Rent collection — the heart of it</h3><p>This is the feature that sells itself. Centresidence generates each tenant’s <strong>rent invoice automatically</strong> and the tenant pays with an <strong>M-Pesa prompt on their phone</strong> — the money routes correctly and the owner sees it <em>instantly</em>, with a receipt. <strong>Recurring rent</strong> means invoices regenerate every cycle without the owner lifting a finger. No more chasing, no cash handling, no lost records.</p><h3>Billing Center</h3><p>Beyond rent, owners raise any charge — deposits, water, incidentals, late fees — as invoices from the <strong>Billing Center</strong>, all paid the same easy way and all recorded.</p><p><em>Demo tip:</em> almost every landlord’s biggest pain is <strong>chasing rent</strong>. Lead with automatic M-Pesa rent collection and you have their attention.</p>',
                'questions' => [
                    ['q' => 'How do tenants pay their rent on Centresidence?', 'options' => [
                        'An M-Pesa prompt on their phone; the owner sees it instantly with a receipt' => true,
                        'They must visit a bank branch' => false,
                        'Only in cash to the caretaker' => false,
                    ]],
                    ['q' => 'What does "recurring rent" mean for the owner?', 'options' => [
                        'Rent invoices regenerate each cycle automatically' => true,
                        'The rent amount increases every month' => false,
                        'Tenants can skip paying' => false,
                    ]],
                    ['q' => 'What is usually a landlord’s biggest pain to lead with?', 'options' => [
                        'Chasing rent and poor records' => true,
                        'Choosing paint colours' => false,
                        'Too many tenants applying' => false,
                    ]],
                ],
            ],
            // 3 ──────────────────────────────────────────────────────────────
            [
                'title' => 'The product II — the full management toolkit',
                'duration' => 10,
                'content' => '<h3>Everything else an owner runs from one place</h3><ul><li><strong>Maintenance & maintainers</strong> — tenants log maintenance requests; owners assign a caretaker/maintainer to handle them and track them to completion.</li><li><strong>Tickets</strong> — a support channel between tenant and owner for questions and issues.</li><li><strong>Notice Board</strong> — broadcast notices (water shut-off, rent reminders, meetings) to a unit or the whole property.</li><li><strong>Expenses</strong> — record property costs, so profit is real, not guessed.</li><li><strong>Reports</strong> — earnings, profit/loss by month, expenses, lease, <strong>occupancy</strong> and maintenance — the numbers owners never had before.</li><li><strong>Documents</strong> — store leases, IDs and files safely per tenant/property.</li><li><strong>Agreements (e-sign)</strong> — send a lease for the tenant to sign in-app, verified by an SMS one-time code, producing a certified PDF and audit trail. No printing, no chasing signatures.</li><li><strong>Listings</strong> — advertise vacant units to reduce void periods.</li><li><strong>SMS credits</strong> — message tenants directly from the system.</li><li><strong>Tenant screening</strong> — check a prospective tenant’s history/creditworthiness before letting.</li></ul><h3>The pitch</h3><p>An owner replaces a drawer of receipts, a WhatsApp full of complaints and a notebook of rent with <strong>one organised system</strong>. Pick the two or three of these that hit <em>this</em> owner’s pain.</p>',
                'questions' => [
                    ['q' => 'How are lease agreements signed on Centresidence?', 'options' => [
                        'In-app, verified by an SMS one-time code, producing a certified PDF + audit trail' => true,
                        'Only on printed paper posted by mail' => false,
                        'They cannot be signed digitally' => false,
                    ]],
                    ['q' => 'Which of these does the platform provide to owners?', 'options' => [
                        'Maintenance, tickets, notices, expenses, reports, documents, listings and screening' => true,
                        'Only rent collection and nothing else' => false,
                        'A social media network' => false,
                    ]],
                    ['q' => 'What is a good way to pitch the toolkit?', 'options' => [
                        'Replace scattered receipts, WhatsApp and notebooks with one organised system' => true,
                        'Tell them to use every single feature on day one' => false,
                        'Focus only on the colour scheme' => false,
                    ]],
                ],
            ],
            // 4 ──────────────────────────────────────────────────────────────
            [
                'title' => 'The product III — the money engine (what makes it special)',
                'duration' => 10,
                'content' => '<h3>Flexible pricing — a real selling point</h3><p>Owners aren’t forced into one plan. They can be on a <strong>subscription</strong>, or on <strong>transaction (pay-as-you-go)</strong> mode where the platform simply takes a small percentage of rent collected — no monthly fee. A hesitant owner loves "you only pay when you collect". Explain both and let them choose.</p><h3>The marketplace (My Shop)</h3><p>Owners can sell <strong>products and services to their own tenants</strong> — water, gas, cleaning, anything — through an in-app shop, paid by M-Pesa. It turns a building into a small marketplace and an extra income stream for the owner.</p><h3>Infrastructure financing — the standout</h3><p>This is unique. An owner can install <strong>smart infrastructure</strong> (e.g. prepaid water/gas meters, smart locks) <strong>financed by a partner and repaid automatically from rent</strong>. Tenants top up utilities by M-Pesa; that usage becomes prepaid, recurring income; and the financing is deducted at source from rent so the owner upgrades their property <em>with no upfront cash</em>. Utilities stop being a cost and a hassle and become an income line.</p><h3>Why this matters to you</h3><p>These are the things competitors don’t have. When an owner says "I already have a system", the money engine — transaction pricing, the tenant marketplace, and rent-repaid infrastructure — is what sets Centresidence apart.</p>',
                'questions' => [
                    ['q' => 'In transaction (pay-as-you-go) mode, how does the owner pay?', 'options' => [
                        'A small percentage of rent collected, with no monthly subscription fee' => true,
                        'A large fixed fee whether or not they collect rent' => false,
                        'Nothing — it is completely free forever' => false,
                    ]],
                    ['q' => 'How does infrastructure financing get repaid?', 'options' => [
                        'Automatically from rent, deducted at source' => true,
                        'The owner pays the full cost upfront in cash' => false,
                        'It is never repaid' => false,
                    ]],
                    ['q' => 'What is the tenant marketplace (My Shop)?', 'options' => [
                        'Owners sell products/services to their tenants via M-Pesa — an extra income stream' => true,
                        'A place tenants buy other houses' => false,
                        'A stock-trading platform' => false,
                    ]],
                ],
            ],
            // 5 ──────────────────────────────────────────────────────────────
            [
                'title' => 'Finding and qualifying leads',
                'duration' => 8,
                'content' => '<h3>Who to target</h3><p>Your best prospects feel the pain the product solves: <strong>landlords with several rental units</strong>, small agencies, and owners still chasing rent by hand or on paper. The more units an owner has, the more value they get — and the bigger your recurring commission.</p><h3>Where to find them</h3><p>Warm referrals from people you know, local landlord and estate groups (incl. WhatsApp), caretakers who know owners, property managers, and simply asking "who owns this building?". A warm, qualified lead beats a big cold list every time.</p><h3>Qualify quickly</h3><p>In the first chat, learn: how many units? how do they collect rent today? what frustrates them most? This tells you whether they’re a fit and which feature to lead with in the demo.</p><h3>Capture every lead — and protect it</h3><p>Register each prospect under <strong>Leads</strong> the moment you speak to them. This organises your pipeline <em>and</em> protects the lead as yours for <strong>60 days</strong> — within that window the owner is credited to you. An unregistered lead can’t be paid to you, so capture first, sell second.</p>',
                'questions' => [
                    ['q' => 'How long is a registered lead protected/credited to you?', 'options' => [
                        '60 days' => true,
                        '24 hours' => false,
                        'It is never protected' => false,
                    ]],
                    ['q' => 'What should you learn when qualifying a lead?', 'options' => [
                        'How many units they have, how they collect rent now, and their biggest frustration' => true,
                        'Their favourite football team' => false,
                        'Nothing — just sign them up immediately' => false,
                    ]],
                    ['q' => 'What is the very first thing to do with a new prospect?', 'options' => [
                        'Register them under Leads so the lead is captured and protected' => true,
                        'Wait a month before recording anything' => false,
                        'Ask them to pay first' => false,
                    ]],
                ],
            ],
            // 6 ──────────────────────────────────────────────────────────────
            [
                'title' => 'Running a great demo',
                'duration' => 9,
                'content' => '<h3>Structure of a winning demo</h3><ol><li><strong>Ask first.</strong> "How do you collect rent today? What’s the most painful part?" Let them tell you the problem.</li><li><strong>Show the fix.</strong> Open rent collection: an invoice goes out, the tenant gets an M-Pesa prompt, the owner sees the payment and a receipt instantly. This one flow wins most demos.</li><li><strong>Layer 2–3 relevant features.</strong> Pick from tenants/units, maintenance, notices, reports/occupancy, e-sign agreements — only what matches <em>their</em> pain.</li><li><strong>Show a differentiator.</strong> Transaction pricing ("pay only when you collect"), the tenant marketplace, or rent-repaid infrastructure financing.</li><li><strong>Close to a next step.</strong> Offer to start their trial now.</li></ol><h3>Do & don’t</h3><p><strong>Do</strong> keep it about them, use their real numbers, and go slow on the one flow that matters. <strong>Don’t</strong> tour every menu, talk mostly about price, or over-promise. Keep the lead’s stage updated in the system as you go.</p>',
                'questions' => [
                    ['q' => 'What is the single most powerful thing to show in a demo?', 'options' => [
                        'Rent collection: invoice → tenant M-Pesa prompt → owner sees payment instantly' => true,
                        'The login screen animation' => false,
                        'The full list of every menu item' => false,
                    ]],
                    ['q' => 'How should you choose which features to show?', 'options' => [
                        'Only the 2–3 that match this owner’s specific pain' => true,
                        'Every feature, in order, no matter what' => false,
                        'Whichever are the newest' => false,
                    ]],
                    ['q' => 'How should a demo end?', 'options' => [
                        'With a clear next step — offer to start their trial now' => true,
                        'With no follow-up' => false,
                        'By quoting only the highest price' => false,
                    ]],
                ],
            ],
            // 7 ──────────────────────────────────────────────────────────────
            [
                'title' => 'Handling common objections',
                'duration' => 8,
                'content' => '<h3>Turn objections into conversations</h3><p>An objection is interest in disguise — answer it calmly and honestly.</p><ul><li><strong>"It’s too expensive."</strong> → Show <em>transaction mode</em>: no monthly fee, just a small percentage of rent you actually collect. It pays for itself the first time it saves a missed rent.</li><li><strong>"I already have a system / a book."</strong> → Ask what it does when a tenant is late, or when they need last year’s numbers. Show automatic M-Pesa collection, reports/occupancy, and the differentiators (marketplace, rent-repaid infrastructure) a notebook can’t match.</li><li><strong>"My tenants won’t use M-Pesa."</strong> → Almost everyone already pays by M-Pesa; here it’s just a prompt on their phone and a receipt — easier than sending money manually.</li><li><strong>"I don’t trust putting my money online."</strong> → Rent settles to the owner; every payment is recorded with a receipt; access is secure. Transparency is the point.</li><li><strong>"Let me think about it."</strong> → Offer a no-risk trial on one property so they can see it on their own units.</li></ul><h3>Golden rule</h3><p>Never invent an answer. If you don’t know, say you’ll find out — and check the Knowledge Base. Honesty closes more deals than bluffing.</p>',
                'questions' => [
                    ['q' => 'Best answer to "it’s too expensive"?', 'options' => [
                        'Show transaction mode — no monthly fee, just a small % of rent you collect' => true,
                        'Tell them everyone else is more expensive' => false,
                        'End the conversation' => false,
                    ]],
                    ['q' => 'If you don’t know the answer to an objection, you should…', 'options' => [
                        'Say you’ll find out and check the Knowledge Base — never invent an answer' => true,
                        'Make something up that sounds convincing' => false,
                        'Change the subject and hope they forget' => false,
                    ]],
                    ['q' => 'Good response to "let me think about it"?', 'options' => [
                        'Offer a no-risk trial on one property so they can see it on their own units' => true,
                        'Pressure them to pay today or lose the offer' => false,
                        'Walk away permanently' => false,
                    ]],
                ],
            ],
            // 8 ──────────────────────────────────────────────────────────────
            [
                'title' => 'The pipeline: lead → demo → trial → account',
                'duration' => 7,
                'content' => '<h3>The four stages</h3><ol><li><strong>Lead</strong> — prospect captured in the system.</li><li><strong>Demo</strong> — you’ve shown them the product.</li><li><strong>Trial</strong> — they’re trying it on their own property.</li><li><strong>Account</strong> — they convert to a paying subscription. This is when your <strong>first-time commission</strong> is earned; recurring commission then follows on their ongoing payments.</li></ol><h3>Work the pipeline</h3><p>Update each lead’s stage as it moves so you always know where to spend your time, and so you’re credited correctly. <strong>Follow up promptly</strong> — most deals are lost to silence, not to a "no". A quick check-in after the demo, and again after the trial starts, wins conversions.</p><h3>Mind the 60-day window</h3><p>Keep momentum inside the 60-day lead-ownership window so the conversion is clearly yours. A trial that stalls is a reminder to reach out, not to give up.</p>',
                'questions' => [
                    ['q' => 'What is the correct order of the pipeline?', 'options' => [
                        'Lead → demo → trial → account' => true,
                        'Trial → account → lead → demo' => false,
                        'Demo → account → lead → trial' => false,
                    ]],
                    ['q' => 'When is your first-time commission earned?', 'options' => [
                        'When the referred owner converts to a paying subscription' => true,
                        'The moment you register the lead' => false,
                        'Never — only recurring commission exists' => false,
                    ]],
                    ['q' => 'What loses the most deals?', 'options' => [
                        'Silence / not following up' => true,
                        'Following up too politely' => false,
                        'Updating the lead’s stage' => false,
                    ]],
                ],
            ],
            // 9 ──────────────────────────────────────────────────────────────
            [
                'title' => 'Getting paid, tools & selling with integrity',
                'duration' => 7,
                'content' => '<h3>How you’re paid</h3><p>Commissions accrue to your <strong>balance</strong> under <em>Commissions</em> — both the first-time and the recurring amounts. When ready, request a <strong>withdrawal to M-Pesa</strong>; keep your payout details accurate so nothing is delayed.</p><h3>Your tools (unlocked after this Academy)</h3><ul><li><strong>Leads</strong> — your pipeline.</li><li><strong>Referrals</strong> — your links/codes to share.</li><li><strong>Marketing materials</strong> — ready-made assets to send prospects.</li><li><strong>Leaderboard</strong> — see how you rank and aim for the top.</li><li><strong>Knowledge Base</strong> — quick answers whenever you’re unsure.</li></ul><h3>Sell with integrity</h3><p>Your reputation is your biggest asset. Always: describe the product <strong>honestly</strong> (never promise what it can’t do); respect other affiliates’ registered leads and the 60-day ownership; and never spam or pressure. Misrepresenting the product to close a sale is against the rules and can cost you your account. Do it right and your recurring income compounds month after month.</p>',
                'questions' => [
                    ['q' => 'How do you receive your earnings?', 'options' => [
                        'Withdraw your commission balance to M-Pesa' => true,
                        'It is added to your electricity bill' => false,
                        'You are paid only in airtime' => false,
                    ]],
                    ['q' => 'Which behaviour is against the rules?', 'options' => [
                        'Misrepresenting what the product does to close a sale' => true,
                        'Registering a lead promptly' => false,
                        'Following up politely' => false,
                    ]],
                    ['q' => 'Where do you go when you’re unsure of an answer?', 'options' => [
                        'The Knowledge Base' => true,
                        'Guess and hope' => false,
                        'Tell the owner it’s impossible' => false,
                    ]],
                ],
            ],
        ];
    }
}
