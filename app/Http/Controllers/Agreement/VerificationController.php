<?php

namespace App\Http\Controllers\Agreement;

use App\Http\Controllers\Controller;
use App\Models\Agreement;
use Illuminate\Http\Request;

/**
 * PUBLIC certificate verification. Anyone holding a signed certificate can confirm it's
 * genuine: look the agreement up by its Ref # + verification code, see who signed it and
 * when, and — if they have the document — upload it so the SERVER computes its SHA-256 and
 * compares to the recorded hash. No one has to run a hash tool by hand. Gated by the
 * unguessable code (bearer), so agreements can't be enumerated.
 */
class VerificationController extends Controller
{
    public function show(Request $request, ?string $code = null)
    {
        $code      = trim((string) ($code ?: $request->get('code', '')));
        $agreement = $this->resolve($code);

        return view('agreement.verify', [
            'code'      => $code,
            'agreement' => $agreement,
            'notFound'  => $code !== '' && ! $agreement,
        ]);
    }

    /**
     * Verify by DOCUMENT (no code needed) — hash the uploaded file and find the signed
     * agreement whose recorded hash matches. Safe: if it matches, the uploader already
     * holds that exact document, so nothing new is revealed.
     */
    public function checkByDocument(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf|max:20480',
        ]);

        $uploadedHash = hash('sha256', (string) file_get_contents($request->file('document')->getRealPath()));

        // Match EITHER the agreement document OR its signature certificate — for an
        // uploaded-PDF agreement these are two different files, and a person is as likely to
        // hand over the certificate as the original.
        $agreement = Agreement::query()
            ->where('status', Agreement::STATUS_SIGNED)
            ->where(function ($q) use ($uploadedHash) {
                $q->where('document_hash', $uploadedHash)
                  ->orWhere('certificate_hash', $uploadedHash);
            })
            ->with(['owner', 'events'])
            ->first();

        return view('agreement.verify', [
            'code'        => $agreement?->verification_code ?? '',
            'agreement'   => $agreement,
            'notFound'    => false,
            'docNotFound' => ! $agreement,
            'check'       => $agreement
                ? ['match' => true, 'uploadedHash' => $uploadedHash, 'kind' => $this->matchKind($agreement, $uploadedHash)]
                : null,
        ]);
    }

    public function check(Request $request, string $code)
    {
        $agreement = $this->resolve($code);
        if (! $agreement) {
            return redirect()->route('agreement.verify', ['code' => $code]);
        }

        $request->validate([
            'document' => 'required|file|mimes:pdf|max:20480',
        ]);

        $uploadedHash = hash('sha256', (string) file_get_contents($request->file('document')->getRealPath()));
        $kind  = $this->matchKind($agreement, $uploadedHash);
        $match = $kind !== null;

        return view('agreement.verify', [
            'code'      => $code,
            'agreement' => $agreement,
            'notFound'  => false,
            'check'     => ['match' => $match, 'uploadedHash' => $uploadedHash, 'kind' => $kind],
        ]);
    }

    /**
     * Which recorded fingerprint (if any) the uploaded file matches: the agreement
     * 'document', its 'certificate', or null for no match. Constant-time comparisons.
     */
    private function matchKind(Agreement $agreement, string $uploadedHash): ?string
    {
        if ($agreement->document_hash && hash_equals((string) $agreement->document_hash, $uploadedHash)) {
            return 'document';
        }
        if ($agreement->certificate_hash && hash_equals((string) $agreement->certificate_hash, $uploadedHash)) {
            return 'certificate';
        }
        return null;
    }

    /** Resolve a SIGNED agreement by its verification code. */
    private function resolve(string $code): ?Agreement
    {
        if ($code === '') {
            return null;
        }

        return Agreement::query()
            ->where('verification_code', $code)
            ->where('status', Agreement::STATUS_SIGNED)
            ->with(['owner', 'events'])
            ->first();
    }
}
