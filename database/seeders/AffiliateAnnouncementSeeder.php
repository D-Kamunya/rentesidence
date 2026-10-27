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
    }
}
