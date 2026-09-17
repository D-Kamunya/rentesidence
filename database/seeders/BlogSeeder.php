<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Starter blog posts tied to the "go-live-ready" story — so the marketing site launches
 * with real, on-brand content instead of test posts.
 *
 * ASSERTIVE ONCE, then admin-owned. The existing/live blog is all placeholder ("test post"),
 * so the FIRST run CLEARS the posts and their test engagement and installs this real set; a
 * sentinel option then blocks every later run so we never wipe real content or admin edits.
 * To intentionally re-seed, bump the sentinel key. Safe on every deploy.
 *
 *   php artisan db:seed --class=Database\\Seeders\\BlogSeeder
 */
class BlogSeeder extends Seeder
{
    private const SENTINEL = 'starter_blog_seeded_v1';

    public function run(): void
    {
        if (getOption(self::SENTINEL)) {
            return; // already seeded once — admin owns the blog now.
        }

        // Posts need an author; attribute to an admin, bail quietly on a bare install.
        $authorId = User::where('role', USER_ROLE_ADMIN)->value('id') ?? User::value('id');
        if (! $authorId) {
            return;
        }

        // First seed is assertive: the existing blog is placeholder. Clear posts + their test
        // engagement (comments/likes/shares/views) and the categories, then install the real set.
        DB::table('blog_post_views')->delete();
        DB::table('blog_post_likes')->delete();
        DB::table('blog_post_shares')->delete();
        DB::table('blog_comments')->delete();
        BlogPost::withTrashed()->forceDelete();
        BlogCategory::query()->delete();

        $category = BlogCategory::create(
            ['slug' => 'guides', 'name' => 'Guides',
             'description' => 'Practical guides to getting the most out of Centresidence.',
             'color' => '#185FA5', 'sort_order' => 1, 'is_active' => 1]
        );

        $posts = [
            [
                'title'   => 'Go live in a day: your first week on Centresidence',
                'excerpt' => 'You do not need weeks of setup to run your properties properly. Here is how to be collecting rent and sending receipts on day one.',
                'body'    => "<p>Getting started should not feel like a project. On Centresidence, most owners are set up and collecting rent the same day they sign in — no installers, no spreadsheets, no training course.</p>"
                    . "<h3>Day one</h3><p>Add your first property and its units, then add your tenants. Each tenant gets their own login by email and SMS, so they can pay and see their records straight away. Rent invoices generate on their own from the schedule you set — you do not chase anything.</p>"
                    . "<h3>Collecting rent</h3><p>Tenants pay from their phone, M-Pesa included, and the payment is matched to the right invoice automatically. A receipt goes out the moment it clears. You see what is paid, pending and overdue at a glance.</p>"
                    . "<h3>Growing into it</h3><p>The essentials are free. When you are ready, you can add smart meters, offer a marketplace to your tenants, or finance upgrades that repay from rent — but none of that is in your way on day one. Start simple, expand when it earns its place.</p>",
            ],
            [
                'title'   => 'Rent collection, the modern way',
                'excerpt' => 'Cash and follow-up messages do not scale. Here is what rent collection looks like when it is built for phones and M-Pesa.',
                'body'    => "<p>The hardest part of managing property has always been the money: who paid, who did not, and the awkward follow-up. Centresidence turns that into something quiet and automatic.</p>"
                    . "<p>Invoices generate on schedule. Tenants pay from their phone — M-Pesa built in — and every payment is reconciled to the right invoice without you touching it. Receipts are sent instantly, so no one asks \"did you get my rent?\" again.</p>"
                    . "<p>For the tenant, it builds something valuable too: a clean record of on-time payments — a portable rental score they carry to their next home. Paying rent stops being a chore and starts being a reputation.</p>"
                    . "<p>For you, it is one dashboard showing exactly where every unit stands, across every property, in real time.</p>",
            ],
            [
                'title'   => 'What the free tier gives you (and why it is enough to start)',
                'excerpt' => 'Free should not mean crippled. The Centresidence free tier runs the real work of managing a property — here is what is inside.',
                'body'    => "<p>We built the free tier to be genuinely useful, not a demo. It runs the real work: your properties and units, your tenants, rent invoicing, M-Pesa payments, receipts, and the records that matter.</p>"
                    . "<p>Tenants get their own app-like experience — installable to their phone, with their invoices, their rental score and home search — whether or not you ever pay a shilling.</p>"
                    . "<p>What you pay for later is scale and depth: higher limits, infrastructure financing, deeper tooling. But the moat is the free foundation — the day-to-day of running a property, done well, at no cost. Start there, and grow only when it pays you to.</p>",
            ],
        ];

        foreach ($posts as $p) {
            BlogPost::create([
                'blog_category_id' => $category->id,
                'author_id'        => $authorId,
                'title'            => $p['title'],
                'slug'             => Str::slug($p['title']),
                'status'           => 'published',
                'body'             => $p['body'],
                'excerpt'          => $p['excerpt'],
                'reading_time'     => max(1, (int) round(str_word_count(strip_tags($p['body'])) / 200)),
                'is_featured'      => 0,
                'published_at'     => Carbon::now(),
            ]);
        }

        setOption(self::SENTINEL, 1);
    }
}
