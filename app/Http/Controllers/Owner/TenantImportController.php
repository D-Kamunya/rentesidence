<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessTenantImport;
use App\Models\TenantImport;
use App\Services\Import\TenantImportService;
use App\Services\Sms\SmsCreditsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Bulk tenant/unit import (owner). Stage 1: upload → parse → validated row-by-row PREVIEW.
 * Nothing is written to the tenant/property tables here — the preview is a dry run, and the
 * owner confirms on a later step (Stage 2) which dispatches the queued import job.
 */
class TenantImportController extends Controller
{
    public function __construct(private TenantImportService $service)
    {
    }

    /** Upload page + recent import runs. */
    public function index()
    {
        $imports = TenantImport::where('owner_user_id', auth()->id())
            ->latest()
            ->limit(15)
            ->get();

        return view('owner.tenants.import.index', [
            'columns' => $this->service->columns(),
            'imports' => $imports,
        ]);
    }

    /** Download a ready-to-fill CSV template: header labels + one example row. */
    public function template()
    {
        $columns = $this->service->columns();
        $sample  = $this->service->templateSampleRow();

        $headers = [];
        $example = [];
        foreach ($columns as $key => $meta) {
            $headers[] = $meta['label'];
            $example[] = $sample[$key] ?? '';
        }

        $filename = 'tenant-import-template.csv';
        return response()->streamDownload(function () use ($headers, $example) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel opens it cleanly
            fputcsv($out, $headers);
            fputcsv($out, $example);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /** Store the uploaded CSV, dry-run validate it, and show the row-by-row preview. */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10 MB
        ]);

        $file = $request->file('file');
        $ext  = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, ['csv', 'txt'], true)) {
            return back()->with('error', __('Please upload a .csv file (export your spreadsheet as CSV).'));
        }

        // Parse from the temp path first — if it isn't a usable CSV, don't keep the file.
        try {
            $parsed = $this->service->parseCsv($file->getRealPath());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        if (! empty($parsed['missingRequiredHeaders'])) {
            return back()->with('error', __('Your file is missing required column(s): :cols. Download the template and try again.', [
                'cols' => implode(', ', $parsed['missingRequiredHeaders']),
            ]));
        }
        if (empty($parsed['rows'])) {
            return back()->with('error', __('The file has no data rows.'));
        }

        $result = $this->service->validateRows(auth()->id(), $parsed['rows']);

        // Persist the file + the preview so Stage 2 can process it on confirm.
        $disk       = config('app.STORAGE_DRIVER');
        $storedName = 'imports/tenant-' . auth()->id() . '-' . time() . '_' . uniqid() . '.csv';
        Storage::disk($disk)->put($storedName, file_get_contents($file->getRealPath()));

        $import = TenantImport::create([
            'owner_user_id'     => auth()->id(),
            'original_filename' => $file->getClientOriginalName(),
            'stored_path'       => $storedName,
            'status'            => TenantImport::STATUS_PREVIEWED,
            'total_rows'        => $result['summary']['total'],
            'valid_rows'        => $result['valid'],
            'error_rows'        => $result['errors'],
            'error_report'      => $this->errorReport($result['rows']),
            'summary'           => $result['summary'],
            'previewed_at'      => now(),
        ]);

        return view('owner.tenants.import.preview', [
            'import'     => $import,
            'result'     => $result,
            'unmatched'  => $parsed['unmatchedHeaders'],
            'columns'    => $this->service->columns(),
            'smsBalance' => SmsCreditsService::balance(auth()->id()),
            'smsPrice'   => SmsCreditsService::amountForCredits(1),
        ]);
    }

    /**
     * Confirm a previewed import: pick the invite channel, pre-check the SMS budget so we
     * never start an import that can't afford its SMS invites, then dispatch the queued job.
     */
    public function confirm(Request $request, $importId)
    {
        $import = TenantImport::where('owner_user_id', auth()->id())->findOrFail($importId);

        // Only a freshly-previewed import can be started (guards double-submit / re-run).
        if ($import->status !== TenantImport::STATUS_PREVIEWED) {
            return redirect()->route('owner.tenant.import.status', $import->id);
        }

        $request->validate(['invite_channel' => 'required|in:email,sms,both,none']);
        $channel = $request->invite_channel;

        // SMS pre-check: block the start (prompt a top-up) if the SMS invites can't be covered.
        if (in_array($channel, ['sms', 'both'], true)) {
            $need    = (int) ($import->summary['sms_invites'] ?? 0);
            $balance = SmsCreditsService::balance(auth()->id());
            if ($need > $balance) {
                return back()->with('error', __(
                    'This import would send :need SMS invite(s) but you have :bal SMS credit(s). Top up :short more, or choose Email invites.',
                    ['need' => $need, 'bal' => $balance, 'short' => $need - $balance]
                ))->with('sms_topup', true);
            }
        }

        $import->update([
            'options' => array_merge($import->options ?? [], ['invite_channel' => $channel]),
            'status'  => TenantImport::STATUS_PROCESSING,
        ]);

        ProcessTenantImport::dispatch($import->id);

        return redirect()->route('owner.tenant.import.status', $import->id)
            ->with('success', __('Import started — you can watch its progress here.'));
    }

    /** Live progress page (polls the progress endpoint). */
    public function status($importId)
    {
        $import = TenantImport::where('owner_user_id', auth()->id())->findOrFail($importId);
        return view('owner.tenants.import.status', ['import' => $import]);
    }

    /** JSON snapshot for the progress poller. */
    public function progress($importId)
    {
        $import = TenantImport::where('owner_user_id', auth()->id())->findOrFail($importId);

        $invitesDone = (int) $import->invites_sent + (int) $import->invites_failed;
        $rowsDone    = $import->isDone();
        $allDone     = $rowsDone && ((int) $import->invites_queued === 0 || $invitesDone >= (int) $import->invites_queued);

        return response()->json([
            'status'         => $import->status,
            'total'          => (int) $import->valid_rows,
            'processed'      => (int) $import->processed_rows,
            'created'        => (int) $import->created_count,
            'updated'        => (int) $import->updated_count,
            'skipped'        => (int) $import->skipped_count,
            'error_rows'     => (int) $import->error_rows,
            'invites_queued' => (int) $import->invites_queued,
            'invites_sent'   => (int) $import->invites_sent,
            'invites_failed' => (int) $import->invites_failed,
            'rows_done'      => $rowsDone,
            'all_done'       => $allDone,
            'failure'        => $import->failure_reason,
        ]);
    }

    /** Download the rows that couldn't import, with their reasons, to fix and re-upload. */
    public function errorsCsv($importId)
    {
        $import = TenantImport::where('owner_user_id', auth()->id())->findOrFail($importId);
        $report = $import->error_report ?? [];

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Row', 'Problems']);
            foreach ($report as $entry) {
                fputcsv($out, [$entry['line'] ?? '', implode(' | ', $entry['errors'] ?? [])]);
            }
            fclose($out);
        }, 'import-' . $import->id . '-errors.csv', ['Content-Type' => 'text/csv']);
    }

    /** Compact the per-row errors for storage (only rows that actually have errors). */
    private function errorReport(array $rows): array
    {
        $report = [];
        foreach ($rows as $r) {
            if (! empty($r['errors'])) {
                $report[] = ['line' => $r['line'], 'errors' => $r['errors']];
            }
        }
        return $report;
    }
}
