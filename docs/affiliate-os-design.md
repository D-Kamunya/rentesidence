# Affiliate OS — Cross-Project Design Note

> **One affiliate. Many products. One system.** The canonical spec for a shared affiliate/marketing platform across all of the user's verticals — **Centresidence, Solidus, Nexterra, Crylac** — so we run *one* commission-driven contractor network with *one* small team instead of four.
>
> This file lives in the **hub repo (Centresidence)**. Each spoke repo carries a tailored `affiliate-os-*` **memory** file with its role + the integration contract + the guardrail, so the right instructions auto-load wherever we're working.
>
> Status: **design agreed 2026-07-31** (build not yet started). Constraint that shapes every decision: **speed of implementation + lean resource use.**

---

## 1. Why (the lean-ops thesis)
Marketing across all four products is done by **independent contractors paid on commission**. Four separate affiliate programs = four dashboards, four payout pipelines, four suggestion engines, four teams to run them → the opposite of lean. Unify them: an affiliate **joins once, picks which products to work**, has **one wallet, one payout, one activity engine**. One team runs the network for every vertical.

## 2. Architecture — hub & spokes (don't greenfield)
Centresidence already has the most mature affiliate system (leads + lifecycle + commissions + suggestion engine + academy + leaderboard + marketplace + payouts). **Generalise that into the Affiliate OS — do not rebuild.**

- **Hub (the Affiliate OS)** owns, product-agnostically: affiliate **identity**, the **commission-event ledger**, one **wallet + payouts**, the **suggestion/activity engine**, **academy**, **leaderboard**, **marketing materials**, **KB**.
- **Spokes (each product)** own only: their **commission-rule strategy**, their **lead stages + nudge rules**, and **emitting events** to the OS. **A spoke never builds its own affiliate/commission/wallet/payout system.**

Two ways to physically host it — the real decision:
- **(A) Central service + thin clients** *(true unification — one account/wallet across all apps).* The OS is its own deployable with its own datastore; spokes call it via API/webhook. Leaner to *operate*, heavier to *stand up*.
- **(B) Shared package + SSO** *(faster to ship).* Extract the OS as a package each app installs; shared affiliate identity via SSO, but wallets/payouts stay per-app. Less lean operationally.
- **Recommendation:** aim for **(A)**, but reach it by **generalising the existing Centresidence system in place first** (it already runs), then extracting the service — never a from-scratch build.

## 3. The integration contract (what a spoke actually implements)
A spoke is "done" when it can:
1. **Attribute** — tell the OS "affiliate X owns lead/customer Y for product Z" (referral link / code / claim).
2. **Emit commission events** — on a real, collected money event, POST `{product, affiliate_id, external_ref, source, gross_amount, our_commission, currency, occurred_at}`. The OS applies that product's rule strategy and accrues.
3. **Emit activity events** (optional but recommended) — lead stage changes, so the suggestion engine can nudge.
4. **Declare its rules** — a small config the OS reads: commission source types, rate basis (**always a share of *our* take, like Centresidence rent 15%-of-1%**, never a % of gross), lead stages, and nudge rules.

Everything else (accrual, netting, wallet, withdrawal, suggestions, leaderboard, academy) is the OS's job. The spoke stays tiny.

## 4. Generic commission-event model
The abstraction that lets four different economies share one ledger:
```
CommissionEvent { product, affiliate, source, external_ref (idempotency),
                  gross_amount, base_amount, rate, commission_amount,
                  currency, cadence (one_time|recurring), occurred_at }
```
Per-product rule **strategies** compute `commission_amount` from the event — never hardcoded formulas. One-time (Nexterra bike) and recurring (SaaS/rent) both fit. Recurring windows (`RECURRING_COMMISSION_MONTHS`) carry over from Centresidence.

## 5. The suggestion / activity engine (the crown jewel — generalise it)
Centresidence's engine reasons over **lead stage × temperature × idle-hours × time-to-event** and fires prioritised, channel-specific next actions (caps per lead, dedupes, expires, daily email). Today its rules are **hardcoded for the property-sales motion.** Generalise so **each product supplies its own stages + nudge rules** into the *same* nudge/email/leaderboard machinery. One inactivity-killer for every vertical = the highest-leverage lean move.

## 6. Money invariants (do NOT rush this)
- Every commission event is idempotent on `external_ref` (no double-credit).
- Accrue only on **actually-collected** money (as Centresidence does).
- Wallet reconciles **per product** and **in aggregate**.
- **Multi-currency:** Nexterra is FX-exposed; the wallet must hold/settle per currency or convert at a recorded rate. Don't sum KES and USD naively.
- **Payout friction is the #1 activity killer** — design auto-approval under a threshold (with fraud checks); keep human approval only above it.

## 7. Sequencing (speed + lean)
1. **Centresidence:** generalise the existing system in place — add the `product` dimension, the commission-event ledger + strategy interface, generalise the suggestion engine. (No greenfield.)
2. **Solidus next** — SaaS/recurring, closest in shape; proves the abstraction with a *second* product.
3. **Crylac / Nexterra** after — their models diverge more (per-repair service; one-time FX-heavy cohort).
Do **not** attempt all four at once — that's the speed killer.

## 8. Per-repo guardrail (why the fifth system never gets built by hand)
Each spoke repo's memory carries: *"Do NOT build a native affiliate / commission / wallet / payout system here. This product is a **spoke** of the Affiliate OS — implement only the §3 contract (attribute, emit events, declare rules). Full spec: this file."* So the instruction is present the moment we open that repo.

## 9. Per-repo status
| Repo | Role | Memory note | Status |
|---|---|---|---|
| Centresidence (hub) | Hosts + is generalised into the OS | `affiliate-os-hub` | design agreed |
| Solidus | First spoke | `affiliate-os-integration` | design agreed |
| Nexterra | Later spoke (FX/one-time) | `affiliate-os-integration` | design agreed |
| Crylac | Later spoke (service) | *(add when repo/memory exists)* | noted here |
