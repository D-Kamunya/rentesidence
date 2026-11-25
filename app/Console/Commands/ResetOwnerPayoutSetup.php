<?php

namespace App\Console\Commands;

use App\Models\Gateway;
use App\Models\MpesaAccount;
use App\Models\User;
use App\Models\UserTour;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Puts an owner back to the "haven't set up how I get paid" state so the dashboard setup checklist
 * reappears with the inline M-Pesa (Till/Paybill) step pending — handy for demoing the onboarding
 * flow on a real account. Fully REVERSIBLE and non-destructive: it only flips statuses to inactive
 * (never deletes a Till/Paybill) and clears the "checklist dismissed" flag. `--undo` reactivates.
 *
 * Preview-first: prints exactly what it will change and asks to confirm (bypass with --yes on cPanel,
 * where interactive confirm() does not capture input).
 *
 *   php artisan owner:reset-payout owner@gmail.com          # preview + confirm
 *   php artisan owner:reset-payout owner@gmail.com --yes     # no prompt (cPanel)
 *   php artisan owner:reset-payout owner@gmail.com --undo    # put the payout setup back
 */
class ResetOwnerPayoutSetup extends Command
{
    protected $signature = 'owner:reset-payout {email : The owner account email}
        {--undo : Reactivate the M-Pesa gateway + accounts (reverse a previous reset)}
        {--yes : Skip the confirmation prompt (cPanel CLI)}';

    protected $description = 'Reset (or restore) an owner\'s M-Pesa payout setup so the checklist step reappears. Reversible.';

    public function handle(): int
    {
        $email = trim((string) $this->argument('email'));
        $undo  = (bool) $this->option('undo');

        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->error("No user with email \"{$email}\".");
            return self::FAILURE;
        }

        if (! Schema::hasTable('mpesa_accounts') || ! Schema::hasTable('gateways')) {
            $this->error('Gateway/M-Pesa tables are missing on this database.');
            return self::FAILURE;
        }

        $name     = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: '(no name)';
        $accounts = MpesaAccount::where('owner_user_id', $user->id)->get();
        $gateway  = Gateway::where('owner_user_id', $user->id)->where('slug', 'mpesa')->first();

        $this->info("Owner: {$name} <{$email}>  (user id {$user->id})");
        $this->line('M-Pesa accounts on file: ' . $accounts->count()
            . '  |  gateway: ' . ($gateway ? ('status ' . $gateway->status) : 'none'));

        if ($undo) {
            $this->line('<comment>UNDO</comment> — will REACTIVATE the M-Pesa gateway and all its accounts.');
        } else {
            $this->line('<comment>RESET</comment> — will DEACTIVATE the M-Pesa gateway + accounts (not delete) and reopen the setup checklist.');
        }

        if (! $this->option('yes') && ! $this->confirm('Proceed?', true)) {
            $this->line('Aborted. Nothing changed.');
            return self::SUCCESS;
        }

        $targetStatus = $undo ? ACTIVE : DEACTIVATE;

        $accountsChanged = MpesaAccount::where('owner_user_id', $user->id)->update(['status' => $targetStatus]);
        if ($gateway) {
            $gateway->status = $targetStatus;
            $gateway->save();
        }

        if (! $undo && Schema::hasTable('user_tours')) {
            // Clear the "I dismissed the checklist" flag so it shows again on next dashboard load.
            UserTour::where('user_id', $user->id)->where('tour_key', 'owner_setup')->delete();
        }

        $this->info(($undo ? 'Restored' : 'Reset') . ": {$accountsChanged} M-Pesa account(s) set to status {$targetStatus}"
            . ($gateway ? ' + gateway updated' : '') . ($undo ? '.' : ', checklist reopened.'));
        $this->line('<comment>Tip:</comment> run `php artisan optimize:clear` if the dashboard view is cached.');

        return self::SUCCESS;
    }
}
