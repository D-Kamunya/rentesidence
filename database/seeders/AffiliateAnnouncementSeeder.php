<?php

namespace Database\Seeders;

use App\Models\FeatureAnnouncement;
use App\Models\KnowledgeBaseArticle;
use Illuminate\Database\Seeder;

/**
 * Feature announcements for affiliates — the "what's changed" cards that surface on the dashboard
 * and pull an affiliate back into the Knowledge Base.
 *
 * Needed specifically because affiliates are mid-academy (a leading one is already past the lead
 * modules), so a behavioural change to a module they've PASSED won't be re-read silently. An
 * announcement is how they learn about it. Idempotent (updateOrCreate by title); runs after the
 * KB seeder so the linked article exists.
 */
class AffiliateAnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $onboardArticle = KnowledgeBaseArticle::where('slug', 'af-onboard-your-owner')->first();

        FeatureAnnouncement::updateOrCreate(
            ['title' => 'New: set owners up on Free in one click'],
            [
                'body'          => 'You can now put a lead straight onto a free account from the lead page — no waiting on admin. '
                    . 'Open a lead and use “Set up on Free” on the Sales Actions card: it invites the owner to activate by '
                    . 'choosing their own password, attributes the account to you automatically, and shows you whether they’ve '
                    . 'accepted yet. A trial is now the optional step — only for a lead who specifically wants to try the paid '
                    . 'features first. Read how it works and how to follow up.',
                'icon'          => '🎉',
                'kb_article_id' => $onboardArticle?->id,
                'link_url'      => null,
                'link_label'    => 'How to set an owner up on Free',
                'audience'      => (string) USER_ROLE_AFFILIATE,
                'is_active'     => true,
                'published_at'  => now(),
            ]
        );

        // Earnings uplift (2026-10-28 sitting) — a POSITIVE announcement so the first cohort hears
        // their terms just got better. Quotes the LIVE config so it's always accurate even if the
        // numbers are tuned again later.
        $firstRate   = (int) (getOption('FIRST_TIME_COMMISSION_RATE') ?: 40);
        $recurRate   = (int) (getOption('RECURRING_COMMISSION_RATE') ?: 10);
        $recurMonths = (int) (getOption('RECURRING_COMMISSION_MONTHS') ?: 24);

        FeatureAnnouncement::updateOrCreate(
            ['title' => 'Good news — we’ve increased your earnings'],
            [
                'body'          => 'We’ve made your commission more rewarding. Your first-time commission is now '
                    . $firstRate . '% of our take on an owner’s first payment (up from 30%), and every commission line '
                    . 'now runs for ' . $recurMonths . ' months per owner (up from 12) — so each owner you bring on keeps '
                    . 'paying you for twice as long. The recurring rate stays at ' . $recurRate . '%. Nothing you need to do: '
                    . 'the new terms apply to your owners automatically, and your dashboard, academy and earnings calculator '
                    . 'already show the updated numbers.',
                'icon'          => '💰',
                'kb_article_id' => null,
                'link_url'      => null,
                'link_label'    => null,
                'audience'      => (string) USER_ROLE_AFFILIATE,
                'is_active'     => true,
                'published_at'  => now(),
            ]
        );
    }
}
