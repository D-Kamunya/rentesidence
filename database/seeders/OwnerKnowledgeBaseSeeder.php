<?php

namespace Database\Seeders;

use App\Models\KnowledgeBaseArticle;
use App\Models\KnowledgeBaseCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * PRODUCTION-SAFE knowledge-base content for PROPERTY OWNERS — a rich, self-serve
 * help library covering every action an owner can take, so onboarding doesn't rely
 * on hand-holding. Mirrors KnowledgeBaseSeeder (finance partners): keyed by slug with
 * updateOrCreate, so re-running refreshes the SHIPPED content to the current product.
 * Run it on deploy to keep the owner KB in step with the code.
 *
 * (After go-live, admin edits in the UI are the source of truth — don't re-run blindly
 * if owners rely on hand-tuned articles; slugs here are the anchor for a controlled refresh.)
 *
 *   php artisan db:seed --class=Database\\Seeders\\OwnerKnowledgeBaseSeeder
 */
class OwnerKnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        // Articles need an author (kb_articles.created_by FK) — attribute to an admin;
        // bail quietly on a bare install with no users yet.
        $authorId = User::where('role', USER_ROLE_ADMIN)->value('id') ?? User::value('id');
        if (! $authorId) {
            return;
        }

        // [name, description, remix-icon class, sort_order]
        $categories = [
            'ow-start'       => ['Getting started', 'Your dashboard, the two pricing modes, and a first-week checklist.', 'ri-rocket-line', 1],
            'ow-properties'  => ['Properties & units', 'Add, edit and remove properties and the units inside them.', 'ri-building-2-line', 2],
            'ow-tenants'     => ['Tenants', 'Add, onboard, edit, move out and import your tenants.', 'ri-group-line', 3],
            'ow-billing'     => ['Rent, invoices & charges', 'How rent is billed, manual invoices, receipts and expenses.', 'ri-bill-line', 4],
            'ow-payments'    => ['Getting paid', 'How tenants pay you, your wallet, and withdrawing to M-Pesa.', 'ri-wallet-3-line', 5],
            'ow-shop'        => ['Marketplace (My Shop)', 'Sell products to your tenants and fulfil the orders.', 'ri-store-2-line', 6],
            'ow-financing'   => ['Financing your property', 'Fund smart infrastructure and repay from rent, at source.', 'ri-coins-line', 7],
            'ow-screening'   => ['Tenant screening', 'Check a tenant\'s objective rental payment record before you sign.', 'ri-shield-user-line', 8],
            'ow-agreements'  => ['Agreements & e-signing', 'Send a lease and have the tenant sign it in-portal.', 'ri-quill-pen-line', 9],
            'ow-listings'    => ['Listings (House Hunt)', 'Advertise a vacant unit for rent or sale and take enquiries.', 'ri-home-4-line', 10],
            'ow-maintenance' => ['Maintenance & caretakers', 'Caretakers, maintenance requests, tickets and the notice board.', 'ri-tools-line', 11],
            'ow-sms'         => ['SMS credits & notifications', 'Buy SMS credits and understand what sends a message.', 'ri-message-2-line', 12],
            'ow-reports'     => ['Reports', 'Earnings, occupancy, lease and tenant reports — and exporting them.', 'ri-bar-chart-box-line', 13],
            'ow-settings'    => ['Settings, profile & subscription', 'Your profile, currency, tax, email and your plan.', 'ri-settings-3-line', 14],
        ];

        $cat = [];
        foreach ($categories as $slug => [$name, $desc, $icon, $order]) {
            $cat[$slug] = KnowledgeBaseCategory::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'description' => $desc, 'icon' => $icon,
                'audience' => 'owners', 'sort_order' => $order, 'is_active' => true,
            ]);
        }

        foreach ($this->articles() as $i => $a) {
            KnowledgeBaseArticle::updateOrCreate(['slug' => $a['slug']], [
                'kb_category_id' => $cat[$a['category']]->id, 'created_by' => $authorId, 'updated_by' => $authorId,
                'title' => $a['title'], 'type' => 'article', 'audience' => 'owners',
                'status' => 'published', 'excerpt' => $a['excerpt'], 'body' => $a['body'],
                'sort_order' => $i + 1, 'published_at' => Carbon::now(),
            ]);
        }
    }

    /** All owner articles, in display order, grouped by the category they belong to. */
    private function articles(): array
    {
        return array_merge(
            $this->gettingStarted(),
            $this->properties(),
            $this->tenants(),
            $this->billing(),
            $this->payments(),
            $this->shop(),
            $this->financing(),
            $this->screening(),
            $this->agreements(),
            $this->listings(),
            $this->maintenance(),
            $this->sms(),
            $this->reports(),
            $this->settings(),
        );
    }

    /** Small helper to keep the article arrays terse and consistent. */
    private function a(string $category, string $slug, string $title, string $excerpt, string $body): array
    {
        return compact('category', 'slug', 'title', 'excerpt', 'body');
    }

    // ══════════════════════════════════════════════════════════════════════
    // 1 · GETTING STARTED
    // ══════════════════════════════════════════════════════════════════════
    private function gettingStarted(): array
    {
        return [
            $this->a('ow-start', 'ow-welcome',
                'Welcome — a quick tour of your dashboard',
                'What each part of your owner account does and where to find it.',
                '<h3>Welcome</h3><p>Your owner account is where you manage everything about your rentals — properties and units, tenants, rent and invoices, payments, maintenance, and more. This guide library explains every action step by step.</p><h3>The main sections (left sidebar)</h3><ul><li><strong>Dashboard</strong> — a snapshot: occupancy, pending items, recent activity.</li><li><strong>Properties</strong> — your buildings and the units inside them.</li><li><strong>Tenants</strong> — everyone renting from you, plus applications and history.</li><li><strong>Billing Center</strong> — invoices, recurring rent settings, expenses, documents and SMS credits.</li><li><strong>Financing</strong> — fund smart meters/locks and repay from rent.</li><li><strong>My Shop</strong> — sell products to tenants.</li><li><strong>Maintenance</strong> — caretakers, requests, tickets and the notice board.</li><li><strong>Report</strong> — earnings, occupancy and more.</li><li><strong>Wallet</strong> — your money and withdrawals.</li><li><strong>Settings</strong> &amp; <strong>Profile</strong> — how your account behaves and your login.</li></ul><p>New here? Start with <em>Your first week: a setup checklist</em>.</p>'),
            $this->a('ow-start', 'ow-pricing-modes',
                'Subscription vs Transaction mode — what changes',
                'The one setting that decides how rent reaches you and whether you can finance.',
                '<h3>Two ways to run your account</h3><p>Your account runs in one of two pricing modes, and it changes how money moves:</p><ul><li><strong>Subscription (or Free) mode</strong> — tenant rent goes <em>straight to your own payment account</em>. You pay a monthly plan (or use the free tier). Simple and direct.</li><li><strong>Transaction mode</strong> — rent is collected through the Centresidence account, a small fee (1%) is taken, and the rest is credited to your in-app <strong>wallet</strong>, which you withdraw. There\'s no monthly subscription.</li></ul><h3>Why it matters</h3><p><strong>Financing requires transaction mode.</strong> Because a facility is repaid from your rent at source, the rent has to flow through the system first. If you want to finance smart meters or locks, you switch to transaction mode when you apply.</p><p>You can see and change your mode from <strong>My Subscription</strong>. If you have an active facility, you can\'t leave transaction mode until it\'s cleared.</p>'),
            $this->a('ow-start', 'ow-first-week',
                'Your first week: a setup checklist',
                'The fastest path from empty account to collecting rent.',
                '<h3>Set up in order</h3><ol><li><strong>Add a property</strong> — Properties → All Property → <em>Add</em>. See <em>Add a property</em>.</li><li><strong>Add its units</strong> — open the property and add each rentable unit with its rent. See <em>Add units to a property</em>.</li><li><strong>Add your tenants</strong> — assign a tenant to a unit; their login is sent automatically. See <em>Add a tenant to a unit</em>.</li><li><strong>Choose how you get paid</strong> — set up your payment gateway (subscription mode) or switch to transaction mode for a wallet. See <em>How your tenants pay you</em>.</li><li><strong>Turn on recurring rent</strong> — confirm Recurring Setting so invoices generate every month.</li></ol><h3>Optional but powerful</h3><ul><li><strong>Screen tenants</strong> before you sign them.</li><li><strong>Send a lease</strong> for in-app signing.</li><li><strong>Finance</strong> smart infrastructure to add recurring income.</li></ul><p>Each step has its own article — search the knowledge base any time from the search box.</p>'),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 2 · PROPERTIES & UNITS
    // ══════════════════════════════════════════════════════════════════════
    private function properties(): array
    {
        return [
            $this->a('ow-properties', 'ow-add-property',
                'Add a property',
                'Register a building so you can add units and tenants to it.',
                '<h3>Add a property</h3><ol><li>Go to <strong>Properties → All Property</strong>.</li><li>Click <strong>Add</strong>.</li><li>Fill in the name, type and location (you can pin it on the map).</li><li>Add a photo so it\'s easy to recognise.</li><li>Save.</li></ol><p>A property is just the container — it holds the individual <strong>units</strong> your tenants actually rent. After saving, open the property and add its units next.</p><h3>Own vs leased property</h3><p>If you manage a building you don\'t own, you can still add it. <strong>Own Property</strong> and <strong>Lease Property</strong> in the sidebar simply filter your list by that distinction.</p>'),
            $this->a('ow-properties', 'ow-edit-property',
                'Edit or update a property',
                'Change a property\'s details, location or photo.',
                '<h3>Edit a property</h3><ol><li>Go to <strong>Properties → All Property</strong>.</li><li>Find the property and open it (or use its <strong>Edit</strong> action).</li><li>Update any detail — name, type, address/map pin, or photo.</li><li>Save.</li></ol><p>Editing a property does not affect the tenants or invoices attached to its units — it only updates the building\'s own information.</p>'),
            $this->a('ow-properties', 'ow-add-units',
                'Add units to a property',
                'Create the individual rentable spaces and set their rent.',
                '<h3>Add a unit</h3><ol><li>Open the property from <strong>All Property</strong>.</li><li>Choose <strong>Add unit</strong>.</li><li>Give the unit a name/number, its rent amount and rent type (monthly, yearly or custom).</li><li>Add a photo if you like, then save.</li></ol><p>Repeat for every rentable space. Units are what you assign tenants to and what rent invoices are raised against.</p><h3>Where rent lives</h3><p><strong>All Unit</strong> in the sidebar lists every unit across all your properties and is the single place to review or change a unit\'s rent. Set the rent correctly here — invoices are generated from it.</p>'),
            $this->a('ow-properties', 'ow-edit-unit-rent',
                'Edit a unit and change its rent',
                'Update a unit\'s details or its monthly rent.',
                '<h3>Edit a unit</h3><ol><li>Go to <strong>Properties → All Unit</strong> (or open the property and find the unit).</li><li>Use the unit\'s <strong>Edit</strong> action.</li><li>Change the name, rent amount, rent type or photo.</li><li>Save.</li></ol><h3>About changing rent</h3><p>A new rent amount applies to <em>future</em> invoices — invoices already generated for the current period stand. If a tenant is assigned, review their rent from All Unit, the single source of truth for rent, so figures never disagree across pages.</p>'),
            $this->a('ow-properties', 'ow-unit-property-photos',
                'Add or change property & unit photos',
                'Upload, replace or remove images and set a main photo.',
                '<h3>Photos</h3><p>Good photos make your properties and units easy to recognise and are reused if you list a unit publicly.</p><ol><li>Open the property or unit and use its image/edit action.</li><li>Upload one or more images. Large phone photos are automatically shrunk in your browser before upload, so they upload quickly.</li><li>Set a main/thumbnail image, or delete any image you don\'t want.</li></ol>'),
            $this->a('ow-properties', 'ow-delete-property-unit',
                'Delete a unit or a property',
                'Remove a unit or a whole property, and what to do first.',
                '<h3>Before you delete</h3><p>Deleting is permanent. If a unit currently has a tenant, <strong>move the tenant out first</strong> (close the tenancy) — see <em>Move a tenant out</em>. A property should be emptied of units before it\'s removed.</p><h3>Delete a unit</h3><ol><li>Go to <strong>All Unit</strong> (or open the property).</li><li>Use the unit\'s delete action and confirm.</li></ol><h3>Delete a property</h3><ol><li>Go to <strong>All Property</strong>.</li><li>Use the property\'s delete action and confirm.</li></ol><p>Historical records tied to past tenancies remain for your reports even after a unit is removed.</p>'),
            $this->a('ow-properties', 'ow-property-lists',
                'All Property, Own, Lease and All Unit — what each list is',
                'Find the right view quickly.',
                '<h3>The four property views</h3><ul><li><strong>All Property</strong> — every building you\'ve added.</li><li><strong>Own Property</strong> — only the ones you own.</li><li><strong>Lease Property</strong> — only ones you manage on behalf of someone else.</li><li><strong>All Unit</strong> — every individual unit across all properties, and the place to manage rent.</li></ul><p>They\'re filters over the same data, so a unit you add always shows up wherever it belongs.</p>'),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 3 · TENANTS
    // ══════════════════════════════════════════════════════════════════════
    private function tenants(): array
    {
        return [
            $this->a('ow-tenants', 'ow-add-tenant',
                'Add a tenant to a unit',
                'Create a tenant and place them in a unit in one step.',
                '<h3>Add a tenant</h3><ol><li>Go to <strong>Tenants → All Tenants</strong> and choose <strong>Add</strong> (or assign from within a unit).</li><li>Enter the tenant\'s name, phone and email, and pick the property and vacant unit.</li><li>Set the tenancy start date and any opening balance.</li><li>Save.</li></ol><h3>What happens next</h3><p>The tenant gets a login automatically — see <em>How tenants get their login</em>. From then on, rent invoices for their unit are generated each month and they can pay in their own tenant portal.</p>'),
            $this->a('ow-tenants', 'ow-tenant-credentials',
                'How tenants get their login (and the first-login reset)',
                'Credentials are sent by email and SMS, and the tenant sets their own password.',
                '<h3>Automatic credentials</h3><p>When you add a tenant (or assign an applicant to a unit), the system creates their account, generates a temporary password, and <strong>sends it by both email and SMS</strong>. You don\'t need to share anything manually.</p><h3>First login is secured</h3><p>The first time a tenant signs in, they\'re <strong>required to set their own password</strong> before they can do anything else. This means the temporary password can\'t be reused and only the tenant knows their final password.</p><p>If a tenant didn\'t receive their details, you can resend them — see <em>Resend login details</em>.</p>'),
            $this->a('ow-tenants', 'ow-resend-login',
                'Resend login details (one tenant or in bulk)',
                'Re-send credentials to a tenant who didn\'t get them.',
                '<h3>Resend to one tenant</h3><ol><li>Go to <strong>Tenants → All Tenants</strong>.</li><li>Open the tenant and use <strong>Resend login</strong>.</li></ol><p>A fresh temporary password is generated and sent by email and SMS; they\'ll be asked to set their own on next login.</p><h3>Resend to many at once</h3><p>Onboarding a whole building? Use <strong>bulk resend</strong> from the tenants list to send credentials to everyone who needs them in one action — handy right after a bulk import.</p>'),
            $this->a('ow-tenants', 'ow-edit-tenant',
                'Edit a tenant\'s details',
                'Update a tenant\'s contact info and profile.',
                '<h3>Edit a tenant</h3><ol><li>Go to <strong>Tenants → All Tenants</strong>.</li><li>Open the tenant and choose <strong>Edit</strong>.</li><li>Update their name, phone, email or other profile details, then save.</li></ol><h3>Changing rent?</h3><p>Rent isn\'t edited on the tenant — it lives on the <strong>unit</strong>. To change what a tenant pays, edit the unit in <strong>All Unit</strong>. This keeps a single source of truth so figures never disagree between pages.</p>'),
            $this->a('ow-tenants', 'ow-tenant-documents',
                'Tenant documents & KYC',
                'Collect and view the documents you require from a tenant.',
                '<h3>Documents</h3><p>You can require documents (ID, references, and so on) from tenants. What\'s requested is configured once in <strong>Settings → document configuration</strong> and applies to new tenants automatically, so you don\'t re-ask each time.</p><h3>View a tenant\'s documents</h3><ol><li>Open the tenant from <strong>All Tenants</strong>.</li><li>Go to their <strong>Documents</strong> tab to view, download or manage what they\'ve provided.</li></ol>'),
            $this->a('ow-tenants', 'ow-close-tenancy',
                'Move a tenant out (close a tenancy)',
                'End a tenancy cleanly and free up the unit.',
                '<h3>Close a tenancy</h3><ol><li>Open the tenant from <strong>All Tenants</strong>.</li><li>Use the close/move-out action and record the move-out details.</li><li>Confirm.</li></ol><p>The unit becomes vacant and available for a new tenant, while the past tenancy is preserved.</p><h3>Tenant History</h3><p><strong>Tenants → Tenant History</strong> keeps every past tenancy and its closing details, so you always have the record for reports or reference even after the unit is re-let.</p>'),
            $this->a('ow-tenants', 'ow-bulk-import',
                'Bulk import tenants from a spreadsheet',
                'Onboard many tenants and units at once — preview first, then confirm.',
                '<h3>When to use it</h3><p>Moving an existing portfolio onto the system? Import everyone from a spreadsheet instead of adding them one by one.</p><h3>How it works</h3><ol><li>Go to <strong>Tenants → import</strong> and download the template.</li><li>Fill one row per tenant-in-a-unit (it can create the property, the unit, the tenant and an opening balance together).</li><li>Upload the file — you get a <strong>row-by-row preview with nothing written yet</strong>, flagging any problems.</li><li>Fix flagged rows if needed, then <strong>Confirm</strong>. The import runs in the background; you can watch progress and download an errors report.</li></ol><p>After importing, use <strong>bulk resend logins</strong> to send everyone their credentials.</p>'),
            $this->a('ow-tenants', 'ow-tenant-applications',
                'Approve applicants (Tenant Applications)',
                'Turn a listing enquiry into a tenant in a unit.',
                '<h3>Applications</h3><p>When someone applies through one of your public listings, they appear under <strong>Tenants → Tenant Applications</strong>.</p><ol><li>Open an application to review the applicant\'s details (the info they provided is reused, so you won\'t re-ask for it).</li><li>To accept, <strong>assign</strong> them to a vacant unit.</li></ol><p>Assigning creates their tenant account and sends login details automatically, exactly like adding a tenant directly — including the first-login password reset.</p>'),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 4 · RENT, INVOICES & CHARGES
    // ══════════════════════════════════════════════════════════════════════
    private function billing(): array
    {
        return [
            $this->a('ow-billing', 'ow-how-rent-billed',
                'How monthly rent invoices are generated',
                'Rent invoices are raised automatically each cycle from the unit\'s rent.',
                '<h3>Automatic rent</h3><p>Once a unit has a tenant and recurring rent is on, an invoice is generated automatically each cycle (on the 1st) from the unit\'s rent amount. The tenant sees it in their portal and can pay it.</p><h3>Turn it on</h3><p>Check <strong>Billing Center → Recurring Setting</strong> so recurring rent is enabled. If a unit\'s rent looks wrong on an invoice, fix the rent on the unit in <strong>All Unit</strong> — future invoices will use the new figure.</p>'),
            $this->a('ow-billing', 'ow-manual-invoice',
                'Create a one-off (instant) invoice',
                'Bill a tenant for something outside the normal monthly rent.',
                '<h3>Raise a manual invoice</h3><ol><li>Go to <strong>Billing Center → All Invoices</strong>.</li><li>Create a new invoice, pick the tenant/unit, and add the line items and amounts.</li><li>Choose the invoice type (e.g. rent, deposit, a custom charge) and save.</li></ol><p>The tenant can pay it from their portal just like a rent invoice. Use this for deposits, one-off repairs you\'re passing on, or any ad-hoc charge.</p>'),
            $this->a('ow-billing', 'ow-invoice-types',
                'Invoice types',
                'Define the kinds of charges you raise.',
                '<h3>Invoice types</h3><p>Invoice types label what a charge is for (rent, deposit, penalty, a custom category, and so on). A sensible default set is provided automatically, and you can add your own.</p><ol><li>Go to <strong>Settings → invoice types</strong> (or add one inline while creating an invoice).</li><li>Add or edit a type and save.</li></ol><p>Types keep your reports meaningful — earnings can be understood by what they were for.</p>'),
            $this->a('ow-billing', 'ow-rent-charges',
                'Set rent & recurring charges',
                'Control the amount billed each cycle and any add-ons.',
                '<h3>The base rent</h3><p>A unit\'s rent is the core recurring charge and is set on the unit in <strong>All Unit</strong>. That figure drives each monthly invoice.</p><h3>Recurring settings</h3><p><strong>Billing Center → Recurring Setting</strong> is where recurring billing is switched on and configured for your account, so invoices generate reliably every cycle without you lifting a finger.</p>'),
            $this->a('ow-billing', 'ow-record-payment',
                'Record a manual payment / mark an invoice paid',
                'Log rent you received in cash or outside the system.',
                '<h3>Mark as paid</h3><ol><li>Go to <strong>Billing Center → All Invoices</strong>.</li><li>Open the invoice the tenant paid.</li><li>Record the payment / mark it paid, noting the method and amount.</li></ol><p>This keeps the tenant\'s balance and your reports accurate even when money came to you directly (e.g. cash). Online payments are recorded automatically when the tenant pays in their portal.</p>'),
            $this->a('ow-billing', 'ow-receipts',
                'Receipts',
                'Where receipts come from and how tenants get them.',
                '<h3>Receipts</h3><p>When an invoice is paid, a receipt is available against it. Tenants can view and download their receipts from their portal, and you can view them from the invoice in <strong>All Invoices</strong>.</p>'),
            $this->a('ow-billing', 'ow-expenses',
                'Track expenses',
                'Log what you spend so your profit is accurate.',
                '<h3>Expenses</h3><ol><li>Go to <strong>Billing Center → Expenses</strong>.</li><li>Add an expense with its type, amount and date (attach a note if useful).</li></ol><p>Expense types are configurable in <strong>Settings → expense types</strong>. Your <strong>Loss / Profit</strong> report uses income minus these expenses, so keeping them current makes that report trustworthy.</p>'),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 5 · GETTING PAID — WALLET, M-PESA, WITHDRAWALS
    // ══════════════════════════════════════════════════════════════════════
    private function payments(): array
    {
        return [
            $this->a('ow-payments', 'ow-how-tenants-pay',
                'How your tenants pay you',
                'The two payment paths, decided by your pricing mode.',
                '<h3>It depends on your mode</h3><ul><li><strong>Subscription / Free mode</strong> — the tenant pays into <em>your own</em> connected payment account. Money reaches you directly; the system records the payment.</li><li><strong>Transaction mode</strong> — the tenant pays via an M-Pesa prompt into the Centresidence account. A 1% fee is taken and the rest lands in your in-app <strong>wallet</strong> to withdraw.</li></ul><p>Either way the tenant just opens the invoice in their portal and pays. See <em>Subscription vs Transaction mode</em> to understand the trade-off, and <em>Set up your payment gateway</em> for subscription mode.</p>'),
            $this->a('ow-payments', 'ow-transaction-fee',
                'The 1% transaction fee explained',
                'What the fee is, when it applies, and when it doesn\'t.',
                '<h3>When the 1% applies</h3><p>The 1% fee applies <strong>only in transaction mode</strong>, because that\'s when rent is collected through the Centresidence account. It\'s taken at the moment of payment, and your wallet is credited with the remaining 99%.</p><h3>When it doesn\'t</h3><p>In subscription or free mode, rent goes straight to your own account and never passes through Centresidence — so there\'s no 1% at all (you\'re on a monthly plan instead).</p>'),
            $this->a('ow-payments', 'ow-wallet',
                'Your wallet explained',
                'The in-app balance where transaction-mode rent and other income collect.',
                '<h3>What the wallet is</h3><p>In transaction mode, your net rent (and net income from tokens and marketplace sales) is credited to your <strong>Wallet</strong> as a withdrawable balance. Open <strong>Wallet</strong> to see the balance and a line-by-line history of what came in.</p><h3>Reading a rent credit</h3><p>Each rent payment shows as a single entry with its breakdown: gross rent, the 1% fee, any financing/infrastructure deductions, and the net that was added. Tap an entry to see the detail.</p>'),
            $this->a('ow-payments', 'ow-withdraw',
                'Withdraw your money to M-Pesa',
                'Move your wallet balance to your phone.',
                '<h3>Request a withdrawal</h3><ol><li>Go to <strong>Wallet</strong> and choose <strong>Withdraw</strong>.</li><li>Enter the amount (up to your available balance) and confirm your payout number.</li><li>Submit the request.</li></ol><h3>What happens</h3><p>Your request is reviewed and then paid out to M-Pesa. The money stays reserved while it\'s in flight and only leaves your balance once the payout is confirmed — if a payout ever fails, the amount is returned to your balance automatically.</p>'),
            $this->a('ow-payments', 'ow-rent-deductions',
                'Rent & deductions (financing and infrastructure)',
                'See exactly what came out of each rent payment and why.',
                '<h3>Where deductions show</h3><p>If you\'re financing infrastructure, part of your rent may go toward the facility repayment and running (infrastructure) costs before the rest lands in your wallet. <strong>Financing → Rent &amp; deductions</strong> breaks down every rent payment: rent in, module/infrastructure costs, financing repayment, any overdue recovery, and the net you kept.</p><h3>A fair ceiling</h3><p>Total deductions from a single rent payment are capped, so you always keep a meaningful share of the rent even when several costs compete for it.</p>'),
            $this->a('ow-payments', 'ow-payment-gateway',
                'Set up your payment gateway (subscription mode)',
                'Connect the account tenants pay into when you\'re not in transaction mode.',
                '<h3>Connect your gateway</h3><ol><li>Go to <strong>Settings → gateway</strong>.</li><li>Enter the details for your payment method so tenant payments land in your account.</li><li>Save.</li></ol><p>This is what makes direct tenant payments possible in subscription/free mode. In transaction mode you don\'t need this — rent flows through Centresidence into your wallet instead.</p>'),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 6 · MARKETPLACE (MY SHOP)
    // ══════════════════════════════════════════════════════════════════════
    private function shop(): array
    {
        return [
            $this->a('ow-shop', 'ow-list-product',
                'List a product for sale',
                'Offer goods to your tenants through your shop.',
                '<h3>Add a product</h3><ol><li>Go to <strong>My Shop</strong> and add a product.</li><li>Give it a name, description, price, a photo and a category.</li><li>Publish it.</li></ol><p>Tenants can browse and buy your products from their portal. Payment works like rent: in transaction mode the net proceeds land in your wallet.</p>'),
            $this->a('ow-shop', 'ow-product-orders',
                'Manage product orders',
                'Track and progress the orders tenants place.',
                '<h3>Orders</h3><p><strong>My Shop → Product Orders</strong> lists every order and its status. From here you confirm an order, mark it complete, or handle a cancellation.</p><p>Payment status and fulfilment status are separate: an order can be <em>paid</em> while still being <em>dispatched</em> and <em>delivered</em> — so you always know both where the money is and where the goods are.</p>'),
            $this->a('ow-shop', 'ow-dispatch',
                'Dispatch & deliver an order',
                'Move a paid order from dispatched to delivered — yourself or via a caretaker.',
                '<h3>Fulfilment steps</h3><p>A paid order moves <strong>awaiting dispatch → dispatched → delivered</strong>. Delivery completes the order. Your on-site <strong>caretaker (maintainer)</strong> can handle these steps, or you can. Each step notifies the tenant automatically.</p><h3>Who dispatches</h3><p>Use <strong>Product Orders → dispatch settings</strong> to control how dispatch is handled for your shop. Fulfilment never changes the payment status, so money and delivery stay independent.</p>'),
            $this->a('ow-shop', 'ow-refunds',
                'Cancellations & refunds',
                'Cancel an order and confirm a refund when needed.',
                '<h3>Cancel / refund</h3><ol><li>Open the order in <strong>Product Orders</strong>.</li><li>Cancel it, and where a refund is due, confirm the refund.</li></ol><p>Confirming a refund reverses the sale\'s money correctly, including any commission that was taken, so your balances stay accurate.</p>'),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 7 · FINANCING
    // ══════════════════════════════════════════════════════════════════════
    private function financing(): array
    {
        return [
            $this->a('ow-financing', 'ow-financing-what',
                'What financing is & why it needs transaction mode',
                'Fund smart meters and locks now, repay from rent over time.',
                '<h3>The idea</h3><p>Financing lets you deploy smart infrastructure — water and gas meters, smart locks — without paying the full cost upfront. A finance partner funds it, and you repay over time. That infrastructure also turns utilities into prepaid, recurring income.</p><h3>Why transaction mode</h3><p>Repayment is deducted from your rent <strong>at source</strong> — safely and automatically — so the rent has to flow through the system. That\'s why financing requires <strong>transaction mode</strong>; you\'ll be prompted to switch when you apply.</p>'),
            $this->a('ow-financing', 'ow-financing-apply',
                'Browse offers & apply for financing',
                'Pick a module, see the live cost breakdown, and submit.',
                '<h3>Apply</h3><ol><li>Go to <strong>Financing → Browse offers</strong>.</li><li>Choose a module and the property/units to deploy on.</li><li>Review the live breakdown — hardware, installation, any platform fee, your optional down-payment, the amount financed and the estimated monthly repayment.</li><li>Submit. If you\'re not already in transaction mode, you\'ll switch as part of applying.</li></ol><p>The finance partner reviews your application against their rules and, once approved and funded, the equipment is installed.</p>'),
            $this->a('ow-financing', 'ow-financing-downpayment',
                'Down payment, partial financing & self-finance',
                'Reduce what you borrow, or pay for it yourself.',
                '<h3>Pay some upfront</h3><p>On the application you can add a <strong>down-payment</strong> and finance only the remainder, which lowers your monthly repayment. Buttons let you quickly set a half down-payment or finance the whole thing.</p><h3>Self-finance</h3><p>Prefer not to borrow at all? Choose <strong>self-finance</strong> to pay for the deployment yourself and skip the facility entirely — you still get the smart infrastructure and its recurring income.</p>'),
            $this->a('ow-financing', 'ow-facility-repayment',
                'Your facility & how repayment works',
                'Track a live loan and understand each deduction.',
                '<h3>My financing</h3><p><strong>Financing → My financing</strong> shows your live facilities: amount financed, what\'s outstanding, the schedule and progress. Repayment is taken from your rent each cycle, up to a set share, so it never swallows all of your rent.</p><h3>Interest &amp; costs</h3><p>The partner sets the rate and terms. Running (infrastructure) costs for the equipment are billed separately and also recovered from rent. See <em>Rent &amp; deductions</em> to view every deduction line by line.</p>'),
            $this->a('ow-financing', 'ow-settle-early',
                'Settle early or accelerate repayment',
                'Clear a facility ahead of schedule if your partner allows it.',
                '<h3>Settle early</h3><p>If your finance partner permits it, you can pay a facility off early. Open the facility in <strong>My financing</strong> and choose <strong>Settle early</strong> — you\'ll see an itemised payoff (outstanding principal, interest to date, any early-settlement fee) and can pay it.</p><h3>Accelerate</h3><p>Where enabled, <strong>Accelerate</strong> lets more of each rent payment go toward the facility so it clears faster. Both options depend on your partner\'s terms.</p>'),
            $this->a('ow-financing', 'ow-infra-bill',
                'Pay an infrastructure bill',
                'Settle the running costs of your smart equipment.',
                '<h3>Infrastructure costs</h3><p>Smart equipment has ongoing software and gateway costs. These are normally recovered from rent, but if a bill is outstanding you can settle it directly from the financing area using <strong>pay</strong> on the bill. Keeping these current keeps your equipment fully in service.</p>'),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 8 · TENANT SCREENING
    // ══════════════════════════════════════════════════════════════════════
    private function screening(): array
    {
        return [
            $this->a('ow-screening', 'ow-screen-run',
                'Screen a tenant: run a lookup',
                'Check an applicant\'s objective rental payment record before you sign.',
                '<h3>Run a screening</h3><ol><li>Go to <strong>Tenants → Screen a Tenant</strong>.</li><li>Enter the person\'s phone number and run the lookup.</li><li>You get their objective payment record and score summary.</li></ol><p>The record is built from how they\'ve actually paid rent across the system — factual behaviour, not opinions — so it\'s a fair, consistent signal when deciding whether to sign someone.</p>'),
            $this->a('ow-screening', 'ow-screen-score',
                'What the score & band mean',
                'How to read the 0–100 rental score.',
                '<h3>The score</h3><p>The score is a transparent 0–100 rating of rental payment behaviour, with a band (e.g. good/fair) and a breakdown of the factors behind it — on-time rate, lateness, arrears and tenure. It\'s <strong>explainable</strong>: you can see <em>why</em> it is what it is.</p><h3>Thin files</h3><p>A tenant with very little history sits near a neutral middle rather than being scored too high or too low — so a brand-new tenant is never unfairly branded either way.</p>'),
            $this->a('ow-screening', 'ow-screen-credits',
                'Screening credits, the free allowance & miss-is-free',
                'What a lookup costs and when it\'s free.',
                '<h3>Coverage</h3><p>Depending on your plan, screening may be included, or drawn from a small monthly free allowance and then from purchased <strong>screening credits</strong>. You can top up credits when you need more.</p><h3>You\'re never charged for a miss</h3><p>If there\'s no record anywhere in the system for that number, the lookup is <strong>free</strong> — you only ever spend a credit when there\'s an actual record to see.</p>'),
            $this->a('ow-screening', 'ow-screen-disputes',
                'When a tenant disputes their record',
                'How disputes work and your part in them.',
                '<h3>Fairness &amp; disputes</h3><p>Because the record is about a real person, tenants can see that they were screened and can raise a <strong>dispute</strong> if they believe something is wrong. Disputes are handled transparently, and you may be asked to confirm details of a tenancy so the objective record stays accurate for everyone.</p>'),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 9 · AGREEMENTS & E-SIGNING
    // ══════════════════════════════════════════════════════════════════════
    private function agreements(): array
    {
        return [
            $this->a('ow-agreements', 'ow-agreement-template',
                'Set up your agreement template',
                'Create a reusable lease you can send to any tenant.',
                '<h3>Templates</h3><p>Go to <strong>Settings → Agreement</strong> (or the Agreements area) to set up a reusable template. A sensible starter template is provided automatically, so you can send straight away and refine later.</p><ol><li>Open your template and edit its wording, or upload your own agreement PDF.</li><li>Save it — you can reuse it for every tenant.</li></ol>'),
            $this->a('ow-agreements', 'ow-agreement-send',
                'Send an agreement to a tenant',
                'Fire off a lease for the tenant to sign in their portal.',
                '<h3>Send it</h3><ol><li>From the Agreements area, choose <strong>Send</strong> and pick the tenant.</li><li>The template auto-fills their details; review and send.</li></ol><p>The tenant receives it in their portal to review and sign. Sending uses your free monthly allowance first, then a purchased <strong>agreement credit</strong> — see <em>Agreement credits</em>.</p>'),
            $this->a('ow-agreements', 'ow-agreement-sign',
                'How signing works (OTP + certificate)',
                'Secure, verifiable in-portal signing that replaced paper and DocuSign.',
                '<h3>The tenant signs</h3><p>The tenant opens the agreement, requests a one-time code by SMS, enters it and signs — all in their portal. On signing, a <strong>certified, tamper-evident PDF</strong> is produced with a verification code, and every step is recorded in an audit trail.</p><h3>Verify later</h3><p>Anyone can confirm a signed agreement is genuine using its verification code, so the document stands on its own without a third-party service.</p>'),
            $this->a('ow-agreements', 'ow-agreement-credits',
                'Agreement credits',
                'Buy credits to send agreements beyond your free allowance.',
                '<h3>Credits</h3><p>Each account gets a small free monthly allowance of agreement sends. Beyond that, top up <strong>agreement credits</strong> (they don\'t expire). Some plans include unlimited sending. Buy credits from the Agreements area when you need more; one credit covers one send.</p>'),
        ];
    }
    // ══════════════════════════════════════════════════════════════════════
    // 10 · LISTINGS (HOUSE HUNT)
    // ══════════════════════════════════════════════════════════════════════
    private function listings(): array
    {
        return [
            $this->a('ow-listings', 'ow-publish-listing',
                'Publish a vacant unit (for rent or sale)',
                'Advertise an empty unit on House Hunt to find a tenant or buyer.',
                '<h3>Create a listing</h3><ol><li>Go to <strong>My Listing → Upload List</strong>.</li><li>Pick the vacant unit (or property, for a sale), choose whether it\'s <strong>to rent</strong> or <strong>for sale</strong>, and set the price.</li><li>Add a strong description and photos, then publish.</li></ol><p>Your listing appears publicly on House Hunt where seekers can browse it and enquire or apply.</p>'),
            $this->a('ow-listings', 'ow-manage-listings',
                'Manage your listings & photos',
                'Edit, refresh or take down a listing.',
                '<h3>Your listings</h3><p><strong>My Listing → All List</strong> shows everything you\'ve published. Open any listing to edit its details, update photos and price, or unpublish it once the unit is taken.</p><p>Keep photos current — the same images you use on the unit are reused here, so good unit photos pay off twice.</p>'),
            $this->a('ow-listings', 'ow-listing-contacts',
                'Enquiries & applications from listings',
                'Handle interest from people who saw your listing.',
                '<h3>Contacts</h3><p><strong>My Listing → Contact List</strong> collects enquiries from your listings. When someone applies to rent, they also appear under <strong>Tenants → Tenant Applications</strong>, where you can assign them to the unit and onboard them automatically. See <em>Approve applicants</em>.</p>'),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 11 · MAINTENANCE & CARETAKERS
    // ══════════════════════════════════════════════════════════════════════
    private function maintenance(): array
    {
        return [
            $this->a('ow-maintenance', 'ow-add-maintainer',
                'Add a caretaker (maintainer)',
                'Give an on-site person their own login to help run a property.',
                '<h3>Add a caretaker</h3><ol><li>Go to <strong>Maintenance → Maintainers</strong> and add one.</li><li>Enter their name and contact details and save.</li></ol><p>They get their own login to handle maintenance requests and, if you use the shop, to dispatch and deliver orders on your behalf — without needing your full owner access.</p>'),
            $this->a('ow-maintenance', 'ow-maintenance-requests',
                'Handle maintenance requests',
                'Track repairs from reported to resolved.',
                '<h3>Requests</h3><p>Tenants raise maintenance requests from their portal; they land in <strong>Maintenance → Maintenance Request</strong>. Open a request to review it, assign your caretaker, update its status as work progresses, and close it when done. The tenant can follow the status from their side.</p>'),
            $this->a('ow-maintenance', 'ow-tickets-topics',
                'Tickets & topics',
                'Support-style conversations, organised by topic.',
                '<h3>Tickets</h3><p><strong>Maintenance → Tickets</strong> handles back-and-forth queries. Tickets are organised by <strong>topic</strong>; a default set of topics is provided, and you can manage your own in <strong>Settings → ticket topics</strong>. Reply to a ticket to keep the conversation in one place.</p>'),
            $this->a('ow-maintenance', 'ow-notice-board',
                'Post to the notice board',
                'Broadcast a message to your tenants.',
                '<h3>Notice board</h3><ol><li>Go to <strong>Maintenance → Notice Board</strong>.</li><li>Write your notice and publish it.</li></ol><p>Tenants see your notices in their portal — handy for water shut-offs, reminders, or building-wide announcements.</p>'),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 12 · SMS CREDITS & NOTIFICATIONS
    // ══════════════════════════════════════════════════════════════════════
    private function sms(): array
    {
        return [
            $this->a('ow-sms', 'ow-sms-buy',
                'SMS credits: buy & check your balance',
                'Top up the credits that power text messages to tenants.',
                '<h3>SMS credits</h3><p>Text messages (like sending a tenant their login by SMS) draw on <strong>SMS credits</strong>. See your balance and top up in <strong>Billing Center → SMS Credits</strong>.</p><ol><li>Open SMS Credits.</li><li>Choose a quantity and pay.</li></ol><p>Your balance keeps both any monthly granted credits and purchased credits; granted ones are used first so you never lose credits you paid for at renewal.</p>'),
            $this->a('ow-sms', 'ow-sms-what-sends',
                'What sends an SMS',
                'Know when a credit is used.',
                '<h3>When credits are used</h3><p>A credit is spent when the system sends a tenant a text — for example delivering login credentials, or notifications you\'ve chosen to send by SMS. Email notifications don\'t use SMS credits. If you\'re ever low, the system lets you know so a message isn\'t missed.</p>'),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 13 · REPORTS
    // ══════════════════════════════════════════════════════════════════════
    private function reports(): array
    {
        return [
            $this->a('ow-reports', 'ow-report-earning',
                'Earnings & Loss / Profit reports',
                'See what you\'ve earned and what you\'ve kept.',
                '<h3>Earnings</h3><p><strong>Report → Earning</strong> summarises rent and other income over a period. <strong>Loss / Profit By Month</strong> takes that income and subtracts your recorded <strong>expenses</strong>, so it\'s only as accurate as the expenses you log — see <em>Track expenses</em>.</p><p>Use the date filters to focus on a month, quarter or year.</p>'),
            $this->a('ow-reports', 'ow-report-occupancy-lease-tenant',
                'Occupancy, Lease & Tenant reports',
                'Understand how full you are and who\'s renting.',
                '<h3>The other reports</h3><ul><li><strong>Occupancy</strong> — how many of your units are occupied vs vacant.</li><li><strong>Lease</strong> — active tenancies and their terms.</li><li><strong>Tenant</strong> — a view across your tenants.</li></ul><p>Together they give you a quick read on portfolio health at any time.</p>'),
            $this->a('ow-reports', 'ow-report-export',
                'Export or print a report',
                'Take a report out as a PDF.',
                '<h3>Export</h3><p>Most reports offer an <strong>export</strong> or <strong>print</strong> action to produce a PDF you can save or share (for example with an accountant). Set your date range first, then export so the file matches what\'s on screen.</p>'),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 14 · SETTINGS, PROFILE & SUBSCRIPTION
    // ══════════════════════════════════════════════════════════════════════
    private function settings(): array
    {
        return [
            $this->a('ow-settings', 'ow-profile-password',
                'Update your profile & change your password',
                'Keep your details current and your account secure.',
                '<h3>Profile</h3><p><strong>Profile → My Profile</strong> is where you update your name, contact details and profile photo. Large photos are shrunk in your browser before upload, so they save quickly.</p><h3>Password</h3><p><strong>Profile → Change Password</strong> lets you set a new password any time. Choose something strong and unique.</p>'),
            $this->a('ow-settings', 'ow-settings-currency-tax',
                'Currency, tax & expense/invoice types',
                'Set the defaults that shape your billing.',
                '<h3>Money settings</h3><ul><li><strong>Currency</strong> — the currency your amounts are shown in.</li><li><strong>Tax</strong> — configure any tax you apply to charges.</li><li><strong>Invoice types</strong> and <strong>Expense types</strong> — the categories behind your invoices and expenses; sensible defaults are provided and you can add your own.</li></ul><p>Find these under <strong>Settings</strong>. Getting them right up front keeps every invoice and report consistent.</p>'),
            $this->a('ow-settings', 'ow-settings-email',
                'Email & email templates',
                'Control the emails your account sends.',
                '<h3>Mail settings</h3><p>Under <strong>Settings → Mail</strong> you can manage email configuration and the <strong>email templates</strong> used for messages sent from your account, so what your tenants receive reads the way you want.</p>'),
            $this->a('ow-settings', 'ow-subscription',
                'Your subscription & plan',
                'See your plan, switch pricing mode, and understand your monthly billing.',
                '<h3>My Subscription</h3><p><strong>My Subscription</strong> shows your current plan and pricing mode. From here you can move between <strong>subscription</strong> and <strong>transaction</strong> mode (remember: financing needs transaction mode, and you can\'t leave it while a facility is active).</p><h3>What you pay</h3><p>On a subscription plan you see your plan price plus any smart-module running costs bundled into a monthly figure. In transaction mode there\'s no subscription — you pay the 1% per rent payment instead. See <em>Subscription vs Transaction mode</em>.</p>'),
        ];
    }
}
