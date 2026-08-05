<?php

namespace App\Centresidence\Services;

use App\Centresidence\Events\ApplicationApproved;
use App\Centresidence\Events\ApplicationRejected;
use App\Centresidence\Events\ApplicationStatusChanged;
use App\Centresidence\Events\ApplicationSubmitted;
use App\Centresidence\Exceptions\FacilityInfeasibleException;
use App\Centresidence\Exceptions\InvalidApplicationTransitionException;
use App\Centresidence\Exceptions\UnderwritingFailedException;
use App\Centresidence\Models\ApplicationStatusHistory;
use App\Centresidence\Models\FinanceApplication;
use App\Centresidence\Models\FinancePartnerModule;
use App\Centresidence\Models\Module;
use App\Centresidence\Models\ModuleCostComponent;
use App\Centresidence\Models\ModulePlatformFeeConfig;
use App\Centresidence\Support\Money;
use App\Centresidence\Models\ModulePricingCatalogueItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Finance Application Engine (handbook §9.3) — owns the application lifecycle
 * state machine, the auto-calculated facility maths, soft underwriting at
 * submission, and the immutable status-history audit trail.
 *
 * Facility creation on approval is handled by the Finance Facility Engine (WP7),
 * which listens for ApplicationApproved; this service stops at status=approved.
 */
class FinanceApplicationService
{
    /** Allowed status transitions (handbook §9.3.1 lifecycle). */
    private const TRANSITIONS = [
        FinanceApplication::STATUS_DRAFT        => [FinanceApplication::STATUS_SUBMITTED, FinanceApplication::STATUS_WITHDRAWN, FinanceApplication::STATUS_CANCELLED],
        FinanceApplication::STATUS_SUBMITTED    => [FinanceApplication::STATUS_UNDER_REVIEW, FinanceApplication::STATUS_APPROVED, FinanceApplication::STATUS_REJECTED, FinanceApplication::STATUS_WITHDRAWN, FinanceApplication::STATUS_CANCELLED],
        FinanceApplication::STATUS_UNDER_REVIEW => [FinanceApplication::STATUS_APPROVED, FinanceApplication::STATUS_REJECTED, FinanceApplication::STATUS_CANCELLED],
        FinanceApplication::STATUS_APPROVED     => [FinanceApplication::STATUS_DISBURSED, FinanceApplication::STATUS_CANCELLED],
        FinanceApplication::STATUS_REJECTED     => [],
        FinanceApplication::STATUS_DISBURSED    => [],
        FinanceApplication::STATUS_WITHDRAWN    => [],
        FinanceApplication::STATUS_CANCELLED    => [],
    ];

    /** Status => the timestamp column stamped on entry. */
    private const STATUS_TIMESTAMPS = [
        FinanceApplication::STATUS_SUBMITTED    => 'submitted_at',
        FinanceApplication::STATUS_UNDER_REVIEW => 'under_review_at',
        FinanceApplication::STATUS_APPROVED     => 'approved_at',
        FinanceApplication::STATUS_REJECTED     => 'rejected_at',
        FinanceApplication::STATUS_DISBURSED    => 'disbursed_at',
    ];

    public function __construct(
        private FinancingCalculator $calculator,
        private UnderwritingEngine $underwriting,
        private PaymentModeService $paymentMode
    ) {
    }

    /**
     * Create a DRAFT application with auto-calculated facility maths.
     *
     * @param  array{owner_id:int, property_id:int, module_id:int, finance_partner_id:int,
     *   finance_partner_module_id:int, catalogue_item_id:int, quantity:int,
     *   repayment_months?:int, repayment_percentage?:float, platform_fee_percentage?:float,
     *   application_data_json?:array}  $data
     */
    public function createDraft(array $data): FinanceApplication
    {
        // Financing requires transaction mode (rent must route through the
        // company account for at-source repayment). Block before the owner even
        // begins; the UI prompts them to switch first.
        $this->paymentMode->assertEligibleForFinancing((int) $data['owner_id']);

        $catalogueItem = ModulePricingCatalogueItem::findOrFail($data['catalogue_item_id']);
        $partnerModule = FinancePartnerModule::findOrFail($data['finance_partner_module_id']);

        $feePercentage = (string) ($data['platform_fee_percentage'] ?? $this->resolvePlatformFee($data['module_id']));
        $months = (int) ($data['repayment_months'] ?? $partnerModule->min_repayment_months);

        // Financed per-unit cost = hardware + installation. Test catalogues carry
        // no installation_cost (default 0) so existing expectations hold; real
        // catalogues fold the install fee into the financed principal.
        $perUnitFinanced = bcadd(
            (string) $catalogueItem->unit_price,
            (string) ($catalogueItem->installation_cost ?? 0),
            2
        );

        $maths = $this->calculator->compute(
            $perUnitFinanced,
            (int) $data['quantity'],
            $feePercentage,
            (string) $partnerModule->interest_rate,
            $months,
            $partnerModule->interest_rate_type
        );

        // Partial financing: the owner may put down a contribution and finance
        // only the remainder. financed = total project cost − contribution. The
        // contribution is capped at the total (you can't pay down more than you
        // owe); interest/monthly are computed on the financed portion only.
        $requested = Money::fromDecimal($maths['requested_amount']);
        $contribution = Money::fromDecimal((string) ($data['owner_contribution'] ?? 0))->cappedAt($requested);
        $financed = $requested->minus($contribution);
        $monthly = $this->calculator->monthlyRepayment(
            $financed->toDecimal(),
            (string) $partnerModule->interest_rate,
            $months,
            $partnerModule->interest_rate_type
        );

        // Feasibility gate: the scheduled monthly PLUS the financed module's own
        // monthly infra cost (which also draws from the rent budget, ahead of the
        // facility) must be collectable within the owner's effective cap —
        // otherwise the cap throttles collection below the schedule and the
        // facility could never repay on its term. Skipped when rent is unknown.
        $rent = (float) ($data['property_rent'] ?? 0);
        if ($rent > 0) {
            $defaultCap = (float) config('centresidence.billing.max_total_rent_deduction_percentage', 60);
            $consented = (float) ($data['consented_deduction_cap'] ?? 0);
            $effectiveCap = $consented > $defaultCap ? $consented : $defaultCap;
            // The facility competes with both its own new module's infra AND the
            // owner's already-deployed transaction-module infra on this property —
            // all three draw from the same rent budget.
            $moduleInfra = $this->projectedModuleInfra((int) $data['module_id'], (int) $data['quantity']);
            $existingInfra = (float) ($data['existing_infra'] ?? 0);
            $requiredPct = ($monthly->toFloat() + $moduleInfra + $existingInfra) / $rent * 100;
            if ($requiredPct > $effectiveCap + 0.5) {
                throw new FacilityInfeasibleException($requiredPct, $effectiveCap);
            }
        }

        return DB::transaction(function () use ($data, $partnerModule, $feePercentage, $months, $maths, $contribution, $financed, $monthly) {
            $application = FinanceApplication::create([
                'owner_id' => $data['owner_id'],
                'property_id' => $data['property_id'],
                'module_id' => $data['module_id'],
                'finance_partner_id' => $data['finance_partner_id'],
                'finance_partner_module_id' => $data['finance_partner_module_id'],
                'catalogue_item_id' => $data['catalogue_item_id'],
                'quantity' => $data['quantity'],
                'base_cost' => $maths['base_cost'],
                'platform_fee_percentage' => $feePercentage,
                'platform_fee_amount' => $maths['platform_fee_amount'],
                'requested_amount' => $maths['requested_amount'],
                'owner_contribution' => $contribution->toDecimal(),
                'financed_amount' => $financed->toDecimal(),
                'interest_rate_snapshot' => $partnerModule->interest_rate,
                'repayment_percentage' => $data['repayment_percentage'] ?? $partnerModule->max_rent_deduction_percentage,
                'consented_deduction_cap' => $data['consented_deduction_cap'] ?? null,
                'repayment_months' => $months,
                'estimated_monthly_repayment' => $monthly->toDecimal(),
                'status' => FinanceApplication::STATUS_DRAFT,
                'application_data_json' => $data['application_data_json'] ?? null,
            ]);

            // Human-readable reference: FIN-YYYY-00001.
            $application->forceFill([
                'application_number' => 'FIN-' . now()->year . '-' . str_pad((string) $application->id, 5, '0', STR_PAD_LEFT),
            ])->save();

            $this->logHistory($application, null, FinanceApplication::STATUS_DRAFT, $data['owner_id'] ?? null, 'Application created');

            return $application;
        });
    }

    /**
     * Submit a draft. Runs soft underwriting first; HARD failures block
     * submission (handbook §9.7 step 3). The result is always snapshotted.
     *
     * @param  array<string,mixed>  $underwritingContext
     */
    public function submit(FinanceApplication $application, array $underwritingContext = [], ?int $actorId = null): FinanceApplication
    {
        $result = $this->underwriting->evaluate($application->partnerModule, $underwritingContext);
        $application->forceFill(['underwriting_result_json' => $result])->save();

        if (! $result['passed']) {
            throw new UnderwritingFailedException($result['hard_failures']);
        }

        return $this->transitionTo($application, FinanceApplication::STATUS_SUBMITTED, $actorId, 'Submitted by owner');
    }

    public function moveToReview(FinanceApplication $application, ?int $actorId = null): FinanceApplication
    {
        return $this->transitionTo($application, FinanceApplication::STATUS_UNDER_REVIEW, $actorId, 'Underwriting in progress');
    }

    public function approve(FinanceApplication $application, string $approvedAmount, ?int $actorId = null): FinanceApplication
    {
        $application->forceFill(['approved_amount' => $approvedAmount])->save();

        return $this->transitionTo($application, FinanceApplication::STATUS_APPROVED, $actorId, 'Approved by partner');
    }

    public function reject(FinanceApplication $application, string $reason, ?int $actorId = null): FinanceApplication
    {
        $application->forceFill(['rejection_reason' => $reason])->save();

        return $this->transitionTo($application, FinanceApplication::STATUS_REJECTED, $actorId, $reason);
    }

    public function withdraw(FinanceApplication $application, ?int $actorId = null): FinanceApplication
    {
        return $this->transitionTo($application, FinanceApplication::STATUS_WITHDRAWN, $actorId, 'Withdrawn by owner');
    }

    public function cancel(FinanceApplication $application, string $reason = 'Cancelled', ?int $actorId = null): FinanceApplication
    {
        return $this->transitionTo($application, FinanceApplication::STATUS_CANCELLED, $actorId, $reason);
    }

    /**
     * Core transition: validates against the state machine, stamps the entry
     * timestamp, logs history, and dispatches events.
     */
    public function transitionTo(FinanceApplication $application, string $toStatus, ?int $actorId, ?string $reason = null, array $metadata = []): FinanceApplication
    {
        $from = $application->status;
        $allowed = self::TRANSITIONS[$from] ?? [];

        if (! in_array($toStatus, $allowed, true)) {
            throw new InvalidApplicationTransitionException($from, $toStatus);
        }

        return DB::transaction(function () use ($application, $from, $toStatus, $actorId, $reason, $metadata) {
            $changes = ['status' => $toStatus];
            if (isset(self::STATUS_TIMESTAMPS[$toStatus])) {
                $changes[self::STATUS_TIMESTAMPS[$toStatus]] = Carbon::now();
            }
            $application->forceFill($changes)->save();

            $this->logHistory($application, $from, $toStatus, $actorId, $reason, $metadata);

            ApplicationStatusChanged::dispatch($application, $from, $toStatus);
            $this->dispatchSpecific($application, $toStatus);

            return $application;
        });
    }

    // ── Internals ─────────────────────────────────────────────────────────

    private function dispatchSpecific(FinanceApplication $application, string $toStatus): void
    {
        match ($toStatus) {
            FinanceApplication::STATUS_SUBMITTED => ApplicationSubmitted::dispatch($application),
            FinanceApplication::STATUS_APPROVED  => ApplicationApproved::dispatch($application),
            FinanceApplication::STATUS_REJECTED  => ApplicationRejected::dispatch($application),
            default => null,
        };
    }

    /**
     * Projected monthly infrastructure cost of the financed module — its
     * per-active-device cost components × quantity, plus any flat-monthly
     * components. Used by the feasibility gate (this cost competes with the
     * facility for the rent budget).
     */
    private function projectedModuleInfra(int $moduleId, int $quantity): float
    {
        $module = Module::with('activeCostComponents')->find($moduleId);
        if (! $module) {
            return 0.0;
        }

        $infra = 0.0;
        foreach ($module->activeCostComponents as $component) {
            if ($component->cost_model === ModuleCostComponent::COST_MODEL_PER_ACTIVE_DEVICE) {
                $infra += (float) $component->rate * $quantity;
            } elseif ($component->cost_model === ModuleCostComponent::COST_MODEL_FLAT_MONTHLY) {
                $infra += (float) $component->rate;
            }
        }

        return $infra;
    }

    private function logHistory(FinanceApplication $application, ?string $from, string $to, ?int $actorId, ?string $reason, array $metadata = []): void
    {
        ApplicationStatusHistory::create([
            'finance_application_id' => $application->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => $actorId,
            'change_reason' => $reason,
            'metadata_json' => $metadata ?: null,
            'created_at' => Carbon::now(),
        ]);
    }

    private function resolvePlatformFee(int $moduleId): string
    {
        $config = ModulePlatformFeeConfig::query()
            ->where('module_id', $moduleId)
            ->where('is_active', true)
            ->latest('id')
            ->first();

        return (string) ($config->fee_percentage ?? 0);
    }
}
