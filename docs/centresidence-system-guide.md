# Centresidence — System Guide

> **A living reference for developers.** How the "Infrastructure & Finance OS" actually works, end to end. Read this to get up to speed — or back up to speed after time away — without spelunking the code.
>
> **Status:** living document. Last updated **2026-07-22**. Sections marked _(TBD)_ are pending the ongoing piece-by-piece systems review. Fixes for the findings below are being **decided as a batch** once the full picture is mapped — this doc is the map.

---

## 1. What Centresidence is

- **Rentesidence** is the legacy property-management SaaS: owners, tenants, properties, units, rent, invoices, subscriptions.
- **Centresidence** is a decoupled module (`app/Centresidence`) layered on top that turns it into an **Infrastructure & Finance OS**:
  - **Smart utility metering** — water / gas meters and smart locks over LoRaWAN, sold as prepaid **tokens** to tenants.
  - **Infrastructure financing** — owners finance that hardware through third-party **finance partners** (or self-fund it), repaid from rent.
  - **Billing & settlement** — the engines that compute what everyone owes and move the money.

**Design rule #1 (non-negotiable):** the module must **never destabilise the live product**. It integrates through events, driver-gated seams, and `Schema::hasTable()` guards; it reads legacy tables read-mostly and never alters them.

---

## 2. Architecture & key concepts

| Concept | What it means |
|---|---|
| **Module location** | `app/Centresidence/` — Services, Models, Events, Listeners, Console commands, `database/migrations`, and a `Simulation/` sandbox. |
| **`Money` value object** | Integer minor units + bcmath. **Never use floats for money.** Has `cappedAt`, `percentage`, `minus/plus`, `monthlyRepayment`, etc. |
| **Driver-gated integrations** | `payouts.driver` (`log`\|`mpesa`), `collections.driver`, `chirpstack.driver` (`simulated`\|`live`). Safe defaults now; flip at go-live. |
| **Acceptance gate** | `php artisan centresidence:simulate` + ~92 PHPUnit tests against an **in-memory sqlite sandbox**. The suite stays green and the count is reported on every change. |

**The engines (one line each):**
- `CommissionEngine` — subscription owners' module infra → `CentresidenceCommissionInvoice` (plan + modules).
- `InfrastructureCostEngine` — transaction owners' module infra → `OwnerInfrastructureInvoice` (recovered from rent).
- `TokenEngine` — tenant utility purchases; carves gas commission; credits owner wallet net.
- `DeductionEngine` + `RentSettlementService` — split a rent payment across fee / infra / facility within the cap.
- `CommissionFallbackService` — recover overdue **metered** infra from token revenue (owner's share only).
- `FacilityCollectionsService` — mark overdue facility repayments, accrue penalties, escalate defaults.
- `BillingCycleService` — the monthly run that generates the invoices above.

---

## 3. The spine: pricing modes

The single most important concept. `owner_packages.pricing_model ∈ {free, subscription, transaction}`. **Mode is authoritative** — it decides how rent routes, how module infra is billed, and how (and whether) money flows through Centresidence.

| Mode | Rent routes to | Pays us via | Can deploy modules? | Enables financing? |
|---|---|---|---|---|
| **free** | owner's own account | nothing | ❌ **gated** — no billing rail | ❌ |
| **subscription** | owner's own account | monthly plan fee | ✅ (infra billed on the plan side) | ❌ |
| **transaction** | **Centresidence account** | 1% per rent payment | ✅ (infra recovered from rent) | ✅ (deduct-at-source) |

**Invariants:**
- Mode changes re-tag every one of the owner's modules' `billing_model` to match — wired at **both** entry points: `PaymentModeService::switchTo` (financing button) and `setUserPackage()` (the universal package-activation chokepoint). See `syncModulesToOwnerMode()`.
- Free owners are **blocked from deploying modules** at the chokepoint (`DeviceProvisioningService::deploy` → `ModuleDeploymentRequiresPaidPlanException`), because a deployed module carries a recurring cost that free has no rail to collect.
- An owner **cannot leave transaction mode while a facility is active** (`assertCanSwitchTo`).

---

## 4. Subscription flow (detailed)

**Frame:** in subscription mode Centresidence is meant to be **pure software**. The owner pays a plan fee; their **rent goes to their own account** (we're never in the path — the CBK-clean property, for rent); we bill them the platform + gateway cost of any smart modules they run.

**Worked example** (real seeded rates, one property):

| Item | Calc | KES/mo |
|---|---|---|
| Plan "Growth" (illustrative) | — | 1,500 |
| Water meter · Platform Software | 4 × 50 | 200 |
| Water meter · Gateway usage | 4 × 50 | 200 |
| Gas meter · Platform Software | 1 × 60 | 60 |
| Gas meter · Gateway usage | 1 × 40 | 40 |
| **Module infra subtotal** | | **500** |
| **Displayed monthly total** | 1,500 + 500 | **2,000** |

**Steps:**
1. **Acquire plan** → `setUserPackage` writes `owner_packages(pricing_model='subscription')` + a `SubscriptionOrder` for the plan price (M-Pesa). *This is the only money we actually collect from them today.*
2. **Deploy modules** → `DeviceProvisioningService::deploy`; paying-mode gate passes; `billing_model` stamped **subscription**.
3. **Rent** → tenant checkout shows the **owner's own M-Pesa**; money goes straight to them. No 1%. ✅ CBK-clean.
4. **Tokens** → `TokenEngine` carves gas commission, credits the owner's wallet with the net. ⚠️ Engine-level only; the live token payment endpoint isn't wired yet, and the design routes token money **through Centresidence** regardless of mode (see §6).
5. **Monthly billing cycle** (scheduled 1st @ 02:00, in `CentresidenceServiceProvider`) → `CommissionEngine` writes a `CentresidenceCommissionInvoice`: `1,500 + 500 = 2,000`.
6. **Display** → the mode-aware My Subscription card shows plan + modules = 2,000.
7. **Collection** → **⚠️ see Finding #1.**

> ### 🔴 Finding #1 — subscription module infra is *billed but not collected*
> The 1,500 plan is collected via the `SubscriptionOrder`. The **500 module infra is not**:
> - No owner-facing payment of the CommissionInvoice exists (admin only *views* it).
> - Subscription renewal charges the **plan price only** — no module-cost logic in the subscription services.
> - The metered safety-net is **unwired in production**: `CommissionFallbackService::activateOverdue` runs **only in the simulation**, never from a scheduled command (`process-collections` does facility collections only). `TokenEngine` *does* apply an active fallback — but nothing ever activates one in prod.
> - Non-metered infra has no collection at all.
>
> **Likely fix (batch):** make the recurring charge = plan + that month's module infra (the CommissionInvoice *is* what the owner pays), and wire `activateOverdue` into `process-collections`. **Open Q:** cadence — monthly infra vs a possibly-yearly plan.

---

## 5. Transaction flow (detailed)

**Frame:** transaction mode exists for one reason — to make **financing repayments collectable at source**. Rent flows **through Centresidence**, so we can take the 1% fee, recover infra, and deduct facility repayments before the owner sees the money. This is powerful — and it's where the **CBK money-holding question** lives.

**Worked example** (transaction owner, one property):

| Step | Amount (KES) | Where it goes |
|---|---|---|
| Tenant pays rent | 50,000 | → **Centresidence M-Pesa account** (`centresidence_rent_mpesa_account_id`) — we now hold it |
| `processRentCommission` — 1% fee | −500 | our revenue; **49,500** credited to the owner's `OwnerWallet` (an IOU) |
| `RentSettlementService` — infra recovery | −400 | our revenue (water infra, this owner is transaction-billed) |
| `RentSettlementService` — facility repayment | −8,000 | held for the finance partner (remitted later) |
| **Net left in owner's wallet** | **41,100** | Centresidence-held IOU balance |

- **The 60% cap** governs the settlement deductions (infra + facility + overdue recovery), not the 1% fee, which is always taken. Here 8,400 / 50,000 = 16.8%, well under cap, so everything is deducted in full.
- **Order of operations:** `processRentCommission` credits the wallet with the 99% net **first**; then the WP8 hook `RentSettlementService::handleRentPayment` runs on the **gross** rent and **decrements** the wallet by the deductions. The two are independent and both idempotent.
- **Owner cash-out:** `OwnerWalletController::withdraw` → pending `WithdrawalRequest` → **admin approves** → M-Pesa **B2C** payout.

> ### 🔴 Finding #2 — CBK money-holding exposure
> Centresidence **receives and holds** tenant rent in its own account, maintains **owner wallet balances** (liabilities), and pays owners out on **withdrawal request**. Holding third-party funds + issuing withdrawable balances is structurally close to **deposit-taking / e-money issuance / payment-service** activity — all CBK-licensed.
> - The **1% fee itself is fine** (a service fee is a service fee). The trigger is **holding the float + the drawn-down wallet**.
> - The **token** design does the same for **all** owners (token gross → us → owner wallet net), so this isn't transaction-only — though the live token payment endpoint isn't wired yet.
> - **Not legal advice** — needs a Kenyan fintech/regulatory lawyer. Structural options on the table: instant settlement (no stored balance), a licensed-PSP/bank as the account holder, the installer/software-only positioning, or (conservative interim) **suspend transaction mode and run subscription-only** — remembering that suspending transaction also pauses **partner-financed** repayment collection.

---

## 6. Tokens & utility metering (detailed)

**Frame:** tenants **prepay** for utility units (litres of water, units of gas). The **tenant always receives the full units** — Centresidence's cut (commission) is carved out of the *owner's* revenue, never added to the tenant's price. Per the money-model pivot, **commission is gas-only**; water/other modules carry no per-token commission (Centresidence earns on those via the separately-billed infra cost, not the token).

**How a purchase splits** (`TokenEngine::purchase`):
```
units       = amount × units_per_kes            # tenant always gets these in full
commission  = units × commission_per_unit       # gas: > 0 ; water: 0
ownerGross  = amount − commission
fallback    = intercept(ownerGross)              # overdue metered infra (see caveat)
ownerNet    = ownerGross − fallback              # → credited to owner's OwnerWallet
```

**Worked examples** (illustrative):

| | Water (no commission) | Gas (commission) |
|---|---|---|
| Tenant pays | KES 500 | KES 1,000 |
| Units received | 2,500 L (×5) | 500 units (×0.5) |
| Commission (our cut) | 0 | 150 (500 × 0.3) |
| Owner gross | 500 | 850 |
| **Owner wallet credited** | **500 (full)** | **850** |
| **Centresidence keeps** | **0** (earns via infra cost) | **150** (gas revenue) |

**What else happens on purchase:**
- The tenant's prepaid units go to a **`UtilityWallet`** (per tenant × module); `owner_revenue_net` is credited to the owner's **`OwnerWallet`** (via the `CreditOwnerWalletOnTokenPurchase` listener).
- A downlink **`DeviceCommand`** (`credit_tokens`) is queued for an active meter so it dispenses the units.
- Consumption flows back via `recordConsumption` (device telemetry → debit units from the wallet).

**Money routing (CBK-relevant):** the tenant's payment is designed to land with **Centresidence**, which keeps the commission and credits the owner's wallet with the net. **This happens for _all_ owners, regardless of pricing mode** — so a *subscription* owner's rent is clean, but their *token* money still flows through us into a withdrawable wallet. This is the same holding pattern as transaction rent (see Finding #2), extended to everyone.

> ### 🔴 Finding #3 — there is no live token-purchase path
> `TokenEngine::purchase` has **no caller outside tests and the simulation** — no HTTP controller, no route, no tenant "buy units" UI, no M-Pesa STK trigger. The engine, the owner-wallet credit listener, and the fallback application are all built and green in simulation, but a tenant **cannot actually buy a token today**. The core utility-metering revenue stream is unwired end-to-end, and also depends on the **live ChirpStack adapter** (a stub) to dispatch the downlink and read consumption.
>
> **Compounding effect:** this means the one collection mechanism for a subscription owner's *metered* infra — token-revenue interception via the fallback — is **triply inert** in production: (a) no live token purchases, (b) `activateOverdue` never runs (Finding #1), (c) ChirpStack downlink/telemetry not live. Reinforces Finding #1.

## 7. Financing lifecycle _(TBD)_

Sketch: application (draft → submit → underwrite → approve) → facility (schedule, interest) → disbursement (down-payment collection) → collection (schedule-based default detection) → partner remittance. Feasibility gate ensures the cap can't doom a facility.

## 8. Affiliates & commissions (detailed)

Affiliates refer owners and earn a share of what those owners generate. **Three earning sources**, all wired, all accruing on *actually-collected* money (`AffiliateCommissionService`):

| Source | Fires when | Rate | Base | A true cut of our take? |
|---|---|---|---|---|
| **Subscription** | referred owner pays subscription | `FIRST_TIME` / `RECURRING_COMMISSION_RATE` (admin-set `getOption`) | subscription amount | it *is* our revenue — bounded if rate < 100% |
| **Rent** | referred **transaction** owner's rent paid | 0.15% (15% of the 1%) | gross rent | ✅ yes — a real cut of our 1% |
| **Marketplace** | referred owner's product sale | `product_category.affiliate_commission` | **gross sale** | ❌ **no** — independent of our commission |

- All three are valid only for `RECURRING_COMMISSION_MONTHS` from the affiliate's first commission for that owner (the referral window).
- **Rent earnings accrue only for TRANSACTION owners** (the 1% is transaction-only) — so suspending transaction mode also cuts affiliates' rent stream.
- Earnings are `AffiliateCommission` rows; period rollups live in `AffiliateCommissionPayment`.

**Withdrawal:** affiliate requests → `AffiliateWithdrawal` (pending) → **admin approves** → M-Pesa **B2C**. Available balance = lifetime earnings (per-period payout summaries) − approved withdrawals. This is a revenue-**share payable** (our own money we're sharing), so a lower CBK concern than the owner wallet — but still a payout obligation that must reconcile.

**The commerce/marketplace flow (feeds both owner and affiliate):** a buyer pays for a product → money routes to the **Centresidence M-Pesa account** (`centresidence_mpesa_account_id`) → we take our **plan-based** commission (`processOrderCommission`, rate from `commission_markup`/`discount`) → credit the **owner's wallet** with the net → the affiliate gets their category-rate cut.

> ### ✅ Finding #4 — FIXED (2026-07-22): marketplace cut is now a share of OUR commission
> The affiliate cut is now `AffiliateCommissionService::scopedMarketplaceCommission(ourCommission, rate)` = `rate% × our commission on the sale` (the caller `processOrderCommission` passes its computed commission). So it mirrors rent ("15% of our 1%") and **can never exceed what we earned**. `product_categories.affiliate_commission` is now read as a **% of our commission** (not of gross) — ⚠️ existing category values were tuned for the old %-of-gross meaning and should be **re-tuned** (e.g. 15 to match rent). Covered by `tests/Unit/AffiliateCommissionScopingTest.php`.

> ### 🟡 Finding #5 — the affiliate withdrawable-balance math is fragile; verify the invariant
> `getLifetimeEarningsMinusWithdrawals` = sum of `MAX(id)`-per-period `AffiliateCommissionPayment.total_commission_payout` − approved withdrawals. It relies on `recalculatePeriodSummary` keeping exactly one authoritative cumulative payout row per period. A bug here **mis-pays affiliates directly** — verify the invariant and add a test. Also: no clawback if an underlying payment is later refunded/reversed.

> ### 🔴 Finding #2 (EXPANDED) — the money-holding is broader than transaction rent
> The `OwnerWallet` (a withdrawable IOU) is fed by **three** streams: transaction rent net (**live**), **marketplace product-sale net (LIVE)**, and token net (latent). Marketplace product payments already route to the Centresidence account today. So **even a subscription or free owner has money held by Centresidence** via marketplace sales — **suspending transaction mode does NOT stop us holding customer funds.** This reshapes the CBK question: the exposure is the *wallet-and-hold pattern across the platform*, not just transaction rent.

## 9. Billing cycle, collections & remittance _(partly covered)_

Scheduled in `CentresidenceServiceProvider` (not the legacy Kernel): `run-billing-cycle` (monthly), `process-collections` (daily, **facility only** — see Finding #1), `snapshot-finance-analytics` (daily), `remit-partners` (daily). Driver-gated M-Pesa payout adapter (B2C/B2B) is built; confirmation callback is a go-live item.

---

## 10. Known gaps & findings log

| # | Area | Finding | Status |
|---|---|---|---|
| 1 | Subscription | Module infra billed but **not collected** (no primary charge; fallback unwired in prod) | Logged — fix in batch |
| 2 | Transaction / tokens | **CBK money-holding** exposure (holds rent + withdrawable owner wallets) | Logged — decision pending, needs counsel |
| 3 | Tokens | **No live token-purchase path** (engine only; no route/UI/STK); depends on live ChirpStack. Makes the metered-infra fallback triply inert. | Logged — go-live gap |
| 4 | Affiliates | Marketplace affiliate cut is **off gross sale**, not bounded by our commission → can lose money on a sale | ✅ **Fixed** 2026-07-22 (scoped to our commission + tests) |
| 5 | Affiliates | Withdrawable-balance derivation is **fragile** (per-period `MAX(id)` summaries); a bug mis-pays affiliates; no refund clawback | Verified correct-sequential; refactor deferred (see below) |
| 2b | Platform-wide | Money-holding is **broader than transaction rent** — owner wallet also fed by **live marketplace sales** (+ latent tokens); subscription-only does NOT eliminate the hold | Logged — reshapes CBK decision |

_(Findings from later review passes append here.)_

## 11. Go-live checklist _(TBD)_

Flip drivers to live (M-Pesa payouts + collections, ChirpStack); wire the live ChirpStack adapter (currently a stub); partner-payout confirmation callback; resolve the CBK question; the `commission → infra` naming refactor.
