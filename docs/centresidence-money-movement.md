# Centresidence — How the money actually moves (honest map + open decisions)

> Written 2026-06-20 while wiring the subscription bridge, before finalising fallback
> mechanics. This is the source of truth for the money model; it flags what is decided
> in code vs. what is still an open business decision.

## The three money streams

There are **three independent streams**, and only ONE of them branches by the owner's
pricing mode:

| Stream | Who pays | Where it routes | Branches by mode? |
|---|---|---|---|
| **Rent** | Tenant | Subscription mode → owner's own gateway. Transaction mode → **company account** (then deductions, then owner wallet). | **YES** — this is the only branch that matters |
| **Tokens / marketplace** | Tenant | **Always Centresidence** — the meter/marketplace collects, Centresidence carves commission, credits the owner. | **NO** — mode-independent |
| **Module / subscription invoice** | Owner | A monthly statement (plan + module component costs). | n/a |

**Key clarification (confirmed in code):** the **TokenEngine does not check the owner's
pricing mode**. Token money always flows through Centresidence: tenant pays → tenant always
gets full units → commission is carved from the owner's revenue → owner gets the net. The
*only* thing ever deducted from the token stream beyond commission is **fallback** (see below).
So token money does **not** branch on subscription vs transaction — your instinct is right.

## What each stream services

### Rent (transaction mode only — financing requires transaction mode)
On each rent payment, `RentSettlementService` → `DeductionEngine::plan()` splits gross rent in
this priority:
1. **Overdue metered-commission fallback** — capped at **50%** of the rent (`fallback_rent_cap_percentage`).
2. **Active facility repayments** — oldest first, capped at the most restrictive
   `max_rent_deduction_percentage`; within a facility: **penalty → interest → principal**;
   paused once the monthly target is met (unless accelerated).

The remainder credits the owner's wallet.

### Tokens (always Centresidence)
`TokenEngine::purchase`: tenant pays → gets full units → `commission = units × commission_per_unit`
→ `ownerGross = amount − commission`. If the owner has **overdue metered commission** and fallback
is active, a share of `ownerGross` is intercepted (capped at the outstanding metered balance).
`ownerNet = ownerGross − fallbackDeducted`. **Tenants never lose units; only owner revenue is touched.**

### Subscription / module invoice
`CommissionEngine` (monthly) builds a per-property `CentresidenceCommissionInvoice`:
`total = subscription_amount + metered_commission + non_metered_commission`. The subscription
bridge (now wired in `RunBillingCycleCommand`) fills `subscription_amount` from the owner's plan
price, once per owner.

## Fallback — the dual pathway (metered only)
`CommissionFallbackService`: when an owner's **metered** commission invoice goes overdue, fallback
activates and recovers the outstanding **metered** portion from **either** stream that flows —
token revenue (`applyToOwnerRevenue`) **or** rent (the first slice of `DeductionEngine`). Both are
**capped at the same `meteredOutstanding()` and row-locked**, so the two pathways can't double-recover.
**Non-metered** commission (locks, parking) is **never** token/rent-recovered — it stays on the
invoice as an ordinary debt.

---

## OPEN DECISIONS (business calls, not yet settled)

These are the genuine ambiguities — flagged honestly rather than silently coded.

### D1. Does a financed *metered* module's TOKEN revenue offset its facility?
**Today: NO.** A financed water meter is repaid from **rent** (at source); the tenant's token
purchases are the owner's **ongoing income** (minus commission), not facility repayment. So the
thing being financed (the meter) earns the owner money via tokens, while the loan is paid from rent.
- *Argument for keeping rent-only:* clean separation; rent is the reliable, predictable secured
  stream; token revenue is variable and partly the owner's reward for adopting the module.
- *Argument for tokens-offset-facility:* the financed asset "pays for itself" from its own usage,
  which is intuitive and de-risks the partner.
- **Recommendation:** keep **rent-secured repayment** as the primary, but consider an **optional
  product flag** ("repay partly from module token revenue") for partners who want the asset to
  self-amortise. Decide before go-live.

### D2. Priority + total cap of rent deductions (commission vs facility)
**Today:** commission fallback takes up to **50%** of rent, AND facilities take up to their cap of
the **full** rent — these **stack**, so an owner could see well over half their rent consumed in a
heavy month (e.g. 50% commission + 30% facility = 80%).
- **Decisions:** (a) Should Centresidence's commission recover **before** the partner's facility?
  (b) Should there be a **single combined ceiling** on total rent deductions (e.g. never deduct
  > 60% of a rent payment), rather than two independent caps that stack?
- **Recommendation:** add a **global combined cap** so the owner always keeps a predictable share of
  rent; decide the commission-vs-facility ordering explicitly (current = commission first).

### D3. How is *non-metered* module cost (locks) collected?
**Today:** invoiced on the commission invoice, but with **no automatic recovery** (not token, not
rent-fallback). It relies on the owner paying the invoice; otherwise it just ages as debt.
- **Decision:** for subscription owners, bundle it into the subscription charge (see D4). For
  transaction owners, decide whether non-metered cost may also be rent-recovered (currently it is not).

### D4. Subscription bridge — statement vs charge (collection semantics)
**Today:** the bridge populates `subscription_amount` so the commission invoice is a complete
**statement** (plan + modules). But the legacy SaaS **also** charges the subscription separately, and
nothing yet *collects* the commission invoice's subscription/non-metered portions. So the figure is
currently **informational**, not a second charge.
- **Decision:** pick ONE collector for the subscription portion — legacy SaaS billing **or**
  the Centresidence invoice — to avoid future double-billing. The display already shows the
  combined total correctly regardless.

### D5. `billing_model` is set by module metered-ness, not owner mode
A module's `billing_model` is `transaction` if metered, `subscription` if not — **independent of the
owner's pricing mode**. So a transaction-mode owner can still have a "subscription-billed" lock, and
the lock cost flows to the commission invoice. Confirm this is intended (it currently is), because it
shapes which costs appear where.

---

## Recommended sequence to resolve
1. **D2** (combined rent cap + ordering) — protects owner cashflow; highest tenant/owner-trust impact.
2. **D4** (one collector for subscription) — prevents double-billing before any live charging.
3. **D1** (token-offset-facility option) — product feature for partners.
4. **D3 / D5** (non-metered collection + billing_model semantics) — tidy-up.
