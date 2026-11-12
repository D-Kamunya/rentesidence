<?php

namespace App\Console\Commands;

use App\Models\Owner;
use App\Services\OwnerWelcomeService;
use Illuminate\Console\Command;

/**
 * Sends the in-app welcome message to existing owners who signed up before it existed.
 * Idempotent (OwnerWelcomeService sets owners.welcomed_at), so it's safe to re-run — only
 * owners who haven't been welcomed get a message.
 *
 *   php artisan owner:welcome-backfill
 */
class OwnerWelcomeBackfill extends Command
{
    protected $signature = 'owner:welcome-backfill';
    protected $description = 'Send the in-app welcome message to owners who have not received it yet.';

    public function handle(): int
    {
        $sent = 0;
        Owner::whereNull('welcomed_at')->with('user')->chunkById(100, function ($owners) use (&$sent) {
            foreach ($owners as $owner) {
                if ($owner->user && OwnerWelcomeService::send($owner->user)) {
                    $sent++;
                }
            }
        });

        $this->info("Welcome message sent to {$sent} owner(s).");
        return self::SUCCESS;
    }
}
