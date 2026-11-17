<?php

namespace App\Services;

use App\Models\Owner;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\OwnerAdvisor\OwnerSetupService;
use Illuminate\Support\Facades\Log;

/**
 * The one-time "stuck in setup" follow-up — a SECOND touch after the welcome, for owners who signed
 * up but haven't finished getting set up to collect rent. It's stage-aware: it names exactly where
 * the owner is stuck (their first unfinished step, including the get-paid / rent-account step the
 * email activation-nudge never reaches) and points them at it. Delivered as a reply INTO the owner's
 * existing welcome support thread (so it reads as us checking in, and they can reply), falling back to
 * a fresh thread if none is found. Idempotent via owners.setup_followup_at; $force re-sends for a
 * deliberate one-time backfill of the current stuck cohort.
 */
class OwnerSetupFollowupService
{
    /** Returns true if a follow-up was sent, false if skipped (not owner / not welcomed / already done / setup complete). */
    public static function send(User $user, bool $force = false): bool
    {
        $owner = Owner::where('user_id', $user->id)->first();

        // Only follow up owners we've already welcomed, and (unless forced) only once.
        if (! $owner || ! $owner->welcomed_at || (! $force && $owner->setup_followup_at)) {
            return false;
        }

        try {
            $checklist = app(OwnerSetupService::class)->checklist((int) $user->id);
            // No steps (bare install) or already set up → nothing to nudge.
            if (($checklist['total'] ?? 0) === 0 || ! empty($checklist['complete'])) {
                return false;
            }

            // The first unfinished step is where they're stuck — drives the stage-aware message.
            $next = collect($checklist['steps'])->firstWhere('done', false);
            if (! $next) {
                return false;
            }

            $admin = User::where('role', USER_ROLE_ADMIN)->first();
            if (! $admin) {
                return false;
            }

            $firstName = trim((string) ($user->first_name ?? '')) ?: __('there');
            $appName   = getOption('app_name') ?: 'Centresidence';
            $body      = self::body($firstName, $appName, $next);

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

            $owner->forceFill(['setup_followup_at' => now()])->save();
            return true;
        } catch (\Throwable $e) {
            Log::warning('Owner setup follow-up failed for user ' . $user->id . ': ' . $e->getMessage());
            return false;
        }
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

    /** Warm, stage-aware body naming exactly the owner's next step. */
    private static function body(string $firstName, string $appName, array $next): string
    {
        $guidance = [
            'property'  => __("The first step is adding your property — once it's in, you can add units and tenants and start billing. It only takes a minute."),
            'units'     => __("You've added your property — next add the units inside it, so you can place tenants and bill rent."),
            'tenant'    => __("You've got your property set up — next add your first tenant, and their login is sent to them automatically by SMS and email."),
            'getpaid'   => __("You're almost there — the one thing left before rent can actually reach you is setting up how you get paid: add your M-Pesa Till or Paybill under Settings → Gateway. Without it, invoices go out but the money has nowhere to land."),
            'recurring' => __("You're set up — just switch on automatic rent invoices so billing runs itself each month."),
        ];

        $line = $guidance[$next['key']] ?? __("You're partway through setup — let's get the next step done so you can start collecting rent.");

        return __(":name, just checking in. 👋\n\n", ['name' => $firstName])
            . __("I noticed you haven't quite finished setting up your :app account yet, and I didn't want you to get stuck.\n\n", ['app' => $appName])
            . $line
            . __("\n\nReply right here if anything's unclear or you'd like us to walk you through it — we're happy to help you get your first rent collected.");
    }
}
