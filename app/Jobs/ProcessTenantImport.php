<?php

namespace App\Jobs;

use App\Models\TenantImport;
use App\Services\Import\TenantImportService;
use App\Services\InvoiceRecurringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Commits a validated tenant import: re-parse + re-validate the stored file, then upsert each
 * good row (property → unit → tenant → tenancy → opening balance) through TenantImportService,
 * enforcing the owner's plan limits and progressing the ledger so the UI can watch it. Bad
 * rows are skipped into the error report — the run never aborts wholesale on one bad row.
 * Invites are dispatched as separate credit-gated jobs once the writes finish.
 */
class ProcessTenantImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    public function __construct(public int $importId)
    {
    }

    public function handle(TenantImportService $service, InvoiceRecurringService $recurring): void
    {
        $import = TenantImport::find($this->importId);
        if (! $import) {
            return;
        }
        // Never re-commit a finished run (idempotent against an accidental re-dispatch).
        if (in_array($import->status, [TenantImport::STATUS_COMPLETED, TenantImport::STATUS_COMPLETED_WITH_ERRORS], true)) {
            return;
        }

        $import->update([
            'status'         => TenantImport::STATUS_PROCESSING,
            'started_at'     => now(),
            'processed_rows' => 0, 'created_count' => 0, 'updated_count' => 0, 'skipped_count' => 0,
            'invites_queued' => 0, 'invites_sent' => 0, 'invites_failed' => 0,
        ]);

        $disk = config('app.STORAGE_DRIVER');

        try {
            if (! Storage::disk($disk)->exists($import->stored_path)) {
                throw new \RuntimeException('The uploaded file is no longer available.');
            }

            // Pull the file to a local temp path so the CSV parser can read it on any disk.
            $tmp = tempnam(sys_get_temp_dir(), 'timp');
            file_put_contents($tmp, Storage::disk($disk)->get($import->stored_path));
            $parsed = $service->parseCsv($tmp);
            @unlink($tmp);

            $result  = $service->validateRows((int) $import->owner_user_id, $parsed['rows']);
            $limits  = $service->resolveLimits((int) $import->owner_user_id);
            $channel = $import->options['invite_channel'] ?? 'email';

            $created = 0; $updated = 0; $skipped = 0; $processed = 0;
            $errorReport = $import->error_report ?? []; // preview validation errors carry over
            $invites = [];

            foreach ($result['rows'] as $r) {
                if (! empty($r['errors'])) {
                    continue; // invalid rows are already in the error report; never written
                }

                try {
                    $res = $service->importRow((int) $import->owner_user_id, $r['data'], $limits, $recurring);

                    if ($res['action'] === 'created') {
                        $created++;
                    } elseif ($res['action'] === 'updated') {
                        $updated++;
                    } else { // skipped (e.g. plan limit reached)
                        $skipped++;
                        $errorReport[] = ['line' => $r['line'], 'errors' => $res['errors']];
                    }

                    // Invite only NEW logins, only if a channel was chosen, and only when the
                    // tenant is actually REACHABLE by that channel (e.g. don't queue an Email
                    // invite for a tenant with no email — that would just fail).
                    if (in_array($res['action'], ['created', 'updated'], true)
                        && ! empty($res['invite']) && $res['invite']['is_new'] && $channel !== 'none') {
                        $inv = $res['invite'];
                        $reachable = match ($channel) {
                            'email' => ! empty($inv['email']),
                            'sms'   => ! empty($inv['phone']),
                            'both'  => ! empty($inv['email']) || ! empty($inv['phone']),
                            default => false,
                        };
                        if ($reachable) {
                            $invites[] = $inv;
                        }
                    }
                } catch (\Throwable $e) {
                    $skipped++;
                    $errorReport[] = ['line' => $r['line'], 'errors' => [__('Could not import this row:') . ' ' . $e->getMessage()]];
                    Log::error('Tenant import row ' . $r['line'] . ' failed: ' . $e->getMessage());
                }

                $processed++;
                if ($processed % 25 === 0) {
                    $import->update([
                        'processed_rows' => $processed, 'created_count' => $created,
                        'updated_count' => $updated, 'skipped_count' => $skipped,
                    ]);
                }
            }

            $import->update([
                'processed_rows' => $processed, 'created_count' => $created,
                'updated_count' => $updated, 'skipped_count' => $skipped,
                'invites_queued' => count($invites),
                'error_report'  => $errorReport,
                'status'        => ($skipped > 0 || (int) $import->error_rows > 0)
                    ? TenantImport::STATUS_COMPLETED_WITH_ERRORS
                    : TenantImport::STATUS_COMPLETED,
                'finished_at'   => now(),
            ]);

            // Fan out invites (separate credit-gated jobs; they bump the invite counters).
            foreach ($invites as $inv) {
                SendTenantImportInvite::dispatch($import->id, (int) $inv['user_id'], $inv['password'], $channel);
            }
        } catch (\Throwable $e) {
            $import->update([
                'status'         => TenantImport::STATUS_FAILED,
                'failure_reason' => $e->getMessage(),
                'finished_at'    => now(),
            ]);
            Log::error('Tenant import ' . $import->id . ' failed: ' . $e->getMessage());
        }
    }
}
