# Centresidence Finance OS — Reviewer Test Sequence

> A guided walk for a dev reviewing the Finance OS. It exercises the module end-to-end,
> from owner onboarding to token purchase to partner remittance. Read alongside
> `centresidence-system-guide.md` (how it works) and `centresidence-completion-checklist.md`
> (what's built vs roadmap).
>
> **Everything here runs safely in dev** — all money rails are **driver-gated to `log`**
> by default (no real M-Pesa call is made; `log` settles/collects immediately in-process).
> You only need live M-Pesa credentials for the "live rail" notes, which are optional.

---

## 0. Prerequisites

```bash
# Migrate + seed the production catalogue (idempotent, demo-data-free).
php artisan migrate
php artisan db:seed --class=CentresidenceCatalogSeeder      # modules + cost components + token config
php artisan db:seed --class=KnowledgeBaseSeeder             # owner/affiliate/finance-partner KB

# OPTIONAL — realistic demo data (owner, tenant, property, modules, an overdue infra bill):
php artisan db:seed --class=CentresidenceDemoSeeder
```

Confirm the safe defaults in `config/centresidence.php` (or `.env`):

| Setting | Env | Default | Meaning |
|---|---|---|---|
| Collections driver | `CENTRESIDENCE_COLLECTION_DRIVER` | `log` | STK collections (down-payment, infra bill, tokens) settle immediately, no real STK |
| Payouts driver | `CENTRESIDENCE_PAYOUT_DRIVER` | `log` | Partner remittance records, no real B2B/B2C |
| ChirpStack driver | `CENTRESIDENCE_CHIRPSTACK_DRIVER` | `simulated` | Devices auto-activate; metering runs with no hardware |

> Leave all three on their defaults for this walk. Set them to `mpesa` / `live` only when
> validating the live rails with real credentials.

**Automated tests** (run these first — they prove the engines without a browser):

```bash
php artisan test tests/Feature/Centresidence tests/Unit/Centresidence   # 126 tests
```

---

## 1. Owner onboarding + pricing mode

The pricing mode is the spine (see system-guide §3). An owner's mode follows the **package**
they're on (`pricing_model`: `free` | `subscription` | `transaction`).

1. Sign in as an admin → **Packages** → confirm packages exist for each pricing model
   (the Add/Edit Package modals expose `pricing_model`, marketplace markup/discount, SMS credits).
2. Sign in as an owner → **My Subscription**. Note the current plan.
3. Switching mode is done by switching package. `PaymentModeService::syncModulesToOwnerMode`
   re-tags the owner's modules to match on every activation (central chokepoint in
   `setUserPackage`). An owner with an **active facility** cannot leave transaction mode —
   the guard lives in `Saas\SubscriptionController` (order/activateFree/cancel).

**Expected:** module deployment is gated to paid modes — a `free` owner attempting to deploy
or self-finance is redirected to upgrade (`hasModuleBillingRail`).

---

## 2. Module catalogue → financing application

1. As the owner → **Financing** (sidebar). Browse the module catalogue (`owner.financing.index`).
2. Open a module → **Apply** for partner financing (`owner.financing.apply`).
3. Sign in as the **finance partner** (role 6) → the partner portal → **Applications** →
   approve. A `FinanceFacility` is created on approval.
   - *Self-finance path:* instead of a partner, the owner funds it themselves
     (`owner.financing.self-finance`) → `markDeployed`.

**Expected:** approval creates a facility with a repayment schedule; admin sees it under
**Centresidence → Facilities**.

---

## 3. Facility + down-payment (STK collect)

1. On approval, if the facility carries a down-payment, `DownPaymentCollectionService` initiates
   an STK collect into the Centresidence rent account.
2. **Driver `log`:** the down-payment settles immediately. **Driver `mpesa`:** the owner's phone
   is prompted; the `centresidence.down-payment.callback` confirms it.

**Expected:** facility moves to active/collecting once the down-payment settles.

---

## 4. Rent settlement (transaction owner) — deduct-at-source

1. As a tenant of a **transaction-mode** owner, pay a rent invoice (the normal rent pay flow).
2. On confirmation, `setUserPackage`/`processRentCommission` credits the owner wallet net of the
   1% fee, then `RentSettlementService` deducts, in priority order, module-infra + facility
   repayment (capped so rent is never fully consumed).

**Expected:** the owner wallet reflects rent minus fee minus infra/facility deductions; a
`WalletTransaction` records the split.

---

## 5. Subscription-owner infra collection (B2 — the merge)

The gap this closed: a subscription owner's module-infra is now actually **collected**, merged
into their plan renewal.

1. As a **subscription, monthly** owner with deployed modules, let the plan lapse (or use the
   demo seeder's overdue infra bill). **My Subscription** shows a **plan + infra breakdown**
   and a **Total Due = plan + infra**.
2. Overdue infra puts the account in **readonly** (cadence-aware — a monthly owner is blocked
   only once the plan has lapsed; while active it's a gentle `infra_due` nudge). In readonly:
   - the dashboard banner turns amber/red with a direct-renew CTA + "choose another plan";
   - money-making writes are gated (`infra.standing` middleware): new invoice → the button
     becomes **"Settle to invoice"**, adding property/unit/tenant + new financing are blocked;
   - reads, operational writes, and the pay flow stay open.
3. **Renew** (or **Pay** the infra bill). Driver `log` settles immediately; the readonly gate
   lifts. The plan renewal **bundles** the infra (`PaymentSubscriptionController::placeOrder`
   adds `infra_amount`, KES-gated); `setUserPackage` settles it at the activation chokepoint.

**Expected:** one payment clears both plan + infra; the breakdown reconciles; readonly lifts.
The standalone infra "Pay" button is hidden for monthly owners (bundled at renewal) and shown
for yearly / plan-less owners.

---

## 6. Token purchase (C1 — live tenant front door)

1. As a **tenant** whose unit has a metered module → the dashboard shows a **"Buy prepaid utility
   tokens"** hint, and **Utilities** appears in the sidebar (both only when a metered module
   exists on the unit).
2. Open **Utilities** → a card per metered module (balance + rate). Enter an amount → **Buy tokens**.
3. **Driver `log`:** units are credited immediately. **Driver `mpesa`:** the tenant's phone is
   prompted; `centresidence.token.callback` credits the **Safaricom-confirmed** amount, idempotent
   on the receipt.

**Expected:** the tenant wallet balance rises by `amount × units_per_kes` (tenant always gets full
units); the owner's **net** token revenue (gross − embedded commission − any fallback) lands in
their owner wallet (`CreditOwnerWalletOnTokenPurchase`).

**Authorization to verify:** a tenant can only buy for a module on a property/unit they occupy —
the gate is enforced at both initiate and settle (`TokenPurchaseCollectionService::authorizedModule`).

**Fallback / merge reconciliation:** if the owner's infra is overdue *and* enforceable (yearly /
lapsed-monthly), a share of their token revenue is intercepted toward the overdue **metered** infra
(never the tenant's units). That recovered amount is netted out of the owner's infra bill (no
double charge). An **active monthly** owner's tokens are *not* intercepted (infra rides with renewal).

---

## 7. Billing cycle

```bash
php artisan centresidence:run-billing-cycle --month=YYYY-MM
```

**Expected:** a `CentresidenceCommissionInvoice` per subscription owner = plan + metered infra +
non-metered infra. Idempotent (`updateOrCreate` on owner+property+month) — safe to re-run.

---

## 8. Collections + metered fallback

```bash
php artisan centresidence:process-collections
```

**Expected:** facility collections run; overdue **infra** invoices arm the metered token-recovery
fallback (`activateOverdue`) — but only for **enforceable** owners (skips active monthly owners).
Reports "N infra fallback(s) armed". Inert until live tokens actually sell.

---

## 9. Partner remittance (B1 — B2B send + confirmation)

```bash
php artisan centresidence:remit-partners
```

1. A due batch is prepared and sent. **Driver `log`:** recorded, no transfer. **Driver `mpesa`:**
   `MpesaB2BService::send` pays the partner's settlement account (PayBill/BuyGoods/bank).
2. The async result hits `centresidence.remittance.callback` →
   `PartnerRemittanceService::confirmBatch`/`failBatch` moves SENT→CONFIRMED (receipt stored) or
   SENT→FAILED (retryable). Idempotent — only a SENT batch transitions.

**Expected:** the batch reconciles; admin **Centresidence → Revenue** reflects it.

---

## 10. Analytics snapshot

```bash
php artisan centresidence:snapshot-finance-analytics
```

**Expected:** the partner/admin dashboards reflect portfolio health (facilities, defaults, revenue).

---

## Roadmap (not exercised here — see completion-checklist §A/C2/D)

- **A1–A3** (business/legal): CBK custody of the float, servicing/origination fee model, pilot
  vertical pick — go-live blockers, not code.
- **Live ChirpStack adapter** (`LiveChirpStackDriver` is a stub) — real gateway/device provisioning
  + telemetry ingest + downlink dispatch.
- **M-Pesa callback authenticity** — all four Centresidence callbacks trust the payload; add
  source-IP allowlisting / out-of-band transaction-status verification before live money.
