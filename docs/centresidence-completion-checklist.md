# Centresidence Finance OS — Completion Map

> **The build map to a committable, internally-complete Finance OS.** Reconciles the feature registry (`centresidence-feature-checklist.md`) against the *actual code* as of **2026-08-02** — several items the registry marked ⬜/🟡 were built since its 2026-07-31 snapshot. Use this to drive the remaining build, the Finance-OS commit, and the reviewer's test sequence.
>
> **Legend:** ✅ done & verified in code · 🟡 partial · ⬜ not started · 🔒 blocked on a decision
> Companions: `centresidence-system-guide.md` (how it works, end-to-end) · `centresidence-feature-checklist.md` (proposal ↔ code registry).

---

## 1. Reconciliation — deltas verified against code (2026-08-02)
What actually changed since the registry snapshot:

| Area | Registry said | Code reality now | Status |
|---|---|---|---|
| Owner down-payment collection (partial financing) | (implicit) | `DownPaymentCollectionService` → `MpesaStkService::push` + live callback route `centresidence.down-payment.callback` (`DownPaymentCallbackController`) + `DownPaymentCollectionTest` | ✅ new |
| Partner remittance driver | 🟡 built; live driver ⬜ | `PartnerPayoutService` → `MpesaB2BService::send` (PayBill/BuyGoods) — live driver wired | ✅ driver |
| Partner remittance **confirmation** | ⬜ | B2B send sets `ResultURL` but **no route/handler reconciles** `PartnerRemittanceBatchItem` on the async result/timeout — money out, unconfirmed | ⬜ **gap** |
| Owner financing UI | (not tracked) | `Owner/FinancingController` full: apply, self-finance, accelerate, settle-early, deductions, switch-mode | ✅ new |
| Finance-partner portal | ✅ | dashboard, product CRUD, application approve/reject, facilities, learn, KB | ✅ |
| Live ChirpStack adapter | 🟡 simulated; live ⬜ | `LiveChirpStackDriver` = stub (`return false`, `TODO(go-live)`) | ⬜ (confirmed) |
| Subscription-owner infra collection (Finding #1) | ⬜ | `InfrastructureCostEngine` only settles `billing_model = TRANSACTION` (from rent); subscription owners billed, never charged | ⬜ (confirmed) |
| Live tenant token purchase (Finding #3) | ⬜ | no route/UI; but `TokenEngine` + `CreditOwnerWalletOnTokenPurchase` listener exist (engine ready, no front door) | ⬜ (confirmed) |
| Affiliate balance refactor (Finding #5) | ⬜ deferred | **DONE** (one-row-per-period + unique index + dedupe + lock) | ✅ (this session) |
| Affiliate marketplace cut scoping (Finding #4) | ✅ | ✅ | ✅ |

**Test coverage:** 20 Centresidence test files (unit + feature) — strong on the built engines; **none cover the ⬜ gaps** below (remittance confirmation, subscription infra collection, live token path, live ChirpStack).

---

## 2. To-completion checklist (ordered)

### A · Decisions that block (🔒 — resolve first; business/legal)
- [ ] **A1 · CBK custody** — move the rent/owner-wallet float to the partner bank as holder-of-record. Go-live blocker regardless of features.
- [ ] **A2 · Servicing / origination fee model** — per-partner configurable (origination fee per facility + life-of-facility servicing fee). The "don't sell short" revenue lever.
- [ ] **A3 · Pilot vertical pick** — the bank's chosen vertical calibrates which live-metering/token work is needed first.

### B · Money-safety completions (code-completable now — do before live money)
- [x] **B1 · Partner remittance confirmation callback + reconciliation** — ✅ **DONE 2026-08-04.** `MpesaB2BService::send` now takes a per-call `resultUrl`; `PartnerPayoutService` passes a per-batch callback (`centresidence.remittance.callback` — dedicated, since the shared config result URL routes to the owner-withdrawal B2C handler); `PartnerRemittanceCallbackController` parses the B2B `Result`, and `PartnerRemittanceService::confirmBatch`/`failBatch` move SENT→CONFIRMED (receipt stored, `confirmation_received_at` set) or SENT→FAILED (retryable via `payBatch`). Idempotent — only a SENT batch transitions. Tests: `PartnerRemittanceConfirmationTest` (5) + full Centresidence suite **97/97 green**. *(Follow-up: B2C partner-payout confirmation not yet wired — most partners settle via B2B paybill/bank.)*
- [ ] **B2 · Subscription-owner infra collection** (Finding #1) — 🔨 **IN PROGRESS (merge model, agreed 2026-08-04).** Decision: **merge plan + infra into one bill** (single tap, un-cherry-pickable) for monthly owners; yearly owners pay plan yearly + infra monthly. Enforcement = **account readonly/degraded** on overdue infra (primary; token-revenue fallback kept only as a metered safety net — impractical for large/non-metered/low-usage). Must show a **plan + infra breakdown** so owners see why the total exceeds the plan price. Staged: **① account-standing core + notification rework ✅ DONE** (`OwnerBillingStandingService::infraStanding` current/due/overdue; `SubscriptionService::getSubscriptionState` unified plan+infra, configurable `plan_expiry_notice_days`, new `infra_due`/`restricted` states; dashboard banner reworked to the Centresidence token aesthetic + the new states; 6 tests, 103/103 Centresidence green). **② readonly gate middleware ✅ DONE** (`EnforceInfraStanding` — infra overdue blocks money-making/expansion writes: invoicing, adding property/unit/tenant, new financing, listing products; reads + operational/tenant writes + pay flow stay open; alias `infra.standing` on the owner group; 5 tests, 108/108 green) · **③ infra bill payable via STK + breakdown UI ✅ DONE** (`InfraBillPaymentService` outstanding/initiate/markPaid; `InfraBillCallbackController` STK callback → markPaid idempotent; `owner.infra-bill.pay` [ungated] + `centresidence.infra-bill.callback`; My-Subscription page shows the plan+module breakdown [already existed] + a new outstanding-due block with a Pay-via-M-Pesa button, amber/red per status; only infra collected [plan stays on SubscriptionOrder pre-merge]; 5 tests, 113/113 green) · **④ the merge ✅ DONE (via the reused `gateway-list` pay blade, user-approved 2026-08-04)** — `placeOrder` bundles outstanding infra into `transaction_amount` + new `subscription_orders.infra_amount` (KES-gated: only when the transaction currency is KES; base-currency fields untouched; server recomputes so no client-trust); `setUserPackage` settles the bundled infra via `InfraBillPaymentService::markPaid` at the CENTRAL activation chokepoint (covers every confirmation path — M-Pesa callback, gateway helper, PackageService…), idempotent + guarded so it never rolls back plan activation; pay blade shows a "Module infrastructure" line + Total Due = plan + infra. When infra=0 the flow is byte-identical to before. The two pay buttons (plan-subscribe / standalone infra) don't double-charge (whichever settles first zeroes the other's outstanding). 114/114 green. **Refinement TODO:** converge to a single visible pay button (hide standalone infra button when a plan renewal would bundle it). **CADENCE ALIGNMENT DONE 2026-08-04 (user-decided, option A):** infra-due is tied to the plan renewal for MONTHLY owners — `OwnerBillingStandingService::isReadonly()` (cadence-aware, guarded) blocks a monthly owner ONLY once the plan has lapsed (infra rides with the renewal; while active it's a gentle `infra_due` nudge, never mid-cycle readonly); YEARLY / plan-less owners keep the standalone monthly grace (block on overdue). Wired into the middleware + `getSubscriptionState` (`restricted` flag is now cadence-aware — can be true while the banner reads `expired`). Verified on real data (Mr Owner): expired-monthly+unpaid→readonly YES; active-monthly+unpaid→readonly no/`infra_due`. **Enforcement nuance:** a lapsed monthly owner WITH unpaid infra now gets full readonly (stronger than the old plan-expiry limits) — but only owners who actually have deployed modules; module-less owners are unaffected. · **⑤ wire `activateOverdue` into process-collections ✅ DONE** — `ProcessCollectionsCommand` now injects `CommissionFallbackService` and arms the metered token-recovery fallback on overdue infra invoices each run (guarded so it never breaks facility collections; reports "N infra fallback(s) armed"). Inert until live tokens (C1). Verified: command runs + arms; 114/114 green.

**⇒ B2 IS COMPLETE** (all 5 stages + the cadence alignment + field fixes + banner/renew UX). ~~C1-time reconciliation TODO~~ **→ CLOSED in C1 (2026-08-05):** the metered fallback / merge split is settled — metered recovered from tokens is netted out of the owner's bill (no double charge), and the fallback is now cadence-aware (skips active monthly owners). The standalone infra pay button was already converged into the plan-renewal button for monthly owners in the New-Invoice/standalone-button pass.

### C · Fastest functional finishes (engine ready, needs the front door)
- [x] **C1 · Live tenant token purchase** (Finding #3) — ✅ **DONE 2026-08-05.** The missing front door + payment leg on top of the existing `TokenEngine` + `CreditOwnerWalletOnTokenPurchase` listener:
  - `TokenPurchaseCollectionService` — `modulesFor(tenant)` (metered + active + token-config-active modules on the tenant's own property/unit, decorated with wallet balance + price/unit), `authorizedModule()` (the single IDOR gate — a tenant may only buy for a module on a property/unit they occupy; re-checked at settle), `initiate()` (driver-gated exactly like infra-bill/down-payment: `log` settles immediately via `TokenEngine::purchase`, `mpesa` STK-pushes into the Centresidence rent account with a per-purchase callback), `settle()` (re-authorizes from ids alone, delegates to the idempotent engine).
  - `Tenant/UtilityTokenController` (index + purchase) + `resources/views/tenant/utilities/index.blade.php` (per-module top-up cards, wallet balance, rate; toastr flashes) + sidebar link guarded by `hasUtilities()` (only shows when the tenant actually has metered modules).
  - `Centresidence/TokenPurchaseCallbackController` — STK callback → settles the **Safaricom-confirmed** amount (not the requested one) into units, idempotent on the M-Pesa receipt. Routes: `tenant.utilities.index|purchase` + `centresidence.token.callback`.
  - **Reconciliation (the deferred C1-time TODO) — CLOSED:** (a) **no double charge** — `InfraBillPaymentService::infraOf` now nets out `metered_paid_total`, so metered infra the fallback already recovered from token revenue is not billed again in the merge/standalone charge; `markPaid` settles the remainder + clears fallback; (b) **cadence-aware fallback (option A)** — new `OwnerBillingStandingService::mayEnforceInfra()` (shared cadence predicate with the readonly gate); `CommissionFallbackService::activateOverdue` skips **active monthly** owners (their infra rides with the plan renewal — no mid-cycle token interception), still arms for expired-monthly / yearly / plan-less owners.
  - Tests: `TenantTokenPurchaseTest` (6 — discovery/authorization/IDOR/idempotency/log-settle-credits-both-wallets) + `TokenMergeReconciliationTest` (6 — double-charge netting + cadence gate). Sandbox stubs extended (`tenants.user_id`; `owner_packages.order_id/end_date/softDeletes`; new `subscription_orders`) so the cadence branch is now actually exercised (previously only verifiable on real data). **126/126 Centresidence green.**
- [ ] **C2 · Live ChirpStack adapter** — implement the `LiveChirpStackDriver` stub (gateway/device provisioning + telemetry ingest). *Gated on A3 + go-live infra.*

### D · Net-new scope (build ONLY the pilot vertical — A3)
- [ ] **D1 · Chosen vertical** facility/underwriting variant. Framework seams (`settlement_target`, partial financing, non-infra facility) are mostly in place.
- [ ] **D2 · Credit-file / data export to partner** — needed for owner-loan / apt-expansion / off-plan verticals.
- [ ] **D3 · Bank API / core-banking integration** — post-pilot (onboarding Step 2).

### E · Cleanups (fold into the Finance-OS commit)
- [ ] **E1 · `commission → infra` rename refactor** (queued).
- [ ] **E2 · Update `centresidence-feature-checklist.md` statuses** to match §1 (down-payment ✅, owner financing UI ✅, B2B driver ✅) so the registry stays honest.

---

## 3. Commit scope — what "finishing the Finance OS" means here
Not everything is finishable now: **A** (decisions) and **C2/D** (live infra + pilot verticals) are inherently roadmap. So the committable "internally-complete" Finance OS =

**Close B1, B2, C1 + cleanups E1–E2**, with **A** and **C2/D** captured as clearly-labeled roadmap in `centresidence-system-guide.md`.

Recommended build order: **B1 (remittance confirmation) → B2 (subscription infra) → C1 (live tokens)** — money-safety first (B1 is the one real hole: funds out with no reconciliation), then the billing gap, then the functional finish.

The Finance-OS commit carries: the `App\Centresidence` module + finance-partner portal + owner financing UI + these completions, **plus** `centresidence-system-guide.md`, `centresidence-feature-checklist.md`, and **this** map, **plus** a test-sequence doc (§4).

---

## 4. Test sequence (for the reviewing dev) — to be authored alongside the build
A walk a dev follows to exercise the Finance OS end-to-end. Skeleton (fill in as B1/B2/C1 land):
1. **Owner onboarding + mode** — create owner, set transaction vs subscription mode (`PaymentModeService`).
2. **Module catalogue → financing application** — browse modules, apply for a partner-financed module (`Owner/FinancingController`), partner approves in the portal.
3. **Facility + down-payment** — facility created on approval; down-payment STK collected (sandbox) → callback confirms.
4. **Rent settlement** — pay rent (transaction owner) → deduct-at-source split (fee + infra + facility), 60% cap.
5. **Subscription infra (B2)** — subscription owner's infra actually charged.
6. **Token purchase (C1)** — tenant buys utility units → wallet credited.
7. **Billing cycle** — run monthly billing; infra + commission invoices generated.
8. **Partner remittance (B1)** — remit a batch → B2B send → result callback reconciles the batch items.
9. **Collections / default / restructure** — overdue facility → penalty/default → restructure.
10. **Analytics snapshot** — partner dashboard reflects portfolio health.
