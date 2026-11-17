<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\AcademyModule;
use App\Models\Lead;
use App\Models\Owner;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * The ongoing affiliate engagement cadence — the automated second-and-onward touch after the welcome,
 * for affiliates who signed up but haven't reached their first real win. Mirrors OwnerSetupFollowupService:
 * a bounded, stage-aware CADENCE that nudges the affiliate's CURRENT sticking point and advances with
 * them, so it anticipates the next place they stall and re-engages the two "notorious" idle groups.
 *
 * Stages (first that applies):
 *   - academy:    not certified yet → finish training (names progress, e.g. "9 of 15 modules done").
 *   - first_lead: certified but ZERO leads → add your first landlord (the quiet, certified-and-silent group).
 *   - convert:    certified, has leads, but NO converted/attributed owner → turn a lead into a signed owner
 *                 (the dead-lead group).
 * It STOPS once the affiliate has a real win — an attributed owner (owners.affiliate_id = this affiliate).
 *
 * Bounded like the owner cadence: a post-welcome grace, a spacing interval, and a per-stage cap; when the
 * affiliate advances a stage the cadence RESETS for a fresh sequence. Delivered as a reply INTO the welcome
 * support thread (so it reads as us checking in, and they can reply + it's emailed), falling back to a fresh
 * thread. State on affiliates: engagement_followup_count/_step/_at. $force sends now regardless of gating.
 */
class AffiliateEngagementService
{
    public const GRACE_DAYS   = 3; // give a newly-welcomed affiliate room before the first follow-up
    public const SPACING_DAYS = 7; // minimum gap between follow-ups
    public const MAX_PER_STEP = 3; // never more than this many nudges about the SAME stuck stage

    /** Returns true if a follow-up was sent, false if skipped (not eligible / not due / already active). */
    public static function send(User $user, bool $force = false): bool
    {
        $affiliate = Affiliate::where('user_id', $user->id)->first();

        // Only follow up affiliates we've already welcomed (the welcome is touch 1).
        if (! $affiliate || ! $affiliate->welcomed_at) {
            return false;
        }

        try {
            $stage = self::currentStage($affiliate);
            if ($stage === null) {
                return false; // active (has an attributed owner) → nothing to nudge
            }

            $sameStep = $affiliate->engagement_followup_step === $stage;
            if (! $force && ! self::isDue($affiliate, $sameStep)) {
                return false;
            }

            $admin = User::where('role', USER_ROLE_ADMIN)->first();
            if (! $admin) {
                return false;
            }

            $stepCount = $sameStep ? ((int) $affiliate->engagement_followup_count + 1) : 1;

            $firstName = trim((string) ($user->first_name ?? '')) ?: __('there');
            $appName   = getOption('app_name') ?: 'Centresidence';
            $body      = self::body($firstName, $appName, $stage, $stepCount, $affiliate);

            $ticket  = self::findWelcomeThread($user);
            $support = app(SupportTicketService::class);

            if ($ticket) {
                $support->reply($ticket, $admin, $body, true);
            } else {
                $support->adminStartConversation($user, $admin, __('A quick nudge to get you earning'), $body);
            }

            $affiliate->forceFill([
                'engagement_followup_step'  => $stage,
                'engagement_followup_count' => $stepCount,
                'engagement_followup_at'    => now(),
            ])->save();

            return true;
        } catch (\Throwable $e) {
            Log::warning('Affiliate engagement follow-up failed for user ' . $user->id . ': ' . $e->getMessage());
            return false;
        }
    }

    /** The affiliate's current sticking point, or null if they've reached a real win (attributed owner). */
    public static function currentStage(Affiliate $affiliate): ?string
    {
        // A converted owner attributed to this affiliate = a real win → active, stop nudging.
        if (Owner::where('affiliate_id', $affiliate->id)->exists()) {
            return null;
        }

        if ($affiliate->academy_status !== 'completed') {
            return 'academy';
        }

        // Certified. Leads key on the affiliate's USER id (not the affiliate row id).
        if (! Lead::where('affiliate_id', $affiliate->user_id)->exists()) {
            return 'first_lead';
        }

        return 'convert'; // has leads but none converted to an owner yet
    }

    /** Cadence gate: respect the post-welcome grace, the spacing interval, and the per-stage cap. */
    private static function isDue(Affiliate $affiliate, bool $sameStep): bool
    {
        $welcomedAt = $affiliate->welcomed_at ? \Illuminate\Support\Carbon::parse($affiliate->welcomed_at) : null;
        $lastSentAt = $affiliate->engagement_followup_at ? \Illuminate\Support\Carbon::parse($affiliate->engagement_followup_at) : null;

        if ($welcomedAt && $welcomedAt->gt(now()->subDays(self::GRACE_DAYS))) {
            return false;
        }
        if ($lastSentAt && $lastSentAt->gt(now()->subDays(self::SPACING_DAYS))) {
            return false;
        }
        if ($sameStep && (int) $affiliate->engagement_followup_count >= self::MAX_PER_STEP) {
            return false;
        }
        return true;
    }

    /** The affiliate's most recent still-open support thread that WE started (the welcome), if any. */
    private static function findWelcomeThread(User $user): ?SupportTicket
    {
        return SupportTicket::where('requester_user_id', $user->id)
            ->where('status', '!=', SupportTicket::STATUS_CLOSED)
            ->with(['replies' => fn ($q) => $q->oldest()])
            ->latest('last_reply_at')
            ->get()
            ->first(fn ($t) => (bool) optional($t->replies->first())->is_admin === true);
    }

    /** Warm, stage-aware body; tone escalates with the per-stage count. */
    private static function body(string $firstName, string $appName, string $stage, int $stepCount, Affiliate $affiliate): string
    {
        $guidance = [
            'academy'    => self::academyLine($affiliate),
            'first_lead' => __("You're certified — nice work! The next step is your first landlord: add one you already know (even your own) as a lead. It's the easiest first win, and it's where your earnings start."),
            'convert'    => __("You've got leads in — now let's turn one into a signed-up landlord. Reach out, help them create their account and add a property, and your commission kicks in. Tell us which lead and we'll help you close it."),
        ];
        $line = $guidance[$stage] ?? __("Let's take the next step toward your first earnings.");

        $opener = $stepCount <= 1
            ? __(":name, just checking in. 👋\n\nYou signed up to earn with :app and I didn't want you to get stuck on the way there.\n\n", ['name' => $firstName, 'app' => $appName])
            : __(":name, still here to help you earn. 🙌\n\nYou're close — I'd hate for you to leave money on the table over one step.\n\n", ['name' => $firstName, 'app' => $appName]);

        $closer = __("\n\nJust reply here and we'll help you personally — even hop on a quick call. Let's get you your first payout.");

        return $opener . $line . $closer;
    }

    /** Academy nudge, made specific with real module progress when available. */
    private static function academyLine(Affiliate $affiliate): string
    {
        try {
            if (Schema::hasTable('affiliate_academy_progress')) {
                $total = (int) AcademyModule::count();
                $done  = (int) DB::table('affiliate_academy_progress')
                    ->where('affiliate_id', $affiliate->id)
                    ->whereNotNull('completed_at')
                    ->count();
                if ($total > 0 && $done > 0) {
                    return __("You're :done of :total modules through the academy — so close! Finish the last few to get certified and unlock adding landlords and earning.", ['done' => $done, 'total' => $total]);
                }
            }
        } catch (\Throwable $e) {
            // fall through to the generic line
        }

        return __("Your first step is finishing the academy — it's short, and getting certified unlocks everything: adding landlords, tracking referrals and earning. Jump back in and knock it out.");
    }
}
