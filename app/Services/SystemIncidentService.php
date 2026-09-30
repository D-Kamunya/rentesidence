<?php

namespace App\Services;

use App\Jobs\SendSmsJob;
use App\Models\SystemIncident;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Records GENUINE platform failures as {@see SystemIncident} rows and pages the admin
 * for critical ones — the single choke point every capture hook calls.
 *
 * Design rules (why this is safe to call from a payment callback):
 *   • It NEVER throws. Every path is wrapped; a recorder that breaks the request it is
 *     observing is worse than a missed incident.
 *   • It AGGREGATES. Repeats with the same dedup_key fold onto one unresolved row
 *     (occurrences++ / last_seen_at) instead of spamming — so a persistent fault is one
 *     growing incident, and the count itself is the "is this genuine or a blip?" signal.
 *   • It is DISCIPLINED about paging. A critical row SMSes the admin only once it has
 *     been seen `alertThreshold` times (transient one-offs never page), and then at most
 *     once per throttle window until it's resolved.
 */
class SystemIncidentService
{
    /** Re-page an unresolved critical incident at most this often (seconds) — 6h. */
    private const ALERT_THROTTLE_SECONDS = 21600;

    /**
     * Capture (or fold into) an incident.
     *
     * @param  string  $type           One of SystemIncident::TYPE_*.
     * @param  string  $severity        SEVERITY_WARNING | SEVERITY_CRITICAL.
     * @param  string  $title           Short human label (shown in the list + SMS).
     * @param  string  $message         Detail line.
     * @param  array   $context         Structured context (ids, codes, ip…) — no secrets.
     * @param  string|null $dedupKey    Repeats with this key aggregate; defaults to the type.
     * @param  int     $alertThreshold  Critical pages only once occurrences reach this (transient gate).
     * @param  int|null $escalateToCriticalAt  If set, a WARNING row auto-promotes to CRITICAL once
     *                                         occurrences reach this many — "warn until repeated,
     *                                         then page" (used for forged-callback bursts).
     */
    public function report(
        string $type,
        string $severity,
        string $title,
        string $message = '',
        array $context = [],
        ?string $dedupKey = null,
        int $alertThreshold = 1,
        ?int $escalateToCriticalAt = null
    ): ?SystemIncident {
        try {
            if (! Schema::hasTable('system_incidents')) {
                return null;
            }

            $dedupKey = $dedupKey ?: $type;

            $incident = SystemIncident::unresolved()->where('dedup_key', $dedupKey)->latest('id')->first();

            if ($incident) {
                $incident->occurrences += 1;
                $incident->last_seen_at = now();
                $incident->message      = $message ?: $incident->message;
                $incident->context      = $context ?: $incident->context;
                // A fold can only raise severity, never lower it.
                if ($severity === SystemIncident::SEVERITY_CRITICAL) {
                    $incident->severity = SystemIncident::SEVERITY_CRITICAL;
                }
                // A folded repeat re-opens an acknowledged row — it happened again.
                $incident->status = SystemIncident::STATUS_OPEN;
                $incident->save();
            } else {
                $incident = SystemIncident::create([
                    'type'          => $type,
                    'severity'      => $severity,
                    'status'        => SystemIncident::STATUS_OPEN,
                    'title'         => $title,
                    'message'       => $message,
                    'context'       => $context ?: null,
                    'dedup_key'     => $dedupKey,
                    'occurrences'   => 1,
                    'first_seen_at' => now(),
                    'last_seen_at'  => now(),
                ]);
            }

            // Warn-until-repeated: promote to critical once the burst threshold is crossed.
            if ($escalateToCriticalAt !== null
                && ! $incident->isCritical()
                && $incident->occurrences >= $escalateToCriticalAt) {
                $incident->severity = SystemIncident::SEVERITY_CRITICAL;
                $incident->save();
            }

            $this->maybeAlert($incident, $alertThreshold);

            return $incident;
        } catch (\Throwable $e) {
            // Last-ditch: never let incident recording break the caller.
            try {
                Log::error('SystemIncidentService::report failed: ' . $e->getMessage());
            } catch (\Throwable $ignored) {
            }

            return null;
        }
    }

    /** SMS the admin for a critical incident — gated by threshold, then throttled. */
    private function maybeAlert(SystemIncident $incident, int $alertThreshold): void
    {
        if (! $incident->isCritical() || $incident->occurrences < max(1, $alertThreshold)) {
            return;
        }

        // Throttle: don't re-page the same unresolved incident inside the window.
        if ($incident->alerted_at && $incident->alerted_at->gt(now()->subSeconds(self::ALERT_THROTTLE_SECONDS))) {
            return;
        }

        $phone = $this->alertPhone();
        if ($phone === null) {
            return;
        }

        try {
            $app = getOption('app_name') ?: config('app.name');
            $msg = $this->normalize(sprintf(
                '%s alert: %s. %s Check the incidents page.',
                $app,
                $incident->title,
                $incident->occurrences > 1 ? "Seen {$incident->occurrences}x." : ''
            ));

            // Ungated system SMS (no owner to bill), same path credential SMS uses.
            SendSmsJob::dispatch([$phone], $msg, null);

            $incident->alerted_at = now();
            $incident->saveQuietly();
        } catch (\Throwable $e) {
            Log::error('SystemIncident critical alert SMS failed: ' . $e->getMessage());
        }
    }

    /**
     * Where critical alerts go — config, not code. An explicit `critical_alert_phone`
     * setting wins; otherwise fall back to the first admin's contact number so a fresh
     * install still pages someone.
     */
    private function alertPhone(): ?string
    {
        $configured = trim((string) getOption('critical_alert_phone'));
        if ($configured !== '') {
            return $configured;
        }

        try {
            $admin = User::where('role', USER_ROLE_ADMIN)->whereNotNull('contact_number')->first();

            return $admin && trim((string) $admin->contact_number) !== '' ? $admin->contact_number : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Unresolved incidents (for the nav badge). */
    public function openCount(): int
    {
        try {
            if (! Schema::hasTable('system_incidents')) {
                return 0;
            }

            return SystemIncident::unresolved()->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /** Drop smart punctuation so a paging SMS stays GSM-7 (AdvantaSmsService also normalises). */
    private function normalize(string $s): string
    {
        return strtr($s, ['—' => '-', '–' => '-', '“' => '"', '”' => '"', '’' => "'", '…' => '...']);
    }
}
