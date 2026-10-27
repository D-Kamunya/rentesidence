<?php

namespace App\Services;

use App\Jobs\Mail\SendOutreachMail;
use App\Models\AffiliateApplication;
use App\Models\OutreachCampaign;
use App\Models\OutreachRecipient;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * The engine behind in-system outreach campaigns: parse a pasted list, send the CS-layout mail,
 * detect conversions by email-match (against the invited auto-provision), and run bounded follow-ups.
 * Keeps the controller thin and lets the scheduled command reuse the same conversion/follow-up logic.
 */
class OutreachService
{
    /**
     * Parse a pasted recipient blob into [['email'=>, 'first_name'=>], ...].
     * Accepts one per line as "email", "email, First Name", "email<tab>Name", or "email Name".
     * Invalid emails are skipped; duplicates (case-insensitive) collapse to the first.
     */
    public function parseRecipients(string $raw): array
    {
        $out  = [];
        $seen = [];
        foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            // email is the first token; the remainder (after comma/tab/space) is the name.
            if (preg_match('/^\s*([^\s,;]+@[^\s,;]+)\s*[,;\t ]*(.*)$/', $line, $m)) {
                $email = strtolower(trim($m[1]));
                $name  = trim($m[2]) ?: null;
                if (! filter_var($email, FILTER_VALIDATE_EMAIL) || isset($seen[$email])) {
                    continue;
                }
                $seen[$email] = true;
                $out[] = ['email' => $email, 'first_name' => $name];
            }
        }
        return $out;
    }

    /** Create the recipient rows for a campaign (idempotent per email via the unique index). */
    public function addRecipients(OutreachCampaign $campaign, array $parsed): int
    {
        $added = 0;
        foreach ($parsed as $p) {
            $r = OutreachRecipient::firstOrCreate(
                ['campaign_id' => $campaign->id, 'email' => $p['email']],
                ['first_name' => $p['first_name'], 'status' => OutreachRecipient::STATUS_PENDING]
            );
            if ($r->wasRecentlyCreated) {
                $added++;
            }
        }
        return $added;
    }

    /**
     * Queue the campaign to every PENDING recipient, PACED so it never bursts past the host's hourly
     * cap: each job is delayed so no more than send_per_hour go out per hour. The every-minute queue
     * worker picks up jobs as their delay matures, so hundreds trickle out over hours automatically —
     * no manual waves. Marks each recipient sent (queued) and returns the count queued.
     */
    public function send(OutreachCampaign $campaign): int
    {
        $perHour = max(1, (int) ($campaign->send_per_hour ?: OutreachCampaign::DEFAULT_SEND_PER_HOUR));
        $spacing = 3600 / $perHour; // seconds between each email

        // Continue the pace from anything already in flight for this campaign (re-send / added batch).
        $i = (int) $campaign->recipients()->where('status', OutreachRecipient::STATUS_SENT)->count();

        $count = 0;
        $campaign->recipients()->where('status', OutreachRecipient::STATUS_PENDING)
            ->chunkById(500, function ($recipients) use (&$count, &$i, $spacing) {
                foreach ($recipients as $r) {
                    SendOutreachMail::dispatch($r->id, false)
                        ->delay(now()->addSeconds((int) round($i * $spacing)));
                    $r->update(['status' => OutreachRecipient::STATUS_SENT, 'sent_at' => now()]);
                    $count++;
                    $i++;
                }
            });

        $campaign->update([
            'status'  => 'sent',
            'sent_at' => $campaign->sent_at ?: now(),
        ]);

        return $count;
    }

    /**
     * Mark recipients converted where an affiliate account (or an approved application) now exists for
     * their email. Scopes to one campaign or all. Returns the number newly marked.
     */
    public function reconcileConversions(?OutreachCampaign $campaign = null): int
    {
        $q = OutreachRecipient::whereIn('status', [OutreachRecipient::STATUS_SENT])
            ->whereNull('converted_at');
        if ($campaign) {
            $q->where('campaign_id', $campaign->id);
        }

        $marked = 0;
        $q->chunkById(300, function ($recipients) use (&$marked) {
            $emails = $recipients->pluck('email')->all();

            $affiliateEmails = User::whereIn('email', $emails)
                ->where('role', USER_ROLE_AFFILIATE)->pluck('email')
                ->map(fn ($e) => strtolower($e))->flip();
            $approvedEmails = AffiliateApplication::whereIn('email', $emails)
                ->where('status', AffiliateApplication::STATUS_APPROVED)->pluck('email')
                ->map(fn ($e) => strtolower($e))->flip();

            foreach ($recipients as $r) {
                $e = strtolower($r->email);
                if (isset($affiliateEmails[$e]) || isset($approvedEmails[$e])) {
                    $r->update([
                        'status'       => OutreachRecipient::STATUS_CONVERTED,
                        'converted_at' => now(),
                    ]);
                    $marked++;
                }
            }
        });

        return $marked;
    }

    /**
     * Send bounded follow-ups: recruitment recipients still 'sent' (not converted), whose last touch
     * is older than the campaign's follow_up_days and who are under max_follow_ups. Returns count sent.
     */
    public function processFollowUps(): int
    {
        // Both recruitment AND general campaigns follow up (the mail copy adapts by type); each is
        // still bounded per-campaign by max_follow_ups, so a general campaign set to 0 never nudges.
        $sent = 0;
        OutreachCampaign::where('status', 'sent')
            ->where('max_follow_ups', '>', 0)
            ->each(function (OutreachCampaign $campaign) use (&$sent) {
                $cutoff = Carbon::now()->subDays(max(1, (int) $campaign->follow_up_days));

                $campaign->recipients()
                    ->where('status', OutreachRecipient::STATUS_SENT)
                    ->whereNull('converted_at')
                    ->where('follow_up_count', '<', (int) $campaign->max_follow_ups)
                    ->where(function ($q) use ($cutoff) {
                        $q->whereNull('last_follow_up_at')->where('sent_at', '<=', $cutoff)
                          ->orWhere('last_follow_up_at', '<=', $cutoff);
                    })
                    ->chunkById(200, function ($recipients) use (&$sent) {
                        foreach ($recipients as $r) {
                            SendOutreachMail::dispatch($r->id, true);
                            $r->increment('follow_up_count');
                            $r->update(['last_follow_up_at' => now()]);
                            $sent++;
                        }
                    });
            });

        return $sent;
    }
}
