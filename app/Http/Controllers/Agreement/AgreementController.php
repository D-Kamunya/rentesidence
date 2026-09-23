<?php

namespace App\Http\Controllers\Agreement;

use App\Http\Controllers\Controller;
use App\Models\Agreement;
use App\Models\AgreementTemplate;
use App\Models\FileManager;
use App\Models\Tenant;
use App\Services\Agreement\AgreementService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Internal e-signature agreements. Owner prepares + sends (from a reusable template);
 * tenant reviews + signs in-portal behind an SMS OTP. Replaces the DocuSign flow.
 */
class AgreementController extends Controller
{
    use ResponseTrait;

    public AgreementService $agreementService;

    public function __construct()
    {
        $this->agreementService = new AgreementService();
    }

    /* ───────────────────────────── Owner ───────────────────────────── */

    public function index()
    {
        $ownerId = auth()->id();

        $data['pageTitle']   = __('Agreements');
        $data['agreements']  = $this->agreementService->ownerAgreements($ownerId);
        $data['templates']   = $this->agreementService->templatesFor($ownerId);
        $data['eligibility'] = $this->agreementService->sendEligibility($ownerId);
        // Tenants of this owner (with a lease) for the send picker.
        $data['tenants']    = Tenant::query()
            ->join('users', 'tenants.user_id', '=', 'users.id')
            ->where('tenants.owner_user_id', $ownerId)
            ->where('users.role', USER_ROLE_TENANT)
            ->orderBy('users.first_name')
            ->get(['users.id as user_id', 'users.first_name', 'users.last_name']);

        return view('agreement.owner.index', $data);
    }

    public function send(Request $request)
    {
        $request->validate([
            'template_id' => 'required|integer',
            'user_id'     => 'required|integer',
        ]);

        try {
            $this->agreementService->send((int) auth()->id(), (int) $request->template_id, (int) $request->user_id);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('Agreement sent to the tenant for signing.'));
    }

    public function show($id)
    {
        $agreement = $this->agreementService->forOwner((int) auth()->id(), (int) $id);
        abort_if(! $agreement, 404);

        $data['pageTitle'] = __('Agreement');
        $data['agreement'] = $agreement->load(['tenant', 'events']);
        return view('agreement.owner.show', $data);
    }

    public function download($id)
    {
        $agreement = $this->agreementService->forOwner((int) auth()->id(), (int) $id);
        abort_if(! $agreement, 404);

        try {
            return $this->agreementService->download($agreement);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** View/download the ORIGINAL uploaded document (upload source). */
    public function document($id)
    {
        $agreement = $this->agreementService->forOwner((int) auth()->id(), (int) $id);
        abort_if(! $agreement, 404);

        try {
            return $this->agreementService->viewOriginal($agreement);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /* ───────────────────────── Template (owner) ───────────────────────── */

    public function templates()
    {
        $ownerId = auth()->id();
        $data['pageTitle'] = __('Agreement Template');
        $data['templates'] = $this->agreementService->templatesFor($ownerId);
        return view('agreement.owner.templates', $data);
    }

    public function templateUpdate(Request $request, $id)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'source' => 'required|in:template,upload',
            'body'   => 'required_if:source,template|nullable|string',
            'pdf'    => 'required_if:source,upload|nullable|file|mimes:pdf|max:10240',
        ]);

        $template = $this->agreementService->templateForOwner((int) auth()->id(), (int) $id);
        abort_if(! $template, 404);

        $template->name = $request->name;

        if ($request->source === AgreementTemplate::SOURCE_UPLOAD) {
            if ($request->hasFile('pdf')) {
                $newFile = new FileManager();
                $upload  = $newFile->upload('Agreement', $request->file('pdf'));
                if (! ($upload['status'] ?? false)) {
                    return back()->with('error', $upload['message'] ?? __('Could not upload the PDF.'));
                }
                $template->original_file_id = $upload['file']->id;
            } elseif (! $template->original_file_id) {
                return back()->with('error', __('Please choose a PDF to upload.'));
            }
            $template->source = AgreementTemplate::SOURCE_UPLOAD;
        } else {
            $template->source = AgreementTemplate::SOURCE_TEMPLATE;
            $template->body   = $request->body;
        }

        $template->save();

        return back()->with('success', __('Template saved. New agreements will use it.'));
    }

    /** Stream the template's currently-uploaded PDF inline (owner-scoped) — for the editor preview. */
    public function templateDocument($id)
    {
        $template = $this->agreementService->templateForOwner((int) auth()->id(), (int) $id);
        abort_if(! $template || ! $template->original_file_id, 404);

        $file = FileManager::find($template->original_file_id);
        abort_if(! $file, 404);

        $path = $file->folder_name . '/' . $file->file_name;
        $disk = Storage::disk(config('app.STORAGE_DRIVER'));
        abort_if(! $disk->exists($path), 404);

        return response($disk->get($path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="agreement-template.pdf"',
        ]);
    }

    /* ───────────────────────────── Tenant ───────────────────────────── */

    public function tenantAgreement()
    {
        $data['pageTitle']  = __('Agreements');
        $data['agreements'] = $this->agreementService->tenantAgreements((int) auth()->id());
        return view('agreement.tenant.index', $data);
    }

    public function tenantShow($id)
    {
        $agreement = $this->agreementService->forTenant((int) auth()->id(), (int) $id);
        abort_if(! $agreement, 404);

        // Mark viewed once.
        if ($agreement->status === Agreement::STATUS_SENT) {
            $agreement->status    = Agreement::STATUS_VIEWED;
            $agreement->viewed_at = now();
            $agreement->save();
            $agreement->logEvent(\App\Models\AgreementSignatureEvent::EVENT_VIEWED);
        }

        $data['pageTitle'] = __('Review & Sign');
        $data['agreement'] = $agreement;
        return view('agreement.tenant.sign', $data);
    }

    public function requestOtp($id)
    {
        $agreement = $this->agreementService->forTenant((int) auth()->id(), (int) $id);
        abort_if(! $agreement, 404);

        try {
            $this->agreementService->requestSignOtp($agreement);
        } catch (\Throwable $e) {
            return $this->error([], $e->getMessage());
        }

        $message = __('A signing code has been sent to your phone.');
        $data    = [];

        // DEV ONLY: surface the code so it can be tested without a working SMS gateway.
        // config('app.debug') is false in production, so this never leaks live.
        if (config('app.debug')) {
            $data['dev_otp'] = $agreement->sign_otp;
            $message .= ' [dev code: ' . $agreement->sign_otp . ']';
        }

        return $this->success($data, $message);
    }

    public function sign(Request $request, $id)
    {
        $agreement = $this->agreementService->forTenant((int) auth()->id(), (int) $id);
        abort_if(! $agreement, 404);

        try {
            $this->agreementService->sign($agreement, $request->only([
                'consent', 'otp', 'signer_full_name', 'signature_method', 'signature_data',
            ]));
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('tenant.agreement.index')->with('success', __('Agreement signed. You can download your copy anytime.'));
    }

    public function decline(Request $request, $id)
    {
        $agreement = $this->agreementService->forTenant((int) auth()->id(), (int) $id);
        abort_if(! $agreement, 404);

        try {
            $this->agreementService->decline($agreement, $request->input('reason'));
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('tenant.agreement.index')->with('success', __('Agreement declined.'));
    }

    public function tenantDownload($id)
    {
        $agreement = $this->agreementService->forTenant((int) auth()->id(), (int) $id);
        abort_if(! $agreement, 404);

        try {
            return $this->agreementService->download($agreement);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** View/download the ORIGINAL uploaded document (upload source). */
    public function tenantDocument($id)
    {
        $agreement = $this->agreementService->forTenant((int) auth()->id(), (int) $id);
        abort_if(! $agreement, 404);

        try {
            return $this->agreementService->viewOriginal($agreement);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
