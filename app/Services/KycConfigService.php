<?php

namespace App\Services;

use App\Models\FileManager;
use App\Models\KycConfig;
use App\Models\KycVerification;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\DB;

class KycConfigService
{
    use ResponseTrait;

    public function getAll()
    {
        $this->ensureDefaults(auth()->id());

        // Settings → Document Config is now POLICY only (tenant-wide requirements every
        // tenant must provide). Ad-hoc requests to a SPECIFIC tenant (tenant_id set) are
        // created + managed from that tenant's detail page instead.
        return KycConfig::query()
            ->select('kyc_configs.*')
            ->where('kyc_configs.owner_user_id', auth()->id())
            ->whereNull('kyc_configs.tenant_id')
            ->get();
    }

    /**
     * Ad-hoc document requests the owner made to ONE specific tenant, each annotated with
     * its fulfillment: verification_status = null (not uploaded), or the KYC_STATUS_* the
     * tenant's upload is at. Drives the "Requested documents" panel on the tenant page.
     */
    /**
     * How many per-tenant document REQUESTS the tenant still needs to act on — i.e. requested but
     * not yet submitted (no verification), or rejected (must re-submit). Excludes pending (already
     * submitted, awaiting the owner) + accepted. Drives the tenant dashboard nudge.
     */
    public function outstandingRequestCountForTenant($tenantId): int
    {
        $verifications = KycVerification::where('tenant_id', $tenantId)->get()->keyBy('kyc_config_id');

        return KycConfig::where('tenant_id', $tenantId)->get()
            ->filter(function ($cfg) use ($verifications) {
                $v = $verifications->get($cfg->id);
                $status = $v ? (int) $v->status : null;
                return $status === null || $status === KYC_STATUS_REJECTED; // tenant must submit / re-submit
            })->count();
    }

    public function getTenantRequests($tenantId)
    {
        $verifications = KycVerification::where('tenant_id', $tenantId)->get()->keyBy('kyc_config_id');

        return KycConfig::where('owner_user_id', auth()->id())
            ->where('tenant_id', $tenantId)
            ->latest('id')
            ->get()
            ->map(function ($cfg) use ($verifications) {
                $v = $verifications->get($cfg->id);
                $cfg->verification_status = $v ? (int) $v->status : null;
                $cfg->verification_id = $v?->id;
                $cfg->front = $v?->front;
                $cfg->back = $v?->back;
                $cfg->reject_reason = $v?->reason;
                return $cfg;
            });
    }

    /**
     * Self-healing plug-and-play seeding: give an owner the default document-config
     * requests the FIRST time the document feature is touched (owner opens Document
     * Config, or a tenant opens their documents page) — no deploy command / migration
     * needed, so it works automatically on the live shared host. Uses withTrashed() so
     * an owner who INTENTIONALLY deleted all of theirs is never re-seeded. Guarded so it
     * can never break the page it's called from.
     */
    public function ensureDefaults(?int $ownerUserId): void
    {
        ensureOwnerDefaults($ownerUserId, KycConfig::class, 'setOwnerDefaultDocumentConfig');
    }

    public function getActiveAll()
    {
        return KycConfig::where('tenant_id', null)->where('status', ACTIVE)->get();
    }

    public function getActiveByTenantId($id)
    {
        $this->ensureDefaults(auth()->user()->owner_user_id ?? null);

        $kycVerificationExistIds = KycVerification::query()
            ->where('tenant_id', $id)
            ->pluck('kyc_config_id')
            ->toArray();

        $configs = KycConfig::query()
            ->where(function ($query) use ($id) {
                $query->where('tenant_id', $id)
                    ->orWhereNull('tenant_id');
            })
            ->where('owner_user_id', auth()->user()->owner_user_id)
            ->where('status', ACTIVE)
            ->whereNotIn('id', $kycVerificationExistIds)
            ->get();
        return $configs?->makeHidden(['created_at', 'updated_at', 'deleted_at', 'owner_user_id']);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $kycConfig = KycConfig::updateOrCreate([
                'id' => $request->id,
                'owner_user_id' => auth()->id()
            ], [
                'name' => $request->name,
                'details' => $request->details,
                'status' => $request->status,
                'tenant_id' => $request->tenant_id,
                'owner_user_id' => auth()->id(),
                'is_both' => $request->is_both == 'on' ? ACTIVE : DEACTIVATE,
            ]);

            /*File Manager Call upload*/
            if ($request->hasFile('demo')) {
                $existFile = FileManager::where('origin_type', 'App\Models\KycConfig')->where('origin_id', $kycConfig->id)->first();
                if ($existFile) {
                    $existFile->removeFile();
                    $upload = $existFile->updateUpload($existFile->id, 'KycConfig', $request->demo);
                } else {
                    $new_file = new FileManager();
                    $upload = $new_file->upload('KycConfig', $request->demo);
                }

                if ($upload['status']) {
                    $upload['file']->origin_id = $kycConfig->id;
                    $upload['file']->origin_type = "App\Models\KycConfig";
                    $upload['file']->save();
                } else {
                    throw new Exception($upload['message']);
                }
            }
            /*End*/

            DB::commit();
            $message = $request->id ? __(UPDATED_SUCCESSFULLY) : __(CREATED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    public function getInfo($id)
    {
        return KycConfig::where('owner_user_id', auth()->id())->findOrFail($id);
    }

    public function delete($id)
    {
        $kycConfig = KycConfig::where('owner_user_id', auth()->id())->findOrFail($id);

        if ((int) $kycConfig->is_default === 1) {
            return redirect()->back()->with('error', __('Default document requests cannot be deleted.'));
        }

        $kycConfig->delete();
        return redirect()->back()->with('success', __(DELETED_SUCCESSFULLY));
    }
}
