<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * ONE command to bring a fresh deploy / new environment fully up to date, so no
 * individual setup step can be forgotten on a shared host. Every step is idempotent
 * and safe to re-run on each deploy.
 *
 * ⇢ WHEN YOU ADD A NEW GO-LIVE REQUIREMENT (a new seeder, a new backfill, a new
 *   filesystem/link step, …) ADD IT HERE — this is the single source of truth for
 *   "what has to run to get all systems running live". Prefer self-healing code where
 *   possible; only things that genuinely need a deploy-time run belong in this command.
 *
 * NOT included (run elsewhere by design):
 *   - Scheduled jobs (invoices, reminders, backups, Centresidence billing/collections,
 *     lead/trial expiry, the queue worker) run via the scheduler — they only need the
 *     server cron `* * * * * php artisan schedule:run` to exist (reported at the end).
 *   - Per-owner/tenant defaults that self-heal on access (KYC configs, invoice types,
 *     recurring rent settings, …) don't need a command.
 */
class Deploy extends Command
{
    protected $signature = 'app:deploy
        {--with-cache : Also (re)build config/route/view caches for production performance}
        {--seed-demo  : Also seed Centresidence demo data (NON-production — local/staging only)}';

    protected $description = 'Run every idempotent setup step needed to bring a deploy fully live (migrations, production seed data, storage link, backfills).';

    public function handle(): int
    {
        $this->info('▶ Bringing the application up to date for this environment…');
        $this->newLine();

        $failed = [];

        // 1) Schema — foundational; if this fails, stop (nothing else is safe).
        if (! $this->step('Database migrations', fn () => $this->call('migrate', ['--force' => true]) === 0)) {
            $this->error('Migrations failed — aborting before the remaining steps.');
            return self::FAILURE;
        }

        // 2) Production content seed — DatabaseSeeder is idempotent + demo-data-free
        //    (module catalogue + knowledge base). Ships the configured environment live.
        $this->step('Production seed data (module catalogue, knowledge base)', function () {
            return $this->call('db:seed', ['--force' => true]) === 0;
        }) ?: $failed[] = 'db:seed';

        // 3) Public storage symlink — required for uploaded images / receipts / documents
        //    served from public/storage. Skipped cleanly if already linked.
        $this->step('Public storage symlink', function () {
            if (File::exists(public_path('storage'))) {
                $this->line('   already linked — skipping.');
                return true;
            }
            return $this->call('storage:link') === 0;
        }) ?: $failed[] = 'storage:link';

        // 4) Plug-and-play backfill — default document configs for any existing owner
        //    that has none (new owners get these at creation; idempotent).
        $this->step('Owner document-config backfill', function () {
            return $this->call('owners:seed-document-configs') === 0;
        }) ?: $failed[] = 'owners:seed-document-configs';

        // 5) Optional: demo data (never on production).
        if ($this->option('seed-demo')) {
            $this->step('Centresidence demo data (non-production)', function () {
                return $this->call('db:seed', ['--class' => \Database\Seeders\CentresidenceDemoSeeder::class, '--force' => true]) === 0;
            }) ?: $failed[] = 'demo-seed';
        }

        // 6) Optional: production caches. Off by default — only build when asked, since a
        //    stale config cache after an env change is a classic shared-host footgun.
        if ($this->option('with-cache')) {
            $this->step('Rebuild caches (config, route, view)', function () {
                $this->call('optimize:clear');
                return $this->call('config:cache') === 0
                    && $this->call('route:cache') === 0
                    && $this->call('view:cache') === 0;
            }) ?: $failed[] = 'caches';
        }

        $this->newLine();
        $this->line('<options=bold>Reminders — the two things this command CANNOT do for itself:</>');
        $this->newLine();

        // 1) Composer — must run at the shell BEFORE artisan (a missing dependency would
        //    stop artisan booting, so this command could never self-run it). composer.lock
        //    is gitignored here, so each host resolves its own versions — always safe to run.
        $this->line(' <fg=cyan>1. Dependencies</> — after a pull that changed <fg=yellow>composer.json</>, run this FIRST (before app:deploy):');
        $this->line('    <fg=yellow>composer install --no-dev --optimize-autoloader</>');
        $this->newLine();

        // 2) The cron — EVERYTHING scheduled rests on this single line existing on the host.
        $this->line(' <fg=cyan>2. Server cron</> — set ONCE per host (everything scheduled depends on it):');
        $this->line('    <fg=yellow>* * * * * cd ' . base_path() . ' && php artisan schedule:run >> /dev/null 2>&1</>');
        $this->line('    Drives invoices, reminders, backups, the queue worker, screening recompute,');
        $this->line('    and ALL Centresidence billing / collection / partner-remittance jobs. If it is');
        $this->line('    missing, none of those run — verify it exists on the live server.');
        $this->newLine();

        if (! empty($failed)) {
            $this->warn('Completed with issues in: ' . implode(', ', $failed) . '. Re-run `php artisan app:deploy` after resolving — every step is idempotent.');
            return self::FAILURE;
        }

        $this->info('✔ All systems set up. The deploy is live-ready.');
        return self::SUCCESS;
    }

    /** Run one labelled step, print a ✓/✗, and return whether it succeeded. */
    private function step(string $label, callable $run): bool
    {
        $this->line("• {$label}");
        try {
            $ok = (bool) $run();
        } catch (\Throwable $e) {
            $this->error('   ' . $e->getMessage());
            $ok = false;
        }
        $this->line($ok ? '   <info>✓ done</info>' : '   <error>✗ failed</error>');
        $this->newLine();

        return $ok;
    }
}
