<?php

namespace Database\Seeders;

use App\Models\FeatureAnnouncement;
use App\Models\KnowledgeBaseArticle;
use Illuminate\Database\Seeder;

/**
 * Feature announcements for property owners — the in-account "what's new" cards. Idempotent
 * (updateOrCreate by title); runs after OwnerKnowledgeBaseSeeder so the linked article exists.
 */
class OwnerAnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        // Inline M-Pesa setup (2026-11-25) — the get-paid step now takes a Till/Paybill right on the
        // dashboard instead of sending owners into Settings. Links to the refreshed "how you get paid"
        // article so anyone who wants the full walkthrough has it.
        $getPaidArticle = KnowledgeBaseArticle::where('slug', 'ow-payment-gateway')->first();

        FeatureAnnouncement::updateOrCreate(
            ['title' => 'Set up how you get paid — now right on your dashboard'],
            [
                'body'          => 'Getting paid is now a 10-second step. On your dashboard checklist, open '
                    . '“Set up how you get paid”, tap Set up here, choose Till or Paybill, type your number and '
                    . 'press Save & activate — no more digging through Settings. You only ever enter your own '
                    . 'M-Pesa Till or Paybill; rent is then paid straight to it. You can still change it anytime '
                    . 'in Settings → gateway.',
                'icon'          => '💸',
                'kb_article_id' => $getPaidArticle?->id,
                'link_url'      => null,
                'link_label'    => 'How to set up getting paid',
                'audience'      => (string) USER_ROLE_OWNER,
                'is_active'     => true,
                'published_at'  => now(),
            ]
        );
    }
}
