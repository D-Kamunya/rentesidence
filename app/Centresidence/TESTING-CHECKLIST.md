# Centresidence — Feature Testing Checklist (A → Z)

A run-through of everything built so far. Work top to bottom: **Phase 0–1 first** (if the
automated gate is red, stop and fix before manual testing). Tick `[x]` as you go.

Drivers are on safe defaults — **no real M-Pesa or hardware is touched**:
`payouts=log`, `collections=log`, `chirpstack=simulated`.

Test logins (from the demo seeder, all password `123456`):
- Owner: `owner@gmail.com`
- Admin: `admin@gmail.com`
- Finance partner (role 6): `partner@centresidence.test`

---

## Phase 0 — Pre-flight setup
- [ ] `php artisan migrate:status` ends at `…_000042_add_down_payment_collection … Ran`
- [ ] `php artisan optimize:clear` (routes/config fresh)
- [ ] `php artisan storage:link` exists (needed for module image uploads)
- [ ] Demo data seeded (modules, owner, admin, partner visible)
- [ ] Confirm `.env`: `CENTRESIDENCE_PAYOUT_DRIVER=log`, `CENTRESIDENCE_COLLECTION_DRIVER=log`, `CENTRESIDENCE_CHIRPSTACK_DRIVER=simulated` (or unset = defaults)

## Phase 1 — Automated gate (run before manual testing)
- [ ] `vendor/bin/phpunit tests/Feature/Centresidence tests/Unit/Centresidence` → all green (75+)
- [ ] `php artisan centresidence:simulate` → "All simulation success criteria PASSED — gate is GREEN"

---

## Phase 2 — Auth & roles
- [ ] Owner login lands on owner dashboard
- [ ] Admin login can reach Admin → Centresidence
- [ ] Partner login (role 6) lands on the finance-partner portal, NOT the owner/admin area
- [ ] A deactivated owner is redirected to the account-deactivated page (existing feature)

## Phase 3 — Owner: payment mode & financing gate
- [ ] Owner dashboard shows the "check out modules / financing" notification/banner
- [ ] Owner NOT on transaction mode → opening an Apply page shows the **switch-to-transaction** prompt (not the form)
- [ ] Switching to transaction mode then returns to the apply form
- [ ] **Mode lock:** as an owner WITH an active facility, try to:
  - [ ] Activate a free plan → blocked with the facility-lock message
  - [ ] Start a paid (subscription) plan checkout → blocked
  - [ ] Cancel the subscription → blocked
- [ ] Owner with NO active facility can still switch/cancel normally

## Phase 4 — Owner: financing marketplace (education)
- [ ] Financing index shows color-coded module cards (icon, accent, tagline, financier count)
- [ ] Clicking a card opens the module detail: what it is, "how it grows cashflow", "how it works" steps, benefits, cost card
- [ ] Detail shows the **installer note** for infrastructure modules ("supplied & installed by Centresidence")
- [ ] Financiers are listed; if none, the self-finance CTA shows instead
- [ ] Naming never collides with the existing product "marketplace"/"My Shop"

## Phase 5 — Owner: apply for financing (the dense one)
- [ ] Property dropdown shows each property's unit count, e.g. "Riverside (24 units)"
- [ ] **All units** button fills quantity with the property's unit count
- [ ] Typing a quantity above the unit count snaps back to the max
- [ ] Selecting a **0-unit property** → "Add units first" warning + link to that property + submit disabled
- [ ] Live cost breakdown updates as you type: hardware, installation, platform fee, total deployment cost, − down-payment, financed by partner, est. monthly
- [ ] The displayed financed total & fee **match** what's stored after submit (no drift)
- [ ] **Partial financing:** enter a down-payment (and try the **Half** button) → financed amount + monthly drop accordingly
- [ ] Down-payment ≥ total → "use self-finance instead" message, submit blocked
- [ ] Financed amount above the financier's **max** → blocked (client + server)
- [ ] Financed amount below the financier's **min** → blocked
- [ ] Submit → soft eligibility runs → application appears in "My applications" as submitted
- [ ] Installation is included in the financed principal (cross-check the number on the partner side)

## Phase 6 — Owner: self-finance
- [ ] Self-finance page has the same unit selection (all-units, cap, 0-unit block + "Add units first")
- [ ] Cost breakdown (hardware, installation, total) is correct
- [ ] Create order → appears under "My self-financed" / admin self-financed list

## Phase 7 — Owner: my facilities (repayment controls)
- [ ] My applications list shows correct statuses
- [ ] My facilities show outstanding principal, monthly, payoff-today
- [ ] **Down-payment badge** shows: nothing for full-finance, "paid"/"pending" for partial
- [ ] Toggle **accelerated repayment** on/off → reflects
- [ ] **Settle early** → shows a payoff quote and completes (reducing-balance saves future interest; flat does not)

## Phase 8 — Finance partner portal (role 6)
- [ ] Dashboard shows the partner's pipeline/metrics
- [ ] **Products:** create a product (choose module, set interest rate + type, tenor min/max, amount min/max, deduction %) and edit it
- [ ] Applications list shows incoming applications
- [ ] Application detail shows: total deployment cost, owner down-payment (if any), "you finance" amount, est. monthly
- [ ] Approve → approved amount **defaults to the financed amount** (not the gross)
- [ ] Approving auto-creates the **facility + repayment schedule** (check admin Facilities)
- [ ] Reject with a reason → owner sees it

## Phase 9 — Admin: Centresidence visibility & module authoring
- [ ] Overview metrics populate (active facilities, outstanding, pending applications, commission, devices, gateways…)
- [ ] Partners / Applications / Facilities / Defaults / Revenue tabs all load
- [ ] Facilities tab shows the **Down-pmt** column and a **Deploy** button per row
- [ ] **Modules & Costs:** each module shows cost components; **Edit copy** opens the editor
- [ ] In the editor: change name/tagline/accent colour/icon, **upload an image**, edit benefits & "how it works", set **Facility paid to** (settlement target), set hardware/installation price → save
- [ ] The **live card preview** updates as you type (name/tagline/icon/colour)
- [ ] Saved copy/image/accent reflect on the **owner** marketplace + detail pages
- [ ] Self-financed tab shows orders + a **Deploy** button (non-deployed only)

## Phase 10 — Admin: deployment / devices (ChirpStack = simulated)
- [ ] From a **funded facility**, click **Deploy** → form prefilled with property + module
- [ ] Deploy form respects unit cap + 0-unit block (same as owner side)
- [ ] Deploy a **metered** module (e.g. water) qty N →
  - [ ] redirected to Devices list with N devices
  - [ ] devices are **active** and tagged `· sim`
  - [ ] a **Gateway** was created; Infrastructure tab shows a topology allocation for that property
- [ ] Deploy a **non-metered** module (e.g. lock) → devices created, **no gateway**, gateway column "—"
- [ ] Devices list: edit a device's **DevEUI** and name → save → persists (DevEUIs are editable placeholders by default)
- [ ] Re-deploy the same module with a higher quantity → **tops up** (no duplicates), gateway reused
- [ ] From a **self-financed order**, Deploy → devices created AND the order flips to **deployed**

## Phase 11 — Money flow: facility lifecycle & down-payment
- [ ] Disbursing a facility records a **disbursement** ledger transaction
- [ ] **Down-payment collection** on disbursement (log driver): a facility with a contribution → status `collected`, a `down_payment` ledger entry exists; a full-finance facility → `not_required`
- [ ] Owner "my facilities" reflects the down-payment status after disbursement
- [ ] (Conceptual) collection is payable to Centresidence as installer; partner finances the rest

## Phase 12 — Metered billing chain (needs Phase 10 devices) — the big integration
- [ ] `php artisan centresidence:run-billing-cycle` runs without error
- [ ] An **infrastructure cost invoice** is generated for the owner with deployed metered devices
- [ ] A **commission invoice** (metered + non-metered split) is generated
- [ ] A tenant **token purchase** (buy units) splits correctly: tenant gets full units, Centresidence commission, owner revenue
- [ ] Token purchase is **idempotent** (same payment reference twice → one credit)
- [ ] **Fallback** (overdue commission): metered commission recovered from token-purchase owner revenue / rent, capped; **locks (non-metered) untouched**; tenant still gets full units
- [ ] Partial-month proration: a module activated mid-month bills pro-rata (full month if activated_at is null)

## Phase 13 — Settlement / remittance to partners
- [ ] `php artisan centresidence:remit-partners` runs (log driver = records, no real transfer)
- [ ] A settlement/remittance record is created with status `sent` (log) and a reference
- [ ] Cadence honoured: daily-settlement partners every day; others on their `settlement_day`

## Phase 14 — Scheduled commands (smoke)
- [ ] `centresidence:run-billing-cycle` — OK
- [ ] `centresidence:process-collections` — OK
- [ ] `centresidence:remit-partners` — OK
- [ ] `centresidence:snapshot-finance-analytics` — OK
- [ ] `centresidence:simulate` — GREEN

## Phase 15 — Cross-cutting guards & negative tests
- [ ] Money never shows float drift (amounts always 2dp, totals reconcile)
- [ ] Topology allocation can't exceed 100% on a gateway (deploy many properties to one gateway and confirm the invariant holds)
- [ ] Admin Centresidence pages render a friendly "not migrated" notice if a table is missing (don't error)
- [ ] A crafted POST can't beat the server guards: quantity > units, financed > max, leaving transaction mode with an active facility
- [ ] The **live rental product is unaffected** (rent, invoices, tenants, existing marketplace, subscriptions all behave as before)

---

## Go-live toggles — DO NOT flip during testing (note only)
These stay on safe defaults until M-Pesa creds + ChirpStack are verified:
- [ ] `CENTRESIDENCE_PAYOUT_DRIVER=mpesa` (+ payout confirmation callback) — go-live
- [ ] `CENTRESIDENCE_COLLECTION_DRIVER=mpesa` (down-payment STK) — go-live
- [ ] `CENTRESIDENCE_CHIRPSTACK_DRIVER=live` (real device registration + uplink webhook + downlink) — go-live

## When you find something
Note: page/flow, login used, what you did, what you expected, what happened. Group fixes so
they land against a stable tree rather than one-off mid-test. The financing engines are covered
by the test suite, so re-run Phase 1 after any change to catch regressions.
