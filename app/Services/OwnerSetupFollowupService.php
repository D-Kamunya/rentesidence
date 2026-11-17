<?php

namespace App\Services;

use App\Models\Owner;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\OwnerAdvisor\OwnerSetupService;
use Illuminate\Support\Facades\Log;

/**
 * The ongoing "stuck in setup" follow-up — the automated second-and-onward touch after the welcome,
 * for owners who signed up but haven't finished setting up to collect rent. It's a bounded, stage-
 * aware CADENCE, not a one-off:
 *
 *   - It names the owner's FIRST unfinished step (property → units → tenant → get-paid → recurring)
 *     and points them at it — including the get-paid / rent-account step the email activation-nudge
 *     never reaches.
 *   - It repeats on a spacing interval, up to MAX_PER_STEP nudges per sticking point, so it persists
 *     without pestering.
 *   - When the owner ADVANCES to a new step, the cadence RESETS (count back to 1 on the new step), so
 *     each sticking point gets its own fresh sequence — i.e. it anticipates the next place they stall.
 *   - It stops the moment setup is complete, and only ever touches owners we've already welcomed.
 *
 * Delivered as a reply INTO the owner's welcome support thread (so it reads as us checking in, and
 * they can reply), falling back to a fresh thread. State on owners: setup_followup_count (per current
 * step), setup_followup_step (last step nudged), setup_followup_at (last sent — spacing). $force sends
 * now regardless of grace/spacing/cap (for a deliberate sweep of the current stuck cohort).
 */
class OwnerSetupFollowupService
{
    public const GRACE_DAYS    = 3; // give a newly-welcomed owner room before the first follow-up
    public const SPACING_DAYS  = 7; // minimum gap between follow-ups
    public const MAX_PER_STEP  = 3; // never more than this many nudges about the SAME stuck step

    /** Returns true if a follow-up was sent, false if skipped (not eligible / not due / already set up). */
    public static function send(User $user, bool $force = false): bool
    {
        $owner = Owner::where('user_id', $user->id)->first();

        // Only follow up owners we've already welcomed (the welcome is touch 1).
        if (! $owner || ! $owner->welcomed_at) {
            return false;
        }

        try {
            $checklist = app(OwnerSetupService::class)->checklist((int) $user->id);
            // No steps (bare install) or fully set up → nothing to nudge.
            if (($checklist['total'] ?? 0) === 0 || ! empty($checklist['complete'])) {
                return false;
            }

            // The first unfinished step is where they're stuck now — drives the message AND the cadence.
            $next = collect($checklist['steps'])->firstWhere('done', false);
            if (! $next) {
                return false;
            }
            $currentStep = $next['key'];
            $sameStep    = $owner->setup_followup_step === $currentStep;

            if (! $force && ! self::isDue($owner, $sameStep)) {
                return false;
            }

            $admin = User::where('role', USER_ROLE_ADMIN)->first();
            if (! $admin) {
                return false;
            }

            // Nudge number for THIS step (resets when the owner advances to a new step).
            $stepCount = $sameStep ? ((int) $owner->setup_followup_count + 1) : 1;

            $firstName = trim((string) ($user->first_name ?? '')) ?: __('there');
            $appName   = getOption('app_name') ?: 'Centresidence';
            $body      = self::body($firstName, $appName, $next, $stepCount);

            // Continue the welcome conversation if it's still open; otherwise open a fresh thread.
            $ticket  = self::findWelcomeThread($user);
            $support = app(SupportTicketService::class);

            if ($ticket) {
                $support->reply($ticket, $admin, $body, true);
            } else {
                $support->adminStartConversation(
                    $user,
                    $admin,
                    __('Need a hand finishing your setup?'),
                    $body
                );
            }

            $owner->forceFill([
                'setup_followup_step'  => $currentStep,
                'setup_followup_count' => $stepCount,
                'setup_followup_at'    => now(),
            ])->save();

            return true;
        } catch (\Throwable $e) {
            Log::warning('Owner setup follow-up failed for user ' . $user->id . ': ' . $e->getMessage());
            return false;
        }
    }

    /** Cadence gate: respect the post-welcome grace, the spacing interval, and the per-step cap. */
    private static function isDue(Owner $owner, bool $sameStep): bool
    {
        // welcomed_at / setup_followup_at aren't cast on the model — parse defensively to Carbon.
        $welcomedAt = $owner->welcomed_at ? \Illuminate\Support\Carbon::parse($owner->welcomed_at) : null;
        $lastSentAt = $owner->setup_followup_at ? \Illuminate\Support\Carbon::parse($owner->setup_followup_at) : null;

        // Grace: give a freshly-welcomed owner room before the first follow-up.
        if ($welcomedAt && $welcomedAt->gt(now()->subDays(self::GRACE_DAYS))) {
            return false;
        }
        // Spacing: never two follow-ups closer than SPACING_DAYS.
        if ($lastSentAt && $lastSentAt->gt(now()->subDays(self::SPACING_DAYS))) {
            return false;
        }
        // Per-step cap: only applies while they're stuck on the SAME step. Advancing resets it.
        if ($sameStep && (int) $owner->setup_followup_count >= self::MAX_PER_STEP) {
            return false;
        }
        return true;
    }

    /** The owner's most recent still-open support thread that WE started (the welcome), if any. */
    private static function findWelcomeThread(User $user): ?SupportTicket
    {
        return SupportTicket::where('requester_user_id', $user->id)
            ->where('status', '!=', SupportTicket::STATUS_CLOSED)
            ->with(['replies' => fn ($q) => $q->oldest()])
            ->latest('last_reply_at')
            ->get()
            ->first(fn ($t) => (bool) optional($t->replies->first())->is_admin === true);
    }

    /** Warm, stage-aware body naming the owner's next step; tone escalates with the per-step count. */
    private static function body(string $firstName, string $appName, array $next, int $stepCount): string
    {
        $guidance = [
            'property'  => __("The first step is adding your property — once it's in, you can add units and tenants and start billing. It only takes a minute."),
            'units'     => __("You've added your property — next add the units inside it, so you can place tenants and bill rent."),
            'tenant'    => __("You've got your property set up — next add your first tenant, and their login is sent to them automatically by SMS and email."),
            'getpaid'   => __("You're almost there — the one thing left before rent can actually reach you is setting up how you get paid: add your M-Pesa Till or Paybill under Settings → Gateway. Without it, invoices go out but the money has nowhere to land."),
            'recurring' => __("You're set up — just switch on automatic rent invoices so billing runs itself each month."),
        ];
        $line = $guidance[$next['key']] ?? __("You're partway through setup — let's get the next step done so you can start collecting rent.");

        // First nudge for a step is a gentle check-in; later ones lean in with a direct offer of help.
        $opener = $stepCount <= 1
            ? __(":name, just checking in. 👋\n\nI noticed you haven't quite finished setting up your :app account yet, and I didn't want you to get stuck.\n\n", ['name' => $firstName, 'app' => $appName])
            : __(":name, still here to help. 🙌\n\nYou're nearly set up on :app — I'd hate for you to miss out on collecting rent over one quick step.\n\n", ['name' => $firstName, 'app' => $appName]);

        $closer = $stepCount <= 1
            ? __("\n\nReply right here if anything's unclear or you'd like us to walk you through it — we're happy to help you get your first rent collected.")
            : __("\n\nJust reply here and we'll walk you through it personally — even hop on a quick call if that's easier. We really do want to see you up and running.");

        return $opener . $line . $closer;
    }
}
