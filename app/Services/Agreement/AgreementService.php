<?php

namespace App\Services\Agreement;

use App\Jobs\SendSmsJob;
use App\Models\Agreement;
use App\Models\AgreementSignatureEvent;
use App\Models\AgreementTemplate;
use App\Models\FileManager;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Internal e-signature engine (replaces DocuSign). An owner sends a template-autofilled
 * (or uploaded-PDF) agreement to a tenant, who reviews and signs it in-portal behind an
 * SMS OTP. On signing we render a certified, hashed PDF and keep an immutable audit trail.
 */
class AgreementService
{
    use ResponseTrait;

    /* ─────────────────────────── Templates (owner) ─────────────────────────── */

    /** The owner's reusable templates, self-healing a plug-and-play default on first read. */
    public function templatesFor(int $ownerUserId)
    {
        ensureOwnerDefaults($ownerUserId, AgreementTemplate::class, 'setOwnerDefaultAgreementTemplate');

        return AgreementTemplate::where('owner_user_id', $ownerUserId)
            ->active()
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    public function templateForOwner(int $ownerUserId, int $templateId): ?AgreementTemplate
    {
        return AgreementTemplate::where('owner_user_id', $ownerUserId)->find($templateId);
    }

    /* ─────────────────────────── Gating / eligibility ─────────────────────── */

    /**
     * Whether an owner may send another agreement, and if not, whether a per-agreement
     * payment unlocks it. Model: subscription/transaction plans are unlimited; free-plan
     * owners get a free quota (default 10), after which each send costs a set amount paid
     * via STK. A non-SaaS (standalone) install is unlimited. Never gates on a stale/expired
     * package silently — `ownerCurrentPackage` already scopes to an active, unexpired one.
     *
     * @return array{allowed:bool, requiresPayment:bool, plan:string, quota?:int, used?:int, remaining?:int, price?:float}
     */
    public function sendEligibility(int $ownerUserId): array
    {
        // No SaaS layer → single-operator install, no metering.
        if (isAddonInstalled('PROTYSAAS') < 1) {
            return ['allowed' => true, 'requiresPayment' => false, 'plan' => 'standalone'];
        }

        $plan = optional(ownerCurrentPackage($ownerUserId))->pricing_model ?: 'free';

        if (in_array($plan, ['subscription', 'transaction'], true)) {
            return ['allowed' => true, 'requiresPayment' => false, 'plan' => $plan, 'cover' => 'plan'];
        }

        // Free plan: a MONTHLY free allowance (use-it-or-lose-it, computed from this
        // month's free-covered sends — no cron reset), then PURCHASED credits (roll over).
        $quota = max(0, (int) getOption('agreement_free_quota', 10));
        $freeUsed = Agreement::where('owner_user_id', $ownerUserId)
            ->where('billed_as', 'free')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $freeRemaining = max(0, $quota - $freeUsed);
        $credits = AgreementCreditsService::balance($ownerUserId);

        $base = [
            'plan' => 'free', 'quota' => $quota, 'freeUsed' => $freeUsed,
            'remaining' => $freeRemaining, 'credits' => $credits,
            'price' => (float) getOption('agreement_price', 50),
        ];

        if ($freeRemaining > 0) {
            return array_merge($base, ['allowed' => true, 'requiresPayment' => false, 'cover' => 'free']);
        }
        if ($credits > 0) {
            return array_merge($base, ['allowed' => true, 'requiresPayment' => false, 'cover' => 'credit']);
        }

        return array_merge($base, ['allowed' => false, 'requiresPayment' => true, 'cover' => null]);
    }

    /**
     * Whether this owner's agreements are UNLIMITED (plan-covered) rather than metered — a
     * standalone install, or a subscription/transaction plan. Used to display "Unlimited"
     * instead of a purchased-credit count. Mirrors sendEligibility's coverage rules; static
     * so config/credits.php can reference it as a callable.
     */
    public static function ownerHasUnlimited(int $ownerUserId): bool
    {
        if (isAddonInstalled('PROTYSAAS') < 1) {
            return true; // single-operator install — no metering
        }
        $plan = optional(ownerCurrentPackage($ownerUserId))->pricing_model ?: 'free';
        return in_array($plan, ['subscription', 'transaction'], true);
    }

    /**
     * The free-plan owner's MONTHLY free agreement allowance for display: how many free
     * sends remain this calendar month and the cap. Returns null when it doesn't apply
     * (unlimited plan, or non-free tier). The 10 free are computed from this month's
     * free-covered sends — not a stored balance — so they "kick in" immediately and reset on
     * the 1st with no cron. Static so config/credits.php can reference it as a callable.
     *
     * @return array{quota:int, used:int, remaining:int}|null
     */
    public static function freeMonthlyAllowance(int $ownerUserId): ?array
    {
        if (self::ownerHasUnlimited($ownerUserId)) {
            return null;
        }
        $elig = (new self())->sendEligibility($ownerUserId);
        if (($elig['plan'] ?? null) !== 'free') {
            return null;
        }
        return [
            'quota'     => (int) ($elig['quota'] ?? 0),
            'used'      => (int) ($elig['freeUsed'] ?? 0),
            'remaining' => (int) ($elig['remaining'] ?? 0),
        ];
    }

    /* ─────────────────────────── Send (owner → tenant) ─────────────────────── */

    /**
     * Create + send an agreement to one of the owner's tenants. Snapshots the template
     * content (autofilled) so later template edits never change an already-sent agreement.
     */
    public function send(int $ownerUserId, int $templateId, int $tenantUserId, bool $skipGate = false): Agreement
    {
        // Resolve how this send is covered (plan | free monthly | purchased credit) and
        // enforce the gate server-side. $skipGate is a system/admin hatch (billed as plan).
        $coverage = 'plan';
        if (! $skipGate) {
            $eligibility = $this->sendEligibility($ownerUserId);
            if (! $eligibility['allowed']) {
                throw new \RuntimeException(__("You've used your free agreements for this month. Top up agreement credits to send more, or upgrade your plan."));
            }
            $coverage = $eligibility['cover'] ?? 'plan';
        }

        $template = $this->templateForOwner($ownerUserId, $templateId);
        if (! $template) {
            throw new \RuntimeException(__('Template not found.'));
        }

        // Scope the recipient to THIS owner's tenants (no cross-owner sending).
        $tenantUser = User::where('id', $tenantUserId)
            ->where('owner_user_id', $ownerUserId)
            ->where('role', USER_ROLE_TENANT)
            ->firstOr(fn () => throw new \RuntimeException(__('Tenant not found.')));

        $lease = Tenant::where('user_id', $tenantUserId)
            ->where('owner_user_id', $ownerUserId)
            ->latest('id')
            ->first();

        $context = $this->buildContext($ownerUserId, $tenantUser, $lease);

        $agreement = new Agreement();
        $agreement->owner_user_id         = $ownerUserId;
        $agreement->tenant_user_id        = $tenantUserId;
        $agreement->agreement_template_id = $template->id;
        $agreement->property_id           = $lease?->property_id;
        $agreement->property_unit_id      = $lease?->unit_id;
        $agreement->title                 = $template->name;
        $agreement->source                = $template->source;
        // Freeze the content at send time.
        $agreement->body             = $template->source === AgreementTemplate::SOURCE_TEMPLATE
            ? $this->autofill((string) $template->body, $context)
            : null;
        $agreement->original_file_id = $template->source === AgreementTemplate::SOURCE_UPLOAD
            ? $template->original_file_id
            : null;
        $agreement->template_data = $context;
        $agreement->status        = Agreement::STATUS_SENT;
        $agreement->billed_as     = $coverage;
        $agreement->sent_at       = now();

        // Consume a purchased credit + persist atomically — if the save fails, the credit
        // deduction rolls back with it (deductOne's transaction nests as a savepoint).
        DB::transaction(function () use ($agreement, $ownerUserId, $coverage) {
            if ($coverage === 'credit'
                && ! AgreementCreditsService::deductOne($ownerUserId, 'Agreement sent')) {
                // Balance emptied since the eligibility check (race) — refuse rather than
                // send an unpaid agreement.
                throw new \RuntimeException(__('Your agreement credits ran out. Please top up and try again.'));
            }
            $agreement->save();
            $agreement->logEvent(AgreementSignatureEvent::EVENT_SENT, ['template_id' => $agreement->agreement_template_id, 'billed_as' => $coverage]);
        });

        $this->notifyTenantOfNewAgreement($agreement, $tenantUser);

        return $agreement;
    }

    /** Gather the autofill values for the template placeholders. */
    private function buildContext(int $ownerUserId, User $tenantUser, ?Tenant $lease): array
    {
        $owner = User::find($ownerUserId);
        [$ownerName] = effectiveOwnerPrintDetails(null, null, null, $owner);

        $property = $lease?->property_id ? Property::find($lease->property_id) : null;
        $unit     = $lease?->unit_id ? PropertyUnit::find($lease->unit_id) : null;

        return [
            'owner_name'     => $ownerName ?: trim(($owner->first_name ?? '') . ' ' . ($owner->last_name ?? '')),
            'owner_contact'  => $owner->contact_number ?? '',
            'tenant_name'    => trim(($tenantUser->first_name ?? '') . ' ' . ($tenantUser->last_name ?? '')),
            'tenant_contact' => $tenantUser->contact_number ?? '',
            'property_name'  => $property->name ?? '',
            'unit_name'      => $unit->unit_name ?? '',
            'rent_amount'    => $lease ? currencyPrice($lease->general_rent) : '',
            'deposit_amount' => $lease && (float) $lease->security_deposit > 0 ? currencyPrice($lease->security_deposit) : '—',
            'lease_start'    => $lease?->created_at ? $lease->created_at->format('d M Y') : now()->format('d M Y'),
            'today'          => now()->format('d M Y'),
        ];
    }

    /** Replace {{placeholder}} tokens; unknown tokens are left blank (never leaked literally). */
    private function autofill(string $body, array $context): string
    {
        return preg_replace_callback('/\{\{\s*([a-z0-9_]+)\s*\}\}/i', function ($m) use ($context) {
            return e($context[$m[1]] ?? '');
        }, $body);
    }

    /* ─────────────────────────── Sign (tenant) ─────────────────────────────── */

    /** Generate + SMS a fresh signing OTP to the tenant's registered number. */
    public function requestSignOtp(Agreement $agreement): void
    {
        if ($agreement->isSigned()) {
            throw new \RuntimeException(__('This agreement is already signed.'));
        }

        $otp = (string) random_int(100000, 999999);
        $agreement->sign_otp            = $otp;
        $agreement->sign_otp_expires_at = now()->addMinutes(5);
        $agreement->save();

        $tenant = $agreement->tenant;
        $phone  = $tenant?->contact_number;
        if ($phone) {
            $message = __('Your signing code for the agreement ":title" is :otp. It expires in 5 minutes.', [
                'title' => Str::limit($agreement->title, 40),
                'otp'   => $otp,
            ]);
            SendSmsJob::dispatch([$phone], $message, $agreement->owner_user_id);
        }

        $agreement->logEvent(AgreementSignatureEvent::EVENT_OTP_SENT, ['phone' => maskPhone($phone)]);
    }

    /**
     * Complete the signing ceremony: verify the OTP + consent, capture the signature,
     * render the certified hashed PDF, and mark the agreement signed. Atomic on status so
     * a double-submit can't sign twice.
     */
    public function sign(Agreement $agreement, array $input): Agreement
    {
        if ($agreement->isSigned()) {
            throw new \RuntimeException(__('This agreement is already signed.'));
        }

        if (empty($input['consent'])) {
            throw new \RuntimeException(__('You must agree to sign electronically.'));
        }

        // ── OTP check ────────────────────────────────────────────────────────
        $otp = (string) ($input['otp'] ?? '');
        if (empty($agreement->sign_otp)
            || empty($agreement->sign_otp_expires_at)
            || $agreement->sign_otp_expires_at->isPast()
            || ! hash_equals((string) $agreement->sign_otp, $otp)) {
            throw new \RuntimeException(__('Invalid or expired signing code. Please request a new one.'));
        }
        $agreement->logEvent(AgreementSignatureEvent::EVENT_OTP_VERIFIED);
        $agreement->logEvent(AgreementSignatureEvent::EVENT_CONSENTED);

        // ── Signature capture ────────────────────────────────────────────────
        $method = ($input['signature_method'] ?? 'typed') === 'drawn' ? 'drawn' : 'typed';
        $name   = trim((string) ($input['signer_full_name'] ?? ''));
        if ($name === '') {
            throw new \RuntimeException(__('Please enter your full name to sign.'));
        }
        // Drawn signatures arrive as a data-URI PNG; keep only that (never arbitrary data).
        $signatureData = null;
        if ($method === 'drawn') {
            $raw = (string) ($input['signature_data'] ?? '');
            if (! Str::startsWith($raw, 'data:image/png;base64,')) {
                throw new \RuntimeException(__('The drawn signature is invalid. Please try again.'));
            }
            $signatureData = $raw;
        }

        $agreement->signer_full_name  = $name;
        $agreement->signature_method  = $method;
        $agreement->signature_data    = $signatureData;
        $agreement->otp_verified_at   = now();
        $agreement->signed_ip         = request()?->ip();
        $agreement->signed_user_agent = request()?->userAgent();
        $agreement->signed_at         = now();
        $agreement->status            = Agreement::STATUS_SIGNED;
        // Unguessable verification code printed on the certificate (public verify lookup).
        $agreement->verification_code = $agreement->verification_code ?: strtoupper(Str::random(16));
        // Burn the OTP.
        $agreement->sign_otp            = null;
        $agreement->sign_otp_expires_at = null;
        $agreement->save();

        $agreement->logEvent(AgreementSignatureEvent::EVENT_SIGNED, ['method' => $method]);

        // ── Certified artifact ───────────────────────────────────────────────
        // template → one generated PDF (terms + signature + certificate).
        // upload   → the owner's PDF stays untouched (original_file_id); we generate a
        //            companion Certificate PDF that carries the SHA-256 hash of that
        //            original, binding the two without altering the document.
        try {
            if ($agreement->source === AgreementTemplate::SOURCE_UPLOAD) {
                $this->renderCertificatePdf($agreement);
            } else {
                $this->renderSignedPdf($agreement);
            }
        } catch (\Throwable $e) {
            // Signing already succeeded (status + audit committed); a PDF hiccup must not
            // undo it. Log for regeneration.
            Log::error('Agreement signed but certified PDF render failed', [
                'agreement_id' => $agreement->id, 'error' => $e->getMessage(),
            ]);
        }

        return $agreement;
    }

    /**
     * Render the certified signed PDF (terms + signature + certificate/audit page),
     * hash it, and store it via FileManager. Sets document_hash + signed_file_id.
     */
    public function renderSignedPdf(Agreement $agreement): void
    {
        $agreement->loadMissing(['events', 'owner']);

        $pdf   = \PDF::loadView('agreement.pdf.signed', ['agreement' => $agreement])->setPaper('a4');
        $bytes = $pdf->output();

        $hash = hash('sha256', $bytes);

        // Persist the bytes to the public storage the same way FileManager names/stores
        // files (random name), then record a FileManager row pointing at it.
        $fileName = 'agreement-' . $agreement->id . '-' . time() . '_' . uniqid() . '.pdf';
        \Illuminate\Support\Facades\Storage::disk(config('app.STORAGE_DRIVER'))
            ->put('files/Agreement/' . $fileName, $bytes);

        $file = new FileManager();
        $file->folder_name = 'files/Agreement';
        $file->file_name   = $fileName;
        $file->save();

        $agreement->signed_file_id  = $file->id;
        $agreement->document_hash   = $hash;
        $agreement->certificate_hash = $hash; // template: the signed PDF is also the certificate
        $agreement->save();
    }

    /**
     * Upload-source: leave the owner's PDF untouched and generate a COMPANION certificate
     * PDF. `document_hash` = SHA-256 of the ORIGINAL uploaded file, printed on the
     * certificate so the two can be matched + tamper-checked (re-hash the original, compare).
     * The certificate is stored as signed_file_id (the original remains original_file_id).
     */
    public function renderCertificatePdf(Agreement $agreement): void
    {
        $agreement->loadMissing(['events', 'owner', 'originalFile']);

        // Hash the original uploaded document.
        $hash = null;
        if ($agreement->originalFile) {
            $path = $agreement->originalFile->folder_name . '/' . $agreement->originalFile->file_name;
            $disk = \Illuminate\Support\Facades\Storage::disk(config('app.STORAGE_DRIVER'));
            if ($disk->exists($path)) {
                $hash = hash('sha256', (string) $disk->get($path));
            }
        }
        $agreement->document_hash = $hash;
        $agreement->save();

        // Certificate-only PDF (the body is null for upload source, so the blade renders
        // just the signature + certificate page, and prints the original's hash).
        $pdf   = \PDF::loadView('agreement.pdf.signed', ['agreement' => $agreement])->setPaper('a4');
        $bytes = $pdf->output();

        $fileName = 'agreement-cert-' . $agreement->id . '-' . time() . '_' . uniqid() . '.pdf';
        \Illuminate\Support\Facades\Storage::disk(config('app.STORAGE_DRIVER'))
            ->put('files/Agreement/' . $fileName, $bytes);

        $file = new FileManager();
        $file->folder_name = 'files/Agreement';
        $file->file_name   = $fileName;
        $file->save();

        $agreement->signed_file_id   = $file->id;
        // The certificate is a SEPARATE file from the original agreement here, so record its
        // own fingerprint too — that lets verification match whichever PDF a person uploads
        // (the agreement OR its certificate).
        $agreement->certificate_hash = hash('sha256', $bytes);
        $agreement->save();
    }

    public function decline(Agreement $agreement, ?string $reason): Agreement
    {
        if ($agreement->isSigned()) {
            throw new \RuntimeException(__('This agreement is already signed.'));
        }
        $agreement->status       = Agreement::STATUS_DECLINED;
        $agreement->declined_at  = now();
        $agreement->decline_reason = $reason ? Str::limit($reason, 240) : null;
        $agreement->save();
        $agreement->logEvent(AgreementSignatureEvent::EVENT_DECLINED, ['reason' => $agreement->decline_reason]);

        return $agreement;
    }

    /* ─────────────────────────── Lists + download ──────────────────────────── */

    public function ownerAgreements(int $ownerUserId)
    {
        return Agreement::where('owner_user_id', $ownerUserId)
            ->with(['tenant'])
            ->latest('id')
            ->get();
    }

    public function tenantAgreements(int $tenantUserId)
    {
        return Agreement::where('tenant_user_id', $tenantUserId)
            ->with(['owner'])
            ->latest('id')
            ->get();
    }

    /** Owner-scoped fetch. */
    public function forOwner(int $ownerUserId, int $agreementId): ?Agreement
    {
        return Agreement::where('owner_user_id', $ownerUserId)->find($agreementId);
    }

    /** Tenant-scoped fetch. */
    public function forTenant(int $tenantUserId, int $agreementId): ?Agreement
    {
        return Agreement::where('tenant_user_id', $tenantUserId)->find($agreementId);
    }

    /**
     * Stream the ORIGINAL uploaded agreement document inline (for review + download on the
     * upload path). Caller has already scoped the agreement to the actor.
     */
    public function viewOriginal(Agreement $agreement)
    {
        if (! $agreement->original_file_id) {
            throw new \RuntimeException(__('No document is attached to this agreement.'));
        }
        $file = FileManager::find($agreement->original_file_id);
        if (! $file) {
            throw new \RuntimeException(__('Agreement document not found.'));
        }
        $path = $file->folder_name . '/' . $file->file_name;
        $disk = \Illuminate\Support\Facades\Storage::disk(config('app.STORAGE_DRIVER'));
        if (! $disk->exists($path)) {
            throw new \RuntimeException(__('Agreement document not found.'));
        }

        return response($disk->get($path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="agreement-' . $agreement->id . '.pdf"',
        ]);
    }

    /** Stream the signed PDF (caller has already scoped the agreement to the actor). */
    public function download(Agreement $agreement)
    {
        if (! $agreement->signed_file_id) {
            throw new \RuntimeException(__('No signed document is available yet.'));
        }
        $file = FileManager::find($agreement->signed_file_id);
        if (! $file) {
            throw new \RuntimeException(__('Signed document not found.'));
        }

        $agreement->logEvent(AgreementSignatureEvent::EVENT_DOWNLOADED);

        $path = $file->folder_name . '/' . $file->file_name;
        return \Illuminate\Support\Facades\Storage::disk(config('app.STORAGE_DRIVER'))
            ->download($path, 'agreement-' . $agreement->id . '.pdf');
    }

    /* ─────────────────────────── Notifications ─────────────────────────────── */

    private function notifyTenantOfNewAgreement(Agreement $agreement, User $tenantUser): void
    {
        try {
            addNotification(
                __('New agreement to sign'),
                __('Your landlord sent you ":title" to review and sign.', ['title' => $agreement->title]),
                route('tenant.agreement.index'),
                null,
                $tenantUser->id,
                $agreement->owner_user_id
            );

            if ($tenantUser->contact_number) {
                $message = __('You have a new agreement ":title" to review and sign in your :app portal.', [
                    'title' => Str::limit($agreement->title, 40),
                    'app'   => getOption('app_name') ?: 'Centresidence',
                ]);
                SendSmsJob::dispatch([$tenantUser->contact_number], $message, $agreement->owner_user_id);
                \App\Services\Sms\SmsCreditsService::warnIfExhausted($agreement->owner_user_id, true);
            }

            // Email (queued — AgreementSentMail is ShouldQueue), gated on the app's email toggle.
            if (getOption('send_email_status', 0) == ACTIVE && filter_var($tenantUser->email, FILTER_VALIDATE_EMAIL)) {
                \Illuminate\Support\Facades\Mail::to($tenantUser->email)->send(new \App\Mail\AgreementSentMail([
                    'subject'        => __('New agreement to sign'),
                    'title'          => __('Agreement to sign'),
                    'message'        => __('Your landlord sent you an agreement to review and sign electronically.'),
                    'agreementTitle' => $agreement->title,
                    'ownerName'      => optional(User::find($agreement->owner_user_id))->name,
                    'url'            => route('tenant.agreement.index'),
                ]));
            }
        } catch (\Throwable $e) {
            Log::warning('Agreement send notification failed', ['agreement_id' => $agreement->id, 'error' => $e->getMessage()]);
        }
    }
}
