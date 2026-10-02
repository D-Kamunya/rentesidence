<?php

namespace App\Console\Commands;

use App\Mail\Concerns\SendsCsMail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Release all active tenants from an owner — set each tenancy to CLOSE so the tenant becomes a
 * standalone (ownerless / Tenant-Helper) account that still works, keeping owner_user_id for
 * lineage. Built to clean the real tenants off a commandeered test account without deleting them
 * or touching their rent history.
 *
 * Three gears, so nothing is all-or-nothing:
 *   php artisan tenants:release-from-owner 5             # DRY RUN — list the affected tenants, change nothing
 *   php artisan tenants:release-from-owner 5 --execute   # quietly close the tenancies (REVERSIBLE)
 *   php artisan tenants:release-from-owner 5 --notify     # email the released tenants (IRREVERSIBLE; run later)
 *
 * The Tenant model has no save observers, so setting the status fires no settlement / deposit /
 * notification side-effects — it's a clean release. Reverse by setting status back to ACTIVE.
 */
class ReleaseTenantsFromOwner extends Command
{
    use SendsCsMail;

    protected $signature = 'tenants:release-from-owner
        {ownerId : the owner USER id (users.id — NOT owners.id)}
        {--execute : actually close the active tenancies}
        {--notify : email the already-released tenants that their account is still usable}';

    protected $description = 'Release active tenants from an owner into standalone (ownerless) accounts';

    public function handle(): int
    {
        $ownerId = (int) $this->argument('ownerId');
        $owner   = User::find($ownerId);
        if (! $owner) {
            $this->error("No user found with id {$ownerId}.");
            return self::FAILURE;
        }
        $this->info("Owner: " . trim(($owner->first_name ?? '') . ' ' . ($owner->last_name ?? '')) . " (user id {$ownerId}, " . ($owner->email ?? 'no email') . ")");

        // --notify operates on the ALREADY-RELEASED (CLOSE) tenants; the dry-run / --execute gears
        // operate on the ACTIVE ones still to release.
        if ($this->option('notify')) {
            return $this->notifyReleased($ownerId);
        }

        $tenants = Tenant::where('owner_user_id', $ownerId)
            ->where('status', TENANT_STATUS_ACTIVE)
            ->with('user')
            ->get();

        if ($tenants->isEmpty()) {
            $this->warn('No active tenants under this owner — nothing to release.');
            return self::SUCCESS;
        }

        $this->newLine();
        $this->line("Active tenants to release: <info>{$tenants->count()}</info>");
        foreach ($tenants as $t) {
            $u = $t->user;
            $this->line('  - ' . trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) . ' | ' . ($u->email ?? 'no email'));
        }
        $this->newLine();

        if (! $this->option('execute')) {
            $this->comment('DRY RUN — nothing changed. Re-run with --execute to close these tenancies.');
            return self::SUCCESS;
        }

        $closed = 0;
        foreach ($tenants as $t) {
            $t->status = TENANT_STATUS_CLOSE; // quiet: no Tenant observers → no settlement/notifications
            $t->save();                       // owner_user_id kept (lineage)
            $closed++;
        }

        $this->info("Released {$closed} tenancies — they are now standalone (ownerless) accounts and can still sign in.");
        $this->comment('Reverse if needed: set those tenants\' status back to ' . TENANT_STATUS_ACTIVE . '.');
        $this->comment('When ready (after the walkthrough), email them: php artisan tenants:release-from-owner ' . $ownerId . ' --notify');
        return self::SUCCESS;
    }

    private function notifyReleased(int $ownerId): int
    {
        $tenants = Tenant::where('owner_user_id', $ownerId)
            ->where('status', TENANT_STATUS_CLOSE)
            ->with('user')
            ->get()
            ->filter(fn ($t) => $t->user && filter_var($t->user->email ?? '', FILTER_VALIDATE_EMAIL));

        if ($tenants->isEmpty()) {
            $this->warn('No released tenants with a valid email to notify.');
            return self::SUCCESS;
        }

        $this->warn("About to email {$tenants->count()} released tenants (IRREVERSIBLE). This only sends if send_email_status is active.");

        $loginUrl = route('login');
        $sent = 0;
        foreach ($tenants as $t) {
            $u    = $t->user;
            $name = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: 'there';

            // NOTE: finalise this value-rich copy with the team before the real send — spell out
            // what the standalone account gives them (rent records, maintenance, services, and
            // that the account is portable and stays with them). Held until after the walkthrough.
            $this->sendCs([$u->email], __('Your Centresidence account is still active'), [
                'eyebrow' => __('Centresidence'),
                'title'   => __('Your account is still yours'),
                'preheader' => __('Sign in to Centresidence — your records and more are still here.'),
                'blocks'  => [
                    ['type' => 'text', 'html' => __('Hi :name, your Centresidence account is active and yours to use. You can still sign in to view your rent records, raise maintenance requests, shop for services and keep everything in one place — it stays with you.', ['name' => e($name)])],
                    ['type' => 'button', 'label' => __('Sign in'), 'url' => $loginUrl],
                ],
            ]);
            $sent++;
        }

        $this->info("Processed {$sent} emails (sent where email delivery is enabled).");
        return self::SUCCESS;
    }
}
